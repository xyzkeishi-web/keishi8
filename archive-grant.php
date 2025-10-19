<?php
/**
 * Archive Template for Grant Post Type - 完全版 v15.0
 * 
 * カテゴリ上位16件表示・開閉式その他カテゴリ・AI機能搭載
 * 
 * @package Grant_Insight_Perfect
 * @version 15.0.0
 */

get_header();

// 各種データ取得
$current_category = get_queried_object();
$is_category_archive = is_tax('grant_category');
$is_prefecture_archive = is_tax('grant_prefecture');

// タイトル・説明文
if ($is_category_archive) {
    $archive_title = $current_category->name . 'の助成金・補助金';
    $archive_description = $current_category->description ?: $current_category->name . 'に関する助成金・補助金の情報をまとめています。';
} elseif ($is_prefecture_archive) {
    $archive_title = $current_category->name . 'の助成金・補助金';
    $archive_description = $current_category->name . 'で利用できる助成金・補助金の情報をまとめています。';
} else {
    $archive_title = '助成金・補助金検索';
    $archive_description = '全国の助成金・補助金情報を検索できます。都道府県・カテゴリで絞り込んで、最適な支援制度を見つけましょう。';
}

// カテゴリデータの取得
$all_categories = get_terms([
    'taxonomy' => 'grant_category',
    'hide_empty' => false,
    'orderby' => 'count',
    'order' => 'DESC'
]);

// SEO対策データ
$current_year = date('Y');
$popular_categories = array_slice($all_categories, 0, 6); // 人気カテゴリトップ6
$current_url = home_url($_SERVER['REQUEST_URI']);

// カテゴリデータ（上位16件とその他）
$top_categories = array_slice($all_categories, 0, 16); // 上位16件
$other_categories = array_slice($all_categories, 16); // 残り

// 都道府県データ
$prefectures = gi_get_all_prefectures();

$region_groups = [
    'hokkaido' => '北海道',
    'tohoku' => '東北',
    'kanto' => '関東',
    'chubu' => '中部',
    'kinki' => '近畿',
    'chugoku' => '中国',
    'shikoku' => '四国',
    'kyushu' => '九州・沖縄'
];
?>

<main id="grant-archive" class="grant-archive-optimized">
    
    <!-- SEO構造化データ -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "CollectionPage",
        "name": "<?php echo esc_js($archive_title); ?>",
        "description": "<?php echo esc_js($archive_description); ?>",
        "url": "<?php echo esc_url($current_url); ?>",
        "provider": {
            "@type": "Organization",
            "name": "助成金インサイト",
            "url": "<?php echo esc_url(home_url()); ?>",
            "sameAs": [
                "https://twitter.com/joseikin_insight",
                "https://www.facebook.com/joseikin.insight"
            ]
        },
        "mainEntity": {
            "@type": "ItemList",
            "name": "<?php echo esc_js($archive_title); ?>",
            "description": "<?php echo esc_js($archive_description); ?>",
            "numberOfItems": "<?php echo wp_count_posts('grant')->publish; ?>",
            "itemListElement": []
        },
        "breadcrumb": {
            "@type": "BreadcrumbList",
            "itemListElement": [
                {
                    "@type": "ListItem",
                    "position": 1,
                    "name": "ホーム",
                    "item": "<?php echo esc_url(home_url()); ?>"
                },
                {
                    "@type": "ListItem",
                    "position": 2,
                    "name": "助成金・補助金検索",
                    "item": "<?php echo esc_url($current_url); ?>"
                }
            ]
        },
        "potentialAction": {
            "@type": "SearchAction",
            "target": "<?php echo esc_url(home_url('/?s={search_term_string}&post_type=grant')); ?>",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    
    <!-- ヘッダーセクション（画像付き） -->
    <section class="archive-hero-section">
        <div class="hero-background">
            <img src="https://joseikin-insight.com/wp-content/uploads/2025/10/名称未設定のデザイン-3.png" 
                 alt="助成金検索" 
                 class="hero-image">
            <div class="hero-overlay"></div>
        </div>
        
        <div class="container hero-content">
            <div class="hero-text-side">
                <h1 class="hero-title"><?php echo esc_html($archive_title); ?></h1>
                <p class="hero-description"><?php echo esc_html($archive_description); ?></p>
                
                <!-- SEO最適化コンテンツ -->
                <div class="hero-value-props">
                    <div class="value-prop-grid">
                        <div class="value-prop-item">
                            <div class="value-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 11H5a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2h-4"/>
                                    <polyline points="9 11 12 14 15 11"/>
                                    <line x1="12" y1="2" x2="12" y2="14"/>
                                </svg>
                            </div>
                            <div class="value-content">
                                <h3 class="value-title">無料で利用</h3>
                                <p class="value-desc">登録不要ですぐに検索開始</p>
                            </div>
                        </div>
                        
                        <div class="value-prop-item">
                            <div class="value-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                            </div>
                            <div class="value-content">
                                <h3 class="value-title">最新情報を毎日更新</h3>
                                <p class="value-desc"><?php echo $current_year; ?>年の新しい助成金情報</p>
                            </div>
                        </div>
                        
                        <div class="value-prop-item">
                            <div class="value-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </div>
                            <div class="value-content">
                                <h3 class="value-title">AIでかんたん検索</h3>
                                <p class="value-desc">自然語で質問して最適な助成金を発見</p>
                            </div>
                        </div>
                        
                        <div class="value-prop-item">
                            <div class="value-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                    <polyline points="7.5 4.21 12 6.81 16.5 4.21"/>
                                    <polyline points="7.5 19.79 7.5 14.6 3 12"/>
                                    <polyline points="21 12 16.5 14.6 16.5 19.79"/>
                                </svg>
                            </div>
                            <div class="value-content">
                                <h3 class="value-title">全国対応</h3>
                                <p class="value-desc">各都道府県・市町村の助成釒情報</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 人気カテゴリタグ -->
                    <div class="popular-categories-tags">
                        <span class="tags-label">人気のカテゴリ:</span>
                        <?php foreach ($popular_categories as $index => $category): ?>
                            <?php if ($index < 6): ?>
                                <a href="<?php echo get_term_link($category); ?>" class="category-tag">
                                    <?php echo esc_html($category->name); ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- フィルターセクション（新UIデザイン） -->
    <section class="filter-section-enhanced">
        <div class="container">
            
            
            
            <!-- 包括的検索セクション -->
            <div class="comprehensive-search-section">
                <h2 class="filter-section-title">
                    <svg class="title-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    助成金検索
                </h2>
                
                <!-- メイン検索バー -->
                <div class="main-search-wrapper">
                    <div class="search-input-group enhanced">
                        <svg class="search-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.35-4.35"/>
                        </svg>
                        <input type="text" 
                               id="keyword-search" 
                               class="search-input enhanced" 
                               placeholder="助成金名、実施機関、対象事業、キーワードなどを入力..."
                               autocomplete="off">
                        <button class="clear-search-btn" id="clear-search-btn" style="display: none;">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M4.646 4.646a.5.5 0 0 1 .708 0L10 9.293l4.646-4.647a.5.5 0 0 1 .708.708L10.707 10l4.647 4.646a.5.5 0 0 1-.708.708L10 10.707l-4.646 4.647a.5.5 0 0 1-.708-.708L9.293 10 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                            </svg>
                        </button>
                        <div class="search-type-selector">
                            <select id="search-type-select" class="search-type-dropdown">
                                <option value="all">全体検索</option>
                                <option value="title">助成金名</option>
                                <option value="organization">実施機関</option>
                                <option value="target">対象者</option>
                                <option value="content">内容</option>
                            </select>
                        </div>
                        <button class="search-btn enhanced" id="search-btn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.35-4.35"/>
                            </svg>
                            検索
                        </button>
                    </div>
                    
                    <!-- 検索候補ドロップダウン -->
                    <div class="search-suggestions-dropdown" id="search-suggestions" style="display: none;">
                        <div class="suggestions-content">
                            <!-- 検索候補がここに動的に表示される -->
                        </div>
                    </div>
                </div>
                
                <!-- クイック検索フィルター -->
                <div class="quick-filters-row">
                    <div class="quick-filter-group">
                        <label class="quick-filter-label">クイック検索:</label>
                        <button class="quick-filter-btn" data-filter="active" data-type="status">募集中</button>
                        <button class="quick-filter-btn" data-filter="high-amount" data-type="amount">高額助成金</button>
                        <button class="quick-filter-btn" data-filter="startup" data-type="target">スタートアップ向け</button>
                        <button class="quick-filter-btn" data-filter="it" data-type="category">IT・デジタル</button>
                        <button class="quick-filter-btn" data-filter="manufacturing" data-type="category">ものづくり</button>
                    </div>
                </div>
            </div>
            

            
            <!-- モバイル用フィルタートグルボタン -->
            <button class="mobile-filter-toggle" id="mobile-filter-toggle">
                <span>フィルターで絞り込む</span>
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/>
                </svg>
            </button>
            
            <!-- メインフィルターグリッド -->
            <div class="main-filters-grid mobile-filter-content" id="main-filters-content">
                
                <!-- カテゴリフィルターボックス -->
                <div class="filter-box category-filter-box" role="region" aria-label="カテゴリ絞り込みフィルター">
                    <div class="filter-box-header">
                        <div class="filter-box-title">
                            <svg class="filter-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                            </svg>
                            <span>カテゴリで絞り込む（複数選択可）</span>
                        </div>
                        <div class="filter-status" id="category-filter-status" aria-live="polite">すべて</div>
                    </div>
                    <div class="filter-box-content category-filter-section" role="group" aria-labelledby="category-filter-status">
                
                        <!-- メインカテゴリ（上位16件・大きめ） -->
                        <div class="category-buttons-main">
                            <button class="category-btn active" data-category="" data-filter-type="category" aria-pressed="true" aria-describedby="category-filter-status">
                                <span class="btn-text">すべて</span>
                            </button>
                            <?php foreach ($top_categories as $category): ?>
                                <button class="category-btn" data-category="<?php echo esc_attr($category->slug); ?>" data-filter-type="category" aria-pressed="false" aria-describedby="category-filter-status">
                                    <span class="btn-text"><?php echo esc_html($category->name); ?></span>
                                    <span class="btn-count"><?php echo $category->count; ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                
                        
                        <!-- その他カテゴリ（開閉式・小さめ） -->
                        <?php if (!empty($other_categories)): ?>
                        <div class="category-other-wrapper">
                            <button class="toggle-other-categories-btn" id="toggle-other-categories">
                                <svg class="toggle-icon" width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                    <path d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/>
                                </svg>
                                <span class="toggle-text">その他のカテゴリを表示</span>
                                <span class="category-count-badge"><?php echo count($other_categories); ?>件</span>
                            </button>
                            
                            <div class="category-other-section" id="other-categories-section" style="display: none;">
                                <div class="category-buttons-other">
                                    <?php foreach ($other_categories as $category): ?>
                                        <button class="category-btn small" data-category="<?php echo esc_attr($category->slug); ?>" data-filter-type="category">
                                            <span class="btn-text"><?php echo esc_html($category->name); ?></span>
                                            <span class="btn-count"><?php echo $category->count; ?></span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            
                
                <!-- 都道府県フィルターボックス -->
                <div class="filter-box prefecture-filter-box" role="region" aria-label="都道府県絞り込みフィルター">
                    <div class="filter-box-header">
                        <div class="filter-box-title">
                            <svg class="filter-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span>都道府県で絞り込む（複数選択可）</span>
                        </div>
                        <div class="filter-status" id="prefecture-filter-status" aria-live="polite">全国</div>
                    </div>
                    <div class="filter-box-content prefecture-filter-section" role="group" aria-labelledby="prefecture-filter-status">
                
                        <!-- 地域タブ -->
                        <div class="region-tabs">
                            <button class="region-tab active" data-region="" data-filter-type="region">全国</button>
                            <?php foreach ($region_groups as $region_slug => $region_name): ?>
                                <button class="region-tab" data-region="<?php echo esc_attr($region_slug); ?>" data-filter-type="region">
                                    <?php echo esc_html($region_name); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                
                        
                        <!-- 都道府県横スクロールバー -->
                        <div class="prefecture-scroll-container">
                            <button class="scroll-arrow scroll-left" id="scroll-left" disabled>
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                    <path d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                                </svg>
                            </button>
                            
                            <div class="prefecture-scroll-bar" id="prefecture-bar">
                                <button class="prefecture-btn active" data-prefecture="" data-region="" data-filter-type="prefecture" aria-pressed="true" aria-describedby="prefecture-filter-status">
                                    すべて
                                </button>
                                <?php foreach ($prefectures as $pref): ?>
                                    <button class="prefecture-btn" 
                                            data-prefecture="<?php echo esc_attr($pref['slug']); ?>"
                                            data-region="<?php echo esc_attr($pref['region']); ?>"
                                            data-filter-type="prefecture"
                                            aria-pressed="false"
                                            aria-describedby="prefecture-filter-status">
                                        <?php echo esc_html($pref['name']); ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                            
                            <button class="scroll-arrow scroll-right" id="scroll-right">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                    <path d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            
                
                <!-- 市町村フィルターボックス（展開式リスト） -->
                <div class="filter-box municipality-filter-box" id="municipality-filter-section" style="display: none;">
                    <div class="filter-box-header">
                        <div class="filter-box-title">
                            <svg class="filter-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 9v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9"/>
                                <path d="M9 22V12h6v10M2 10.6L12 2l10 8.6"/>
                            </svg>
                            <span>市町村で絞り込む</span>
                            <span class="selected-prefecture-name" id="selected-prefecture-name"></span>
                        </div>
                        <div class="filter-status" id="municipality-filter-status">すべて</div>
                    </div>
                    <div class="filter-box-content municipality-filter-section">
                
                        <!-- メイン市町村（上位・大きめ） -->
                        <div class="municipality-buttons-main">
                            <button class="municipality-btn active" data-municipality="" data-filter-type="municipality">
                                <span class="btn-text">すべて</span>
                            </button>
                            <!-- 市町村ボタンは JavaScript で動的に読み込み -->
                        </div>
                
                        
                        <!-- その他市町村（開閉式） -->
                        <div class="municipality-other-wrapper" id="municipality-other-wrapper" style="display: none;">
                            <button class="toggle-other-municipalities-btn" id="toggle-other-municipalities">
                                <svg class="toggle-icon" width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                    <path d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/>
                                </svg>
                                <span class="toggle-text">その他の市町村を表示</span>
                                <span class="municipality-count-badge" id="municipality-count-badge">0件</span>
                            </button>
                            
                            <div class="municipality-other-section" id="other-municipalities-section" style="display: none;">
                                <div class="municipality-buttons-other" id="municipality-buttons-other">
                                    <!-- その他の市町村ボタンがここに動的に読み込まれる -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            
            </div>
            <!-- /main-filters-grid -->

            
            <!-- 詳細フィルターボックス -->
            <div class="filter-box advanced-filters-box">
                <div class="filter-box-header clickable" id="toggle-advanced-btn">
                    <div class="filter-box-title">
                        <svg class="filter-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M12 1v6m0 6v6"/>
                            <path d="m21 12-6-3 6-3"/>
                            <path d="m3 12 6-3-6-3"/>
                        </svg>
                        <span>詳細条件で絞り込む</span>
                    </div>
                    <svg class="toggle-icon" width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/>
                    </svg>
                </div>
                
                <div class="filter-box-content advanced-filters-content" id="advanced-filters" style="display: none;">
                    <div class="advanced-filter-grid">
                        <!-- 金額範囲 -->
                        <div class="advanced-filter-item">
                            <label class="advanced-filter-label">
                                <svg class="filter-item-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="1" x2="12" y2="23"/>
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                </svg>
                                助成金額
                            </label>
                            <select id="amount-filter" class="advanced-filter-select">
                                <option value="">指定なし</option>
                                <option value="0-100">〜100万円</option>
                                <option value="100-500">100万円〜500万円</option>
                                <option value="500-1000">500万円〜1000万円</option>
                                <option value="1000-3000">1000万円〜3000万円</option>
                                <option value="3000+">3000万円以上</option>
                            </select>
                        </div>
                        
                        <!-- ステータス -->
                        <div class="advanced-filter-item">
                            <label class="advanced-filter-label">
                                <svg class="filter-item-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                                募集状況
                            </label>
                            <select id="status-filter" class="advanced-filter-select">
                                <option value="">すべて</option>
                                <option value="active">募集中</option>
                                <option value="upcoming">募集予定</option>
                                <option value="closed">募集終了</option>
                            </select>
                        </div>
                        
                        <!-- 難易度 -->
                        <div class="advanced-filter-item">
                            <label class="advanced-filter-label">
                                <svg class="filter-item-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="20" x2="18" y2="10"/>
                                    <line x1="12" y1="20" x2="12" y2="4"/>
                                    <line x1="6" y1="20" x2="6" y2="14"/>
                                </svg>
                                申請難易度
                            </label>
                            <select id="difficulty-filter" class="advanced-filter-select">
                                <option value="">指定なし</option>
                                <option value="easy">易しい</option>
                                <option value="normal">普通</option>
                                <option value="hard">難しい</option>
                            </select>
                        </div>
                        
                        <!-- ソート -->
                        <div class="advanced-filter-item">
                            <label class="advanced-filter-label">
                                <svg class="filter-item-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                </svg>
                                並び順
                            </label>
                            <select id="sort-filter" class="advanced-filter-select">
                                <option value="date_desc">新着順</option>
                                <option value="amount_desc">金額が高い順</option>
                                <option value="deadline_asc">締切が近い順</option>
                                <option value="featured_first">注目順</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="filter-actions">
                        <button class="btn-reset-filters" id="reset-filters-btn">
                            <svg class="btn-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="1 4 1 10 7 10"/>
                                <polyline points="23 20 23 14 17 14"/>
                                <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"/>
                            </svg>
                            フィルターをクリア
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- 選択中のフィルター表示 -->
            <div class="active-filters-display" id="active-filters" style="display: none;">
                <span class="active-filters-label">選択中:</span>
                <div class="active-filter-tags" id="active-filter-tags"></div>
                <button class="clear-all-filters" id="clear-all-filters">すべてクリア</button>
            </div>
        </div>
    </section>
    
    <!-- 検索結果セクション -->
    <section class="results-section-optimized">
        <div class="container">
            
            <!-- 結果ヘッダー -->
            <div class="results-header">
                <div class="results-info">
                    <span class="results-count">
                        <strong id="current-count">0</strong>件の助成金
                    </span>
                    <span class="results-showing">
                        （<span id="showing-from">1</span>〜<span id="showing-to">12</span>件を表示）
                    </span>
                </div>
                
                <!-- 表示切替 -->
                <div class="view-toggle">
                    <button class="view-btn active" data-view="grid" title="グリッド表示">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <rect x="2" y="2" width="7" height="7"/>
                            <rect x="11" y="2" width="7" height="7"/>
                            <rect x="2" y="11" width="7" height="7"/>
                            <rect x="11" y="11" width="7" height="7"/>
                        </svg>
                    </button>
                    <button class="view-btn" data-view="list" title="リスト表示">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <rect x="2" y="3" width="16" height="2"/>
                            <rect x="2" y="9" width="16" height="2"/>
                            <rect x="2" y="15" width="16" height="2"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- ローディング表示 -->
            <div class="loading-overlay" id="loading-overlay" style="display: none;">
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <p class="loading-text">読み込み中...</p>
                </div>
            </div>
            
            <!-- 助成金カード表示エリア -->
            <div class="grants-grid-optimized" id="grants-container" data-view="grid">
                <?php
                // 初期表示用のグラント取得
                $initial_grants_query = new WP_Query([
                    'post_type' => 'grant',
                    'posts_per_page' => 12,
                    'post_status' => 'publish',
                    'orderby' => 'date',
                    'order' => 'DESC'
                ]);
                
                if ($initial_grants_query->have_posts()) :
                    while ($initial_grants_query->have_posts()) : 
                        $initial_grants_query->the_post();
                        // グラントカードテンプレートを読み込み
                        get_template_part('template-parts/grant-card-unified');
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </div>
            
            <!-- 結果なし表示 -->
            <div class="no-results" id="no-results" style="display: none;">
                <svg class="no-results-icon" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.35-4.35"/>
                </svg>
                <h3 class="no-results-title">該当する助成金が見つかりませんでした</h3>
                <p class="no-results-message">
                    検索条件を変更して再度お試しください。<br>
                    または、<button class="link-btn" id="reset-all-filters-no-results">すべてのフィルターをクリア</button>してください。
                </p>
            </div>
            
            <!-- ページネーション -->
            <div class="pagination-wrapper" id="pagination-wrapper" style="display: none;">
                <nav class="pagination" id="pagination"></nav>
            </div>
        </div>
    </section>
    
</main>

<!-- スタイル（完全版） -->
<style>
/* ===== 基本設定 ===== */
.grant-archive-optimized {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Noto Sans JP', sans-serif;
    color: #1a1a1a;
    background: #ffffff;
    min-height: 100vh;
}

.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px;
}

