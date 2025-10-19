<?php
/**
 * Grant Single Page - Mobile Optimized Design v13.3
 * 助成金詳細ページ - モバイル最適化デザイン
 * 
 * @package Grant_Insight_Perfect
 * @version 13.3.0-mobile-optimized
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!have_posts()) {
    wp_redirect(home_url('/404'), 302);
    exit;
}

get_header();
the_post();

$post_id = get_the_ID();

// SEO用メタデータ
$seo_title = get_the_title();
$seo_description = '';
$canonical_url = get_permalink($post_id);

if (function_exists('get_field')) {
    $ai_summary = get_field('ai_summary', $post_id);
    if ($ai_summary) {
        $seo_description = wp_trim_words(strip_tags($ai_summary), 25, '...');
    }
}

if (empty($seo_description)) {
    $content = get_the_content();
    if ($content) {
        $seo_description = wp_trim_words(strip_tags($content), 25, '...');
    }
}

// ACFデータ取得
$grant_data = array(
    'organization' => function_exists('get_field') ? get_field('organization', $post_id) : '',
    'max_amount' => function_exists('get_field') ? get_field('max_amount', $post_id) : '',
    'max_amount_numeric' => function_exists('get_field') ? intval(get_field('max_amount_numeric', $post_id)) : 0,
    'subsidy_rate' => function_exists('get_field') ? get_field('subsidy_rate', $post_id) : '',
    'deadline' => function_exists('get_field') ? get_field('deadline', $post_id) : '',
    'deadline_date' => function_exists('get_field') ? get_field('deadline_date', $post_id) : '',
    'grant_target' => function_exists('get_field') ? get_field('grant_target', $post_id) : '',
    'contact_info' => function_exists('get_field') ? get_field('contact_info', $post_id) : '',
    'official_url' => function_exists('get_field') ? get_field('official_url', $post_id) : '',
    'application_status' => function_exists('get_field') ? get_field('application_status', $post_id) : 'open',
    'required_documents' => function_exists('get_field') ? get_field('required_documents', $post_id) : '',
    'adoption_rate' => function_exists('get_field') ? floatval(get_field('adoption_rate', $post_id)) : 0,
    'grant_difficulty' => function_exists('get_field') ? get_field('grant_difficulty', $post_id) : 'normal',
    'is_featured' => function_exists('get_field') ? get_field('is_featured', $post_id) : false,
    'views_count' => function_exists('get_field') ? intval(get_field('views_count', $post_id)) : 0,
    'ai_summary' => function_exists('get_field') ? get_field('ai_summary', $post_id) : '',
);

// タクソノミー取得
$taxonomies = array(
    'categories' => wp_get_post_terms($post_id, 'grant_category'),
    'prefectures' => wp_get_post_terms($post_id, 'grant_prefecture'),
    'tags' => wp_get_post_tags($post_id),
);

foreach ($taxonomies as $key => $terms) {
    if (is_wp_error($terms) || empty($terms)) {
        $taxonomies[$key] = array();
    }
}

// 金額フォーマット
$formatted_amount = '';
$max_amount_yen = intval($grant_data['max_amount_numeric']);

if ($max_amount_yen > 0) {
    if ($max_amount_yen >= 100000000) {
        $formatted_amount = number_format($max_amount_yen / 100000000, 1) . '億円';
    } elseif ($max_amount_yen >= 10000) {
        $formatted_amount = number_format($max_amount_yen / 10000) . '万円';
    } else {
        $formatted_amount = number_format($max_amount_yen) . '円';
    }
} elseif (!empty($grant_data['max_amount'])) {
    $formatted_amount = $grant_data['max_amount'];
}

// 締切日計算
$deadline_info = '';
$deadline_class = '';
$days_remaining = 0;

if (!empty($grant_data['deadline_date'])) {
    $deadline_timestamp = strtotime($grant_data['deadline_date']);
    if ($deadline_timestamp && $deadline_timestamp > 0) {
        $deadline_info = date('Y/n/j', $deadline_timestamp);
        $current_time = current_time('timestamp');
        $days_remaining = ceil(($deadline_timestamp - $current_time) / 86400);
        
        if ($days_remaining <= 0) {
            $deadline_class = 'closed';
            $deadline_info .= ' (終了)';
        } elseif ($days_remaining <= 7) {
            $deadline_class = 'urgent';
            $deadline_info .= ' (残' . $days_remaining . '日)';
        } elseif ($days_remaining <= 30) {
            $deadline_class = 'warning';
        }
    }
} elseif (!empty($grant_data['deadline'])) {
    $deadline_info = $grant_data['deadline'];
}

// 難易度設定
$difficulty_configs = array(
    'easy' => array('label' => '易', 'dots' => 1),
    'normal' => array('label' => '中', 'dots' => 2),
    'hard' => array('label' => '難', 'dots' => 3),
);

$difficulty = !empty($grant_data['grant_difficulty']) ? $grant_data['grant_difficulty'] : 'normal';
$difficulty_data = isset($difficulty_configs[$difficulty]) ? $difficulty_configs[$difficulty] : $difficulty_configs['normal'];

// ステータス
$status_configs = array(
    'open' => array('label' => '募集中', 'class' => 'open'),
    'closed' => array('label' => '終了', 'class' => 'closed'),
);

$application_status = !empty($grant_data['application_status']) ? $grant_data['application_status'] : 'open';
$status_data = isset($status_configs[$application_status]) ? $status_configs[$application_status] : $status_configs['open'];

// 閲覧数更新
$current_views = intval($grant_data['views_count']);
$new_views = $current_views + 1;
if (function_exists('update_post_meta')) {
    update_post_meta($post_id, 'views_count', $new_views);
    $grant_data['views_count'] = $new_views;
}

// SEO: OGP画像取得
$og_image = '';
if (has_post_thumbnail($post_id)) {
    $og_image = get_the_post_thumbnail_url($post_id, 'large');
} else {
    $og_image = get_site_icon_url(512);
    if (empty($og_image)) {
        $og_image = home_url('/wp-content/uploads/default-og-image.jpg');
    }
}

// SEO: キーワード生成
$keywords = array();
if (!empty($taxonomies['categories'])) {
    foreach ($taxonomies['categories'] as $cat) {
        $keywords[] = $cat->name;
    }
}
if (!empty($taxonomies['prefectures'])) {
    foreach ($taxonomies['prefectures'] as $pref) {
        $keywords[] = $pref->name;
    }
}
if (!empty($taxonomies['tags'])) {
    foreach ($taxonomies['tags'] as $tag) {
        $keywords[] = $tag->name;
    }
}
$keywords[] = '助成金';
$keywords[] = '補助金';
$keywords_string = implode(', ', array_unique($keywords));

// SEO: 投稿日時
$published_time = get_the_date('c', $post_id);
$modified_time = get_the_modified_date('c', $post_id);

// SEO: サイト情報
$site_name = get_bloginfo('name');
$site_url = home_url('/');
?>

<!-- ============================================
     完璧なSEOメタタグ実装
     ============================================ -->

<!-- 基本メタタグ -->
<title><?php echo esc_html($seo_title); ?> | <?php echo esc_html($site_name); ?></title>
<meta name="description" content="<?php echo esc_attr($seo_description); ?>">
<meta name="keywords" content="<?php echo esc_attr($keywords_string); ?>">
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<meta name="author" content="<?php echo esc_attr($site_name); ?>">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="canonical" href="<?php echo esc_url($canonical_url); ?>">

<!-- Open Graph Protocol (Facebook, LINE等) -->
<meta property="og:type" content="article">
<meta property="og:title" content="<?php echo esc_attr($seo_title); ?>">
<meta property="og:description" content="<?php echo esc_attr($seo_description); ?>">
<meta property="og:url" content="<?php echo esc_url($canonical_url); ?>">
<meta property="og:image" content="<?php echo esc_url($og_image); ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:site_name" content="<?php echo esc_attr($site_name); ?>">
<meta property="og:locale" content="ja_JP">
<meta property="article:published_time" content="<?php echo esc_attr($published_time); ?>">
<meta property="article:modified_time" content="<?php echo esc_attr($modified_time); ?>">
<meta property="article:author" content="<?php echo esc_attr($site_name); ?>">
<?php if (!empty($taxonomies['categories'])): ?>
<meta property="article:section" content="<?php echo esc_attr($taxonomies['categories'][0]->name); ?>">
<?php endif; ?>
<?php if (!empty($taxonomies['tags'])): foreach ($taxonomies['tags'] as $tag): ?>
<meta property="article:tag" content="<?php echo esc_attr($tag->name); ?>">
<?php endforeach; endif; ?>

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo esc_attr($seo_title); ?>">
<meta name="twitter:description" content="<?php echo esc_attr($seo_description); ?>">
<meta name="twitter:image" content="<?php echo esc_url($og_image); ?>">
<meta name="twitter:url" content="<?php echo esc_url($canonical_url); ?>">

<!-- パフォーマンス最適化 -->
<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
<link rel="dns-prefetch" href="//fonts.googleapis.com">
<link rel="dns-prefetch" href="//fonts.gstatic.com">

<!-- ============================================
     構造化データ（JSON-LD）- 完全実装
     ============================================ -->

<!-- Article スキーマ（記事として） -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "<?php echo esc_js($seo_title); ?>",
  "description": "<?php echo esc_js($seo_description); ?>",
  "image": "<?php echo esc_js($og_image); ?>",
  "datePublished": "<?php echo esc_js($published_time); ?>",
  "dateModified": "<?php echo esc_js($modified_time); ?>",
  "author": {
    "@type": "Organization",
    "name": "<?php echo esc_js($site_name); ?>",
    "url": "<?php echo esc_js($site_url); ?>"
  },
  "publisher": {
    "@type": "Organization",
    "name": "<?php echo esc_js($site_name); ?>",
    "url": "<?php echo esc_js($site_url); ?>",
    "logo": {
      "@type": "ImageObject",
      "url": "<?php echo esc_js(get_site_icon_url(512) ?: $og_image); ?>"
    }
  },
  "mainEntityOfPage": {
    "@type": "WebPage",
    "@id": "<?php echo esc_js($canonical_url); ?>"
  }<?php if (!empty($keywords_string)): ?>,
  "keywords": "<?php echo esc_js($keywords_string); ?>"
  <?php endif; ?>
}
</script>

<!-- MonetaryGrant スキーマ（助成金として） -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "MonetaryGrant",
  "name": "<?php echo esc_js($seo_title); ?>",
  "description": "<?php echo esc_js($seo_description); ?>",
  "url": "<?php echo esc_js($canonical_url); ?>"<?php if ($grant_data['organization']): ?>,
  "funder": {
    "@type": "Organization",
    "name": "<?php echo esc_js($grant_data['organization']); ?>"
  }<?php endif; ?><?php if ($max_amount_yen > 0): ?>,
  "maximumAmount": {
    "@type": "MonetaryAmount",
    "currency": "JPY",
    "value": <?php echo intval($max_amount_yen); ?>
  }<?php endif; ?><?php if (!empty($grant_data['deadline_date'])): ?>,
  "applicationDeadline": "<?php echo esc_js(date('c', strtotime($grant_data['deadline_date']))); ?>"<?php endif; ?><?php if (!empty($grant_data['grant_target'])): ?>,
  "eligibilityCriteria": "<?php echo esc_js(wp_trim_words(strip_tags($grant_data['grant_target']), 30, '...')); ?>"<?php endif; ?>
}
</script>

<!-- GovernmentService スキーマ（政府サービスとして） -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "GovernmentService",
  "name": "<?php echo esc_js($seo_title); ?>",
  "description": "<?php echo esc_js($seo_description); ?>",
  "url": "<?php echo esc_js($canonical_url); ?>"<?php if ($grant_data['organization']): ?>,
  "provider": {
    "@type": "GovernmentOrganization",
    "name": "<?php echo esc_js($grant_data['organization']); ?>"
  }<?php endif; ?><?php if ($grant_data['official_url']): ?>,
  "serviceUrl": "<?php echo esc_js($grant_data['official_url']); ?>"<?php endif; ?>,
  "areaServed": {
    "@type": "Country",
    "name": "日本"<?php if (!empty($taxonomies['prefectures'])): foreach ($taxonomies['prefectures'] as $pref): ?>,
    "containsPlace": {
      "@type": "AdministrativeArea",
      "name": "<?php echo esc_js($pref->name); ?>"
    }<?php break; endforeach; endif; ?>
  }<?php if (!empty($grant_data['contact_info'])): ?>,
  "availableChannel": {
    "@type": "ServiceChannel",
    "servicePhone": {
      "@type": "ContactPoint",
      "contactType": "Customer Service"
    }
  }<?php endif; ?>
}
</script>

<!-- BreadcrumbList スキーマ（パンくずリスト） -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "name": "ホーム",
      "item": "<?php echo esc_js($site_url); ?>"
    },
    {
      "@type": "ListItem",
      "position": 2,
      "name": "助成金一覧",
      "item": "<?php echo esc_js($site_url); ?>grant/"
    }<?php if (!empty($taxonomies['categories'])): ?>,
    {
      "@type": "ListItem",
      "position": 3,
      "name": "<?php echo esc_js($taxonomies['categories'][0]->name); ?>",
      "item": "<?php echo esc_js(get_term_link($taxonomies['categories'][0])); ?>"
    },
    {
      "@type": "ListItem",
      "position": 4,
      "name": "<?php echo esc_js($seo_title); ?>",
      "item": "<?php echo esc_js($canonical_url); ?>"
    }<?php else: ?>,
    {
      "@type": "ListItem",
      "position": 3,
      "name": "<?php echo esc_js($seo_title); ?>",
      "item": "<?php echo esc_js($canonical_url); ?>"
    }<?php endif; ?>
  ]
}
</script>

<!-- FAQPage スキーマ（よくある質問） -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    <?php if (!empty($grant_data['deadline_date']) || !empty($grant_data['deadline'])): ?>
    {
      "@type": "Question",
      "name": "この助成金の申請締切はいつですか？",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "申請締切は<?php echo esc_js($deadline_info ?: '未定'); ?>です。締切日を過ぎると応募できませんので、お早めにご準備ください。"
      }
    }<?php if ($formatted_amount || $grant_data['organization'] || $grant_data['adoption_rate'] > 0): ?>,<?php endif; ?>
    <?php endif; ?>
    <?php if ($formatted_amount): ?>
    {
      "@type": "Question",
      "name": "最大助成額はいくらですか？",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "この助成金の最大助成額は<?php echo esc_js($formatted_amount); ?>です<?php if ($grant_data['subsidy_rate']): ?>。補助率は<?php echo esc_js($grant_data['subsidy_rate']); ?>となっています<?php endif; ?>。"
      }
    }<?php if ($grant_data['organization'] || $grant_data['adoption_rate'] > 0): ?>,<?php endif; ?>
    <?php endif; ?>
    <?php if ($grant_data['organization']): ?>
    {
      "@type": "Question",
      "name": "この助成金はどこが実施していますか？",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "<?php echo esc_js($grant_data['organization']); ?>が実施している助成金です。"
      }
    }<?php if ($grant_data['adoption_rate'] > 0): ?>,<?php endif; ?>
    <?php endif; ?>
    <?php if ($grant_data['adoption_rate'] > 0): ?>
    {
      "@type": "Question",
      "name": "採択率はどのくらいですか？",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "この助成金の採択率は約<?php echo number_format($grant_data['adoption_rate'], 1); ?>%です。難易度は<?php echo esc_js($difficulty_data['label']); ?>レベルとなっています。"
      }
    }
    <?php endif; ?>
  ]
}
</script>

<style>
/* ===============================================
   MOBILE OPTIMIZED DESIGN - 四角形
   =============================================== */

