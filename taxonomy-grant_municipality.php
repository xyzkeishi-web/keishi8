<?php
/**
 * 市町村別助成金アーカイブページ - SEO最適化版 v3.0
 * 
 * 白黒スタイリッシュデザイン、SEO対策、カテゴリー・都道府県選択機能搭載
 * ページネーション・レスポンシブ対応
 * 
 * @package Grant_Insight_Perfect
 * @version 3.0.0
 */

get_header();

// 現在の市町村情報を取得
$current_municipality = get_queried_object();
$municipality_name = $current_municipality->name;
$municipality_slug = $current_municipality->slug;
$municipality_description = $current_municipality->description;
$municipality_count = $current_municipality->count;

// SEO用データ
$current_year = date('Y');
$page_title = $municipality_name . 'の助成金・補助金一覧｜' . $current_year . '年度最新情報';
$page_description = $municipality_name . 'で利用できる助成金・補助金情報を' . $municipality_count . '件掲載中。地域特有の制度から国の制度まで、' . $current_year . '年度の最新情報をお届けします。';

// カテゴリーデータ
$categories = get_terms([
    'taxonomy' => 'grant_category',
    'hide_empty' => true,
    'orderby' => 'count',
    'order' => 'DESC'
]);

// 都道府県データ（市町村の親）
$prefecture_data = gi_get_all_prefectures();
$parent_prefecture = '';
$related_municipalities = [];

// 現在の市町村の都道府県を特定
foreach ($prefecture_data as $pref) {
    if (isset($pref['municipalities']) && is_array($pref['municipalities'])) {
        foreach ($pref['municipalities'] as $municipality) {
            if ($municipality['slug'] === $municipality_slug) {
                $parent_prefecture = $pref;
                $related_municipalities = array_filter($pref['municipalities'], function($m) use ($municipality_slug) {
                    return $m['slug'] !== $municipality_slug;
                });
                break 2;
            }
        }
    }
}

// パンくずリスト用データ
$breadcrumbs = [
    ['name' => 'ホーム', 'url' => home_url()],
    ['name' => '助成金・補助金検索', 'url' => get_post_type_archive_link('grant')],
];

if ($parent_prefecture) {
    $breadcrumbs[] = ['name' => $parent_prefecture['name'], 'url' => get_term_link($parent_prefecture['slug'], 'grant_prefecture')];
}

$breadcrumbs[] = ['name' => $municipality_name, 'url' => ''];

// 現在のページ情報
$paged = get_query_var('paged') ? get_query_var('paged') : 1;

// フィルターパラメータの取得
$selected_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
$selected_prefecture = isset($_GET['prefecture']) ? sanitize_text_field($_GET['prefecture']) : '';
$search_keyword = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
?>

<!-- SEOメタ情報 -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "CollectionPage",
  "name": "<?php echo esc_js($page_title); ?>",
  "description": "<?php echo esc_js($page_description); ?>",
  "url": "<?php echo esc_js(get_term_link($current_municipality)); ?>",
  "breadcrumb": {
    "@type": "BreadcrumbList",
    "itemListElement": [
      <?php foreach ($breadcrumbs as $index => $crumb): ?>
      {
        "@type": "ListItem",
        "position": <?php echo $index + 1; ?>,
        "name": "<?php echo esc_js($crumb['name']); ?>"
        <?php if ($crumb['url']): ?>
        ,"item": "<?php echo esc_js($crumb['url']); ?>"
        <?php endif; ?>
      }<?php if ($index < count($breadcrumbs) - 1): ?>,<?php endif; ?>
      <?php endforeach; ?>
    ]
  },
  "mainEntity": {
    "@type": "ItemList",
    "numberOfItems": <?php echo intval($municipality_count); ?>,
    "itemListElement": "助成金・補助金一覧"
  }
}
</script>