/* ===== ヘッダーセクション（画像付き） ===== */
.archive-hero-section {
    position: relative;
    min-height: 450px;
    background: #ffffff;
    overflow: hidden;
    margin-bottom: 0;
}

.hero-background {
    position: absolute;
    top: 0;
    right: 0;
    width: 50%;
    height: 100%;
    z-index: 1;
}

.hero-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    object-position: center;
}


.hero-content {
    position: relative;
    z-index: 2;
    padding: 80px 20px;
    display: flex;
    align-items: center;
    min-height: 450px;
}

.hero-text-side {
    max-width: 600px;
}

.hero-title {
    font-size: 48px;
    font-weight: 700;
    color: #000000;
    margin: 0 0 20px 0;
    line-height: 1.2;
    letter-spacing: -0.02em;
}

.hero-description {
    font-size: 18px;
    color: #333333;
    line-height: 1.7;
    margin: 0 0 40px 0;
}

/* ===== SEO最適化バリュープロポジション ===== */
.hero-value-props {
    margin-top: 30px;
}

.value-prop-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.value-prop-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 24px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.value-prop-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: #fbbf24;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
}

.value-prop-item:hover {
    border-color: #fbbf24;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.value-prop-item:hover::before {
    transform: translateX(0);
}

.value-icon {
    color: #374151;
    flex-shrink: 0;
    padding: 8px;
    background: #f9fafb;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.value-prop-item:hover .value-icon {
    color: #fbbf24;
    background: rgba(251, 191, 36, 0.1);
}

.value-content {
    flex: 1;
}

.value-title {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 8px 0;
    line-height: 1.3;
}

.value-desc {
    font-size: 13px;
    color: #6b7280;
    font-weight: 400;
    margin: 0;
    line-height: 1.4;
}

/* ===== 人気カテゴリタグ ===== */
.popular-categories-tags {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 12px;
    padding: 20px;
    background: rgba(0, 0, 0, 0.03);
    border-radius: 10px;
    border: 1px solid #e5e7eb;
}

.tags-label {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    white-space: nowrap;
}

.category-tag {
    display: inline-flex;
    align-items: center;
    padding: 6px 14px;
    background: #ffffff;
    color: #374151;
    text-decoration: none;
    border: 1px solid #d1d5db;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.category-tag:hover {
    background: #fbbf24;
    color: #000000;
    border-color: #fbbf24;
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(251, 191, 36, 0.3);
}

/* ===== 新フィルターセクション（ボックス化UI） ===== */
.filter-section-enhanced {
    background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 50%, #f0f4ff 100%);
    padding: 50px 0;
    border-bottom: 3px solid #e2e8f0;
    position: relative;
}

.filter-section-enhanced::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: #fbbf24;
}

/* ===== メインフィルターグリッド ===== */
.main-filters-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 50px;
    margin-bottom: 30px;
}

/* ===== フィルターボックス基本スタイル ===== */
.filter-box {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.filter-box.has-selections {
    border-color: #fbbf24;
    box-shadow: 0 2px 8px rgba(251, 191, 36, 0.15);
}

.filter-box::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: #fbbf24;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.filter-box:hover {
    border-color: #fbbf24;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transform: translateY(-1px);
}

.filter-box:hover::before {
    opacity: 1;
}

/* ===== モバイル用フィルタートグル ===== */
.mobile-filter-toggle {
    display: none;
    width: 100%;
    padding: 16px 20px;
    background: #000000;
    color: #ffffff;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    margin-bottom: 20px;
    position: relative;
    overflow: hidden;
}

.mobile-filter-toggle::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(251, 191, 36, 0.3), transparent);
    transition: left 0.5s ease;
}

.mobile-filter-toggle:hover::before {
    left: 100%;
}

.mobile-filter-toggle:hover {
    background: #1a1a1a;
}

.mobile-filter-content {
    display: block;
}

@media (max-width: 768px) {
    .mobile-filter-toggle {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .mobile-filter-content {
        display: none;
    }
    
    .mobile-filter-content.active {
        display: block;
        animation: slideDown 0.3s ease;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
}

/* ===== フィルターボックスヘッダー ===== */
.filter-box-header {
    padding: 20px 24px;
    border-bottom: 1px solid #f3f4f6;
    background: #ffffff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
}

.filter-box-header.clickable {
    cursor: pointer;
    transition: background 0.3s ease;
}

.filter-box-header.clickable:hover {
    background: #f9fafb;
}

.filter-box-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 16px;
    font-weight: 600;
    color: #111827;
}

.filter-icon {
    color: #374151;
    transition: color 0.3s ease;
}

.filter-box:hover .filter-icon {
    color: #fbbf24;
}

.filter-status {
    background: #000000;
    color: #ffffff;
    padding: 6px 14px;
    border-radius: 16px;
    font-size: 12px;
    font-weight: 500;
    min-width: 60px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.filter-status::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: #fbbf24;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.filter-status.active {
    background: #fbbf24;
    color: #000000;
    font-weight: 600;
}

.filter-status.active::before {
    opacity: 1;
}

/* ===== フィルターボックスコンテンツ ===== */
.filter-box-content {
    padding: 28px;
    background: #ffffff;
}

.filter-note {
    font-size: 13px;
    color: #666666;
    font-weight: 400;
    margin-left: 8px;
}

/* ===== AI検索バー ===== */
.ai-search-wrapper {
    margin-bottom: 30px;
    padding: 30px;
    background: #fbbf24;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
}

.ai-search-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}

.ai-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 600;
    backdrop-filter: blur(10px);
}

.ai-icon {
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-5px); }
}

.ai-description {
    color: rgba(255, 255, 255, 0.9);
    font-size: 14px;
}

.ai-input-group {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    background: #ffffff;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.ai-search-icon {
    color: #fbbf24;
    margin-top: 8px;
    flex-shrink: 0;
}

.ai-search-input {
    flex: 1;
    border: none;
    padding: 8px;
    font-size: 15px;
    background: transparent;
    outline: none;
    resize: none;
    min-height: 60px;
    font-family: inherit;
}

.ai-search-btn {
    background: #fbbf24;
    color: #ffffff;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    white-space: nowrap;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.ai-search-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
}

