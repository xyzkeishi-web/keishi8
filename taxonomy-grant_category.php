<?php
/**
 * カテゴリー別助成金アーカイブページ - SEO最適化版 v2.0
 * 
 * 白黒スタイリッシュデザイン、SEO対策、都道府県選択機能搭載
 * 
 * @package Grant_Insight_Perfect
 * @version 2.0.0
 */

get_header();

// 現在のカテゴリー情報を取得
$current_category = get_queried_object();
$category_name = $current_category->name;
$category_slug = $current_category->slug;
$category_description = $current_category->description;
$category_count = $current_category->count;

// SEO用データ
$current_year = date('Y');
$page_title = $category_name . 'の助成金・補助金一覧｜' . $current_year . '年度最新情報';
$page_description = $category_name . 'に関する助成金・補助金情報を' . $category_count . '件掲載中。申請方法から採択のポイントまで、' . $current_year . '年度の最新情報をお届けします。';

// 都道府県データ
$prefectures = gi_get_all_prefectures();

// 関連カテゴリーの取得
$related_categories = get_terms([
    'taxonomy' => 'grant_category',
    'hide_empty' => true,
    'exclude' => [$current_category->term_id],
    'orderby' => 'count',
    'order' => 'DESC',
    'number' => 8
]);

// パンくずリスト用データ
$breadcrumbs = [
    ['name' => 'ホーム', 'url' => home_url()],
    ['name' => '助成金・補助金検索', 'url' => get_post_type_archive_link('grant')],
    ['name' => $category_name, 'url' => '']
];
?>

<!-- SEOメタ情報 -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "CollectionPage",
  "name": "<?php echo esc_js($page_title); ?>",
  "description": "<?php echo esc_js($page_description); ?>",
  "url": "<?php echo esc_url(get_term_link($current_category)); ?>",
  "mainEntity": {
    "@type": "ItemList",
    "name": "<?php echo esc_js($category_name); ?>の助成金一覧",
    "numberOfItems": <?php echo intval($category_count); ?>
  },
  "breadcrumb": {
    "@type": "BreadcrumbList",
    "itemListElement": [
      <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
      {
        "@type": "ListItem",
        "position": <?php echo $index + 1; ?>,
        "name": "<?php echo esc_js($breadcrumb['name']); ?>"
        <?php if (!empty($breadcrumb['url'])): ?>
        ,"item": "<?php echo esc_url($breadcrumb['url']); ?>"
        <?php endif; ?>
      }<?php echo $index < count($breadcrumbs) - 1 ? ',' : ''; ?>
      <?php endforeach; ?>
    ]
  }
}
</script>