<main class="municipality-archive-page">
    <!-- ページヘッダー -->
    <header class="archive-header">
        <div class="container">
            <!-- パンくずナビゲーション -->
            <nav class="breadcrumb-nav" aria-label="パンくず">
                <ol class="breadcrumb-list">
                    <?php foreach ($breadcrumbs as $index => $crumb): ?>
                    <li class="breadcrumb-item">
                        <?php if ($crumb['url']): ?>
                        <a href="<?php echo esc_url($crumb['url']); ?>" class="breadcrumb-link">
                            <?php echo esc_html($crumb['name']); ?>
                        </a>
                        <?php else: ?>
                        <span class="breadcrumb-current"><?php echo esc_html($crumb['name']); ?></span>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ol>
            </nav>

            <!-- メインタイトル -->
            <div class="header-content">
                <div class="header-text">
                    <h1 class="archive-title">
                        <span class="location-icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                        </span>
                        <span class="title-main"><?php echo esc_html($municipality_name); ?></span>
                        <span class="title-sub">の助成金・補助金</span>
                    </h1>
                    
                    <?php if ($municipality_description): ?>
                    <p class="archive-description"><?php echo esc_html($municipality_description); ?></p>
                    <?php endif; ?>
                    
                    <div class="archive-stats">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo number_format($municipality_count); ?></span>
                            <span class="stat-label">件の制度</span>
                        </div>
                        <?php if ($parent_prefecture): ?>
                        <div class="stat-item">
                            <span class="stat-label">都道府県:</span>
                            <a href="<?php echo esc_url(get_term_link($parent_prefecture['slug'], 'grant_prefecture')); ?>" 
                               class="prefecture-link"><?php echo esc_html($parent_prefecture['name']); ?></a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="header-visual">
                    <div class="map-icon">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- フィルターセクション -->
    <section class="filter-section">
        <div class="container">
            <div class="filter-header">
                <h2 class="filter-title">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                    </svg>
                    絞り込み検索
                </h2>
                <button class="filter-toggle" id="filterToggle">
                    <span class="toggle-text">フィルターを開く</span>
                    <svg class="toggle-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </button>
            </div>

            <div class="filter-panel" id="filterPanel">
                <form class="filter-form" method="GET" action="">
                    <div class="filter-grid">
                        <!-- カテゴリー選択 -->
                        <div class="filter-group">
                            <label for="category-select" class="filter-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                                </svg>
                                カテゴリー
                            </label>
                            <select name="category" id="category-select" class="filter-select">
                                <option value="">すべてのカテゴリー</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo esc_attr($category->slug); ?>" 
                                        <?php selected($selected_category, $category->slug); ?>>
                                    <?php echo esc_html($category->name); ?> (<?php echo $category->count; ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- 都道府県選択 -->
                        <?php if ($parent_prefecture): ?>
                        <div class="filter-group">
                            <label for="prefecture-select" class="filter-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                                都道府県
                            </label>
                            <select name="prefecture" id="prefecture-select" class="filter-select">
                                <option value="">すべての都道府県</option>
                                <option value="<?php echo esc_attr($parent_prefecture['slug']); ?>" selected>
                                    <?php echo esc_html($parent_prefecture['name']); ?>
                                </option>
                            </select>
                        </div>
                        <?php endif; ?>

                        <!-- キーワード検索 -->
                        <div class="filter-group search-group">
                            <label for="search-input" class="filter-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"/>
                                    <path d="M21 21l-4.35-4.35"/>
                                </svg>
                                キーワード検索
                            </label>
                            <div class="search-input-wrapper">
                                <input type="text" name="search" id="search-input" 
                                       class="search-input" placeholder="制度名や内容で検索..."
                                       value="<?php echo esc_attr($search_keyword); ?>">
                                <button type="submit" class="search-button">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="11" cy="11" r="8"/>
                                        <path d="M21 21l-4.35-4.35"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="filter-apply-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            検索実行
                        </button>
                        <a href="<?php echo esc_url(get_term_link($current_municipality)); ?>" class="filter-reset-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="1 4 1 10 7 10"/>
                                <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>
                            </svg>
                            リセット
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- 助成金一覧 -->
    <section class="grants-section">
        <div class="container">
            <div class="grants-header">
                <h2 class="section-title">検索結果</h2>
                <div class="results-info">
                    <span class="results-count" id="resultsCount">
                        <?php echo number_format($municipality_count); ?>件の助成金・補助金
                    </span>
                </div>
            </div>

            <!-- 助成金リスト -->
            <div class="grants-grid" id="grantsGrid">
                <?php
                // 初期表示用：現在の市町村の助成金を取得
                $initial_query = new WP_Query([
                    'post_type' => 'grant',
                    'posts_per_page' => 12,
                    'post_status' => 'publish',
                    'tax_query' => [
                        [
                            'taxonomy' => 'grant_municipality',
                            'field'    => 'slug',
                            'terms'    => $municipality_slug,
                        ],
                    ],
                    'orderby' => 'date',
                    'order' => 'DESC'
                ]);

                if ($initial_query->have_posts()) :
                    while ($initial_query->have_posts()) : 
                        $initial_query->the_post();
                        
                        // カード表示用のデータを取得
                        $grant_id = get_the_ID();
                        $grant_title = get_the_title();
                        $grant_excerpt = wp_trim_words(get_the_excerpt(), 30);
                        $grant_url = get_permalink();
                        $grant_category = wp_get_post_terms($grant_id, 'grant_category', ['fields' => 'names']);
                        $category_name = !empty($grant_category) ? $grant_category[0] : '未分類';
                        $application_deadline = get_post_meta($grant_id, 'application_deadline', true);
                        $grant_amount = get_post_meta($grant_id, 'grant_amount_max', true);
                        $organization = get_post_meta($grant_id, 'implementing_organization', true);
                ?>
                <article class="grant-card">
                    <div class="card-header">
                        <div class="card-category">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                            </svg>
                            <span><?php echo esc_html($category_name); ?></span>
                        </div>
                        <?php if ($application_deadline): ?>
                        <div class="card-deadline">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            <span><?php echo esc_html(date('Y/m/d', strtotime($application_deadline))); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="card-content">
                        <h3 class="card-title">
                            <a href="<?php echo esc_url($grant_url); ?>"><?php echo esc_html($grant_title); ?></a>
                        </h3>
                        <p class="card-excerpt"><?php echo esc_html($grant_excerpt); ?></p>
                    </div>

                    <div class="card-meta">
                        <?php if ($organization): ?>
                        <div class="meta-item organization">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/>
                            </svg>
                            <span><?php echo esc_html($organization); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($grant_amount): ?>
                        <div class="meta-item amount">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="1" x2="12" y2="23"/>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                            </svg>
                            <span>最大 <?php echo esc_html(number_format($grant_amount)); ?>円</span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="card-footer">
                        <a href="<?php echo esc_url($grant_url); ?>" class="card-link">
                            詳細を見る
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"/>
                            </svg>
                        </a>
                    </div>
                </article>
                <?php 
                    endwhile;
                    wp_reset_postdata();
                else :
                ?>
                <div class="no-results">
                    <div class="no-results-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="M21 21l-4.35-4.35"/>
                        </svg>
                    </div>
                    <h3>該当する助成金・補助金が見つかりませんでした</h3>
                    <p><?php echo esc_html($municipality_name); ?>に関連する助成金・補助金は現在登録されていません。</p>
                    <div class="no-results-actions">
                        <a href="<?php echo get_post_type_archive_link('grant'); ?>" class="btn-primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            </svg>
                            すべての助成金を見る
                        </a>
                        <?php if ($parent_prefecture): ?>
                        <a href="<?php echo esc_url(get_term_link($parent_prefecture['slug'], 'grant_prefecture')); ?>" class="btn-secondary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <?php echo esc_html($parent_prefecture['name']); ?>の助成金を見る
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- ページネーション -->
            <div class="pagination-wrapper">
                <nav class="pagination-nav" aria-label="ページネーション" id="paginationNav">
                    <!-- AJAX処理でここにページネーションが表示されます -->
                </nav>
            </div>
        </div>
    </section>

    <!-- 関連情報 -->
    <?php if (!empty($related_municipalities) || $parent_prefecture): ?>
    <aside class="related-section">
        <div class="container">
            <h2 class="section-title">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                </svg>
                関連する地域
            </h2>

            <div class="related-grid">
                <?php if ($parent_prefecture): ?>
                <div class="related-card prefecture-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                        </div>
                        <h3 class="card-title"><?php echo esc_html($parent_prefecture['name']); ?></h3>
                    </div>
                    <p class="card-description">都道府県全体の助成金・補助金情報</p>
                    <a href="<?php echo esc_url(get_term_link($parent_prefecture['slug'], 'grant_prefecture')); ?>" 
                       class="card-link">
                        詳細を見る
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </a>
                </div>
                <?php endif; ?>

                <?php if (!empty($related_municipalities)): ?>
                <?php $displayed = 0; foreach ($related_municipalities as $municipality): 
                    if ($displayed >= 5) break; // 最大5つまで表示
                    $displayed++;
                ?>
                <div class="related-card municipality-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/>
                            </svg>
                        </div>
                        <h3 class="card-title"><?php echo esc_html($municipality['name']); ?></h3>
                    </div>
                    <p class="card-description">同じ都道府県内の市町村</p>
                    <a href="<?php echo esc_url(get_term_link($municipality['slug'], 'grant_municipality')); ?>" 
                       class="card-link">
                        詳細を見る
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </a>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </aside>
    <?php endif; ?>
</main>

<style>
/* =============================================================================
   市町村アーカイブページ - 白黒スタイリッシュデザイン
   ============================================================================= */

:root {
    --color-black: #000000;
    --color-white: #ffffff;
    --color-gray-50: #fafafa;
    --color-gray-100: #f5f5f5;
    --color-gray-200: #e5e5e5;
    --color-gray-300: #d4d4d8;
    --color-gray-400: #a1a1aa;
    --color-gray-500: #71717a;
    --color-gray-600: #52525b;
    --color-gray-700: #3f3f46;
    --color-gray-800: #27272a;
    --color-gray-900: #18181b;
    --color-yellow-400: #facc15;
    --color-yellow-500: #eab308;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

/* 助成金カード */
.grant-card {
    background: var(--color-white);
    border: 2px solid var(--color-gray-200);
    border-radius: 16px;
    padding: 24px;
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.grant-card:hover {
    border-color: var(--color-yellow-500);
    box-shadow: var(--shadow-lg), 0 0 0 1px var(--color-yellow-500);
    transform: translateY(-2px);
}

.card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    flex-wrap: wrap;
    gap: 8px;
}

.card-category {
    display: flex;
    align-items: center;
    gap: 6px;
    background: var(--color-gray-100);
    color: var(--color-gray-700);
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.card-deadline {
    display: flex;
    align-items: center;
    gap: 6px;
    color: var(--color-gray-600);
    font-size: 12px;
    font-weight: 500;
}

.card-content {
    flex: 1;
    margin-bottom: 16px;
}

.card-title {
    margin: 0 0 12px 0;
    font-size: 18px;
    font-weight: 700;
    line-height: 1.4;
}

.card-title a {
    color: var(--color-black);
    text-decoration: none;
    transition: color 0.2s ease;
}

.card-title a:hover {
    color: var(--color-gray-600);
}

.card-excerpt {
    color: var(--color-gray-600);
    font-size: 14px;
    line-height: 1.6;
    margin: 0;
}

.card-meta {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 16px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--color-gray-600);
}

.meta-item svg {
    flex-shrink: 0;
    color: var(--color-gray-500);
}

.card-footer {
    margin-top: auto;
}

.card-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: var(--color-black);
    color: var(--color-white);
    text-decoration: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s ease;
}

.card-link:hover {
    background: var(--color-gray-800);
    color: var(--color-white);
    text-decoration: none;
    transform: translateX(2px);
}

/* 結果なしメッセージ */
.no-results {
    text-align: center;
    padding: 60px 20px;
    grid-column: 1 / -1;
}

.no-results-icon {
    color: var(--color-gray-300);
    margin-bottom: 24px;
}

.no-results h3 {
    font-size: 24px;
    font-weight: 700;
    color: var(--color-black);
    margin: 0 0 12px 0;
}

.no-results p {
    font-size: 16px;
    color: var(--color-gray-600);
    margin: 0 0 32px 0;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.no-results-actions {
    display: flex;
    justify-content: center;
    gap: 16px;
    flex-wrap: wrap;
}

.btn-primary, .btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 24px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-primary {
    background: var(--color-black);
    color: var(--color-white);
}

.btn-primary:hover {
    background: var(--color-gray-800);
    color: var(--color-white);
    text-decoration: none;
}

.btn-secondary {
    background: var(--color-white);
    color: var(--color-black);
    border: 2px solid var(--color-black);
}

.btn-secondary:hover {
    background: var(--color-black);
    color: var(--color-white);
    text-decoration: none;
}

/* ベーススタイル */
.municipality-archive-page {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Noto Sans JP', sans-serif;
    color: var(--color-gray-900);
    line-height: 1.6;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* ページヘッダー */
.archive-header {
    background: linear-gradient(135deg, var(--color-white) 0%, var(--color-gray-50) 100%);
    border-bottom: 2px solid var(--color-black);
    padding: 40px 0 60px;
    position: relative;
}

.archive-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--color-black) 0%, var(--color-gray-400) 100%);
}