.ai-search-btn .btn-icon {
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

/* AI検索結果エリア */
.ai-results-area {
    margin-top: 20px;
    background: #ffffff;
    border-radius: 12px;
    padding: 20px;
}

.ai-thinking {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #fbbf24;
    font-size: 14px;
    font-weight: 500;
}

.thinking-spinner {
    width: 20px;
    height: 20px;
    border: 3px solid rgba(102, 126, 234, 0.2);
    border-top-color: #fbbf24;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.ai-response {
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.ai-response-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.response-icon {
    color: #fbbf24;
}

.ai-response-header span {
    font-size: 16px;
    font-weight: 600;
    color: #1a1a1a;
    flex: 1;
}

.close-ai-btn {
    background: transparent;
    border: none;
    color: #666666;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.close-ai-btn:hover {
    background: #f0f0f0;
    color: #000000;
}

.ai-response-content {
    color: #333333;
    line-height: 1.7;
    margin-bottom: 20px;
}

.ai-response-content p {
    margin: 0 0 12px 0;
}

.ai-response-content strong {
    color: #fbbf24;
    font-weight: 600;
}

.apply-ai-filters-btn {
    background: #fbbf24;
    color: #000000;
    border: 2px solid #fbbf24;
    padding: 14px 28px;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    width: 100%;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(251, 191, 36, 0.3);
}

.apply-ai-filters-btn:hover {
    background: #000000;
    color: #fbbf24;
    border-color: #000000;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

/* AI検索例 */
.ai-examples {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 15px;
    align-items: center;
}

.examples-label {
    color: rgba(255, 255, 255, 0.8);
    font-size: 13px;
    font-weight: 500;
}

.example-btn {
    background: rgba(255, 255, 255, 0.15);
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.3);
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
    backdrop-filter: blur(10px);
}

.example-btn:hover {
    background: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.5);
    transform: translateY(-2px);
}

/* ===== 包括的検索セクション ===== */
.comprehensive-search-section {
    margin-bottom: 30px;
    padding: 30px;
    background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
    border-radius: 16px;
    border: 2px solid #e8ebf7;
}

.main-search-wrapper {
    position: relative;
    margin-bottom: 20px;
}

.search-input-group.enhanced {
    position: relative;
    display: flex;
    align-items: center;
    max-width: 100%;
    background: #ffffff;
    border: 2px solid #d1d5db;
    border-radius: 12px;
    padding: 6px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.search-input-group.enhanced:focus-within {
    border-color: #000000;
    box-shadow: 0 0 0 4px rgba(0, 0, 0, 0.1);
}

.search-input.enhanced {
    flex: 1;
    border: none;
    padding: 14px 16px;
    font-size: 16px;
    background: transparent;
    outline: none;
    min-width: 0;
}

.search-type-selector {
    margin-right: 8px;
}

.search-type-dropdown {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 10px 12px;
    font-size: 13px;
    font-weight: 500;
    color: #495057;
    cursor: pointer;
    outline: none;
    min-width: 100px;
}

.search-type-dropdown:hover {
    background: #e9ecef;
}

.search-btn.enhanced {
    background: #000000;
    color: #ffffff;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.search-btn.enhanced:hover {
    background: #1f2937;
    transform: translateY(-1px);
}

/* 検索候補ドロップダウン */
.search-suggestions-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #ffffff;
    border: 2px solid #e5e7eb;
    border-top: none;
    border-radius: 0 0 12px 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    z-index: 1000;
    max-height: 300px;
    overflow-y: auto;
}

.suggestions-content {
    padding: 12px 0;
}

.suggestion-item {
    padding: 12px 20px;
    cursor: pointer;
    transition: all 0.2s ease;
    border-bottom: 1px solid #f3f4f6;
}

.suggestion-item:hover {
    background: #f8f9fa;
}

.suggestion-item:last-child {
    border-bottom: none;
}

/* クイック検索フィルター */
.quick-filters-row {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
}

.quick-filter-group {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.quick-filter-label {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-right: 8px;
}

.quick-filter-btn {
    background: #ffffff;
    border: 2px solid #e5e7eb;
    color: #374151;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.quick-filter-btn:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
}

.quick-filter-btn.active {
    background: #000000;
    color: #ffffff;
    border-color: #000000;
}



/* 旧スタイルとの互換性 */
.search-filter-wrapper {
    margin-bottom: 40px;
}

.search-input-group {
    position: relative;
    display: flex;
    align-items: center;
    max-width: 800px;
    background: #ffffff;
    border: 2px solid #e0e0e0;
    border-radius: 50px;
    padding: 4px 4px 4px 20px;
    transition: all 0.3s ease;
}

.search-input-group:focus-within {
    border-color: #000000;
    box-shadow: 0 0 0 4px rgba(0, 0, 0, 0.1);
}

.search-icon {
    color: #666666;
    margin-right: 10px;
    flex-shrink: 0;
}

.search-input {
    flex: 1;
    border: none;
    padding: 14px 10px;
    font-size: 15px;
    background: transparent;
    outline: none;
}

.clear-search-btn {
    background: transparent;
    border: none;
    color: #666666;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    margin-right: 8px;
}

.clear-search-btn:hover {
    background: #f0f0f0;
    color: #000000;
}

.search-btn {
    background: #000000;
    color: #ffffff;
    border: none;
    padding: 12px 32px;
    border-radius: 50px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.search-btn:hover {
    background: #2d2d2d;
}

/* ===== カテゴリフィルター（ボックス内） ===== */
.category-filter-section {
    /* ボックス内に移動したのでスタイル簡略化 */
}

/* メインカテゴリ（上位16件・大きめ） */
.category-buttons-main {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 24px;
}

.category-btn {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    color: #374151;
    padding: 12px 18px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    position: relative;
    overflow: hidden;
    min-height: 44px;
}

.category-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(251, 191, 36, 0.1), transparent);
    transition: left 0.5s ease;
}

.category-btn:hover::before {
    left: 100%;
}

.category-btn:hover {
    background: #f9fafb;
    border-color: #fbbf24;
    color: #111827;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.category-btn.active {
    background: #fbbf24;
    color: #000000;
    border-color: #fbbf24;
    box-shadow: 0 4px 12px rgba(251, 191, 36, 0.4);
    transform: translateY(-2px);
    font-weight: 600;
    position: relative;
}

.category-btn.active::after {
    content: "✓";
    position: absolute;
    top: -5px;
    right: -5px;
    width: 20px;
    height: 20px;
    background: #000000;
    color: #fbbf24;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.btn-text {
    font-weight: 600;
    flex: 1;
}

.btn-count {
    font-size: 11px;
    background: #f3f4f6;
    color: #6b7280;
    padding: 4px 8px;
    border-radius: 8px;
    font-weight: 500;
    min-width: 28px;
    text-align: center;
    line-height: 1;
}

.category-btn:hover .btn-count {
    background: rgba(251, 191, 36, 0.2);
    color: #92400e;
}

.category-btn.active .btn-count {
    background: #000000;
    color: #fbbf24;
}

/* その他カテゴリ開閉ボタン */
.category-other-wrapper {
    margin-top: 20px;
}

.toggle-other-categories-btn {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border: 2px solid #cbd5e0;
    color: #475569;
    padding: 14px 24px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    width: 100%;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.toggle-other-categories-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    transition: left 0.5s ease;
}

.toggle-other-categories-btn:hover::before {
    left: 100%;
}

.toggle-other-categories-btn:hover {
    background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
    border-color: #94a3b8;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
}

.toggle-other-categories-btn.active {
    background: #fbbf24;
    color: #000000;
    border-color: #fbbf24;
    box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);
}

.toggle-icon {
    transition: transform 0.3s ease;
}

.toggle-other-categories-btn.active .toggle-icon {
    transform: rotate(180deg);
}

.category-count-badge {
    background: #e0e0e0;
    color: #666666;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    margin-left: auto;
}

.toggle-other-categories-btn.active .category-count-badge {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
}

/* その他カテゴリセクション（開閉式） */
.category-other-section {
    margin-top: 15px;
    padding: 20px;
    background: #f8f8f8;
    border-radius: 12px;
    border: 1px solid #e0e0e0;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* その他カテゴリボタン（小さめ） */
.category-buttons-other {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.category-btn.small {
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 500;
    border-width: 2px;
    min-height: 40px;
}

.category-btn.small .btn-text {
    font-weight: 500;
}

.category-btn.small .btn-count {
    font-size: 10px;
    padding: 3px 6px;
    min-width: 24px;
}

.category-btn.small:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* ===== 都道府県フィルター（ボックス内） ===== */
.prefecture-filter-section {
    /* ボックス内に移動したのでスタイル簡略化 */
}

.region-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 24px;
    overflow-x: auto;
    padding: 4px;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}

.region-tab {
    background: transparent;
    border: none;
    color: #64748b;
    padding: 10px 18px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    position: relative;
}

.region-tab:hover {
    background: rgba(102, 126, 234, 0.1);
    color: #fbbf24;
    transform: translateY(-1px);
}

.region-tab.active {
    background: #fbbf24;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    transform: translateY(-1px);
}

.prefecture-scroll-container {
    position: relative;
    display: flex;
    align-items: center;
    gap: 12px;
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    padding: 20px;
    border-radius: 16px;
    border: 2px solid #e2e8f0;
}

.scroll-arrow {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: 2px solid #e2e8f0;
    color: #64748b;
    width: 44px;
    height: 44px;
    border-radius: 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    flex-shrink: 0;
}

.scroll-arrow:hover:not(:disabled) {
    background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e0 100%);
    border-color: #94a3b8;
    color: #475569;
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.scroll-arrow:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    background: #f1f5f9;
}

.prefecture-scroll-bar {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    scroll-behavior: smooth;
    padding: 5px 0;
    flex: 1;
}

.prefecture-scroll-bar::-webkit-scrollbar {
    height: 6px;
}

.prefecture-scroll-bar::-webkit-scrollbar-track {
    background: #f0f0f0;
    border-radius: 3px;
}

.prefecture-scroll-bar::-webkit-scrollbar-thumb {
    background: #cccccc;
    border-radius: 3px;
}

.prefecture-btn {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    color: #374151;
    padding: 10px 16px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
    flex-shrink: 0;
    min-height: 38px;
    display: flex;
    align-items: center;
}

.prefecture-btn:hover {
    background: #f9fafb;
    border-color: #fbbf24;
    color: #111827;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.prefecture-btn.active {
    background: #fbbf24;
    color: #000000;
    border-color: #fbbf24;
    transform: translateY(-1px);
    box-shadow: 0 3px 10px rgba(251, 191, 36, 0.4);
    font-weight: 600;
    position: relative;
}

.prefecture-btn.active::after {
    content: "✓";
    position: absolute;
    top: -5px;
    right: -5px;
    width: 16px;
    height: 16px;
    background: #000000;
    color: #fbbf24;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: bold;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

/* ===== 市町村フィルター（展開式リスト） ===== */
.municipality-filter-section {
    /* ボックス内に移動したのでスタイル簡略化 */
}

/* メイン市町村ボタン（上位・大きめ） */
.municipality-buttons-main {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 20px;
}

.municipality-btn {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    color: #374151;
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    position: relative;
    overflow: hidden;
    min-height: 42px;
}

.municipality-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(251, 191, 36, 0.1), transparent);
    transition: left 0.5s ease;
}

.municipality-btn:hover::before {
    left: 100%;
}

.municipality-btn:hover {
    background: #f9fafb;
    border-color: #fbbf24;
    color: #111827;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.municipality-btn.active {
    background: #fbbf24;
    color: #000000;
    border-color: #fbbf24;
    box-shadow: 0 4px 12px rgba(251, 191, 36, 0.4);
    transform: translateY(-1px);
    font-weight: 600;
}

.municipality-btn .btn-text {
    font-weight: 600;
}

.municipality-btn .btn-count {
    font-size: 12px;
    opacity: 0.7;
    background: rgba(0, 0, 0, 0.1);
    padding: 2px 8px;
    border-radius: 12px;
    font-weight: 500;
}

.municipality-btn.active .btn-count {
    background: rgba(255, 255, 255, 0.2);
}

/* その他市町村開閉ボタン */
.municipality-other-wrapper {
    margin-top: 20px;
}

.toggle-other-municipalities-btn {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border: 2px solid #cbd5e0;
    color: #475569;
    padding: 14px 24px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    width: 100%;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.toggle-other-municipalities-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    transition: left 0.5s ease;
}

.toggle-other-municipalities-btn:hover::before {
    left: 100%;
}

.toggle-other-municipalities-btn:hover {
    background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
    border-color: #94a3b8;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
}

.toggle-other-municipalities-btn.active {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #ffffff;
    border-color: #2563eb;
    box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
}

.toggle-other-municipalities-btn .toggle-icon {
    transition: transform 0.3s ease;
}

.toggle-other-municipalities-btn.active .toggle-icon {
    transform: rotate(180deg);
}

.municipality-count-badge {
    background: rgba(71, 85, 105, 0.15);
    color: #475569;
    padding: 4px 10px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 700;
    margin-left: auto;
    min-width: 32px;
    text-align: center;
}

.toggle-other-municipalities-btn:hover .municipality-count-badge {
    background: rgba(51, 65, 85, 0.2);
    color: #334155;
}

.toggle-other-municipalities-btn.active .municipality-count-badge {
    background: rgba(255, 255, 255, 0.25);
    color: #ffffff;
}

/* その他市町村セクション（開閉式） */
.municipality-other-section {
    margin-top: 15px;
    padding: 20px;
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e0e0e0;
    animation: slideDown 0.3s ease;
}

/* その他市町村ボタン（小さめ） */
.municipality-buttons-other {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.municipality-btn.small {
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 500;
    border-width: 1px;
}

.municipality-btn.small .btn-text {
    font-weight: 500;
}

.municipality-btn.small .btn-count {
    font-size: 11px;
    padding: 1px 6px;
}

.municipality-btn.small:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}

.selected-prefecture-name {
    font-size: 12px;
    color: #666;
    margin-left: 8px;
}

/* ===== 詳細フィルター（ボックス化） ===== */
.advanced-filters-box {
    margin-top: 0; /* グリッド内で管理 */
}

.toggle-icon {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    color: #64748b;
}

.filter-box-header.active .toggle-icon {
    transform: rotate(180deg);
    color: #fbbf24;
}

.advanced-filters-content {
    background: #ffffff;
    animation: slideDown 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.advanced-filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 24px;
    margin-bottom: 24px;
}

.advanced-filter-item {
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s ease;
}

.advanced-filter-item:hover {
    border-color: #cbd5e0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transform: translateY(-2px);
}

.advanced-filter-label {
    font-size: 14px;
    font-weight: 700;
    color: #334155;
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}

.filter-item-icon {
    color: #fbbf24;
}

.advanced-filter-select {
    width: 100%;
    background: #ffffff;
    border: 2px solid #e2e8f0;
    color: #475569;
    padding: 14px 16px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23fbbf24' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 12px center;
    background-repeat: no-repeat;
    background-size: 16px;
    padding-right: 40px;
}

.advanced-filter-select:hover {
    border-color: #cbd5e0;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15);
}

.advanced-filter-select:focus {
    outline: none;
    border-color: #fbbf24;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.filter-actions {
    padding-top: 24px;
    border-top: 2px solid #f1f5f9;
    display: flex;
    justify-content: center;
}