<main class="category-archive-page" id="category-<?php echo esc_attr($category_slug); ?>">

    <!-- ヒーローセクション -->
    <section class="category-hero">
        <div class="container">
            <!-- パンくずリスト -->
            <nav class="breadcrumb-nav" aria-label="パンくずリスト">
                <ol class="breadcrumb-list">
                    <?php foreach ($breadcrumbs as $breadcrumb): ?>
                    <li class="breadcrumb-item">
                        <?php if (!empty($breadcrumb['url'])): ?>
                            <a href="<?php echo esc_url($breadcrumb['url']); ?>" class="breadcrumb-link">
                                <?php echo esc_html($breadcrumb['name']); ?>
                            </a>
                        <?php else: ?>
                            <span class="breadcrumb-current"><?php echo esc_html($breadcrumb['name']); ?></span>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ol>
            </nav>

            <!-- ページヘッダー -->
            <div class="category-header">
                <h1 class="category-title">
                    <span class="category-name"><?php echo esc_html($category_name); ?></span>
                    <span class="title-suffix">の助成金・補助金</span>
                </h1>
                
                <div class="category-meta">
                    <span class="category-count">
                        <svg class="icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9 11H7v10a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V11h-2v8H9v-8z"/>
                            <path d="M13 7h2l-5-5-5 5h2v4h6V7z"/>
                        </svg>
                        <strong><?php echo number_format($category_count); ?></strong>件の助成金
                    </span>
                    <span class="last-updated">
                        <?php echo $current_year; ?>年度最新情報
                    </span>
                </div>

                <?php if ($category_description): ?>
                <div class="category-description">
                    <p><?php echo esc_html($category_description); ?></p>
                </div>
                <?php endif; ?>

                <!-- カテゴリーの特徴 -->
                <div class="category-features">
                    <div class="feature-grid">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </div>
                            <div class="feature-content">
                                <h3>厳選された情報</h3>
                                <p><?php echo esc_html($category_name); ?>分野の信頼できる助成金のみを掲載</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                            </div>
                            <div class="feature-content">
                                <h3>リアルタイム更新</h3>
                                <p>募集開始・締切情報を毎日チェック</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                                </svg>
                            </div>
                            <div class="feature-content">
                                <h3>申請サポート</h3>
                                <p>申請書類の書き方から採択のコツまで</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- フィルター＆検索セクション -->
    <section class="category-filters">
        <div class="container">
            <div class="filters-wrapper">
                
                <!-- 検索バー -->
                <div class="search-section">
                    <h2 class="section-title">絞り込み検索</h2>
                    <div class="search-input-wrapper">
                        <input type="text" 
                               id="category-search" 
                               class="search-input" 
                               placeholder="助成金名、実施機関、キーワードで検索..."
                               data-category="<?php echo esc_attr($category_slug); ?>">
                        <button class="search-btn" id="search-execute">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.35-4.35"/>
                            </svg>
                            検索
                        </button>
                    </div>
                </div>

                <!-- 都道府県フィルター -->
                <div class="prefecture-filter">
                    <h3 class="filter-title">
                        <svg class="filter-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        対象地域で絞り込み
                    </h3>
                    
                    <!-- 地域タブ -->
                    <div class="region-tabs">
                        <button class="region-tab active" data-region="">全国</button>
                        <button class="region-tab" data-region="kanto">関東</button>
                        <button class="region-tab" data-region="kinki">近畿</button>
                        <button class="region-tab" data-region="chubu">中部</button>
                        <button class="region-tab" data-region="tohoku">東北</button>
                        <button class="region-tab" data-region="chugoku">中国</button>
                        <button class="region-tab" data-region="kyushu">九州</button>
                        <button class="region-tab" data-region="shikoku">四国</button>
                        <button class="region-tab" data-region="hokkaido">北海道</button>
                    </div>

                    <!-- 都道府県選択 -->
                    <div class="prefecture-selector" id="prefecture-selector">
                        <div class="prefecture-grid">
                            <button class="prefecture-btn active" data-prefecture="">全て</button>
                            <?php if (!empty($prefectures)): ?>
                                <?php foreach ($prefectures as $prefecture): ?>
                                <button class="prefecture-btn" 
                                        data-prefecture="<?php echo esc_attr($prefecture['slug']); ?>"
                                        data-region="<?php echo esc_attr($prefecture['region']); ?>">
                                    <?php echo esc_html($prefecture['name']); ?>
                                </button>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- その他のフィルター -->
                <div class="additional-filters">
                    <div class="filter-group">
                        <label class="filter-label">募集状況</label>
                        <select id="status-filter" class="filter-select">
                            <option value="">すべて</option>
                            <option value="active">募集中</option>
                            <option value="upcoming">募集予定</option>
                            <option value="closed">募集終了</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">助成金額</label>
                        <select id="amount-filter" class="filter-select">
                            <option value="">指定なし</option>
                            <option value="0-100">〜100万円</option>
                            <option value="100-500">100万円〜500万円</option>
                            <option value="500-1000">500万円〜1000万円</option>
                            <option value="1000+">1000万円以上</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">並び順</label>
                        <select id="sort-filter" class="filter-select">
                            <option value="date_desc">新着順</option>
                            <option value="amount_desc">金額が高い順</option>
                            <option value="deadline_asc">締切が近い順</option>
                            <option value="featured">注目順</option>
                        </select>
                    </div>
                </div>

                <!-- 現在の絞り込み条件 -->
                <div class="active-filters" id="active-filters" style="display: none;">
                    <span class="filters-label">絞り込み条件：</span>
                    <div class="filter-tags" id="filter-tags"></div>
                    <button class="clear-filters" id="clear-filters">すべてクリア</button>
                </div>
            </div>
        </div>
    </section>

    <!-- 助成金一覧セクション -->
    <section class="grants-listing">
        <div class="container">
            
            <!-- 結果ヘッダー -->
            <div class="results-header">
                <div class="results-info">
                    <h2 class="results-title">
                        <span class="category-highlight"><?php echo esc_html($category_name); ?></span>の助成金一覧
                    </h2>
                    <div class="results-meta">
                        <span class="total-count" id="total-count"><?php echo number_format($category_count); ?>件</span>
                        <span class="showing-range" id="showing-range">1-12件を表示</span>
                    </div>
                </div>

                <div class="view-controls">
                    <button class="view-toggle active" data-view="grid" title="グリッド表示">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <rect x="3" y="3" width="7" height="7"/>
                            <rect x="14" y="3" width="7" height="7"/>
                            <rect x="3" y="14" width="7" height="7"/>
                            <rect x="14" y="14" width="7" height="7"/>
                        </svg>
                    </button>
                    <button class="view-toggle" data-view="list" title="リスト表示">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <line x1="8" y1="6" x2="21" y2="6"/>
                            <line x1="8" y1="12" x2="21" y2="12"/>
                            <line x1="8" y1="18" x2="21" y2="18"/>
                            <line x1="3" y1="6" x2="3.01" y2="6"/>
                            <line x1="3" y1="12" x2="3.01" y2="12"/>
                            <line x1="3" y1="18" x2="3.01" y2="18"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- ローディング表示 -->
            <div class="loading-spinner" id="loading-spinner" style="display: none;">
                <div class="spinner"></div>
                <p>検索中...</p>
            </div>

            <!-- 助成金グリッド -->
            <div class="grants-grid" id="grants-grid" data-view="grid">
                <?php
                // 現在のカテゴリーに属する助成金を取得
                $grants_query = new WP_Query([
                    'post_type' => 'grant',
                    'posts_per_page' => 12,
                    'post_status' => 'publish',
                    'tax_query' => [
                        [
                            'taxonomy' => 'grant_category',
                            'field'    => 'slug',
                            'terms'    => $category_slug,
                        ],
                    ],
                    'orderby' => 'date',
                    'order' => 'DESC'
                ]);

                if ($grants_query->have_posts()) :
                    while ($grants_query->have_posts()) : 
                        $grants_query->the_post();
                        // 統一カードテンプレートを使用
                        get_template_part('template-parts/grant-card-unified');
                    endwhile;
                    wp_reset_postdata();
                else :
                ?>
                <div class="no-grants-message">
                    <svg class="no-results-icon" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    <h3>該当する助成金が見つかりませんでした</h3>
                    <p>現在、<?php echo esc_html($category_name); ?>カテゴリーには助成金が登録されていません。</p>
                    <a href="<?php echo get_post_type_archive_link('grant'); ?>" class="btn-primary">すべての助成金を見る</a>
                </div>
                <?php endif; ?>
            </div>

            <!-- ページネーション -->
            <?php if ($grants_query->max_num_pages > 1): ?>
            <div class="pagination-wrapper" id="pagination-wrapper">
                <nav class="pagination" aria-label="助成金一覧ページネーション">
                    <?php
                    echo paginate_links([
                        'total' => $grants_query->max_num_pages,
                        'current' => max(1, get_query_var('paged')),
                        'format' => '?paged=%#%',
                        'show_all' => false,
                        'type' => 'list',
                        'end_size' => 2,
                        'mid_size' => 1,
                        'prev_text' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg> 前へ',
                        'next_text' => '次へ <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>',
                    ]);
                    ?>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- 関連カテゴリーセクション -->
    <?php if (!empty($related_categories)): ?>
    <section class="related-categories">
        <div class="container">
            <h2 class="section-title">関連カテゴリー</h2>
            <div class="related-categories-grid">
                <?php foreach ($related_categories as $related_cat): ?>
                <a href="<?php echo get_term_link($related_cat); ?>" class="related-category-card">
                    <h3 class="category-name"><?php echo esc_html($related_cat->name); ?></h3>
                    <span class="category-count"><?php echo number_format($related_cat->count); ?>件</span>
                    <svg class="arrow-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                    </svg>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- SEO用追加コンテンツ -->
    <section class="seo-content">
        <div class="container">
            <div class="seo-text">
                <h2><?php echo esc_html($category_name); ?>の助成金について</h2>
                <p>
                    <?php echo esc_html($category_name); ?>分野の助成金・補助金は、
                    <?php echo esc_html($category_description ?: $category_name . 'に関連する事業や研究開発'); ?>を支援するための重要な資金調達手段です。
                    当サイトでは、<?php echo $current_year; ?>年度に募集される<?php echo esc_html($category_name); ?>関連の助成金情報を
                    <?php echo number_format($category_count); ?>件掲載しており、
                    定期的に最新情報を更新しています。
                </p>
                <p>
                    各助成金の詳細な申請要件、対象事業、助成金額、申請期限などを分かりやすく整理し、
                    申請を検討されている方が効率的に情報収集できるよう配慮しています。
                    また、都道府県別での絞り込み機能により、地域に特化した助成金制度も簡単に見つけることができます。
                </p>
            </div>
        </div>
    </section>