:root {
    /* カラー */
    --gus-white: #ffffff;
    --gus-black: #1a1a1a;
    --gus-gray-50: #fafafa;
    --gus-gray-100: #f5f5f5;
    --gus-gray-200: #eeeeee;
    --gus-gray-300: #e0e0e0;
    --gus-gray-500: #9e9e9e;
    --gus-gray-600: #757575;
    --gus-gray-700: #616161;
    --gus-gray-800: #424242;
    --gus-gray-900: #212121;
    --gus-yellow: #ffeb3b;
    
    /* タイポグラフィ - モバイル最適化 */
    --gus-text-xs: 0.75rem;      /* 12px */
    --gus-text-sm: 0.875rem;     /* 14px */
    --gus-text-base: 0.875rem;   /* 14px */
    --gus-text-md: 0.9375rem;    /* 15px */
    --gus-text-lg: 1rem;         /* 16px */
    --gus-text-xl: 1.125rem;     /* 18px */
    --gus-text-2xl: 1.5rem;      /* 24px */
    
    /* スペーシング - モバイル最適化 */
    --gus-space-xs: 4px;
    --gus-space-sm: 8px;
    --gus-space-md: 12px;
    --gus-space-lg: 16px;
    --gus-space-xl: 24px;
    
    /* その他 - 四角形 */
    --gus-radius: 0px;
    --gus-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
    --gus-transition: 0.2s ease;
}