/* パンくずナビゲーション */
.breadcrumb-nav {
    margin-bottom: 30px;
}

.breadcrumb-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    list-style: none;
    padding: 0;
    margin: 0;
}

.breadcrumb-item {
    display: flex;
    align-items: center;
}

.breadcrumb-item:not(:last-child)::after {
    content: '›';
    margin-left: 8px;
    color: var(--color-gray-400);
    font-weight: bold;
}

.breadcrumb-link {
    color: var(--color-gray-600);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: color 0.2s ease;
}

.breadcrumb-link:hover {
    color: var(--color-black);
}

.breadcrumb-current {
    color: var(--color-black);
    font-size: 14px;
    font-weight: 600;
}

/* ヘッダーコンテンツ */
.header-content {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 40px;
    align-items: center;
}

.archive-title {
    font-size: 48px;
    font-weight: 900;
    color: var(--color-black);
    margin: 0 0 20px;
    line-height: 1.2;
}

.location-icon {
    font-size: 40px;
    margin-right: 12px;
}

.title-main {
    color: var(--color-black);
}

.title-sub {
    color: var(--color-gray-600);
    font-weight: 600;
}

.archive-description {
    font-size: 18px;
    color: var(--color-gray-600);
    margin: 0 0 30px;
    line-height: 1.6;
}