</main>

<!-- 白黒スタイリッシュデザイン - Prefecture テンプレートと統一 -->
<style>
/* CSS Variables for consistent black/white design */
:root {
    --color-black: #000000;
    --color-white: #ffffff;
    --color-gray-50: #fafafa;
    --color-gray-100: #f5f5f5;
    --color-gray-200: #e5e5e5;
    --color-gray-300: #d4d4d4;
    --color-gray-400: #a3a3a3;
    --color-gray-500: #737373;
    --color-gray-600: #525252;
    --color-gray-700: #404040;
    --color-gray-800: #262626;
    --color-gray-900: #171717;
    --color-blue-50: #eff6ff;
    --color-blue-100: #dbeafe;
    --color-blue-600: #2563eb;
    --color-blue-700: #1d4ed8;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

/* ===== ベーススタイル ===== */
.category-archive-page {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Noto Sans JP', sans-serif;
    color: var(--color-black);
    background: var(--color-white);
    line-height: 1.6;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* ===== パンくずリスト ===== */
.breadcrumb-nav {
    margin-bottom: 30px;
    padding: 15px 0;
}

.breadcrumb-list {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    margin: 0;
    padding: 0;
    list-style: none;
}

.breadcrumb-item {
    display: flex;
    align-items: center;
    font-size: 14px;
}

.breadcrumb-item:not(:last-child)::after {
    content: '>';
    margin-left: 8px;
    color: #999;
    font-size: 12px;
}

.breadcrumb-link {
    color: #666;
    text-decoration: none;
    transition: color 0.2s ease;
}

.breadcrumb-link:hover {
    color: #000;
}

.breadcrumb-current {
    color: #000;
    font-weight: 600;
}

/* ===== ヒーローセクション ===== */
.category-hero {
    padding: 40px 0 60px;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-bottom: 1px solid #e0e0e0;
}

.category-header {
    text-align: center;
    max-width: 800px;
    margin: 0 auto;
}

.category-title {
    font-size: 48px;
    font-weight: 800;
    color: #000;
    margin: 0 0 20px 0;
    line-height: 1.2;
}

.category-name {
    color: #000;
}

.title-suffix {
    color: #666;
    font-weight: 600;
}

.category-meta {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 30px;
    margin: 20px 0;
    flex-wrap: wrap;
}

.category-count {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 16px;
    color: #333;
}

.category-count .icon {
    color: #666;
}

.last-updated {
    color: #666;
    font-size: 14px;
    padding: 6px 12px;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 20px;
}

.category-description {
    margin: 30px 0;
    padding: 20px;
    background: rgba(0, 0, 0, 0.03);
    border-radius: 8px;
    border-left: 4px solid #000;
}

.category-description p {
    margin: 0;
    font-size: 16px;
    color: #444;
}

/* ===== 特徴セクション ===== */
.category-features {
    margin-top: 40px;
}

.feature-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.feature-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 24px;
    background: #ffffff;
    border: 2px solid #f0f0f0;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.feature-item:hover {
    border-color: #000;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.feature-icon {
    flex-shrink: 0;
    width: 48px;
    height: 48px;
    background: #000;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
}

.feature-content h3 {
    margin: 0 0 8px 0;
    font-size: 16px;
    font-weight: 700;
    color: #000;
}

.feature-content p {
    margin: 0;
    font-size: 14px;
    color: #666;
}

/* ===== フィルターセクション ===== */
.category-filters {
    padding: 50px 0;
    background: #ffffff;
    border-bottom: 1px solid #e0e0e0;
}

.filters-wrapper {
    background: #fafafa;
    border: 2px solid #f0f0f0;
    border-radius: 16px;
    padding: 40px;
}

.section-title {
    font-size: 24px;
    font-weight: 700;
    color: #000;
    margin: 0 0 20px 0;
}

.search-section {
    margin-bottom: 60px;
}

.search-input-wrapper {
    display: flex;
    max-width: 600px;
    background: #ffffff;
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    overflow: hidden;
    transition: border-color 0.3s ease;
}

.search-input-wrapper:focus-within {
    border-color: #000;
}

.search-input {
    flex: 1;
    padding: 16px 20px;
    border: none;
    outline: none;
    font-size: 16px;
    background: transparent;
}

.search-btn {
    background: #000;
    color: #fff;
    border: none;
    padding: 16px 24px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: background 0.3s ease;
}

.search-btn:hover {
    background: #333;
}

/* ===== 都道府県フィルター ===== */
.prefecture-filter {
    margin-bottom: 50px;
}

.filter-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 18px;
    font-weight: 600;
    color: #000;
    margin: 0 0 20px 0;
}