/* ベース */
.gus-single {
    max-width: 1200px;
    margin: 0 auto;
    padding: var(--gus-space-lg);
    background: var(--gus-white);
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Noto Sans JP', sans-serif;
    font-size: var(--gus-text-base);
    color: var(--gus-gray-800);
    line-height: 1.6;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* ヘッダー */
.gus-header {
    margin-bottom: var(--gus-space-lg);
}

.gus-header-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: var(--gus-space-md);
    flex-wrap: wrap;
    gap: var(--gus-space-sm);
}

.gus-status-badge {
    display: inline-flex;
    align-items: center;
    gap: var(--gus-space-xs);
    padding: 6px 12px;
    border-radius: var(--gus-radius);
    font-size: var(--gus-text-sm);
    font-weight: 700;
    text-transform: uppercase;
    min-height: 32px;
}

.gus-status-badge.open {
    background: var(--gus-gray-800);
    color: var(--gus-white);
}

.gus-status-badge.urgent {
    background: var(--gus-gray-900);
    color: var(--gus-yellow);
}

.gus-status-badge.closed {
    background: var(--gus-gray-500);
    color: var(--gus-white);
}

.gus-featured-badge {
    background: var(--gus-yellow);
    color: var(--gus-black);
    padding: 6px 12px;
    font-size: var(--gus-text-sm);
    font-weight: 700;
    text-transform: uppercase;
    min-height: 32px;
    display: inline-flex;
    align-items: center;
}