.archive-stats {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
}

.stat-item {
    display: flex;
    align-items: baseline;
    gap: 8px;
}

.stat-number {
    font-size: 32px;
    font-weight: 900;
    color: var(--color-black);
    position: relative;
}

.stat-number::after {
    content: '';
    position: absolute;
    bottom: -4px;
    left: 0;
    width: 30px;
    height: 3px;
    background: var(--color-yellow-500);
    border-radius: 2px;
}

.stat-label {
    font-size: 16px;
    color: var(--color-gray-600);
    font-weight: 500;
}

.prefecture-link {
    color: var(--color-black);
    text-decoration: none;
    font-weight: 600;
    transition: all 0.2s ease;
    position: relative;
}

.prefecture-link:hover {
    color: var(--color-black);
    text-decoration: none;
}

.prefecture-link::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: var(--color-yellow-500);
    transition: width 0.3s ease;
}

.prefecture-link:hover::after {
    width: 100%;
}

/* ヘッダービジュアル */
.header-visual {
    display: flex;
    justify-content: center;
    align-items: center;
}

.map-icon {
    width: 120px;
    height: 120px;
    background: var(--color-black);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-white);
    box-shadow: var(--shadow-lg);
}

/* フィルターセクション */
.filter-section {
    background: var(--color-white);
    border-bottom: 1px solid var(--color-gray-200);
    padding: 50px 0;
}