.filter-icon {
    color: #666;
}

.region-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.region-tab {
    background: #ffffff;
    border: 2px solid #e0e0e0;
    color: #666;
    padding: 10px 16px;
    border-radius: 0;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.region-tab:hover {
    border-color: #000;
    color: #000;
}

.region-tab.active {
    background: #000;
    border-color: #000;
    color: #fff;
}

.prefecture-selector {
    background: #ffffff;
    border: 2px solid #f0f0f0;
    border-radius: 0;
    padding: 20px;
}

.prefecture-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 8px;
}

.prefecture-btn {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    color: #333;
    padding: 10px 12px;
    border-radius: 0;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
}

.prefecture-btn:hover {
    border-color: #000;
    color: #000;
}

.prefecture-btn.active {
    background: #000;
    border-color: #000;
    color: #fff;
    font-weight: 600;
}

/* ===== その他のフィルター ===== */
.additional-filters {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.filter-label {
    font-size: 14px;
    font-weight: 600;
    color: #000;
}

.filter-select {
    padding: 12px 16px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    background: #ffffff;
    font-size: 14px;
    cursor: pointer;
    transition: border-color 0.3s ease;
}

.filter-select:focus {
    outline: none;
    border-color: #000;
}

/* ===== アクティブフィルター ===== */
.active-filters {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 16px;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 8px;
    flex-wrap: wrap;
}

.filters-label {
    font-size: 14px;
    font-weight: 600;
    color: #000;
}

.filter-tags {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.filter-tag {
    background: #000;
    color: #fff;
    padding: 6px 12px;
    border-radius: 16px;
    font-size: 12px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 6px;
}

.filter-tag .remove-tag {
    background: none;
    border: none;
    color: #fff;
    cursor: pointer;
    padding: 0;
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.clear-filters {
    background: #ffffff;
    border: 1px solid #000;
    color: #000;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.clear-filters:hover {
    background: #000;
    color: #fff;
}

/* ===== 助成金一覧セクション ===== */
.grants-listing {
    padding: 60px 0;
}

.results-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 40px;
    flex-wrap: wrap;
    gap: 20px;
}

.results-title {
    font-size: 28px;
    font-weight: 700;
    color: #000;
    margin: 0 0 10px 0;
    line-height: 1.2;
}

.category-highlight {
    color: #000;
    background: linear-gradient(180deg, transparent 65%, rgba(255, 235, 59, 0.3) 65%);
    padding: 0 4px;
}

.results-meta {
    display: flex;
    align-items: center;
    gap: 20px;
    font-size: 14px;
    color: #666;
}

.total-count {
    font-weight: 600;
    color: #000;
}

.view-controls {
    display: flex;
    gap: 4px;
    background: #f0f0f0;
    border-radius: 8px;
    padding: 4px;
}

.view-toggle {
    background: transparent;
    border: none;
    padding: 10px 12px;
    border-radius: 6px;
    cursor: pointer;
    color: #666;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.view-toggle:hover {
    color: #000;
}

.view-toggle.active {
    background: #000;
    color: #fff;
}

/* ===== ローディングスピナー ===== */
.loading-spinner {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 0;
    color: #666;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f0f0f0;
    border-top-color: #000;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 16px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* ===== 助成金グリッド ===== */
.grants-grid {
    display: grid;
    gap: 24px;
    margin-bottom: 60px;
}

.grants-grid[data-view="grid"] {
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
}

.grants-grid[data-view="list"] {
    grid-template-columns: 1fr;
}

/* ===== ページネーション ===== */
.pagination-wrapper {
    margin-top: 60px;
    display: flex;
    justify-content: center;
}

.pagination {
    display: flex;
    align-items: center;
    gap: 8px;
    background: var(--color-white);
    border: 2px solid var(--color-black);
    border-radius: 12px;
    padding: 12px 20px;
    box-shadow: var(--shadow-md);
}

.pagination .page-numbers {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0 12px;
    font-size: 14px;
    font-weight: 600;
    color: var(--color-gray-600);
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.2s ease;
    gap: 6px;
}

.pagination .page-numbers:hover {
    background: var(--color-gray-100);
    color: var(--color-black);
    transform: translateY(-1px);
}

.pagination .page-numbers.current {
    background: var(--color-black);
    color: var(--color-white);
    cursor: default;
}

.pagination .page-numbers.current:hover {
    transform: none;
    background: var(--color-black);
}

.pagination .page-numbers.prev,
.pagination .page-numbers.next {
    font-weight: 500;
    padding: 0 16px;
}

.pagination .page-numbers.dots {
    color: var(--color-gray-400);
    cursor: default;
    min-width: auto;
    padding: 0 8px;
}

.pagination .page-numbers.dots:hover {
    background: none;
    transform: none;
}

/* ===== 結果なしメッセージ ===== */
.no-grants-message {
    text-align: center;
    padding: 80px 20px;
    color: #666;
}

.no-results-icon {
    color: #ccc;
    margin-bottom: 20px;
}

.no-grants-message h3 {
    font-size: 24px;
    font-weight: 600;
    color: #000;
    margin: 0 0 12px 0;
}

.no-grants-message p {
    font-size: 16px;
    margin: 0 0 30px 0;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.btn-primary {
    background: #000;
    color: #fff;
    padding: 14px 28px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    display: inline-block;
    transition: background 0.3s ease;
}

.btn-primary:hover {
    background: #333;
    color: #fff;
    text-decoration: none;
}

/* ===== ページネーション ===== */
.pagination-wrapper {
    margin-top: 60px;
}

.pagination {
    display: flex;
    justify-content: center;
}

.pagination .page-numbers {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 4px;
}

.pagination .page-numbers li {
    list-style: none;
}

.pagination .page-numbers a,
.pagination .page-numbers span {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 44px;
    height: 44px;
    padding: 0 12px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    color: #333;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.pagination .page-numbers a:hover {
    border-color: #000;
    color: #000;
}

.pagination .page-numbers .current {
    background: #000;
    border-color: #000;
    color: #fff;
}

/* ===== 関連カテゴリー ===== */
.related-categories {
    padding: 60px 0;
    background: #fafafa;
}

.related-categories .section-title {
    text-align: center;
    margin-bottom: 40px;
}

.related-categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.related-category-card {
    background: #ffffff;
    border: 2px solid #f0f0f0;
    border-radius: 12px;
    padding: 20px;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all 0.3s ease;
}

.related-category-card:hover {
    border-color: #000;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    text-decoration: none;
}

.related-category-card .category-name {
    font-size: 16px;
    font-weight: 600;
    color: #000;
    margin: 0;
}

.related-category-card .category-count {
    font-size: 13px;
    color: #666;
    background: rgba(0, 0, 0, 0.05);
    padding: 4px 8px;
    border-radius: 12px;
    margin-right: 8px;
}

.arrow-icon {
    color: #666;
    transition: transform 0.3s ease;
}

.related-category-card:hover .arrow-icon {
    transform: translateX(4px);
    color: #000;
}

/* ===== SEOコンテンツ ===== */
.seo-content {
    padding: 60px 0;
    background: #ffffff;
}

.seo-text {
    max-width: 800px;
    margin: 0 auto;
    text-align: center;
}

.seo-text h2 {
    font-size: 28px;
    font-weight: 700;
    color: #000;
    margin: 0 0 30px 0;
}

.seo-text p {
    font-size: 16px;
    color: #444;
    line-height: 1.8;
    margin: 0 0 20px 0;
}

/* ===== レスポンシブ対応 ===== */
@media (max-width: 768px) {
    .category-title {
        font-size: 32px;
    }

    .category-meta {
        flex-direction: column;
        gap: 16px;
    }

    .feature-grid {
        grid-template-columns: 1fr;
    }

    .filters-wrapper {
        padding: 24px;
    }

    .region-tabs {
        justify-content: center;
    }

    .prefecture-grid {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    }

    .additional-filters {
        grid-template-columns: 1fr;
    }

    .results-header {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }

    .grants-grid[data-view="grid"] {
        grid-template-columns: 1fr;
    }

    .related-categories-grid {
        grid-template-columns: 1fr;
    }

    .seo-text h2 {
        font-size: 24px;
    }
}

@media (max-width: 480px) {
    .container {
        padding: 0 16px;
    }

    .category-title {
        font-size: 28px;
    }

    .search-input-wrapper {
        flex-direction: column;
    }

    .search-btn {
        border-radius: 0 0 12px 12px;
    }

    .prefecture-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // フィルター機能の初期化
    initializeFilters();
    
    // 検索機能の初期化
    initializeSearch();
    
    // ビュー切り替え機能
    initializeViewToggle();
    
    // 地域・都道府県フィルター
    initializePrefectureFilter();
});

function initializeFilters() {
    const statusFilter = document.getElementById('status-filter');
    const amountFilter = document.getElementById('amount-filter');
    const sortFilter = document.getElementById('sort-filter');
    const clearFiltersBtn = document.getElementById('clear-filters');
    
    [statusFilter, amountFilter, sortFilter].forEach(filter => {
        if (filter) {
            filter.addEventListener('change', applyFilters);
        }
    });
    
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', clearAllFilters);
    }
}

function initializeSearch() {
    const searchInput = document.getElementById('category-search');
    const searchBtn = document.getElementById('search-execute');
    
    if (searchInput && searchBtn) {
        searchBtn.addEventListener('click', performSearch);
        
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }
}

function initializeViewToggle() {
    const viewToggleBtns = document.querySelectorAll('.view-toggle');
    const grantsGrid = document.getElementById('grants-grid');
    
    viewToggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const view = this.dataset.view;
            
            // ボタンの状態を更新
            viewToggleBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // グリッドのビューを切り替え
            if (grantsGrid) {
                grantsGrid.setAttribute('data-view', view);
            }
        });
    });
}