.btn-reset-filters {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    border: 2px solid #ef4444;
    color: #ffffff;
    padding: 14px 28px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.btn-reset-filters:hover {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    border-color: #dc2626;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
}

.btn-icon {
    transition: transform 0.3s ease;
}

.btn-reset-filters:hover .btn-icon {
    transform: rotate(180deg);
}

/* ===== 選択中のフィルター ===== */
.active-filters-display {
    margin-top: 20px;
    padding: 15px 20px;
    background: #ffffff;
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.active-filters-label {
    font-size: 13px;
    font-weight: 600;
    color: #666666;
}

.active-filter-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    flex: 1;
}

.filter-tag {
    background: #000000;
    color: #ffffff;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.filter-tag-remove {
    background: transparent;
    border: none;
    color: #ffffff;
    cursor: pointer;
    font-size: 16px;
    padding: 0;
    opacity: 0.8;
    transition: opacity 0.2s ease;
}

.filter-tag-remove:hover {
    opacity: 1;
}

.clear-all-filters {
    background: #dc2626;
    color: #ffffff;
    border: none;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.clear-all-filters:hover {
    background: #b91c1c;
}

/* ===== 検索結果セクション ===== */
.results-section-optimized {
    padding: 40px 0 80px;
    background: #f8f8f8;
}

.results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding: 20px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.results-count strong {
    font-size: 28px;
    font-weight: 700;
    color: #000000;
}

.view-toggle {
    display: flex;
    gap: 5px;
    background: #f8f8f8;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 4px;
}

.view-btn {
    background: transparent;
    border: none;
    color: #666666;
    width: 40px;
    height: 40px;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.view-btn:hover {
    background: #ffffff;
    color: #333333;
}

.view-btn.active {
    background: #000000;
    color: #ffffff;
}

.grants-grid-optimized {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 24px;
    min-height: 400px;
}

.grants-grid-optimized[data-view="list"] {
    grid-template-columns: 1fr;
}

/* ===== ローディング ===== */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.95);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #e0e0e0;
    border-top-color: #000000;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

/* ===== 結果なし ===== */
.no-results {
    text-align: center;
    padding: 60px 20px;
    background: #ffffff;
    border-radius: 12px;
    border: 2px dashed #e0e0e0;
}

.no-results-icon {
    color: #cccccc;
    margin-bottom: 20px;
}

.link-btn {
    background: transparent;
    border: none;
    color: #000000;
    text-decoration: underline;
    cursor: pointer;
    font-size: inherit;
}

/* ===== ページネーション ===== */
.pagination-wrapper {
    margin-top: 40px;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    flex-wrap: wrap;
}

.pagination-btn {
    background: #ffffff;
    border: 2px solid #e0e0e0;
    color: #333333;
    min-width: 44px;
    height: 44px;
    padding: 0 14px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.pagination-btn:hover:not(:disabled) {
    background: #f8f8f8;
    border-color: #333333;
}

.pagination-btn.active {
    background: #000000;
    color: #ffffff;
    border-color: #000000;
}

.pagination-btn:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

/* ===== 新レスポンシブデザイン ===== */
@media (max-width: 1200px) {
    .main-filters-grid {
        gap: 25px;
    }
    
    .advanced-filter-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }
}

@media (max-width: 1024px) {
    .hero-background {
        width: 40%;
    }
    
    .hero-title {
        font-size: 36px;
    }
    
    .filter-section-enhanced {
        padding: 40px 0;
    }
    
    .filter-box {
        border-radius: 14px;
    }
    
    .filter-box-header {
        padding: 20px 24px;
    }
    
    .filter-box-content {
        padding: 24px;
    }
}

@media (max-width: 768px) {
    .hero-background {
        display: none;
    }
    
    .hero-content {
        padding: 60px 20px;
        min-height: auto;
    }
    
    .hero-title {
        font-size: 28px;
    }
    
    .value-prop-grid {
        grid-template-columns: 1fr;
        gap: 16px;
        margin-bottom: 24px;
    }
    
    .value-prop-item {
        padding: 20px;
    }
    
    .value-title {
        font-size: 15px;
    }
    
    .value-desc {
        font-size: 12px;
    }
    
    .popular-categories-tags {
        padding: 16px;
        gap: 8px;
    }
    
    .tags-label {
        font-size: 13px;
        width: 100%;
        margin-bottom: 4px;
    }
    
    .category-tag {
        font-size: 12px;
        padding: 5px 12px;
    }
    
    .filter-section-enhanced {
        padding: 30px 0;
    }
    
    .comprehensive-search-section {
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .search-input-group.enhanced {
        flex-direction: column;
        gap: 12px;
        padding: 16px;
    }
    
    .search-type-selector {
        order: -1;
        margin-right: 0;
        margin-bottom: 8px;
    }
    
    .search-type-dropdown {
        width: 100%;
        margin-bottom: 8px;
    }
    
    .search-btn.enhanced {
        width: 100%;
        justify-content: center;
        margin-top: 10px;
    }
    
    .quick-filters-row {
        margin-top: 15px;
        padding-top: 15px;
    }
    
    .quick-filter-group {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .quick-filter-label {
        margin-right: 0;
        margin-bottom: 5px;
    }
    
    /* フィルターボックスのモバイル対応 */
    .filter-box {
        border-radius: 12px;
        margin-bottom: 20px;
    }
    
    .filter-box-header {
        padding: 18px 20px;
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .filter-box-title {
        font-size: 16px;
    }
    
    .filter-status {
        align-self: flex-end;
        margin-top: -8px;
    }
    
    .filter-box-content {
        padding: 20px;
    }
    
    /* カテゴリボタンのモバイル対応 */
    .category-buttons-main {
        gap: 8px;
    }
    
    .category-btn {
        padding: 12px 16px;
        font-size: 13px;
        min-height: 44px;
    }
    
    .category-btn.small {
        padding: 10px 14px;
        font-size: 12px;
        min-height: 38px;
    }
    
    /* 地域タブのモバイル対応 */
    .region-tabs {
        gap: 6px;
        padding: 3px;
    }
    
    .region-tab {
        padding: 8px 14px;
        font-size: 12px;
    }
    
    /* 都道府県ボタンのモバイル対応 */
    .prefecture-scroll-container {
        padding: 16px;
        gap: 10px;
    }
    
    .scroll-arrow {
        width: 40px;
        height: 40px;
    }
    
    .prefecture-btn {
        padding: 10px 16px;
        font-size: 12px;
        min-height: 36px;
    }
    
    /* 詳細フィルターのモバイル対応 */
    .advanced-filter-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .advanced-filter-item {
        padding: 16px;
    }
    
    .advanced-filter-label {
        font-size: 13px;
    }
    
    .advanced-filter-select {
        padding: 12px 14px;
        padding-right: 36px;
    }
    
    .grants-grid-optimized {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .filter-section-enhanced {
        padding: 20px 0;
    }
    
    .filter-box-header {
        padding: 16px 18px;
    }
    
    .filter-box-title {
        font-size: 15px;
        gap: 8px;
    }
    
    .filter-icon {
        width: 18px;
        height: 18px;
    }
    
    .filter-box-content {
        padding: 18px;
    }
    
    .category-btn {
        padding: 10px 14px;
        font-size: 12px;
        min-height: 40px;
    }
    
    .category-btn.small {
        padding: 8px 12px;
        font-size: 11px;
        min-height: 36px;
    }
    
    .prefecture-btn {
        padding: 8px 14px;
        font-size: 11px;
        min-height: 32px;
    }
    
    .municipality-btn {
        padding: 10px 14px;
        font-size: 12px;
        min-height: 40px;
    }
    
    .advanced-filter-item {
        padding: 14px;
    }
    
    .btn-reset-filters {
        padding: 12px 24px;
        font-size: 13px;
    }
}
</style>

<!-- JavaScript（完全版・AI機能搭載） -->
<script>
(function() {
    'use strict';
    
    const AJAX_URL = '<?php echo admin_url("admin-ajax.php"); ?>';
    const NONCE = '<?php echo wp_create_nonce("gi_ajax_nonce"); ?>';
    
    const state = {
        currentPage: 1,
        perPage: 12,
        view: 'grid',
        filters: {
            search: '',
            category: [],
            prefecture: [],
            municipality: '',
            region: '',
            amount: '',
            status: '',
            difficulty: '',
            sort: 'date_desc'
        },
        isLoading: false,
        currentMunicipalities: [],
        aiSuggestions: null
    };
    
    const elements = {
        grantsContainer: document.getElementById('grants-container'),
        loadingOverlay: document.getElementById('loading-overlay'),
        noResults: document.getElementById('no-results'),
        resultsCount: document.getElementById('current-count'),
        showingFrom: document.getElementById('showing-from'),
        showingTo: document.getElementById('showing-to'),
        pagination: document.getElementById('pagination'),
        paginationWrapper: document.getElementById('pagination-wrapper'),
        activeFilters: document.getElementById('active-filters'),
        activeFilterTags: document.getElementById('active-filter-tags'),
        
        // AI検索
        aiSearchInput: document.getElementById('ai-search-input'),
        aiSearchBtn: document.getElementById('ai-search-btn'),
        aiResultsArea: document.getElementById('ai-results-area'),
        aiThinking: document.getElementById('ai-thinking'),
        aiResponse: document.getElementById('ai-response'),
        aiResponseContent: document.getElementById('ai-response-content'),
        applyAiFiltersBtn: document.getElementById('apply-ai-filters-btn'),
        closeAiResults: document.getElementById('close-ai-results'),
        exampleBtns: document.querySelectorAll('.example-btn'),
        
        // 通常検索
        keywordSearch: document.getElementById('keyword-search'),
        searchBtn: document.getElementById('search-btn'),
        clearSearchBtn: document.getElementById('clear-search-btn'),
        
        // Enhanced search elements (simplified)
        searchTypeSelect: document.getElementById('search-type-select'),
        searchSuggestions: document.getElementById('search-suggestions'),
        quickFilterBtns: document.querySelectorAll('.quick-filter-btn'),
        
        // Filter status displays
        categoryFilterStatus: document.getElementById('category-filter-status'),
        prefectureFilterStatus: document.getElementById('prefecture-filter-status'),
        municipalityFilterStatus: document.getElementById('municipality-filter-status'),
        
        // Mobile filter toggle
        mobileFilterToggle: document.getElementById('mobile-filter-toggle'),
        mobileFilterContent: document.getElementById('main-filters-content'),
        
        // カテゴリ
        categoryBtns: document.querySelectorAll('.category-btn'),
        toggleOtherCategoriesBtn: document.getElementById('toggle-other-categories'),
        otherCategoriesSection: document.getElementById('other-categories-section'),
        
        // 都道府県・市町村
        prefectureBtns: document.querySelectorAll('.prefecture-btn'),
        regionTabs: document.querySelectorAll('.region-tab'),
        municipalityFilterSection: document.getElementById('municipality-filter-section'),
        municipalityButtonsMain: document.querySelector('.municipality-buttons-main'),
        municipalityBtns: [], // 動的に設定される
        toggleOtherMunicipalitiesBtn: document.getElementById('toggle-other-municipalities'),
        otherMunicipalitiesSection: document.getElementById('other-municipalities-section'),
        municipalityButtonsOther: document.getElementById('municipality-buttons-other'),
        municipalityOtherWrapper: document.getElementById('municipality-other-wrapper'),
        municipalityCountBadge: document.getElementById('municipality-count-badge'),
        selectedPrefectureName: document.getElementById('selected-prefecture-name'),
        
        // 詳細フィルター
        amountFilter: document.getElementById('amount-filter'),
        statusFilter: document.getElementById('status-filter'),
        difficultyFilter: document.getElementById('difficulty-filter'),
        sortFilter: document.getElementById('sort-filter'),
        toggleAdvancedBtn: document.getElementById('toggle-advanced-btn'),
        advancedFilters: document.getElementById('advanced-filters'),
        
        // その他
        viewBtns: document.querySelectorAll('.view-btn'),
        scrollLeft: document.getElementById('scroll-left'),
        scrollRight: document.getElementById('scroll-right'),
        prefectureBar: document.getElementById('prefecture-bar'),
        resetFiltersBtn: document.getElementById('reset-filters-btn'),
        clearAllFilters: document.getElementById('clear-all-filters'),
        resetAllFiltersNoResults: document.getElementById('reset-all-filters-no-results')
    };
    
    function init() {
        // ← この下に追加
        console.log('🚀 Archive page initialized');
        console.log('📊 Configuration:', {
            ajaxUrl: AJAX_URL,
            noncePresent: !!NONCE,
            municipalityBarExists: !!elements.municipalityBar,
            municipalityFilterSectionExists: !!elements.municipalityFilterSection,
            prefectureButtonCount: elements.prefectureBtns.length
        });
        // ← ここまで追加
        
        setupEventListeners();
        loadGrants();
        updateScrollButtons();
    }
    
    function setupEventListeners() {
        // Mobile filter toggle
        if (elements.mobileFilterToggle) {
            elements.mobileFilterToggle.addEventListener('click', toggleMobileFilters);
        }
        
        // Enhanced search functionality
        setupEnhancedSearchListeners();
        
        // AI検索
        if (elements.aiSearchBtn) {
            elements.aiSearchBtn.addEventListener('click', handleAISearch);
        }
        
        if (elements.aiSearchInput) {
            elements.aiSearchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    handleAISearch();
                }
            });
        }
        
        if (elements.applyAiFiltersBtn) {
            elements.applyAiFiltersBtn.addEventListener('click', applyAIFilters);
        }
        
        if (elements.closeAiResults) {
            elements.closeAiResults.addEventListener('click', closeAIResults);
        }
        
        elements.exampleBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                elements.aiSearchInput.value = this.dataset.query;
                handleAISearch();
            });
        });
        
        // 通常検索
        if (elements.keywordSearch) {
            elements.keywordSearch.addEventListener('input', debounce(handleSearchInput, 300));
            elements.keywordSearch.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    handleSearch();
                }
            });
        }
        
        if (elements.searchBtn) {
            elements.searchBtn.addEventListener('click', handleSearch);
        }
        
        if (elements.clearSearchBtn) {
            elements.clearSearchBtn.addEventListener('click', clearSearch);
        }
        
        // カテゴリ（メイン + その他）- Multi-select enabled
        elements.categoryBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const categoryValue = this.dataset.category;
                
                // Handle "All" button
                if (!categoryValue) {
                    // Reset all categories
                    elements.categoryBtns.forEach(b => {
                        b.classList.remove('active');
                        b.setAttribute('aria-pressed', 'false');
                    });
                    this.classList.add('active');
                    this.setAttribute('aria-pressed', 'true');
                    state.filters.category = [];
                } else {
                    // Handle specific category selection
                    // Remove "All" button active state
                    const allBtn = document.querySelector('.category-btn[data-category=""]');
                    if (allBtn) {
                        allBtn.classList.remove('active');
                        allBtn.setAttribute('aria-pressed', 'false');
                    }
                    
                    toggleMultiSelectButton(this, state.filters.category, categoryValue);
                }
                
                state.currentPage = 1;
                updateMultiSelectDisplay('category', state.filters.category);
                loadGrants();
            });
        });
        
        // その他カテゴリ開閉
        if (elements.toggleOtherCategoriesBtn) {
            elements.toggleOtherCategoriesBtn.addEventListener('click', toggleOtherCategories);
        }
        
        // 都道府県（市町村機能は除く）- Multi-select enabled
        elements.prefectureBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const prefectureValue = this.dataset.prefecture;
                
                // Handle "All" button
                if (!prefectureValue) {
                    // Reset all prefectures
                    elements.prefectureBtns.forEach(b => {
                        b.classList.remove('active');
                        b.setAttribute('aria-pressed', 'false');
                    });
                    this.classList.add('active');
                    this.setAttribute('aria-pressed', 'true');
                    state.filters.prefecture = [];
                } else {
                    // Handle specific prefecture selection
                    // Remove "All" button active state
                    const allBtn = document.querySelector('.prefecture-btn[data-prefecture=""]');
                    if (allBtn) {
                        allBtn.classList.remove('active');
                        allBtn.setAttribute('aria-pressed', 'false');
                    }
                    
                    toggleMultiSelectButton(this, state.filters.prefecture, prefectureValue);
                }
                
                state.filters.municipality = ''; // 市町村リセット
                state.currentPage = 1;
                
                // 市町村フィルターを表示/非表示 (only show if single prefecture selected)
                if (state.filters.prefecture.length === 1) {
                    loadMunicipalities(state.filters.prefecture[0], this.textContent.trim());
                } else {
                    hideMunicipalityFilter();
                }
                
                updateMultiSelectDisplay('prefecture', state.filters.prefecture);
                loadGrants();
            });
        });
        
        // 地域タブ
        elements.regionTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                setActiveButton(elements.regionTabs, this);
                state.filters.region = this.dataset.region;
                filterPrefecturesByRegion(this.dataset.region);
                
                // Reset prefecture multi-selection
                elements.prefectureBtns.forEach(btn => btn.classList.remove('active'));
                const allPrefBtn = document.querySelector('.prefecture-btn[data-prefecture=""]');
                if (allPrefBtn) allPrefBtn.classList.add('active');
                state.filters.prefecture = [];
                updateMultiSelectDisplay('prefecture', state.filters.prefecture);

                state.currentPage = 1;
                loadGrants();
            });
        });
        
        // 詳細フィルター
        if (elements.amountFilter) {
            elements.amountFilter.addEventListener('change', function() {
                state.filters.amount = this.value;
                state.currentPage = 1;
                loadGrants();
            });
        }
        
        if (elements.statusFilter) {
            elements.statusFilter.addEventListener('change', function() {
                state.filters.status = this.value;
                state.currentPage = 1;
                loadGrants();
            });
        }
        
        if (elements.difficultyFilter) {
            elements.difficultyFilter.addEventListener('change', function() {
                state.filters.difficulty = this.value;
                state.currentPage = 1;
                loadGrants();
            });
        }
        
        if (elements.sortFilter) {
            elements.sortFilter.addEventListener('change', function() {
                state.filters.sort = this.value;
                state.currentPage = 1;
                loadGrants();
            });
        }
        
        // 詳細フィルタートグル
        if (elements.toggleAdvancedBtn) {
            elements.toggleAdvancedBtn.addEventListener('click', toggleAdvancedFilters);
        }
        
        // 詳細フィルターのリセットボタン
        if (elements.resetFiltersBtn) {
            elements.resetFiltersBtn.addEventListener('click', function() {
                console.log('🔄 Resetting detailed filters');
                
                // 詳細フィルターをリセット
                if (elements.amountFilter) elements.amountFilter.value = '';
                if (elements.statusFilter) elements.statusFilter.value = '';
                if (elements.difficultyFilter) elements.difficultyFilter.value = '';
                if (elements.sortFilter) elements.sortFilter.value = 'date_desc';
                
                // ステートをリセット
                state.filters.amount = '';
                state.filters.status = '';
                state.filters.difficulty = '';
                state.filters.sort = 'date_desc';
                state.currentPage = 1;
                
                console.log('✅ Detailed filters reset completed');
                loadGrants();
            });
        }
        
        // 表示切替
        elements.viewBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                setActiveButton(elements.viewBtns, this);
                state.view = this.dataset.view;
                elements.grantsContainer.setAttribute('data-view', state.view);
                loadGrants();
            });
        });
        
        // スクロール
        if (elements.scrollLeft) {
            elements.scrollLeft.addEventListener('click', () => scrollPrefectures(-300));
        }
        
        if (elements.scrollRight) {
            elements.scrollRight.addEventListener('click', () => scrollPrefectures(300));
        }
        
        if (elements.prefectureBar) {
            elements.prefectureBar.addEventListener('scroll', updateScrollButtons);
        }
        
        // リセット
        if (elements.resetFiltersBtn) {
            elements.resetFiltersBtn.addEventListener('click', resetFilters);
        }
        
        if (elements.clearAllFilters) {
            elements.clearAllFilters.addEventListener('click', resetFilters);
        }
        
        if (elements.resetAllFiltersNoResults) {
            elements.resetAllFiltersNoResults.addEventListener('click', resetFilters);
        }
    }
    
    // ===== AI検索機能 =====
    function handleAISearch() {
        const query = elements.aiSearchInput.value.trim();
        
        if (!query) {
            alert('検索内容を入力してください');
            return;
        }
        
        // 結果エリアを表示
        elements.aiResultsArea.style.display = 'block';
        elements.aiThinking.style.display = 'flex';
        elements.aiResponse.style.display = 'none';
        
        // AJAX でAI検索実行
        const formData = new FormData();
        formData.append('action', 'gi_ai_search_grants');
        formData.append('nonce', NONCE);
        formData.append('query', query);
        
        fetch(AJAX_URL, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            elements.aiThinking.style.display = 'none';
            
            if (data.success && data.data) {
                displayAIResults(data.data);
            } else {
                elements.aiResponseContent.innerHTML = '<p style="color: #dc2626;">AI検索に失敗しました。もう一度お試しください。</p>';
                elements.aiResponse.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('AI Search Error:', error);
            elements.aiThinking.style.display = 'none';
            elements.aiResponseContent.innerHTML = '<p style="color: #dc2626;">通信エラーが発生しました。</p>';
            elements.aiResponse.style.display = 'block';
        });
    }
    
    function displayAIResults(data) {
        state.aiSuggestions = data.suggestions;
        
        let html = `<p><strong>検索内容の理解:</strong></p>`;
        html += `<p>${escapeHtml(data.interpretation)}</p>`;
        
        if (data.suggestions) {
            html += `<p style="margin-top: 15px;"><strong>推奨フィルター条件:</strong></p>`;
            html += '<ul style="margin: 10px 0; padding-left: 20px;">';
            
            if (data.suggestions.prefecture) {
                html += `<li>都道府県: ${escapeHtml(data.suggestions.prefecture)}</li>`;
            }
            // 市町村提案は削除済み
            if (data.suggestions.category) {
                html += `<li>カテゴリ: ${escapeHtml(data.suggestions.category)}</li>`;
            }
            if (data.suggestions.keywords && data.suggestions.keywords.length > 0) {
                html += `<li>キーワード: ${data.suggestions.keywords.map(k => escapeHtml(k)).join(', ')}</li>`;
            }
            
            html += '</ul>';
        }
        
        elements.aiResponseContent.innerHTML = html;
        elements.aiResponse.style.display = 'block';
    }
    
    function applyAIFilters() {
        if (!state.aiSuggestions) return;
        
        const suggestions = state.aiSuggestions;
        
        // 都道府県（市町村連携は除く）
        if (suggestions.prefecture) {
            const prefBtn = Array.from(elements.prefectureBtns).find(btn => 
                btn.textContent.trim() === suggestions.prefecture
            );
            if (prefBtn) {
                prefBtn.click();
            }
        }
        
        // カテゴリ
        if (suggestions.category) {
            const catBtn = Array.from(elements.categoryBtns).find(btn => 
                btn.querySelector('.btn-text')?.textContent.trim() === suggestions.category
            );
            if (catBtn) {
                catBtn.click();
            }
        }
        
        // キーワード
        if (suggestions.keywords && suggestions.keywords.length > 0) {
            elements.keywordSearch.value = suggestions.keywords.join(' ');
            handleSearch();
        }
        
        // AI結果を閉じる
        closeAIResults();
        
        // 検索結果までスクロール
        elements.grantsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    
    // 市町村選択機能は削除済み
    
    function closeAIResults() {
        elements.aiResultsArea.style.display = 'none';
        elements.aiSearchInput.value = '';
    }
    
    // ===== モバイルフィルター開閉 =====
    function toggleMobileFilters() {
        if (!elements.mobileFilterContent || !elements.mobileFilterToggle) return;
        
        const isVisible = elements.mobileFilterContent.classList.contains('active');
        elements.mobileFilterContent.classList.toggle('active');
        elements.mobileFilterToggle.classList.toggle('active');
        
        const toggleIcon = elements.mobileFilterToggle.querySelector('svg');
        if (toggleIcon) {
            toggleIcon.style.transform = isVisible ? 'rotate(0deg)' : 'rotate(180deg)';
        }
        
        // Update button text
        const buttonText = elements.mobileFilterToggle.querySelector('span');
        if (buttonText) {
            buttonText.textContent = isVisible ? 'フィルターで絞り込む' : 'フィルターを閉じる';
        }
    }
    
    // ===== その他カテゴリ開閉 =====
    function toggleOtherCategories() {
        const isVisible = elements.otherCategoriesSection.style.display !== 'none';
        elements.otherCategoriesSection.style.display = isVisible ? 'none' : 'block';
        elements.toggleOtherCategoriesBtn.classList.toggle('active');
        
        const toggleText = elements.toggleOtherCategoriesBtn.querySelector('.toggle-text');
        if (toggleText) {
            toggleText.textContent = isVisible ? 'その他のカテゴリを表示' : 'その他のカテゴリを非表示';
        }
    }
    
    // ===== 市町村フィルター機能 =====
    function hideMunicipalityFilter() {
        console.log('⚠️ Hiding municipality filter (multiple or no prefectures selected)');
        if (elements.municipalityFilterSection) {
            elements.municipalityFilterSection.style.display = 'none';
        }
    }
    
    function loadMunicipalities(prefectureSlug, prefectureName) {
        console.log('========================================');
        console.log('🏘️ Loading municipalities');
        console.log('Prefecture Slug:', prefectureSlug);
        console.log('Prefecture Name:', prefectureName);
        console.log('========================================');
        
        if (!prefectureSlug) {
            console.log('⚠️ No prefecture selected, hiding municipality filter');
            hideMunicipalityFilter();
            return;
        }
        
        if (elements.municipalityFilterSection) {
            elements.municipalityFilterSection.style.display = 'block';
        }
        
        if (elements.selectedPrefectureName) {
            elements.selectedPrefectureName.textContent = `（${prefectureName}）`;
        }
        
        const formData = new FormData();
        formData.append('action', 'gi_get_municipalities_for_prefecture');
        formData.append('prefecture_slug', prefectureSlug);
        formData.append('nonce', NONCE);
        
        console.log('📡 Sending AJAX request:', {
            action: 'gi_get_municipalities_for_prefecture',
            prefecture_slug: prefectureSlug,
            ajax_url: AJAX_URL
        });
        
        fetch(AJAX_URL, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('📥 Response received:', {
                status: response.status,
                statusText: response.statusText,
                ok: response.ok
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return response.json();
        })
        .then(data => {
            console.log('📦 Full API Response:', data);
            
            // 詳細なデバッグ情報を出力
            console.log('🔍 Response Analysis:');
            console.log('- success:', data.success);
            console.log('- data exists:', !!data.data);
            console.log('- data type:', typeof data.data);
            console.log('- data content:', data.data);
            
            if (data.data) {
                console.log('- data keys:', Object.keys(data.data));
                console.log('- municipalities key exists:', 'municipalities' in data.data);
                console.log('- municipalities type:', typeof data.data.municipalities);
                console.log('- municipalities is array:', Array.isArray(data.data.municipalities));
                if (data.data.municipalities) {
                    console.log('- municipalities length:', data.data.municipalities.length);
                    console.log('- first 2 items:', data.data.municipalities.slice(0, 2));
                }
            }
            
            // 修正されたロジック：複数のレスポンス構造パターンに対応
            let municipalities = [];
            
            if (data.success) {
                // パターン1: data.data.data.municipalities (ネストした構造)
                if (data.data && data.data.data && Array.isArray(data.data.data.municipalities)) {
                    municipalities = data.data.data.municipalities;
                    console.log('✅ Pattern 1: data.data.data.municipalities found (nested structure)');
                }
                // パターン2: data.data.municipalities
                else if (data.data && Array.isArray(data.data.municipalities)) {
                    municipalities = data.data.municipalities;
                    console.log('✅ Pattern 2: data.data.municipalities found');
                }
                // パターン3: data.municipalities  
                else if (Array.isArray(data.municipalities)) {
                    municipalities = data.municipalities;
                    console.log('✅ Pattern 3: data.municipalities found');
                }
                // パターン4: data.data直下が配列
                else if (Array.isArray(data.data)) {
                    municipalities = data.data;
                    console.log('✅ Pattern 4: data.data is array');
                }
                // パターン5: dataが直接配列
                else if (Array.isArray(data)) {
                    municipalities = data;
                    console.log('✅ Pattern 5: data is array');
                }
            }
            
            console.log('🎯 Final municipalities count:', municipalities.length);
            
            if (municipalities.length > 0) {
                console.log('✅ Municipalities loaded successfully:', municipalities.length, 'items');
                console.log('📝 First 3 municipalities:', municipalities.slice(0, 3));
                renderMunicipalityButtons(municipalities);
            } else {
                console.warn('⚠️ No municipalities found in response');
                console.log('Full response structure for debugging:', JSON.stringify(data, null, 2));
                renderMunicipalityButtons([]);
            }
        })
        .catch(error => {
            console.error('🚨 Municipality fetch error:', error);
            console.error('Error details:', {
                name: error.name,
                message: error.message,
                stack: error.stack
            });
            renderMunicipalityButtons([]);
        });
    }
    
    function renderMunicipalityButtons(municipalities) {
        console.log('========================================');
        console.log('🏗️ Rendering municipality buttons (expandable list)');
        console.log('Municipalities received:', municipalities);
        console.log('Count:', municipalities ? municipalities.length : 0);
        console.log('Municipality buttons main element exists:', !!elements.municipalityButtonsMain);
        console.log('========================================');
        
        if (!elements.municipalityButtonsMain) {
            console.error('❌ Municipality buttons main element not found');
            return;
        }
        
        // メイン市町村ボタン（「すべて」ボタンのみ）
        let mainHtml = '<button class="municipality-btn active" data-municipality="" data-filter-type="municipality"><span class="btn-text">すべて</span></button>';
        
        // 最初の数個を メイン表示用として取得（例：上位5個）
        const mainMunicipalities = municipalities.slice(0, 5);
        const otherMunicipalities = municipalities.slice(5);
        
        mainMunicipalities.forEach(municipality => {
            const count = municipality.count || 0;
            mainHtml += `<button class="municipality-btn" data-municipality="${municipality.slug}" data-filter-type="municipality"><span class="btn-text">${municipality.name}</span><span class="btn-count">${count}</span></button>`;
        });
        
        elements.municipalityButtonsMain.innerHTML = mainHtml;
        
        // その他の市町村がある場合
        if (otherMunicipalities.length > 0) {
            // その他市町村セクションを表示
            if (elements.municipalityOtherWrapper) {
                elements.municipalityOtherWrapper.style.display = 'block';
            }
            
            // カウントバッジを更新
            if (elements.municipalityCountBadge) {
                elements.municipalityCountBadge.textContent = `${otherMunicipalities.length}件`;
            }
            
            // その他市町村ボタンを生成
            let otherHtml = '';
            otherMunicipalities.forEach(municipality => {
                const count = municipality.count || 0;
                otherHtml += `<button class="municipality-btn small" data-municipality="${municipality.slug}" data-filter-type="municipality"><span class="btn-text">${municipality.name}</span><span class="btn-count">${count}</span></button>`;
            });
            
            if (elements.municipalityButtonsOther) {
                elements.municipalityButtonsOther.innerHTML = otherHtml;
            }
            
            // その他市町村開閉ボタンのイベント設定
            setupOtherMunicipalitiesToggle();
        } else {
            // その他市町村セクションを隠す
            if (elements.municipalityOtherWrapper) {
                elements.municipalityOtherWrapper.style.display = 'none';
            }
        }
        
        // イベント委譲でクリックイベントを処理（動的生成ボタンに対応）
        setupMunicipalityClickHandlers();
    }

    // 市町村ボタンクリックハンドラー設定
    function setupMunicipalityClickHandlers() {
        // メイン市町村ボタンのイベント設定
        if (elements.municipalityButtonsMain) {
            elements.municipalityButtonsMain.removeEventListener('click', handleMunicipalityClick);
            elements.municipalityButtonsMain.addEventListener('click', handleMunicipalityClick);
        }
        
        // その他市町村ボタンのイベント設定
        if (elements.municipalityButtonsOther) {
            elements.municipalityButtonsOther.removeEventListener('click', handleMunicipalityClick);
            elements.municipalityButtonsOther.addEventListener('click', handleMunicipalityClick);
        }
    }
    
    // 市町村ボタンクリックハンドラー（更新版）
    function handleMunicipalityClick(e) {
        const btn = e.target.closest('.municipality-btn');
        if (!btn) return;
        
        e.preventDefault();
        e.stopPropagation();
        
        // すべての市町村ボタンからactiveクラスを削除
        const allMainBtns = elements.municipalityButtonsMain ? elements.municipalityButtonsMain.querySelectorAll('.municipality-btn') : [];
        const allOtherBtns = elements.municipalityButtonsOther ? elements.municipalityButtonsOther.querySelectorAll('.municipality-btn') : [];
        
        [...allMainBtns, ...allOtherBtns].forEach(b => b.classList.remove('active'));
        
        // クリックされたボタンにactiveクラスを追加
        btn.classList.add('active');
        
        // フィルター状態を更新
        state.filters.municipality = btn.dataset.municipality;
        state.currentPage = 1;
        
        // Update municipality filter status
        const btnText = btn.querySelector('.btn-text');
        updateFilterStatus('municipality', btnText ? btnText.textContent.trim() : btn.textContent.trim());
        
        console.log('✅ Municipality selected:', state.filters.municipality || '(all)');
        
        // 助成金を再読み込み
        loadGrants();
    }
    
    // その他市町村開閉機能
    function setupOtherMunicipalitiesToggle() {
        if (elements.toggleOtherMunicipalitiesBtn) {
            elements.toggleOtherMunicipalitiesBtn.removeEventListener('click', toggleOtherMunicipalities);
            elements.toggleOtherMunicipalitiesBtn.addEventListener('click', toggleOtherMunicipalities);
        }
    }
    
    // その他市町村開閉関数
    function toggleOtherMunicipalities() {
        if (!elements.otherMunicipalitiesSection || !elements.toggleOtherMunicipalitiesBtn) return;
        
        const isVisible = elements.otherMunicipalitiesSection.style.display !== 'none';
        elements.otherMunicipalitiesSection.style.display = isVisible ? 'none' : 'block';
        elements.toggleOtherMunicipalitiesBtn.classList.toggle('active');
        
        const toggleText = elements.toggleOtherMunicipalitiesBtn.querySelector('.toggle-text');
        if (toggleText) {
            toggleText.textContent = isVisible ? 'その他の市町村を表示' : 'その他の市町村を非表示';
        }
        
        console.log(`🔄 Other municipalities ${isVisible ? 'hidden' : 'shown'}`);
    }

    
    // ===== 助成金データ読み込み =====
    function loadGrants() {
        if (state.isLoading) return;
        
        state.isLoading = true;
        showLoading(true);
        
        const formData = new FormData();
        formData.append('action', 'gi_ajax_load_grants');
        formData.append('nonce', NONCE);
        formData.append('page', state.currentPage);
        formData.append('posts_per_page', state.perPage);
        formData.append('view', state.view);
        
        if (state.filters.search) {
            formData.append('search', state.filters.search);
        }
        
        if (state.filters.category && state.filters.category.length > 0) {
            formData.append('categories', JSON.stringify(state.filters.category));
        }
        
        if (state.filters.prefecture && state.filters.prefecture.length > 0) {
            formData.append('prefectures', JSON.stringify(state.filters.prefecture));
        }
        
        // 市町村フィルター - 空文字列をチェック
        if (state.filters.municipality && state.filters.municipality !== '') {
            const municipalityArray = [state.filters.municipality];
            formData.append('municipalities', JSON.stringify(municipalityArray));
            
            console.log('========================================');
            console.log('📍 Municipality filter applied');
            console.log('Filter value:', state.filters.municipality);
            console.log('Sending as array:', municipalityArray);
            console.log('JSON string:', JSON.stringify(municipalityArray));
            console.log('========================================');
        } else {
            console.log('ℹ️ No municipality filter applied');
        }
        
        // 地域フィルターも追加
        if (state.filters.region) {
            formData.append('region', state.filters.region);
        }
        
        if (state.filters.amount) {
            formData.append('amount', state.filters.amount);
        }
        
        // ステータスフィルター（配列として送信）
        if (state.filters.status && state.filters.status !== '') {
            formData.append('status', JSON.stringify([state.filters.status]));
            console.log('📊 Status filter applied:', state.filters.status);
        }
        
        // 難易度フィルター（配列として送信）
        if (state.filters.difficulty && state.filters.difficulty !== '') {
            formData.append('difficulty', JSON.stringify([state.filters.difficulty]));
            console.log('📊 Difficulty filter applied:', state.filters.difficulty);
        }
        
        formData.append('sort', state.filters.sort);
        
        // 詳細フィルターのデバッグログ
        console.log('🔍 Detailed Filters Debug:', {
            amount: state.filters.amount,
            status: state.filters.status,
            difficulty: state.filters.difficulty,
            sort: state.filters.sort
        });
        
        fetch(AJAX_URL, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayGrants(data.data.grants);
                updateStats(data.data.stats);
                updatePagination(data.data.pagination);
                updateActiveFilters();
            } else {
                showError('データの読み込みに失敗しました。');
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            showError('通信エラーが発生しました。');
        })
        .finally(() => {
            state.isLoading = false;
            showLoading(false);
        });
    }
    
    function displayGrants(grants) {
        if (!elements.grantsContainer) return;
        
        if (!grants || grants.length === 0) {
            elements.grantsContainer.innerHTML = '';
            elements.grantsContainer.style.display = 'none';
            if (elements.noResults) {
                elements.noResults.style.display = 'block';
            }
            return;
        }
        
        elements.grantsContainer.style.display = 'grid';
        if (elements.noResults) {
            elements.noResults.style.display = 'none';
        }
        
        elements.grantsContainer.innerHTML = grants.map(grant => grant.html).join('');
    }
    
    function updateStats(stats) {
        if (elements.resultsCount) {
            elements.resultsCount.textContent = (stats.total_found || 0).toLocaleString();
        }
        if (elements.showingFrom) {
            elements.showingFrom.textContent = (stats.showing_from || 0).toLocaleString();
        }
        if (elements.showingTo) {
            elements.showingTo.textContent = (stats.showing_to || 0).toLocaleString();
        }
    }
    
    function updatePagination(pagination) {
        if (!elements.pagination || !elements.paginationWrapper) return;
        
        if (!pagination || pagination.total_pages <= 1) {
            elements.paginationWrapper.style.display = 'none';
            return;
        }
        
        elements.paginationWrapper.style.display = 'block';
        
        let html = '';
        const current = pagination.current_page;
        const total = pagination.total_pages;
        
        html += `<button class="pagination-btn" ${current === 1 ? 'disabled' : ''} data-page="${current - 1}">前へ</button>`;
        
        const maxPages = 7;
        let startPage = Math.max(1, current - Math.floor(maxPages / 2));
        let endPage = Math.min(total, startPage + maxPages - 1);
        
        if (endPage - startPage < maxPages - 1) {
            startPage = Math.max(1, endPage - maxPages + 1);
        }
        
        if (startPage > 1) {
            html += `<button class="pagination-btn" data-page="1">1</button>`;
            if (startPage > 2) {
                html += `<span class="pagination-ellipsis">...</span>`;
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            html += `<button class="pagination-btn ${i === current ? 'active' : ''}" data-page="${i}">${i}</button>`;
        }
        
        if (endPage < total) {
            if (endPage < total - 1) {
                html += `<span class="pagination-ellipsis">...</span>`;
            }
            html += `<button class="pagination-btn" data-page="${total}">${total}</button>`;
        }
        
        html += `<button class="pagination-btn" ${current === total ? 'disabled' : ''} data-page="${current + 1}">次へ</button>`;
        
        elements.pagination.innerHTML = html;
        
        elements.pagination.querySelectorAll('.pagination-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (!this.disabled) {
                    state.currentPage = parseInt(this.dataset.page);
                    loadGrants();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
        });
    }
    
    function updateActiveFilters() {
        if (!elements.activeFilters || !elements.activeFilterTags) return;
        
        const tags = [];
        
        if (state.filters.search) {
            tags.push({
                type: 'search',
                label: `検索: ${state.filters.search}`,
                value: state.filters.search
            });
        }
        
        if (state.filters.category && state.filters.category.length > 0) {
            state.filters.category.forEach(categorySlug => {
                const btn = document.querySelector(`.category-btn[data-category="${categorySlug}"]`);
                if (btn) {
                    const text = btn.querySelector('.btn-text');
                    tags.push({
                        type: 'category',
                        label: text ? text.textContent.trim() : categorySlug,
                        value: categorySlug
                    });
                }
            });
        }
        
        if (state.filters.prefecture && state.filters.prefecture.length > 0) {
            state.filters.prefecture.forEach(prefectureSlug => {
                const btn = document.querySelector(`.prefecture-btn[data-prefecture="${prefectureSlug}"]`);
                if (btn) {
                    tags.push({
                        type: 'prefecture',
                        label: btn.textContent.trim(),
                        value: prefectureSlug
                    });
                }
            });
        }
        
        if (state.filters.municipality) {
            // Look for municipality button in both main and other sections
            let btn = elements.municipalityButtonsMain ? elements.municipalityButtonsMain.querySelector(`.municipality-btn[data-municipality="${state.filters.municipality}"]`) : null;
            if (!btn && elements.municipalityButtonsOther) {
                btn = elements.municipalityButtonsOther.querySelector(`.municipality-btn[data-municipality="${state.filters.municipality}"]`);
            }
            if (btn) {
                const btnText = btn.querySelector('.btn-text');
                tags.push({
                    type: 'municipality',
                    label: btnText ? btnText.textContent.trim() : btn.textContent.trim(),
                    value: state.filters.municipality
                });
            }
        }
        
        if (state.filters.amount && state.filters.amount !== '') {
            const labels = {
                '0-100': '〜100万円',
                '100-500': '100万円〜500万円',
                '500-1000': '500万円〜1000万円',
                '1000-3000': '1000万円〜3000万円',
                '3000+': '3000万円以上'
            };
            tags.push({
                type: 'amount',
                label: labels[state.filters.amount] || state.filters.amount,
                value: state.filters.amount
            });
        }
        
        if (state.filters.status && state.filters.status !== '') {
            const labels = {
                'active': '募集中',
                'upcoming': '募集予定', 
                'closed': '募集終了'
            };
            tags.push({
                type: 'status',
                label: labels[state.filters.status] || state.filters.status,
                value: state.filters.status
            });
        }
        
        if (state.filters.difficulty && state.filters.difficulty !== '') {
            const labels = {
                'easy': '易しい',
                'normal': '普通',
                'hard': '難しい'
            };
            tags.push({
                type: 'difficulty',
                label: labels[state.filters.difficulty] || state.filters.difficulty,
                value: state.filters.difficulty
            });
        }
        
        if (tags.length === 0) {
            elements.activeFilters.style.display = 'none';
            return;
        }
        
        elements.activeFilters.style.display = 'flex';
        elements.activeFilterTags.innerHTML = tags.map(tag => `
            <div class="filter-tag">
                <span>${escapeHtml(tag.label)}</span>
                <button class="filter-tag-remove" data-type="${tag.type}" data-value="${escapeHtml(tag.value)}">×</button>
            </div>
        `).join('');
        
        elements.activeFilterTags.querySelectorAll('.filter-tag-remove').forEach(btn => {
            btn.addEventListener('click', function() {
                removeFilter(this.dataset.type, this.dataset.value);
            });
        });
    }
    
    function handleSearchInput() {
        const query = elements.keywordSearch.value.trim();
        
        if (query.length >= 2) {
            elements.clearSearchBtn.style.display = 'flex';
        } else {
            elements.clearSearchBtn.style.display = 'none';
        }
    }
    
    function handleSearch() {
        const query = elements.keywordSearch.value.trim();
        state.filters.search = query;
        state.currentPage = 1;
        loadGrants();
        
        if (query) {
            elements.clearSearchBtn.style.display = 'flex';
        }
    }
    
    function clearSearch() {
        elements.keywordSearch.value = '';
        state.filters.search = '';
        elements.clearSearchBtn.style.display = 'none';
        state.currentPage = 1;
        loadGrants();
    }
    
    function removeFilter(type, value) {
        switch(type) {
            case 'search':
                clearSearch();
                break;
            case 'category':
                if (value) {
                    // Remove specific category from array
                    const index = state.filters.category.indexOf(value);
                    if (index > -1) {
                        state.filters.category.splice(index, 1);
                    }
                    // Update UI
                    const btn = document.querySelector(`.category-btn[data-category="${value}"]`);
                    if (btn) btn.classList.remove('active');
                    updateMultiSelectDisplay('category', state.filters.category);
                    // If no categories selected, activate "All" button
                    if (state.filters.category.length === 0) {
                        const allBtn = document.querySelector('.category-btn[data-category=""]');
                        if (allBtn) allBtn.classList.add('active');
                    }
                } else {
                    // Remove all categories
                    state.filters.category = [];
                    elements.categoryBtns.forEach(btn => btn.classList.remove('active'));
                    const allBtn = document.querySelector('.category-btn[data-category=""]');
                    if (allBtn) allBtn.classList.add('active');
                    updateMultiSelectDisplay('category', state.filters.category);
                }
                break;
            case 'prefecture':
                if (value) {
                    // Remove specific prefecture from array
                    const index = state.filters.prefecture.indexOf(value);
                    if (index > -1) {
                        state.filters.prefecture.splice(index, 1);
                    }
                    // Update UI
                    const btn = document.querySelector(`.prefecture-btn[data-prefecture="${value}"]`);
                    if (btn) btn.classList.remove('active');
                    updateMultiSelectDisplay('prefecture', state.filters.prefecture);
                    // If no prefectures selected, activate "All" button
                    if (state.filters.prefecture.length === 0) {
                        const allBtn = document.querySelector('.prefecture-btn[data-prefecture=""]');
                        if (allBtn) allBtn.classList.add('active');
                    }
                    // Hide municipality filter if needed
                    if (state.filters.prefecture.length !== 1) {
                        hideMunicipalityFilter();
                        state.filters.municipality = '';
                    }
                } else {
                    // Remove all prefectures
                    state.filters.prefecture = [];
                    elements.prefectureBtns.forEach(btn => btn.classList.remove('active'));
                    const allBtn = document.querySelector('.prefecture-btn[data-prefecture=""]');
                    if (allBtn) allBtn.classList.add('active');
                    updateMultiSelectDisplay('prefecture', state.filters.prefecture);
                    hideMunicipalityFilter();
                    state.filters.municipality = '';
                }
                break;
            case 'municipality':
                state.filters.municipality = '';
                // Reset all municipality buttons
                const allMainBtns = elements.municipalityButtonsMain ? elements.municipalityButtonsMain.querySelectorAll('.municipality-btn') : [];
                const allOtherBtns = elements.municipalityButtonsOther ? elements.municipalityButtonsOther.querySelectorAll('.municipality-btn') : [];
                [...allMainBtns, ...allOtherBtns].forEach(btn => btn.classList.remove('active'));
                
                // Set "すべて" button as active
                const allBtn = elements.municipalityButtonsMain ? elements.municipalityButtonsMain.querySelector('.municipality-btn[data-municipality=""]') : null;
                if (allBtn) allBtn.classList.add('active');
                
                hideMunicipalityFilter();
                break;
            case 'amount':
                state.filters.amount = '';
                if (elements.amountFilter) elements.amountFilter.value = '';
                break;
            case 'status':
                state.filters.status = '';
                if (elements.statusFilter) elements.statusFilter.value = '';
                break;
            case 'difficulty':
                state.filters.difficulty = '';
                if (elements.difficultyFilter) elements.difficultyFilter.value = '';
                break;
        }
        
        state.currentPage = 1;
        loadGrants();
    }
    
    function resetFilters() {
        state.filters = {
            search: '',
            category: [],
            prefecture: [],
            municipality: '',
            region: '',
            amount: '',
            status: '',
            difficulty: '',
            sort: 'date_desc'
        };
        state.currentPage = 1;
        
        if (elements.keywordSearch) elements.keywordSearch.value = '';
        elements.clearSearchBtn.style.display = 'none';
        
        // Reset multi-select buttons
        elements.categoryBtns.forEach(btn => btn.classList.remove('active'));
        elements.prefectureBtns.forEach(btn => btn.classList.remove('active'));
        
        setActiveButton(elements.categoryBtns, document.querySelector('.category-btn[data-category=""]'));
        setActiveButton(elements.prefectureBtns, document.querySelector('.prefecture-btn[data-prefecture=""]'));
        setActiveButton(elements.regionTabs, document.querySelector('.region-tab[data-region=""]'));
        
        // Update multi-select displays
        updateMultiSelectDisplay('category', state.filters.category);
        updateMultiSelectDisplay('prefecture', state.filters.prefecture);
        
        // 市町村フィルターをリセット
        const allMainBtns = elements.municipalityButtonsMain ? elements.municipalityButtonsMain.querySelectorAll('.municipality-btn') : [];
        const allOtherBtns = elements.municipalityButtonsOther ? elements.municipalityButtonsOther.querySelectorAll('.municipality-btn') : [];
        [...allMainBtns, ...allOtherBtns].forEach(btn => btn.classList.remove('active'));
        
        // Set "すべて" button as active
        const allMunicipalityBtn = elements.municipalityButtonsMain ? elements.municipalityButtonsMain.querySelector('.municipality-btn[data-municipality=""]') : null;
        if (allMunicipalityBtn) allMunicipalityBtn.classList.add('active');
        
        if (elements.amountFilter) elements.amountFilter.value = '';
        if (elements.statusFilter) elements.statusFilter.value = '';
        if (elements.difficultyFilter) elements.difficultyFilter.value = '';
        if (elements.sortFilter) elements.sortFilter.value = 'date_desc';
        
        elements.prefectureBtns.forEach(btn => {
            btn.style.display = 'flex';
        });
        
        // その他カテゴリを閉じる
        if (elements.otherCategoriesSection) {
            elements.otherCategoriesSection.style.display = 'none';
            elements.toggleOtherCategoriesBtn.classList.remove('active');
            const toggleText = elements.toggleOtherCategoriesBtn.querySelector('.toggle-text');
            if (toggleText) {
                toggleText.textContent = 'その他のカテゴリを表示';
            }
        }
        
        // 市町村フィルターセクションを隠す
        hideMunicipalityFilter();
        closeAIResults();
        
        // Reset all filter statuses
        updateFilterStatus('category', '');
        updateFilterStatus('prefecture', '');
        updateFilterStatus('municipality', '');
        
        loadGrants();
    }
    
    function filterPrefecturesByRegion(region) {
        elements.prefectureBtns.forEach(btn => {
            const btnRegion = btn.dataset.region;
            if (!region || btnRegion === region || !btnRegion) {
                btn.style.display = 'flex';
            } else {
                btn.style.display = 'none';
            }
        });
        
        elements.prefectureBar.scrollLeft = 0;
        updateScrollButtons();
    }
    
    function scrollPrefectures(amount) {
        elements.prefectureBar.scrollBy({ left: amount, behavior: 'smooth' });
    }
    
    function updateScrollButtons() {
        if (!elements.prefectureBar || !elements.scrollLeft || !elements.scrollRight) return;
        
        const scrollLeft = elements.prefectureBar.scrollLeft;
        const scrollWidth = elements.prefectureBar.scrollWidth;
        const clientWidth = elements.prefectureBar.clientWidth;
        
        elements.scrollLeft.disabled = scrollLeft === 0;
        elements.scrollRight.disabled = scrollLeft + clientWidth >= scrollWidth - 1;
    }
    
    function toggleAdvancedFilters() {
        if (!elements.advancedFilters || !elements.toggleAdvancedBtn) {
            console.error('❌ Advanced filter elements not found');
            return;
        }
        
        const isVisible = elements.advancedFilters.style.display !== 'none';
        
        if (isVisible) {
            // フィルターを閉じる
            elements.advancedFilters.style.display = 'none';
            elements.toggleAdvancedBtn.classList.remove('active');
            
            console.log('📂 Advanced filters closed');
        } else {
            // フィルターを開く
            elements.advancedFilters.style.display = 'block';
            elements.toggleAdvancedBtn.classList.add('active');
            
            console.log('📂 Advanced filters opened');
        }
    }
    
    function setActiveButton(buttons, activeBtn) {
        buttons.forEach(btn => btn.classList.remove('active'));
        if (activeBtn) {
            activeBtn.classList.add('active');
        }
    }
    
    function toggleMultiSelectButton(button, filterArray, value) {
        const isActive = button.classList.contains('active');
        
        if (isActive) {
            // Remove from selection
            button.classList.remove('active');
            button.setAttribute('aria-pressed', 'false');
            const index = filterArray.indexOf(value);
            if (index > -1) {
                filterArray.splice(index, 1);
            }
        } else {
            // Add to selection
            button.classList.add('active');
            button.setAttribute('aria-pressed', 'true');
            if (value && !filterArray.includes(value)) {
                filterArray.push(value);
            }
        }
        
        return !isActive;
    }
    
    function updateMultiSelectDisplay(filterType, filterArray) {
        const statusElement = elements[filterType + 'FilterStatus'];
        if (!statusElement) return;
        
        // Update filter status text
        if (filterArray.length === 0) {
            statusElement.textContent = filterType === 'category' ? 'すべて' : '全国';
            statusElement.classList.remove('active');
        } else {
            statusElement.textContent = `${filterArray.length}件選択中`;
            statusElement.classList.add('active');
        }
        
        // Update filter box visual indicator
        const filterBox = statusElement.closest('.filter-box');
        if (filterBox) {
            if (filterArray.length > 0) {
                filterBox.classList.add('has-selections');
            } else {
                filterBox.classList.remove('has-selections');
            }
        }
    }
    
    function toggleAdvancedFilters() {
        const isVisible = elements.advancedFilters.style.display !== 'none';
        elements.advancedFilters.style.display = isVisible ? 'none' : 'block';
        elements.toggleAdvancedBtn.classList.toggle('active');
    }
    
    function showLoading(show) {
        if (elements.loadingOverlay) {
            elements.loadingOverlay.style.display = show ? 'flex' : 'none';
        }
        
        if (elements.grantsContainer) {
            elements.grantsContainer.style.opacity = show ? '0.5' : '1';
        }
    }
    
    function showError(message) {
        alert(message);
    }
    
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
    
    // ===== Enhanced Search Functionality =====
    function setupEnhancedSearchListeners() {
        // Quick filter buttons
        if (elements.quickFilterBtns) {
            elements.quickFilterBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const filterType = this.dataset.type;
                    const filterValue = this.dataset.filter;
                    
                    // Toggle active state
                    const wasActive = this.classList.contains('active');
                    elements.quickFilterBtns.forEach(b => b.classList.remove('active'));
                    
                    if (!wasActive) {
                        this.classList.add('active');
                        applyQuickFilter(filterType, filterValue);
                    } else {
                        clearQuickFilter(filterType);
                    }
                });
            });
        }
        

        
        // Search suggestions
        if (elements.keywordSearch) {
            elements.keywordSearch.addEventListener('input', debounce(function() {
                if (this.value.length >= 2) {
                    showSearchSuggestions(this.value);
                } else {
                    hideSearchSuggestions();
                }
            }, 300));
            
            // Hide suggestions when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.main-search-wrapper')) {
                    hideSearchSuggestions();
                }
            });
        }
        
        // Enhanced search type selection
        if (elements.searchTypeSelect) {
            elements.searchTypeSelect.addEventListener('change', function() {
                updateSearchPlaceholder(this.value);
            });
        }
    }
    
    function applyQuickFilter(type, value) {
        console.log(`Applying quick filter: ${type} = ${value}`);
        
        switch(type) {
            case 'status':
                if (value === 'active') {
                    state.filters.status = 'active';
                    if (elements.statusFilter) elements.statusFilter.value = 'active';
                }
                break;
                
            case 'amount':
                if (value === 'high-amount') {
                    state.filters.amount = '3000+';
                    if (elements.amountFilter) elements.amountFilter.value = '3000+';
                }
                break;
                
            case 'category':
                const categoryBtn = Array.from(elements.categoryBtns).find(btn => 
                    btn.dataset.category === value);
                if (categoryBtn) {
                    setActiveButton(elements.categoryBtns, categoryBtn);
                    state.filters.category = value;
                }
                break;
                
            case 'target':
                // For target-based filters, use search
                if (value === 'startup') {
                    elements.keywordSearch.value = 'スタートアップ 創業 起業';
                    state.filters.search = 'スタートアップ 創業 起業';
                }
                break;
        }
        
        state.currentPage = 1;
        loadGrants();
    }
    
    function clearQuickFilter(type) {
        console.log(`Clearing quick filter: ${type}`);
        
        switch(type) {
            case 'status':
                state.filters.status = '';
                if (elements.statusFilter) elements.statusFilter.value = '';
                break;
                
            case 'amount':
                state.filters.amount = '';
                if (elements.amountFilter) elements.amountFilter.value = '';
                break;
                
            case 'category':
                const allCategoryBtn = document.querySelector('.category-btn[data-category=""]');
                if (allCategoryBtn) {
                    setActiveButton(elements.categoryBtns, allCategoryBtn);
                    state.filters.category = '';
                }
                break;
                
            case 'target':
                elements.keywordSearch.value = '';
                state.filters.search = '';
                break;
        }
        
        state.currentPage = 1;
        loadGrants();
    }
    
    // フィルターステータス表示更新関数
    function updateFilterStatus(type, value) {
        switch(type) {
            case 'category':
                if (elements.categoryFilterStatus) {
                    elements.categoryFilterStatus.textContent = value || 'すべて';
                }
                break;
            case 'prefecture':
                if (elements.prefectureFilterStatus) {
                    elements.prefectureFilterStatus.textContent = value || '全国';
                }
                break;
            case 'municipality':
                if (elements.municipalityFilterStatus) {
                    elements.municipalityFilterStatus.textContent = value || 'すべて';
                }
                break;
        }
    }
    
    function showSearchSuggestions(query) {
        if (!elements.searchSuggestions) return;
        
        // Get search type
        const searchType = elements.searchTypeSelect ? elements.searchTypeSelect.value : 'all';
        
        // AJAX call for dynamic suggestions
        const formData = new FormData();
        formData.append('action', 'gi_enhanced_search_suggestions');
        formData.append('nonce', NONCE);
        formData.append('query', query);
        formData.append('search_type', searchType);
        formData.append('limit', 8);
        
        fetch(AJAX_URL, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.suggestions.length > 0) {
                let html = '';
                data.data.suggestions.forEach(suggestion => {
                    html += `<div class="suggestion-item" onclick="selectSearchSuggestion('${escapeHtml(suggestion)}')">${escapeHtml(suggestion)}</div>`;
                });
                
                elements.searchSuggestions.querySelector('.suggestions-content').innerHTML = html;
                elements.searchSuggestions.style.display = 'block';
            } else {
                // Fallback to local suggestions
                const localSuggestions = generateSearchSuggestions(query);
                if (localSuggestions.length > 0) {
                    let html = '';
                    localSuggestions.forEach(suggestion => {
                        html += `<div class="suggestion-item" onclick="selectSearchSuggestion('${escapeHtml(suggestion)}')">${escapeHtml(suggestion)}</div>`;
                    });
                    
                    elements.searchSuggestions.querySelector('.suggestions-content').innerHTML = html;
                    elements.searchSuggestions.style.display = 'block';
                } else {
                    hideSearchSuggestions();
                }
            }
        })
        .catch(error => {
            console.warn('Search suggestions error:', error);
            // Fallback to local suggestions
            const localSuggestions = generateSearchSuggestions(query);
            if (localSuggestions.length > 0) {
                let html = '';
                localSuggestions.forEach(suggestion => {
                    html += `<div class="suggestion-item" onclick="selectSearchSuggestion('${escapeHtml(suggestion)}')">${escapeHtml(suggestion)}</div>`;
                });
                
                elements.searchSuggestions.querySelector('.suggestions-content').innerHTML = html;
                elements.searchSuggestions.style.display = 'block';
            } else {
                hideSearchSuggestions();
            }
        });
    }
    
    function hideSearchSuggestions() {
        if (elements.searchSuggestions) {
            elements.searchSuggestions.style.display = 'none';
        }
    }
    
    function generateSearchSuggestions(query) {
        const commonSuggestions = [
            'IT導入補助金',
            'ものづくり補助金',
            '小規模事業者持続化補助金',
            '事業再構築補助金',
            'キャリアアップ助成金',
            'デジタル化支援',
            'スタートアップ支援',
            '研究開発支援',
            '人材育成支援',
            '省エネ設備導入'
        ];
        
        return commonSuggestions.filter(suggestion => 
            suggestion.toLowerCase().includes(query.toLowerCase())
        ).slice(0, 5);
    }
    
    function selectSearchSuggestion(suggestion) {
        if (elements.keywordSearch) {
            elements.keywordSearch.value = suggestion;
            state.filters.search = suggestion;
            state.currentPage = 1;
            loadGrants();
        }
        hideSearchSuggestions();
    }
    
    function updateSearchPlaceholder(searchType) {
        if (!elements.keywordSearch) return;
        
        const placeholders = {
            all: '助成金名、実施機関、対象事業、キーワードなどを入力...',
            title: '助成金名を入力...',
            organization: '実施機関名を入力...',
            target: '対象者・対象事業を入力...',
            content: '内容・キーワードを入力...'
        };
        
        elements.keywordSearch.placeholder = placeholders[searchType] || placeholders.all;
    }
    
    // Global function for suggestion selection
    window.selectSearchSuggestion = selectSearchSuggestion;
    

    
    function hideMunicipalityFilter() {
        if (elements.municipalityFilterSection) {
            elements.municipalityFilterSection.style.display = 'none';
        }
        // Close other municipalities section if open
        if (elements.otherMunicipalitiesSection) {
            elements.otherMunicipalitiesSection.style.display = 'none';
        }
        if (elements.toggleOtherMunicipalitiesBtn) {
            elements.toggleOtherMunicipalitiesBtn.classList.remove('active');
            const toggleText = elements.toggleOtherMunicipalitiesBtn.querySelector('.toggle-text');
            if (toggleText) {
                toggleText.textContent = 'その他の市町村を表示';
            }
        }
        // Reset municipality filter status
        updateFilterStatus('municipality', '');
    }
    
    // ============================================
    // AI質問機能をアーカイブページに追加
    // ============================================
    
    // HTMLエスケープ関数
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // グローバル変数
    let currentEscHandler = null;
    
    // AIチャットモーダル表示関数
    window.showAIChatModal = function(postId, grantTitle) {
        console.log('📱 Opening AI Chat Modal:', postId, grantTitle);
        
        // 既存のモーダルを削除
        const existingModal = document.querySelector('.grant-ai-modal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // モーダルHTML作成
        const modalHTML = `
            <div class="grant-ai-modal" id="grant-ai-modal" style="
                position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 10000;
                display: flex; align-items: center; justify-content: center;
                background: rgba(0, 0, 0, 0.8); opacity: 0; animation: fadeIn 0.3s ease forwards;
            ">
                <div class="grant-ai-modal-container" style="
                    position: relative; width: 90%; max-width: 550px; height: 75vh; max-height: 650px;
                    background: white; border: 2px solid black; border-radius: 12px;
                    display: flex; flex-direction: column; overflow: hidden;
                    animation: slideUp 0.3s ease;
                ">
                    <div class="grant-ai-modal-header" style="
                        padding: 20px; background: black; color: white; position: relative;
                    ">
                        <div class="grant-ai-modal-title" style="
                            display: flex; align-items: center; gap: 10px; font-size: 16px;
                            font-weight: 800; margin-bottom: 8px;
                        ">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                <circle cx="9" cy="10" r="1"/>
                                <circle cx="15" cy="10" r="1"/>
                            </svg>
                            <span>AI助成金アシスタント</span>
                        </div>
                        <div class="grant-ai-modal-subtitle" style="
                            font-size: 13px; opacity: 0.9; max-width: 85%; white-space: nowrap;
                            overflow: hidden; text-overflow: ellipsis;
                        ">${escapeHtml(grantTitle)}</div>
                        <button class="grant-ai-modal-close" style="
                            position: absolute; top: 16px; right: 16px; width: 32px; height: 32px;
                            border: 1.5px solid rgba(255, 255, 255, 0.3); background: transparent;
                            color: white; border-radius: 4px; cursor: pointer;
                            display: flex; align-items: center; justify-content: center;
                        " onclick="closeAIChatModal()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"/>
                                <line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>
                    <div class="grant-ai-modal-body" style="
                        flex: 1; display: flex; flex-direction: column; overflow: hidden;
                    ">
                        <div class="grant-ai-chat-messages" id="ai-chat-messages-${postId}" style="
                            flex: 1; padding: 20px; overflow-y: auto; background: #fafafa;
                            display: flex; flex-direction: column; gap: 16px;
                        ">
                            <div class="grant-ai-message grant-ai-message--assistant" style="
                                display: flex; gap: 10px; max-width: 85%; align-self: flex-start;
                            ">
                                <div class="grant-ai-message-avatar" style="
                                    width: 36px; height: 36px; border-radius: 4px;
                                    display: flex; align-items: center; justify-content: center;
                                    background: black; color: white; border: 1.5px solid black;
                                ">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 2v20M2 12h20"/>
                                    </svg>
                                </div>
                                <div class="grant-ai-message-content" style="
                                    background: white; padding: 14px; border-radius: 12px;
                                    border: 1.5px solid #e5e5e5; font-size: 14px; line-height: 1.6;
                                ">
                                    こんにちは！この助成金について何でもお聞きください。申請条件、必要書類、申請方法、対象経費など、詳しくお答えします。
                                </div>
                            </div>
                        </div>
                        <div class="grant-ai-chat-input-container" style="
                            padding: 20px; background: white; border-top: 1.5px solid #e5e5e5;
                        ">
                            <div class="grant-ai-chat-input-wrapper" style="
                                display: flex; gap: 10px; margin-bottom: 12px;
                            ">
                                <textarea class="grant-ai-chat-input" id="ai-chat-input-${postId}" style="
                                    flex: 1; padding: 12px; border: 1.5px solid #d4d4d4;
                                    border-radius: 12px; font-family: inherit; font-size: 14px;
                                    resize: none; min-height: 48px; max-height: 96px;
                                " placeholder="例：申請条件は何ですか？"></textarea>
                                <button class="grant-ai-chat-send" id="ai-chat-send-${postId}" style="
                                    width: 48px; height: 48px; background: black; color: white;
                                    border: 1.5px solid black; border-radius: 12px; cursor: pointer;
                                    display: flex; align-items: center; justify-content: center;
                                " onclick="sendAIQuestion('${postId}')">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="22" y1="2" x2="11" y2="13"/>
                                        <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="grant-ai-chat-suggestions" style="
                                display: flex; flex-wrap: wrap; gap: 8px;
                            ">
                                <button class="grant-ai-suggestion" onclick="selectSuggestion('${postId}', '申請条件を詳しく教えてください')" style="
                                    padding: 8px 14px; background: white; border: 1.5px solid #d4d4d4;
                                    border-radius: 12px; font-size: 12px; font-weight: 600;
                                    color: #525252; cursor: pointer;
                                ">申請条件は？</button>
                                <button class="grant-ai-suggestion" onclick="selectSuggestion('${postId}', '必要な書類を教えてください')" style="
                                    padding: 8px 14px; background: white; border: 1.5px solid #d4d4d4;
                                    border-radius: 12px; font-size: 12px; font-weight: 600;
                                    color: #525252; cursor: pointer;
                                ">必要書類は？</button>
                                <button class="grant-ai-suggestion" onclick="selectSuggestion('${postId}', 'どんな費用が対象になりますか？')" style="
                                    padding: 8px 14px; background: white; border: 1.5px solid #d4d4d4;
                                    border-radius: 12px; font-size: 12px; font-weight: 600;
                                    color: #525252; cursor: pointer;
                                ">対象経費は？</button>
                                <button class="grant-ai-suggestion" onclick="selectSuggestion('${postId}', '申請方法を教えてください')" style="
                                    padding: 8px 14px; background: white; border: 1.5px solid #d4d4d4;
                                    border-radius: 12px; font-size: 12px; font-weight: 600;
                                    color: #525252; cursor: pointer;
                                ">申請方法は？</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // モーダルをDOMに追加
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        console.log('✅ Modal HTML inserted');
        
        // 入力欄にフォーカス
        setTimeout(() => {
            const input = document.getElementById(`ai-chat-input-${postId}`);
            if (input) {
                input.focus();
            }
        }, 100);
        
        // ESCキーで閉じる
        currentEscHandler = function(e) {
            if (e.key === 'Escape') {
                closeAIChatModal();
            }
        };
        document.addEventListener('keydown', currentEscHandler);
    };
    
    // モーダルを閉じる関数
    window.closeAIChatModal = function() {
        console.log('🚷 Closing AI Chat Modal');
        const modal = document.querySelector('.grant-ai-modal');
        if (modal) {
            modal.style.opacity = '0';
            setTimeout(() => {
                modal.remove();
                if (currentEscHandler) {
                    document.removeEventListener('keydown', currentEscHandler);
                    currentEscHandler = null;
                }
                console.log('✅ Modal closed and removed');
            }, 300);
        }
    };
    
    // 質問候補選択関数
    window.selectSuggestion = function(postId, question) {
        console.log('💡 Selecting suggestion:', question);
        const input = document.getElementById(`ai-chat-input-${postId}`);
        if (input) {
            input.value = question;
            input.focus();
            setTimeout(() => {
                sendAIQuestion(postId);
            }, 300);
        }
    };
    
    // AI質問送信関数
    window.sendAIQuestion = function(postId) {
        console.log('📤 Sending AI question for post:', postId);
        
        const input = document.getElementById(`ai-chat-input-${postId}`);
        const sendBtn = document.getElementById(`ai-chat-send-${postId}`);
        const messagesContainer = document.getElementById(`ai-chat-messages-${postId}`);
        
        if (!input || !messagesContainer) {
            console.error('❌ Required elements not found');
            return;
        }
        
        const question = input.value.trim();
        if (!question) {
            console.warn('⚠️ Empty question');
            return;
        }
        
        console.log('📝 Question:', question);
        
        // 送信ボタンを無効化
        if (sendBtn) {
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>';
        }
        
        // ユーザーメッセージを追加
        const userMessage = document.createElement('div');
        userMessage.className = 'grant-ai-message grant-ai-message--user';
        userMessage.style.cssText = 'display: flex; gap: 10px; max-width: 85%; align-self: flex-end; flex-direction: row-reverse;';
        userMessage.innerHTML = `
            <div style="
                width: 36px; height: 36px; border-radius: 4px;
                display: flex; align-items: center; justify-content: center;
                background: white; color: black; border: 1.5px solid black;
            ">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <div style="
                background: black; color: white; padding: 14px; border-radius: 12px;
                border: 1.5px solid black; font-size: 14px; line-height: 1.6;
            ">${escapeHtml(question)}</div>
        `;
        messagesContainer.appendChild(userMessage);
        
        // 入力をクリア
        input.value = '';
        
        // スクロールダウン
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        
        console.log('📡 Sending AJAX request...');
        
        // AJAX リクエスト
        const formData = new FormData();
        formData.append('action', 'handle_grant_ai_question');
        formData.append('post_id', postId);
        formData.append('question', question);
        formData.append('nonce', NONCE);
        
        fetch(AJAX_URL, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('📥 Response received:', response.status, response.statusText);
            
            if (!response.ok) {
                if (response.status === 403) {
                    throw new Error('セキュリティエラー: ページをリフレッシュしてください');
                } else if (response.status === 500) {
                    throw new Error('サーバーエラー: しばらく待ってから再度お試しください');
                } else {
                    throw new Error(`HTTPエラー: ${response.status}`);
                }
            }
            
            return response.json();
        })
        .then(data => {
            console.log('📦 Response data:', data);
            
            // ローディング表示を追加
            const loadingMessage = document.createElement('div');
            loadingMessage.className = 'grant-ai-message grant-ai-message--assistant';
            loadingMessage.style.cssText = 'display: flex; gap: 10px; max-width: 85%; align-self: flex-start;';
            loadingMessage.innerHTML = `
                <div style="
                    width: 36px; height: 36px; border-radius: 4px;
                    display: flex; align-items: center; justify-content: center;
                    background: black; color: white; border: 1.5px solid black;
                ">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                    </svg>
                </div>
                <div style="
                    background: white; padding: 14px; border-radius: 12px;
                    border: 1.5px solid #e5e5e5; font-size: 14px; line-height: 1.6;
                ">
                    <div style="display: flex; gap: 4px;">
                        <span style="width: 7px; height: 7px; background: #a3a3a3; border-radius: 50%; animation: typing 1.4s infinite ease-in-out;"></span>
                        <span style="width: 7px; height: 7px; background: #a3a3a3; border-radius: 50%; animation: typing 1.4s infinite ease-in-out; animation-delay: 0.2s;"></span>
                        <span style="width: 7px; height: 7px; background: #a3a3a3; border-radius: 50%; animation: typing 1.4s infinite ease-in-out; animation-delay: 0.4s;"></span>
                    </div>
                </div>
            `;
            messagesContainer.appendChild(loadingMessage);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            
            // 1.5秒後にレスポンスを表示
            setTimeout(() => {
                loadingMessage.remove();
                
                if (data.success) {
                    console.log('✅ Success response');
                    const assistantMessage = document.createElement('div');
                    assistantMessage.className = 'grant-ai-message grant-ai-message--assistant';
                    assistantMessage.style.cssText = 'display: flex; gap: 10px; max-width: 85%; align-self: flex-start;';
                    assistantMessage.innerHTML = `
                        <div style="
                            width: 36px; height: 36px; border-radius: 4px;
                            display: flex; align-items: center; justify-content: center;
                            background: black; color: white; border: 1.5px solid black;
                        ">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2v20M2 12h20"/>
                            </svg>
                        </div>
                        <div style="
                            background: white; padding: 14px; border-radius: 12px;
                            border: 1.5px solid #e5e5e5; font-size: 14px; line-height: 1.6;
                        ">${escapeHtml(data.data.response)}</div>
                    `;
                    messagesContainer.appendChild(assistantMessage);
                } else {
                    console.error('❌ Error response:', data);
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'grant-ai-message grant-ai-message--error';
                    errorMessage.style.cssText = 'display: flex; gap: 10px; max-width: 85%; align-self: flex-start;';
                    errorMessage.innerHTML = `
                        <div style="
                            width: 36px; height: 36px; border-radius: 4px;
                            display: flex; align-items: center; justify-content: center;
                            background: #525252; color: white; border: 1.5px solid #525252;
                        ">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                        </div>
                        <div style="
                            background: #f5f5f5; padding: 14px; border-radius: 12px;
                            border: 1.5px solid #a3a3a3; font-size: 14px; line-height: 1.6; color: #262626;
                        ">申し訳ございません。エラーが発生しました。しばらく時間をおいて再度お試しください。</div>
                    `;
                    messagesContainer.appendChild(errorMessage);
                }
                
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }, 1500);
        })
        .catch(error => {
            console.error('❌ Fetch error details:', error);
            
            // エラーメッセージを表示
            const errorMessage = document.createElement('div');
            errorMessage.className = 'grant-ai-message grant-ai-message--error';
            errorMessage.style.cssText = 'display: flex; gap: 10px; max-width: 85%; align-self: flex-start;';
            
            let errorText = 'エラーが発生しました。';
            if (error.message.includes('セキュリティ')) {
                errorText = 'セキュリティエラー: ページをリフレッシュして再度お試しください。';
            } else if (error.message.includes('サーバー')) {
                errorText = 'サーバーエラー: しばらく待ってから再度お試しください。';
            } else if (error.name === 'TypeError' && error.message.includes('fetch')) {
                errorText = 'インターネット接続エラー: 接続を確認して再度お試しください。';
            }
            
            errorMessage.innerHTML = `
                <div style="
                    width: 36px; height: 36px; border-radius: 4px;
                    display: flex; align-items: center; justify-content: center;
                    background: #525252; color: white; border: 1.5px solid #525252;
                ">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                </div>
                <div style="
                    background: #f5f5f5; padding: 14px; border-radius: 12px;
                    border: 1.5px solid #a3a3a3; font-size: 14px; line-height: 1.6; color: #262626;
                ">${errorText}</div>
            `;
            messagesContainer.appendChild(errorMessage);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        })
        .finally(() => {
            // 送信ボタンを復活
            if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>';
            }
            input.focus();
            console.log('✅ Request completed');
        });
    };
    
    // AIボタンにイベントリスナーを追加
    function setupAIButtonListeners() {
        console.log('🔧 Setting up AI button listeners...');
        const aiButtons = document.querySelectorAll('.grant-btn-compact--ai');
        console.log(`📊 Found ${aiButtons.length} AI buttons`);
        
        aiButtons.forEach((btn, index) => {
            // 既存のリスナーを削除して重複を防止
            btn.removeEventListener('click', handleAIButtonClick);
            btn.addEventListener('click', handleAIButtonClick);
            
            const postId = btn.getAttribute('data-post-id');
            const title = btn.getAttribute('data-grant-title');
            console.log(`✅ Button ${index + 1}: postId=${postId}, title="${title}"`);
        });
        
        console.log('🎯 AI button listeners setup completed');
    }
    
    function handleAIButtonClick(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        console.log('🚀 AI Button clicked via event listener!');
        
        const postId = this.getAttribute('data-post-id');
        const title = this.getAttribute('data-grant-title');
        
        console.log('📋 Data attributes:', { postId, title });
        
        if (postId && title) {
            console.log('🎪 Opening AI modal...');
            showAIChatModal(postId, title);
        } else {
            console.error('❌ Missing data attributes:', { postId, title });
        }
    }
    
    // グローバルアクセス用に関数を公開
    window.setupAIButtonListeners = setupAIButtonListeners;
    window.handleAIButtonClick = handleAIButtonClick;
    
    // 初期設定とAJAX後の再設定
    setupAIButtonListeners();
    
    // displayGrants関数を拡張してAIボタンのイベントを再設定
    const originalDisplayGrants = window.displayGrants || function() {};
    window.displayGrants = function(grants) {
        // 元のdisplayGrantsを実行
        if (originalDisplayGrants && typeof originalDisplayGrants === 'function') {
            originalDisplayGrants(grants);
        } else {
            // フォールバック処理
            if (elements.grantsContainer) {
                if (!grants || grants.length === 0) {
                    elements.grantsContainer.innerHTML = '';
                    elements.grantsContainer.style.display = 'none';
                    if (elements.noResults) {
                        elements.noResults.style.display = 'block';
                    }
                    return;
                }
                elements.grantsContainer.style.display = 'grid';
                if (elements.noResults) {
                    elements.noResults.style.display = 'none';
                }
                elements.grantsContainer.innerHTML = grants.map(grant => grant.html).join('');
            }
        }
        
        // AIボタンのイベントリスナーを再設定
        setTimeout(setupAIButtonListeners, 100);
    };
    
    // CSSアニメーションを追加
    const aiStyles = document.createElement('style');
    aiStyles.innerHTML = `
        @keyframes fadeIn {
            to { opacity: 1; }
        }
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes typing {
            0%, 80%, 100% { 
                transform: scale(0.8); 
                opacity: 0.5; 
            }
            40% { 
                transform: scale(1); 
                opacity: 1; 
            }
        }
    `;
    document.head.appendChild(aiStyles);
    
    
    // ============================================
    // 初期化処理の継続
    // ============================================
    
    // 初期化処理
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    console.log('✅ Archive page script loaded successfully');
})();
</script>

<?php get_footer(); ?>