.filter-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
}

.filter-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--color-black);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.filter-toggle {
    display: none;
    align-items: center;
    gap: 8px;
    background: var(--color-black);
    color: var(--color-white);
    border: none;
    padding: 10px 16px;
    border-radius: 0;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.filter-toggle:hover {
    background: var(--color-gray-800);
}

.toggle-icon {
    transition: transform 0.2s ease;
}

.filter-toggle.active .toggle-icon {
    transform: rotate(180deg);
}

.filter-panel {
    background: var(--color-gray-50);
    border: 2px solid var(--color-black);
    border-radius: 0;
    padding: 24px;
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.filter-label {
    font-size: 14px;
    font-weight: 600;
    color: var(--color-black);
    display: flex;
    align-items: center;
    gap: 6px;
}

.filter-select,
.search-input {
    padding: 12px;
    border: 2px solid var(--color-gray-300);
    border-radius: 0;
    font-size: 14px;
    background: var(--color-white);
    transition: border-color 0.2s ease;
}

.filter-select:focus,
.search-input:focus {
    outline: none;
    border-color: var(--color-black);
    box-shadow: 0 0 0 3px rgba(234, 179, 8, 0.2);
}

.search-input-wrapper {
    display: flex;
    gap: 2px;
}

.search-input {
    flex: 1;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}

.search-button {
    background: var(--color-black);
    color: var(--color-white);
    border: none;
    padding: 12px;
    border-top-right-radius: 6px;
    border-bottom-right-radius: 6px;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.search-button:hover {
    background: var(--color-gray-800);
}

.filter-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

.filter-apply-btn,
.filter-reset-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 12px 20px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 0;
    text-decoration: none;
    transition: all 0.2s ease;
}

.filter-apply-btn {
    background: var(--color-black);
    color: var(--color-white);
    border: none;
    cursor: pointer;
}

.filter-apply-btn:hover {
    background: var(--color-gray-800);
}

.filter-reset-btn {
    background: var(--color-white);
    color: var(--color-gray-600);
    border: 2px solid var(--color-gray-300);
}

.filter-reset-btn:hover {
    border-color: var(--color-black);
    color: var(--color-black);
}

/* 助成金セクション */
.grants-section {
    padding: 60px 0;
    background: var(--color-white);
}

.grants-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
}

.section-title {
    font-size: 28px;
    font-weight: 700;
    color: var(--color-black);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.results-info {
    color: var(--color-gray-600);
    font-size: 16px;
    font-weight: 500;
}

.results-count {
    color: var(--color-black);
    font-weight: 700;
}

/* ローディング */
.loading-placeholder {
    text-align: center;
    padding: 60px 20px;
    color: var(--color-gray-600);
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid var(--color-gray-200);
    border-top-color: var(--color-black);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* ページネーション */
.pagination-wrapper {
    margin-top: 60px;
}

.pagination-nav {
    display: flex;
    justify-content: center;
}

/* 関連セクション */
.related-section {
    background: var(--color-gray-50);
    padding: 60px 0;
    border-top: 1px solid var(--color-gray-200);
}

.related-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 30px;
}

.related-card {
    background: var(--color-white);
    border: 2px solid var(--color-gray-200);
    border-radius: 12px;
    padding: 24px;
    transition: all 0.3s ease;
}

.related-card:hover {
    border-color: var(--color-yellow-500);
    box-shadow: var(--shadow-md), 0 0 0 1px var(--color-yellow-500);
    transform: translateY(-2px);
}

.card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.card-icon {
    width: 40px;
    height: 40px;
    background: var(--color-black);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-white);
}

.prefecture-card .card-icon {
    background: var(--color-yellow-500);
    color: var(--color-black);
}

.card-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--color-black);
    margin: 0;
}