function initializePrefectureFilter() {
    const regionTabs = document.querySelectorAll('.region-tab');
    const prefectureBtns = document.querySelectorAll('.prefecture-btn');
    
    // 地域タブの処理
    regionTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const region = this.dataset.region;
            
            // タブの状態を更新
            regionTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // 都道府県ボタンをフィルター
            prefectureBtns.forEach(btn => {
                if (!region || btn.dataset.region === region || !btn.dataset.prefecture) {
                    btn.style.display = 'block';
                } else {
                    btn.style.display = 'none';
                }
            });
            
            // 現在選択中の都道府県が非表示になった場合はリセット
            const activePrefBtn = document.querySelector('.prefecture-btn.active');
            if (activePrefBtn && activePrefBtn.style.display === 'none') {
                activePrefBtn.classList.remove('active');
                prefectureBtns[0].classList.add('active'); // "全て"を選択
                applyFilters();
            }
        });
    });
    
    // 都道府県ボタンの処理
    prefectureBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const prefecture = this.dataset.prefecture;
            
            // ボタンの状態を更新
            prefectureBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            applyFilters();
        });
    });
}

function applyFilters() {
    const filters = getCurrentFilters();
    
    showLoading();
    updateActiveFiltersDisplay(filters);
    
    // AJAX リクエストでフィルタリングされた結果を取得
    fetch(ajaxurl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'filter_category_grants',
            nonce: gi_ajax_nonce,
            category: document.getElementById('category-search').dataset.category,
            ...filters
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            updateGrantsGrid(data.data.html);
            updateResultsCount(data.data.total, data.data.showing_from, data.data.showing_to);
            updatePagination(data.data.pagination);
        } else {
            console.error('Filter error:', data.data.message);
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Network error:', error);
    });
}