.gus-title {
    font-size: var(--gus-text-2xl);
    font-weight: 900;
    color: var(--gus-black);
    line-height: 1.4;
    margin: 0 0 var(--gus-space-lg);
    letter-spacing: -0.02em;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

/* キー情報 */
.gus-key-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: var(--gus-space-sm);
    background: var(--gus-gray-50);
    border: 1px solid var(--gus-gray-300);
    border-radius: var(--gus-radius);
    padding: var(--gus-space-md);
    margin-bottom: var(--gus-space-lg);
}

.gus-key-item {
    display: flex;
    flex-direction: column;
    gap: var(--gus-space-xs);
    min-height: 48px;
}

.gus-key-label {
    font-size: var(--gus-text-xs);
    color: var(--gus-gray-600);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.gus-key-value {
    font-size: var(--gus-text-md);
    font-weight: 800;
    color: var(--gus-black);
    line-height: 1.3;
    word-wrap: break-word;
}

/* レイアウト */
.gus-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--gus-space-lg);
}

@media (min-width: 960px) {
    .gus-layout {
        grid-template-columns: 2fr 1fr;
    }
}

/* コンテンツセクション */
.gus-section {
    background: var(--gus-gray-50);
    border: 1px solid var(--gus-gray-300);
    border-radius: var(--gus-radius);
    padding: var(--gus-space-md);
    margin-bottom: var(--gus-space-md);
    border-left: 3px solid var(--gus-gray-800);
}

.gus-section-header {
    display: flex;
    align-items: center;
    gap: var(--gus-space-sm);
    margin-bottom: var(--gus-space-md);
    padding-bottom: var(--gus-space-sm);
    border-bottom: 1px solid var(--gus-gray-300);
}

.gus-section-icon {
    width: 18px;
    height: 18px;
    opacity: 0.7;
    flex-shrink: 0;
}

.gus-section-title {
    font-size: var(--gus-text-lg);
    font-weight: 700;
    color: var(--gus-black);
    margin: 0;
}

.gus-section-content {
    font-size: var(--gus-text-base);
    color: var(--gus-gray-700);
    line-height: 1.7;
}

.gus-section-content p {
    margin-bottom: var(--gus-space-md);
}

.gus-section-content p:last-child {
    margin-bottom: 0;
}

/* テーブル */
.gus-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: var(--gus-white);
    border: 1px solid var(--gus-gray-300);
    border-radius: var(--gus-radius);
    overflow: hidden;
}

.gus-table th,
.gus-table td {
    padding: var(--gus-space-sm) var(--gus-space-md);
    text-align: left;
    border-bottom: 1px solid var(--gus-gray-300);
    font-size: var(--gus-text-sm);
    line-height: 1.6;
}

.gus-table th {
    background: var(--gus-gray-100);
    font-weight: 700;
    color: var(--gus-gray-700);
    width: 35%;
    vertical-align: top;
}

.gus-table td {
    font-weight: 500;
    color: var(--gus-gray-800);
    word-wrap: break-word;
}

.gus-table tr:last-child th,
.gus-table tr:last-child td {
    border-bottom: none;
}

/* サイドバー */
.gus-sidebar {
    display: flex;
    flex-direction: column;
    gap: var(--gus-space-md);
}

.gus-sidebar-card {
    background: var(--gus-gray-50);
    border: 1px solid var(--gus-gray-300);
    border-radius: var(--gus-radius);
    padding: var(--gus-space-md);
}

.gus-sidebar-title {
    font-size: var(--gus-text-lg);
    font-weight: 700;
    color: var(--gus-black);
    margin-bottom: var(--gus-space-md);
    display: flex;
    align-items: center;
    gap: var(--gus-space-sm);
}

/* ボタン */
.gus-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--gus-space-sm);
    padding: 12px var(--gus-space-md);
    border-radius: var(--gus-radius);
    font-size: var(--gus-text-sm);
    font-weight: 700;
    text-decoration: none;
    transition: var(--gus-transition);
    border: none;
    cursor: pointer;
    width: 100%;
    min-height: 44px;
    text-align: center;
    -webkit-tap-highlight-color: transparent;
}

.gus-btn-primary {
    background: var(--gus-gray-900);
    color: var(--gus-white);
}

.gus-btn-primary:hover {
    background: var(--gus-gray-800);
    transform: translateY(-1px);
}

.gus-btn-secondary {
    background: var(--gus-white);
    color: var(--gus-gray-700);
    border: 1px solid var(--gus-gray-300);
}

.gus-btn-secondary:hover {
    border-color: var(--gus-gray-600);
    background: var(--gus-gray-50);
}

.gus-btn-yellow {
    background: var(--gus-yellow);
    color: var(--gus-black);
}

.gus-btn-yellow:hover {
    background: #ffc107;
    transform: translateY(-1px);
}

.gus-actions {
    display: flex;
    flex-direction: column;
    gap: var(--gus-space-sm);
}

/* 統計 */
.gus-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--gus-space-sm);
}