.card-description {
    color: var(--color-gray-600);
    margin: 0 0 16px;
    font-size: 14px;
}

.card-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: var(--color-black);
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: color 0.2s ease;
}

.card-link:hover {
    color: var(--color-yellow-500);
}

/* 助成金グリッドレイアウト */
.grants-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 24px;
    margin-top: 24px;
}

/* レスポンシブデザイン */
@media (max-width: 1024px) {
    .grants-grid {
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }
}

/* タブレット・スマホ対応 */
@media (max-width: 768px) {
    .container {
        padding: 0 16px;
    }

    .archive-header {
        padding: 30px 0 40px;
    }

    .grants-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .header-content {
        grid-template-columns: 1fr;
        gap: 20px;
        text-align: center;
    }

    .archive-title {
        font-size: 32px;
    }

    .header-visual {
        order: -1;
    }

    .map-icon {
        width: 80px;
        height: 80px;
    }

    .filter-toggle {
        display: flex;
    }

    .filter-panel {
        display: none;
        margin-top: 16px;
    }

    .filter-panel.active {
        display: block;
    }

    .filter-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .filter-actions {
        justify-content: stretch;
    }

    .filter-apply-btn,
    .filter-reset-btn {
        flex: 1;
        justify-content: center;
    }

    .grants-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }

    .section-title {
        font-size: 24px;
    }

    .related-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .archive-title {
        font-size: 28px;
    }

    .location-icon {
        font-size: 32px;
    }

    .stat-number {
        font-size: 24px;
    }

    .archive-stats {
        flex-direction: column;
        gap: 16px;
    }
}