function getCurrentFilters() {
    const activePrefecture = document.querySelector('.prefecture-btn.active');
    
    return {
        prefecture: activePrefecture ? activePrefecture.dataset.prefecture : '',
        status: document.getElementById('status-filter').value,
        amount: document.getElementById('amount-filter').value,
        sort: document.getElementById('sort-filter').value,
        search: document.getElementById('category-search').value.trim()
    };
}

function updateActiveFiltersDisplay(filters) {
    const activeFiltersDiv = document.getElementById('active-filters');
    const filterTagsDiv = document.getElementById('filter-tags');
    
    if (!activeFiltersDiv || !filterTagsDiv) return;
    
    // フィルタータグをクリア
    filterTagsDiv.innerHTML = '';
    
    let hasActiveFilters = false;
    
    // 各フィルターのタグを生成
    if (filters.prefecture) {
        const prefBtn = document.querySelector(`[data-prefecture="${filters.prefecture}"]`);
        if (prefBtn) {
            addFilterTag(filterTagsDiv, prefBtn.textContent, 'prefecture');
            hasActiveFilters = true;
        }
    }
    
    if (filters.status) {
        const statusOption = document.querySelector(`#status-filter option[value="${filters.status}"]`);
        if (statusOption) {
            addFilterTag(filterTagsDiv, `募集状況: ${statusOption.textContent}`, 'status');
            hasActiveFilters = true;
        }
    }
    
    if (filters.amount) {
        const amountOption = document.querySelector(`#amount-filter option[value="${filters.amount}"]`);
        if (amountOption) {
            addFilterTag(filterTagsDiv, `金額: ${amountOption.textContent}`, 'amount');
            hasActiveFilters = true;
        }
    }
    
    if (filters.search) {
        addFilterTag(filterTagsDiv, `検索: "${filters.search}"`, 'search');
        hasActiveFilters = true;
    }
    
    // アクティブフィルターの表示/非表示
    activeFiltersDiv.style.display = hasActiveFilters ? 'flex' : 'none';
}