.gus-stat {
    text-align: center;
    padding: var(--gus-space-md);
    background: var(--gus-white);
    border: 1px solid var(--gus-gray-300);
    border-radius: var(--gus-radius);
    min-height: 72px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.gus-stat-number {
    font-size: var(--gus-text-xl);
    font-weight: 800;
    color: var(--gus-black);
    display: block;
    line-height: 1.2;
}

.gus-stat-label {
    font-size: var(--gus-text-xs);
    color: var(--gus-gray-600);
    margin-top: var(--gus-space-xs);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

/* 難易度 */
.gus-difficulty {
    display: flex;
    align-items: center;
    gap: var(--gus-space-sm);
}

.gus-difficulty-dots {
    display: flex;
    gap: 4px;
}

.gus-difficulty-dot {
    width: 6px;
    height: 6px;
    border-radius: var(--gus-radius);
    background: var(--gus-gray-300);
}

.gus-difficulty-dot.filled {
    background: var(--gus-gray-900);
}

/* タグ */
.gus-tags {
    display: flex;
    flex-wrap: wrap;
    gap: var(--gus-space-sm);
}

.gus-tag {
    display: inline-flex;
    align-items: center;
    gap: var(--gus-space-xs);
    padding: 8px 12px;
    background: var(--gus-white);
    color: var(--gus-gray-700);
    border: 1px solid var(--gus-gray-300);
    border-radius: var(--gus-radius);
    font-size: var(--gus-text-sm);
    text-decoration: none;
    transition: var(--gus-transition);
    font-weight: 600;
    min-height: 36px;
    -webkit-tap-highlight-color: transparent;
}

.gus-tag:hover,
.gus-tag:active {
    background: var(--gus-gray-900);
    color: var(--gus-white);
    border-color: var(--gus-gray-900);
}

.gus-tags-section {
    margin-bottom: var(--gus-space-md);
}

.gus-tags-section:last-child {
    margin-bottom: 0;
}

.gus-tags-label {
    font-size: var(--gus-text-xs);
    color: var(--gus-gray-600);
    font-weight: 600;
    margin-bottom: var(--gus-space-xs);
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

/* アイコン */
.gus-icon {
    width: 18px;
    height: 18px;
    display: inline-block;
    background-repeat: no-repeat;
    background-position: center;
    background-size: contain;
    flex-shrink: 0;
}

.gus-icon-money {
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="%23424242"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>');
}

.gus-icon-calendar {
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="%23424242"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>');
}

.gus-icon-building {
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="%23424242"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>');
}

.gus-icon-chart {
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="%23424242"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>');
}

.gus-icon-document {
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="%23424242"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>');
}

.gus-icon-link {
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="%23424242"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>');
}

.gus-icon-tag {
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="%23424242"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>');
}

/* レスポンシブ - モバイル最適化 */
@media (max-width: 768px) {
    .gus-single {
        padding: var(--gus-space-md);
    }
    
    .gus-title {
        font-size: 1.375rem; /* 22px */
        line-height: 1.4;
    }
    
    .gus-key-info {
        grid-template-columns: 1fr;
        gap: var(--gus-space-md);
    }
    
    .gus-key-item {
        padding: var(--gus-space-sm);
        background: var(--gus-white);
        border-radius: var(--gus-radius);
    }
    
    .gus-section {
        padding: var(--gus-space-md);
    }
    
    .gus-section-content {
        font-size: var(--gus-text-base);
        line-height: 1.7;
    }
    
    .gus-table {
        display: block;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .gus-table th,
    .gus-table td {
        padding: var(--gus-space-sm);
        font-size: var(--gus-text-sm);
    }
    
    .gus-stats {
        grid-template-columns: 1fr;
        gap: var(--gus-space-sm);
    }
    
    .gus-stat {
        min-height: 64px;
    }
    
    .gus-btn {
        padding: 14px var(--gus-space-md);
        font-size: var(--gus-text-base);
        min-height: 48px;
    }
    
    .gus-tag {
        padding: 10px 14px;
        font-size: var(--gus-text-base);
        min-height: 40px;
    }
    
    .gus-header-top {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--gus-space-sm);
    }
}

/* 小さいスマホ対応 */
@media (max-width: 375px) {
    .gus-single {
        padding: var(--gus-space-sm);
    }
    
    .gus-title {
        font-size: 1.25rem; /* 20px */
    }
    
    .gus-section {
        padding: var(--gus-space-sm);
    }
    
    .gus-key-info {
        padding: var(--gus-space-sm);
    }
}

/* アクセシビリティ */
.gus-btn:focus,
.gus-tag:focus {
    outline: 2px solid var(--gus-gray-900);
    outline-offset: 2px;
}

.gus-btn:focus:not(:focus-visible),
.gus-tag:focus:not(:focus-visible) {
    outline: none;
}

/* プリント対応 */
@media print {
    .gus-single {
        padding: 0;
    }
    
    .gus-actions,
    .gus-btn {
        display: none;
    }
    
    .gus-section {
        page-break-inside: avoid;
    }
}

/* ダークモード対応（オプション） */
@media (prefers-color-scheme: dark) {
    :root {
        --gus-white: #1a1a1a;
        --gus-black: #ffffff;
        --gus-gray-50: #2a2a2a;
        --gus-gray-100: #333333;
        --gus-gray-200: #3d3d3d;
        --gus-gray-300: #4a4a4a;
        --gus-gray-500: #757575;
        --gus-gray-600: #9e9e9e;
        --gus-gray-700: #bdbdbd;
        --gus-gray-800: #e0e0e0;
        --gus-gray-900: #f5f5f5;
    }
    
    .gus-single {
        background: var(--gus-white);
    }
}

/* ============================================
   内部リンク・関連コンテンツスタイル
   ============================================ */

.gus-related-card {
    display: flex;
    flex-direction: column;
}

.gus-related-card:hover {
    border-color: var(--gus-gray-600);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.gus-related-card h3 a:hover {
    color: var(--gus-gray-700);
    text-decoration: underline;
}

.gus-breadcrumb a:hover {
    color: var(--gus-black);
    text-decoration: underline;
}

/* ============================================
   アクセシビリティ強化
   ============================================ */

/* フォーカス状態の明確化 */
a:focus-visible,
button:focus-visible,
.gus-btn:focus-visible,
.gus-tag:focus-visible {
    outline: 2px solid var(--gus-yellow);
    outline-offset: 2px;
}

/* タップターゲットサイズ（WCAG 2.1準拠） */
.gus-btn,
.gus-tag,
.gus-related-card a,
.gus-breadcrumb a {
    min-height: 44px;
    min-width: 44px;
}

/* スクリーンリーダー用のスキップリンク */
.skip-to-content {
    position: absolute;
    top: -40px;
    left: 0;
    background: var(--gus-yellow);
    color: var(--gus-black);
    padding: 8px;
    text-decoration: none;
    z-index: 100;
}

.skip-to-content:focus {
    top: 0;
}

/* 画像遅延ロード対応 */
img[loading="lazy"] {
    opacity: 0;
    transition: opacity 0.3s ease;
}

img[loading="lazy"].loaded {
    opacity: 1;
}

/* モーション削減対応（アクセシビリティ） */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* ハイコントラストモード対応 */
@media (prefers-contrast: high) {
    :root {
        --gus-gray-300: #000000;
        --gus-gray-600: #000000;
    }
    
    .gus-btn-primary,
    .gus-status-badge {
        border: 2px solid currentColor;
    }
}

/* レスポンシブ調整 - 関連コンテンツ */
@media (max-width: 768px) {
    .gus-related-grid {
        grid-template-columns: 1fr !important;
    }
    
    .gus-breadcrumb ol {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .gus-breadcrumb li span {
        display: none;
    }
}
</style>

<main class="gus-single">
    <!-- ヘッダー -->
    <header class="gus-header">
        <div class="gus-header-top">
            <div class="gus-status-badge <?php echo $status_data['class']; ?> <?php echo $deadline_class; ?>">
                <?php echo $status_data['label']; ?>
                <?php if ($days_remaining > 0 && $days_remaining <= 30): ?>
                    · <?php echo $days_remaining; ?>日
                <?php endif; ?>
            </div>
            
            <?php if ($grant_data['is_featured']): ?>
            <div class="gus-featured-badge">
                注目
            </div>
            <?php endif; ?>
        </div>
        
        <h1 class="gus-title"><?php the_title(); ?></h1>
        
        <!-- キー情報 -->
        <div class="gus-key-info">
            <?php if ($formatted_amount): ?>
            <div class="gus-key-item">
                <div class="gus-key-label">最大助成額</div>
                <div class="gus-key-value"><?php echo esc_html($formatted_amount); ?></div>
            </div>
            <?php endif; ?>
            
            <?php if ($deadline_info): ?>
            <div class="gus-key-item">
                <div class="gus-key-label">申請締切</div>
                <div class="gus-key-value"><?php echo esc_html($deadline_info); ?></div>
            </div>
            <?php endif; ?>
            
            <?php if ($grant_data['adoption_rate'] > 0): ?>
            <div class="gus-key-item">
                <div class="gus-key-label">採択率</div>
                <div class="gus-key-value"><?php echo number_format($grant_data['adoption_rate'], 1); ?>%</div>
            </div>
            <?php endif; ?>
            
            <?php if ($grant_data['organization']): ?>
            <div class="gus-key-item">
                <div class="gus-key-label">実施機関</div>
                <div class="gus-key-value"><?php echo esc_html(wp_trim_words($grant_data['organization'], 3, '...')); ?></div>
            </div>
            <?php endif; ?>
        </div>
    </header>
    
    <!-- レイアウト -->
    <div class="gus-layout">
        <!-- メインコンテンツ -->
        <div class="gus-main">
            <?php if ($grant_data['ai_summary']): ?>
            <section class="gus-section" style="border-left-color: var(--gus-yellow);">
                <header class="gus-section-header">
                    <div class="gus-icon gus-icon-document gus-section-icon"></div>
                    <h2 class="gus-section-title">AI要約</h2>
                </header>
                <div class="gus-section-content">
                    <p><?php echo esc_html($grant_data['ai_summary']); ?></p>
                </div>
            </section>
            <?php endif; ?>
            
            <section class="gus-section">
                <header class="gus-section-header">
                    <div class="gus-icon gus-icon-document gus-section-icon"></div>
                    <h2 class="gus-section-title">詳細情報</h2>
                </header>
                <div class="gus-section-content">
                    <?php the_content(); ?>
                </div>
            </section>
            
            <section class="gus-section">
                <header class="gus-section-header">
                    <div class="gus-icon gus-icon-document gus-section-icon"></div>
                    <h2 class="gus-section-title">助成金詳細</h2>
                </header>
                <div class="gus-section-content">
                    <table class="gus-table">
                        <?php if ($grant_data['organization']): ?>
                        <tr>
                            <th>実施機関</th>
                            <td><?php echo esc_html($grant_data['organization']); ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($formatted_amount): ?>
                        <tr>
                            <th>最大助成額</th>
                            <td><strong><?php echo esc_html($formatted_amount); ?></strong></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($grant_data['subsidy_rate']): ?>
                        <tr>
                            <th>補助率</th>
                            <td><?php echo esc_html($grant_data['subsidy_rate']); ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($deadline_info): ?>
                        <tr>
                            <th>申請締切</th>
                            <td><strong><?php echo esc_html($deadline_info); ?></strong></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($grant_data['adoption_rate'] > 0): ?>
                        <tr>
                            <th>採択率</th>
                            <td><strong><?php echo number_format($grant_data['adoption_rate'], 1); ?>%</strong></td>
                        </tr>
                        <?php endif; ?>
                        
                        <tr>
                            <th>難易度</th>
                            <td>
                                <div class="gus-difficulty">
                                    <strong><?php echo $difficulty_data['label']; ?></strong>
                                    <div class="gus-difficulty-dots">
                                        <?php for ($i = 1; $i <= 3; $i++): ?>
                                            <div class="gus-difficulty-dot <?php echo $i <= $difficulty_data['dots'] ? 'filled' : ''; ?>"></div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        
                        <tr>
                            <th>閲覧数</th>
                            <td><?php echo number_format($grant_data['views_count']); ?></td>
                        </tr>
                    </table>
                </div>
            </section>
            
            <?php if ($grant_data['grant_target']): ?>
            <section class="gus-section">
                <header class="gus-section-header">
                    <div class="gus-icon gus-icon-document gus-section-icon"></div>
                    <h2 class="gus-section-title">対象者・対象事業</h2>
                </header>
                <div class="gus-section-content">
                    <?php echo wp_kses_post($grant_data['grant_target']); ?>
                </div>
            </section>
            <?php endif; ?>
            
            <?php if ($grant_data['required_documents']): ?>
            <section class="gus-section">
                <header class="gus-section-header">
                    <div class="gus-icon gus-icon-document gus-section-icon"></div>
                    <h2 class="gus-section-title">必要書類</h2>
                </header>
                <div class="gus-section-content">
                    <?php echo wp_kses_post($grant_data['required_documents']); ?>
                </div>
            </section>
            <?php endif; ?>
            
            <?php if ($grant_data['contact_info']): ?>
            <section class="gus-section">
                <header class="gus-section-header">
                    <div class="gus-icon gus-icon-document gus-section-icon"></div>
                    <h2 class="gus-section-title">お問い合わせ</h2>
                </header>
                <div class="gus-section-content">
                    <?php echo nl2br(esc_html($grant_data['contact_info'])); ?>
                </div>
            </section>
            <?php endif; ?>
        </div>
        
        <!-- サイドバー -->
        <aside class="gus-sidebar">
            <div class="gus-sidebar-card">
                <h3 class="gus-sidebar-title">
                    <span class="gus-icon gus-icon-link"></span> アクション
                </h3>
                <div class="gus-actions">
                    <?php if ($grant_data['official_url']): ?>
                    <a href="<?php echo esc_url($grant_data['official_url']); ?>" class="gus-btn gus-btn-yellow" target="_blank" rel="noopener">
                        <span class="gus-icon gus-icon-link"></span> 公式サイト
                    </a>
                    <?php endif; ?>
                    
                    <button class="gus-btn gus-btn-secondary" onclick="window.print()">
                        印刷
                    </button>
                </div>
            </div>
            
            <div class="gus-sidebar-card">
                <h3 class="gus-sidebar-title">
                    <span class="gus-icon gus-icon-chart"></span> 統計
                </h3>
                <div class="gus-stats">
                    <?php if ($grant_data['adoption_rate'] > 0): ?>
                    <div class="gus-stat">
                        <span class="gus-stat-number"><?php echo number_format($grant_data['adoption_rate'], 1); ?>%</span>
                        <span class="gus-stat-label">採択率</span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="gus-stat">
                        <span class="gus-stat-number"><?php echo number_format($grant_data['views_count']); ?></span>
                        <span class="gus-stat-label">閲覧</span>
                    </div>
                    
                    <?php if ($days_remaining > 0): ?>
                    <div class="gus-stat">
                        <span class="gus-stat-number"><?php echo $days_remaining; ?></span>
                        <span class="gus-stat-label">残日数</span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="gus-stat">
                        <span class="gus-stat-number"><?php echo $difficulty_data['dots']; ?>/3</span>
                        <span class="gus-stat-label">難易度</span>
                    </div>
                </div>
            </div>
            
            <?php if ($taxonomies['categories'] || $taxonomies['prefectures'] || $taxonomies['tags']): ?>
            <div class="gus-sidebar-card">
                <h3 class="gus-sidebar-title">
                    <span class="gus-icon gus-icon-tag"></span> タグ
                </h3>
                
                <?php if ($taxonomies['categories']): ?>
                <div class="gus-tags-section">
                    <div class="gus-tags-label">カテゴリー</div>
                    <div class="gus-tags">
                        <?php foreach ($taxonomies['categories'] as $cat): ?>
                        <a href="<?php echo get_term_link($cat); ?>" class="gus-tag">
                            <?php echo esc_html($cat->name); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($taxonomies['prefectures']): ?>
                <div class="gus-tags-section">
                    <div class="gus-tags-label">地域</div>
                    <div class="gus-tags">
                        <?php foreach ($taxonomies['prefectures'] as $pref): ?>
                        <a href="<?php echo get_term_link($pref); ?>" class="gus-tag">
                            <?php echo esc_html($pref->name); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($taxonomies['tags']): ?>
                <div class="gus-tags-section">
                    <div class="gus-tags-label">タグ</div>
                    <div class="gus-tags">
                        <?php foreach ($taxonomies['tags'] as $tag): ?>
                        <a href="<?php echo get_term_link($tag); ?>" class="gus-tag">
                            #<?php echo esc_html($tag->name); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </aside>
    </div>
    
    <!-- ============================================
         内部リンク戦略: 関連助成金セクション
         ============================================ -->
    <?php
    // 関連助成金取得（同じカテゴリー）
    $related_args = array(
        'post_type' => 'grant',
        'posts_per_page' => 4,
        'post__not_in' => array($post_id),
        'post_status' => 'publish',
        'orderby' => 'rand',
    );
    
    if (!empty($taxonomies['categories'])) {
        $related_args['tax_query'] = array(
            array(
                'taxonomy' => 'grant_category',
                'field' => 'term_id',
                'terms' => $taxonomies['categories'][0]->term_id,
            ),
        );
    }
    
    $related_query = new WP_Query($related_args);
    
    if ($related_query->have_posts()) :
    ?>
    <section class="gus-related-section" style="margin-top: 40px; padding-top: 40px; border-top: 2px solid var(--gus-gray-300);">
        <h2 class="gus-section-title" style="font-size: var(--gus-text-xl); font-weight: 700; margin-bottom: var(--gus-space-lg); display: flex; align-items: center; gap: var(--gus-space-sm);">
            <span class="gus-icon gus-icon-document"></span>
            関連する助成金
        </h2>
        <div class="gus-related-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: var(--gus-space-lg);">
            <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
            <article class="gus-related-card" style="background: var(--gus-gray-50); border: 1px solid var(--gus-gray-300); border-radius: var(--gus-radius); padding: var(--gus-space-md); transition: var(--gus-transition);">
                <h3 style="font-size: var(--gus-text-md); font-weight: 700; margin-bottom: var(--gus-space-sm); line-height: 1.4;">
                    <a href="<?php the_permalink(); ?>" style="color: var(--gus-black); text-decoration: none;" aria-label="<?php echo esc_attr(get_the_title() . 'の詳細を見る'); ?>">
                        <?php the_title(); ?>
                    </a>
                </h3>
                <?php
                $related_max_amount = function_exists('get_field') ? get_field('max_amount', get_the_ID()) : '';
                $related_deadline = function_exists('get_field') ? get_field('deadline', get_the_ID()) : '';
                ?>
                <?php if ($related_max_amount || $related_deadline): ?>
                <div style="font-size: var(--gus-text-sm); color: var(--gus-gray-600); margin-bottom: var(--gus-space-sm);">
                    <?php if ($related_max_amount): ?>
                    <div><strong>最大:</strong> <?php echo esc_html($related_max_amount); ?></div>
                    <?php endif; ?>
                    <?php if ($related_deadline): ?>
                    <div><strong>締切:</strong> <?php echo esc_html($related_deadline); ?></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <a href="<?php the_permalink(); ?>" class="gus-btn gus-btn-secondary" style="margin-top: auto; font-size: var(--gus-text-sm); padding: 8px 12px;" aria-label="<?php echo esc_attr(get_the_title() . 'の詳細ページへ'); ?>">
                    詳細を見る →
                </a>
            </article>
            <?php endwhile; ?>
        </div>
    </section>
    <?php
    endif;
    wp_reset_postdata();
    ?>
    
    <!-- ============================================
         内部リンク戦略: カテゴリー・地域リンク
         ============================================ -->
    <section class="gus-taxonomy-links" style="margin-top: 40px; padding: var(--gus-space-lg); background: var(--gus-gray-50); border: 1px solid var(--gus-gray-300); border-radius: var(--gus-radius);">
        <h2 class="gus-section-title" style="font-size: var(--gus-text-lg); font-weight: 700; margin-bottom: var(--gus-space-md);">
            この助成金のカテゴリー・地域
        </h2>
        
        <div style="display: grid; gap: var(--gus-space-md);">
            <?php if (!empty($taxonomies['categories'])): ?>
            <div>
                <div class="gus-tags-label" style="font-size: var(--gus-text-xs); color: var(--gus-gray-600); font-weight: 600; margin-bottom: var(--gus-space-xs); text-transform: uppercase;">カテゴリー</div>
                <div style="display: flex; flex-wrap: wrap; gap: var(--gus-space-sm);">
                    <?php foreach ($taxonomies['categories'] as $cat): ?>
                    <a href="<?php echo get_term_link($cat); ?>" class="gus-tag" aria-label="<?php echo esc_attr($cat->name . 'カテゴリーの助成金一覧を見る'); ?>">
                        <?php echo esc_html($cat->name); ?> の助成金を見る →
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($taxonomies['prefectures'])): ?>
            <div>
                <div class="gus-tags-label" style="font-size: var(--gus-text-xs); color: var(--gus-gray-600); font-weight: 600; margin-bottom: var(--gus-space-xs); text-transform: uppercase;">地域</div>
                <div style="display: flex; flex-wrap: wrap; gap: var(--gus-space-sm);">
                    <?php foreach ($taxonomies['prefectures'] as $pref): ?>
                    <a href="<?php echo get_term_link($pref); ?>" class="gus-tag" aria-label="<?php echo esc_attr($pref->name . 'の助成金一覧を見る'); ?>">
                        <?php echo esc_html($pref->name); ?> の助成金を見る →
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- ============================================
         パンくずナビゲーション（視覚的）
         ============================================ -->
    <nav class="gus-breadcrumb" aria-label="パンくずナビゲーション" style="margin-top: 40px; padding: var(--gus-space-md); background: var(--gus-white); border: 1px solid var(--gus-gray-300); border-radius: var(--gus-radius);">
        <ol style="list-style: none; padding: 0; margin: 0; display: flex; flex-wrap: wrap; gap: var(--gus-space-sm); font-size: var(--gus-text-sm);">
            <li style="display: flex; align-items: center;">
                <a href="<?php echo home_url('/'); ?>" style="color: var(--gus-gray-700); text-decoration: none;" aria-label="ホームに戻る">ホーム</a>
                <span style="margin: 0 8px; color: var(--gus-gray-500);">›</span>
            </li>
            <li style="display: flex; align-items: center;">
                <a href="<?php echo home_url('/grant/'); ?>" style="color: var(--gus-gray-700); text-decoration: none;" aria-label="助成金一覧ページ">助成金一覧</a>
                <?php if (!empty($taxonomies['categories'])): ?>
                <span style="margin: 0 8px; color: var(--gus-gray-500);">›</span>
            </li>
            <li style="display: flex; align-items: center;">
                <a href="<?php echo get_term_link($taxonomies['categories'][0]); ?>" style="color: var(--gus-gray-700); text-decoration: none;" aria-label="<?php echo esc_attr($taxonomies['categories'][0]->name . 'カテゴリー'); ?>">
                    <?php echo esc_html($taxonomies['categories'][0]->name); ?>
                </a>
                <span style="margin: 0 8px; color: var(--gus-gray-500);">›</span>
            </li>
            <li style="color: var(--gus-gray-900); font-weight: 600;" aria-current="page">
                <?php echo esc_html(wp_trim_words($seo_title, 8, '...')); ?>
            </li>
                <?php else: ?>
                <span style="margin: 0 8px; color: var(--gus-gray-500);">›</span>
            </li>
            <li style="color: var(--gus-gray-900); font-weight: 600;" aria-current="page">
                <?php echo esc_html(wp_trim_words($seo_title, 8, '...')); ?>
            </li>
                <?php endif; ?>
        </ol>
    </nav>
</main>

<?php get_footer(); ?>