/* パフォーマンス最適化 */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // フィルター切り替え
    const filterToggle = document.getElementById('filterToggle');
    const filterPanel = document.getElementById('filterPanel');
    
    if (filterToggle && filterPanel) {
        filterToggle.addEventListener('click', function() {
            const isActive = filterPanel.classList.contains('active');
            
            if (isActive) {
                filterPanel.classList.remove('active');
                filterToggle.classList.remove('active');
                filterToggle.querySelector('.toggle-text').textContent = 'フィルターを開く';
            } else {
                filterPanel.classList.add('active');
                filterToggle.classList.add('active');
                filterToggle.querySelector('.toggle-text').textContent = 'フィルターを閉じる';
            }
        });
    }

    // AJAX検索処理
    const filterForm = document.querySelector('.filter-form');
    const grantsGrid = document.getElementById('grantsGrid');
    const resultsCount = document.getElementById('resultsCount');
    const paginationNav = document.getElementById('paginationNav');

    if (filterForm) {
        // ページ読み込み時に初期データを取得
        loadGrants();

        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            loadGrants();
        });

        // フィルター変更時の自動検索
        const selects = filterForm.querySelectorAll('select');
        selects.forEach(select => {
            select.addEventListener('change', function() {
                loadGrants();
            });
        });
    }

    function loadGrants(page = 1) {
        if (!grantsGrid) return;

        // ローディング表示
        grantsGrid.innerHTML = `
            <div class="loading-placeholder">
                <div class="loading-spinner"></div>
                <p>検索中...</p>
            </div>
        `;

        // フォームデータの収集
        const formData = new FormData(filterForm);
        formData.append('action', 'filter_municipality_grants');
        formData.append('municipality', '<?php echo esc_js($municipality_slug); ?>');
        formData.append('page', page);
        formData.append('nonce', '<?php echo wp_create_nonce("gi_ajax_nonce"); ?>');

        // AJAX リクエスト
        fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                grantsGrid.innerHTML = data.data.html;
                
                if (resultsCount) {
                    const totalCount = parseInt(data.data.total) || 0;
                    resultsCount.textContent = `${totalCount.toLocaleString()}件の助成金・補助金`;
                }
                
                if (paginationNav) {
                    paginationNav.innerHTML = data.data.pagination;
                }

                // ページネーションのクリックイベント
                setupPagination();
            } else {
                grantsGrid.innerHTML = `
                    <div class="error-message">
                        <p>エラーが発生しました: ${data.data || 'データの取得に失敗しました'}</p>
                    </div>
                `;
                if (resultsCount) {
                    resultsCount.textContent = '0件の助成金・補助金';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            grantsGrid.innerHTML = `
                <div class="error-message">
                    <p>通信エラーが発生しました。しばらく時間をおいてから再度お試しください。</p>
                </div>
            `;
            if (resultsCount) {
                resultsCount.textContent = '0件の助成金・補助金';
            }
        });
    }

    function setupPagination() {
        const paginationLinks = document.querySelectorAll('.pagination-nav a');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = new URL(this.href);
                const page = url.searchParams.get('page') || 1;
                loadGrants(page);
                
                // スムーズスクロール
                grantsGrid.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    }

    // 検索入力のデバウンス処理
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadGrants();
            }, 500);
        });
    }

    // パフォーマンス最適化
    if ('requestIdleCallback' in window) {
        requestIdleCallback(() => {
            console.log('Municipality archive page loaded successfully');
        });
    }
});
</script>

<?php get_footer(); ?>