function addFilterTag(container, text, type) {
    const tag = document.createElement('div');
    tag.className = 'filter-tag';
    tag.innerHTML = `
        ${text}
        <button class="remove-tag" onclick="removeFilter('${type}')">×</button>
    `;
    container.appendChild(tag);
}

function removeFilter(type) {
    switch(type) {
        case 'prefecture':
            document.querySelector('.prefecture-btn[data-prefecture=""]').click();
            break;
        case 'status':
            document.getElementById('status-filter').value = '';
            break;
        case 'amount':
            document.getElementById('amount-filter').value = '';
            break;
        case 'search':
            document.getElementById('category-search').value = '';
            break;
    }
    applyFilters();
}

function clearAllFilters() {
    // すべてのフィルターをリセット
    document.getElementById('status-filter').value = '';
    document.getElementById('amount-filter').value = '';
    document.getElementById('sort-filter').value = 'date_desc';
    document.getElementById('category-search').value = '';
    
    // 都道府県を「全て」にリセット
    document.querySelectorAll('.prefecture-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelector('.prefecture-btn[data-prefecture=""]').classList.add('active');
    
    // 地域タブを「全国」にリセット
    document.querySelectorAll('.region-tab').forEach(tab => tab.classList.remove('active'));
    document.querySelector('.region-tab[data-region=""]').classList.add('active');
    
    // 都道府県ボタンをすべて表示
    document.querySelectorAll('.prefecture-btn').forEach(btn => {
        btn.style.display = 'block';
    });
    
    applyFilters();
}

function performSearch() {
    applyFilters();
}

function showLoading() {
    const loadingSpinner = document.getElementById('loading-spinner');
    const grantsGrid = document.getElementById('grants-grid');
    
    if (loadingSpinner) loadingSpinner.style.display = 'flex';
    if (grantsGrid) grantsGrid.style.opacity = '0.5';
}

function hideLoading() {
    const loadingSpinner = document.getElementById('loading-spinner');
    const grantsGrid = document.getElementById('grants-grid');
    
    if (loadingSpinner) loadingSpinner.style.display = 'none';
    if (grantsGrid) grantsGrid.style.opacity = '1';
}

function updateGrantsGrid(html) {
    const grantsGrid = document.getElementById('grants-grid');
    if (grantsGrid) {
        grantsGrid.innerHTML = html;
    }
}

function updateResultsCount(total, from, to) {
    const totalCountEl = document.getElementById('total-count');
    const showingRangeEl = document.getElementById('showing-range');
    
    const safeTotal = parseInt(total) || 0;
    if (totalCountEl) {
        totalCountEl.textContent = safeTotal.toLocaleString() + '件';
    }
    
    if (showingRangeEl) {
        showingRangeEl.textContent = `${from}-${to}件を表示`;
    }
}

function updatePagination(paginationHtml) {
    const paginationWrapper = document.getElementById('pagination-wrapper');
    if (paginationWrapper) {
        if (paginationHtml) {
            paginationWrapper.innerHTML = paginationHtml;
            paginationWrapper.style.display = 'block';
        } else {
            paginationWrapper.style.display = 'none';
        }
    }
}

// グローバル変数（AJAXに必要）
const gi_ajax_nonce = '<?php echo wp_create_nonce('gi_ajax_nonce'); ?>';
const ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
</script>

<?php get_footer(); ?>