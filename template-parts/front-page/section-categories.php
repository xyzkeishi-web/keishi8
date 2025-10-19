<?php
/**
 * Modern Categories Section - Clean Style v3.3
 * カテゴリー別助成金検索セクション - クリーンスタイル
 *
 * @package Grant_Insight_Perfect
 * @version 24.3-clean-style
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit;
}

// データベースから実際のカテゴリと件数を取得
$main_categories = get_terms(array(
    'taxonomy' => 'grant_category',
    'hide_empty' => false,
    'orderby' => 'count',
    'order' => 'DESC',
    'number' => 6
));

$all_categories = get_terms(array(
    'taxonomy' => 'grant_category',
    'hide_empty' => false,
    'orderby' => 'name',
    'order' => 'ASC'
));

// カテゴリアイコン設定（白黒）
$category_icons = array(
    0 => 'fas fa-laptop-code',
    1 => 'fas fa-industry',
    2 => 'fas fa-rocket',
    3 => 'fas fa-store',
    4 => 'fas fa-leaf',
    5 => 'fas fa-users'
);

$archive_base_url = get_post_type_archive_link('grant');

// 統計情報を取得
if (function_exists('gi_get_cached_stats')) {
    $stats = gi_get_cached_stats();
} else {
    $stats = array(
        'total_grants' => wp_count_posts('grant')->publish ?? 0,
        'active_grants' => 0
    );
}
?>

<!-- フォント・アイコン読み込み -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Noto+Sans+JP:wght@400;500;700;900&display=swap" rel="stylesheet">

<!-- カテゴリーセクション - クリーンスタイル -->
<section class="giac-categories-section" id="grant-categories">
    <div class="giac-container">
        <!-- セクションヘッダー -->
        <div class="giac-header">
            <h2 class="giac-title">
                <span class="giac-title-en">CATEGORY SEARCH</span>
            </h2>
            <p class="giac-title-ja">カテゴリー別AI検索</p>
            <div class="giac-yellow-line"></div>
            <p class="giac-subtitle">最適な補助金を業種・目的別に発見</p>
        </div>

        <!-- メインカテゴリー -->
        <div class="giac-main-categories">
            <?php
            if (!empty($main_categories)) :
                foreach ($main_categories as $index => $category) :
                    if ($index >= 6) break;
                    $icon = $category_icons[$index] ?? 'fas fa-folder';
                    $category_url = get_term_link($category);
            ?>
            <a href="<?php echo esc_url($category_url); ?>" class="giac-category-card" data-aos="fade-up" data-aos-delay="<?php echo $index * 50; ?>">
                <div class="giac-card-number"><?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?></div>
                <div class="giac-card-icon">
                    <i class="<?php echo esc_attr($icon); ?>"></i>
                </div>
                <div class="giac-card-content">
                    <h3 class="giac-card-title"><?php echo esc_html($category->name); ?></h3>
                    <div class="giac-card-count"><?php echo number_format($category->count); ?>件</div>
                </div>
                <div class="giac-card-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </a>
            <?php
                endforeach;
            endif;
            ?>
        </div>

        <!-- その他のカテゴリー -->
        <?php if (!empty($all_categories) && count($all_categories) > 6) :
            $other_categories = array_slice($all_categories, 6);
        ?>
        <div class="giac-more-section">
            <button type="button" class="giac-more-button" id="giac-toggle-more">
                <span>すべてのカテゴリー</span>
                <i class="fas fa-chevron-down"></i>
            </button>

            <div class="giac-more-categories" id="giac-more-categories">
                <div class="giac-more-grid">
                    <?php foreach ($other_categories as $category) :
                        $category_url = get_term_link($category);
                    ?>
                    <a href="<?php echo esc_url($category_url); ?>" class="giac-mini-card">
                        <i class="fas fa-folder"></i>
                        <span class="giac-mini-title"><?php echo esc_html($category->name); ?></span>
                        <span class="giac-mini-count"><?php echo $category->count; ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- 地域選択セクション -->
        <div class="giac-location-section">
            <h3 class="giac-location-title">
                <i class="fas fa-map-marker-alt"></i>
                地域から探す
            </h3>
            <p class="giac-location-subtitle">都道府県・市町村別の助成金・補助金を検索</p>
            
            <!-- 都道府県選択 -->
            <div class="giac-prefecture-selector">
                <h4 class="giac-selector-title">都道府県を選択</h4>
                <div class="giac-prefecture-grid" id="giac-prefecture-grid">
                    <?php
                    // 都道府県一覧を取得
                    $prefectures = gi_get_all_prefectures();
                    if (!empty($prefectures)) :
                        foreach ($prefectures as $index => $pref) :
                            if ($index >= 8) break; // 最初の8つのみ表示
                            $prefecture_count = get_term_by('slug', $pref['slug'], 'grant_prefecture');
                            $count = $prefecture_count ? $prefecture_count->count : 0;
                    ?>
                    <a href="<?php echo esc_url(get_term_link($pref['slug'], 'grant_prefecture')); ?>" 
                       class="giac-prefecture-card" data-aos="fade-up" data-aos-delay="<?php echo $index * 30; ?>">
                        <div class="giac-prefecture-icon">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <div class="giac-prefecture-content">
                            <h5 class="giac-prefecture-name"><?php echo esc_html($pref['name']); ?></h5>
                            <span class="giac-prefecture-count"><?php echo number_format($count); ?>件</span>
                        </div>
                        <div class="giac-prefecture-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>
                    <?php 
                        endforeach;
                    endif; 
                    ?>
                </div>

                <!-- 全都道府県表示ボタン -->
                <button type="button" class="giac-show-all-prefectures" id="giac-show-all-prefectures">
                    <span class="button-text">すべての都道府県を見る</span>
                    <i class="fas fa-chevron-down button-icon"></i>
                </button>

                <!-- 全都道府県グリッド -->
                <div class="giac-all-prefectures" id="giac-all-prefectures">
                    <div class="giac-all-prefectures-grid">
                        <?php
                        if (!empty($prefectures)) :
                            foreach ($prefectures as $pref) :
                                $prefecture_count = get_term_by('slug', $pref['slug'], 'grant_prefecture');
                                $count = $prefecture_count ? $prefecture_count->count : 0;
                        ?>
                        <a href="<?php echo esc_url(get_term_link($pref['slug'], 'grant_prefecture')); ?>" 
                           class="giac-prefecture-mini-card">
                            <span class="giac-prefecture-mini-name"><?php echo esc_html($pref['name']); ?></span>
                            <span class="giac-prefecture-mini-count"><?php echo $count; ?></span>
                        </a>
                        <?php 
                            endforeach;
                        endif; 
                        ?>
                    </div>
                </div>
            </div>

            <!-- 市町村検索 -->
            <div class="giac-municipality-selector">
                <h4 class="giac-selector-title">市町村で検索</h4>
                <div class="giac-search-wrapper">
                    <div class="giac-search-container">
                        <input type="text" 
                               id="giac-municipality-search" 
                               class="giac-search-input" 
                               placeholder="市町村名を入力してください（例：横浜市、大阪市）"
                               autocomplete="off">
                        <button type="button" class="giac-search-button" id="giac-search-municipality">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    
                    <!-- 検索結果 -->
                    <div class="giac-search-results" id="giac-search-results">
                        <!-- 検索結果がここに表示されます -->
                    </div>
                    
                    <!-- 人気の市町村セクションを削除しました -->
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* ============================================
   カテゴリーセクション - クリーンスタイル
   ============================================ */

/* ベース設定 */
.giac-categories-section {
    position: relative;
    padding: 64px 0;
    background: transparent;
    font-family: 'Inter', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, sans-serif;
    isolation: isolate;
}

/* コンテナ */
.giac-container {
    position: relative;
    z-index: 1;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* ヘッダー */
.giac-header {
    text-align: center;
    margin-bottom: 40px;
}

.giac-title {
    margin-bottom: 12px;
}

.giac-title-en {
    display: block;
    font-size: 32px;
    font-weight: 900;
    color: #000000;
    letter-spacing: 0.05em;
    line-height: 1.1;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.05);
}

.giac-title-ja {
    font-size: 15px;
    font-weight: 700;
    color: #000000;
    line-height: 1.4;
    margin: 12px 0;
}

.giac-yellow-line {
    width: 64px;
    height: 3px;
    background: #ffeb3b;
    margin: 0 auto 13px;
    border-radius: 2px;
    box-shadow: 0 2px 8px rgba(255, 235, 59, 0.4);
}

.giac-subtitle {
    font-size: 13px;
    font-weight: 500;
    color: #333333;
    line-height: 1.6;
}

/* メインカテゴリー */
.giac-main-categories {
    display: grid;
    gap: 13px;
    margin-bottom: 32px;
}

.giac-category-card {
    position: relative;
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 16px;
    background: #ffffff;
    border: 2px solid #000000;
    border-radius: 13px;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.giac-card-number {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 32px;
    height: 32px;
    background: #ffeb3b;
    color: #000000;
    border: 2px solid #000000;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 900;
    letter-spacing: -0.02em;
    z-index: 2;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
}

.giac-category-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: #ffeb3b;
    transform: scaleY(0);
    transition: transform 0.3s ease;
}

.giac-category-card:active {
    transform: scale(0.98);
}

.giac-category-card:hover {
    border-color: #000000;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.giac-category-card:hover::before {
    transform: scaleY(1);
}

.giac-card-icon {
    flex-shrink: 0;
    width: 45px;
    height: 45px;
    background: #000000;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 19px;
    transition: all 0.3s ease;
}

.giac-category-card:hover .giac-card-icon {
    background: #333333;
    transform: rotate(-5deg) scale(1.05);
}

.giac-card-content {
    flex: 1;
}

.giac-card-title {
    font-size: 13px;
    font-weight: 700;
    color: #000000;
    line-height: 1.4;
    margin-bottom: 4px;
}

.giac-card-count {
    font-size: 11px;
    font-weight: 600;
    color: #666666;
}

.giac-card-arrow {
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    background: #f5f5f5;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #000000;
    font-size: 14px;
    transition: all 0.3s ease;
}

.giac-category-card:hover .giac-card-arrow {
    background: #ffeb3b;
    transform: translateX(4px);
}

.giac-category-card:hover .giac-card-number {
    background: #000000;
    color: #ffeb3b;
    transform: scale(1.1) rotate(5deg);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
}

/* その他のカテゴリー */
.giac-more-section {
    margin-bottom: 0;
}

.giac-more-button {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 14px;
    background: #ffffff;
    border: 2px solid #000000;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 700;
    color: #000000;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.giac-more-button:active {
    transform: scale(0.98);
}

.giac-more-button:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.giac-more-button.active {
    background: #000000;
    color: #ffffff;
    border-color: #000000;
}

.giac-more-button i {
    transition: transform 0.3s ease;
}

.giac-more-button.active i {
    transform: rotate(180deg);
}

.giac-more-categories {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s ease;
    margin-top: 16px;
}

.giac-more-categories.show {
    max-height: 2000px;
}

.giac-more-grid {
    display: grid;
    gap: 8px;
}

.giac-mini-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
}

.giac-mini-card:active {
    transform: scale(0.98);
}

.giac-mini-card:hover {
    border-color: #000000;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.giac-mini-card i {
    font-size: 18px;
    color: #666666;
}

.giac-mini-title {
    flex: 1;
    font-size: 12px;
    font-weight: 600;
    color: #000000;
}

.giac-mini-count {
    padding: 3px 8px;
    background: #f5f5f5;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 700;
    color: #666666;
}

/* アニメーション */
[data-aos] {
    opacity: 0;
    transition: opacity 0.6s ease, transform 0.6s ease;
}

[data-aos="fade-up"] {
    transform: translateY(20px);
}

[data-aos].aos-animate {
    opacity: 1;
    transform: translateY(0);
}

/* タブレット */
@media (min-width: 768px) {
    .giac-categories-section {
        padding: 80px 0;
    }
    
    .giac-header {
        margin-bottom: 48px;
    }
    
    .giac-title-en {
        font-size: 44px;
    }
    
    .giac-title-ja {
        font-size: 16px;
    }
    
    .giac-subtitle {
        font-size: 12px;
    }
    
    .giac-main-categories {
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
        margin-bottom: 40px;
    }
    
    .giac-category-card {
        padding: 19px;
    }
    
    .giac-card-number {
        width: 28px;
        height: 28px;
        font-size: 12px;
        top: -6px;
        right: -6px;
    }
    
    .giac-card-title {
        font-size: 13px;
    }
    
    .giac-card-count {
        font-size: 10px;
    }
    
    .giac-more-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
}

/* デスクトップ */
@media (min-width: 1024px) {
    .giac-title-en {
        font-size: 48px;
    }
    
    .giac-main-categories {
        grid-template-columns: repeat(3, 1fr);
        gap: 19px;
    }
    
    .giac-category-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    }
    
    .giac-more-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

/* スマホ最適化 */
@media (max-width: 640px) {
    .giac-categories-section {
        padding: 60px 0;
    }
    
    .giac-container {
        padding: 0 16px;
    }
    
    .giac-header {
        margin-bottom: 40px;
    }
    
    .giac-title-en {
        font-size: 36px;
        letter-spacing: 0.02em;
    }
    
    .giac-title-ja {
        font-size: 13px;
        margin: 8px 0;
    }
    
    .giac-yellow-line {
        width: 60px;
        margin: 0 auto 12px;
    }
    
    .giac-subtitle {
        font-size: 12px;
    }
    
    .giac-main-categories {
        gap: 10px;
        margin-bottom: 26px;
    }
    
    .giac-category-card {
        padding: 13px;
    }
    
    .giac-card-number {
        width: 24px;
        height: 24px;
        font-size: 10px;
        top: -5px;
        right: -5px;
    }
    
    .giac-card-icon {
        width: 48px;
        height: 48px;
        font-size: 20px;
    }
    
    .giac-card-title {
        font-size: 13px;
    }
    
    .giac-card-count {
        font-size: 10px;
    }
    
    .giac-more-button {
        padding: 12px;
        font-size: 12px;
    }
    
    .giac-more-grid {
        gap: 8px;
    }
    
    .giac-mini-card {
        padding: 12px 14px;
    }
    
    .giac-mini-card i {
        font-size: 16px;
    }
    
    .giac-mini-title {
        font-size: 11px;
    }
    
    .giac-mini-count {
        font-size: 11px;
        padding: 3px 8px;
    }
}

/* 極小スマホ */
@media (max-width: 375px) {
    .giac-title-en {
        font-size: 26px;
    }
    
    .giac-title-ja {
        font-size: 12px;
    }
    
    .giac-card-icon {
        width: 44px;
        height: 44px;
        font-size: 18px;
    }
    
    .giac-card-title {
        font-size: 12px;
    }
}

/* ========================================
   地域選択セクション - 白黒スタイリッシュデザイン
======================================== */

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
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.giac-location-section {
    margin-top: 60px;
    padding-top: 50px;
    border-top: 3px solid var(--color-black);
    background: var(--color-white);
}

.giac-location-title {
    font-size: 22px;
    font-weight: 900;
    color: var(--color-black);
    margin: 0 0 8px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.giac-location-title i {
    color: var(--color-black);
    font-size: 24px;
}

.giac-location-subtitle {
    font-size: 13px;
    color: var(--color-gray-600);
    margin: 0 0 32px;
    font-weight: 500;
}

/* 都道府県セレクター */
.giac-prefecture-selector {
    margin-bottom: 50px;
}

.giac-selector-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--color-black);
    margin: 0 0 16px;
    padding-left: 10px;
    border-left: 3px solid var(--color-black);
}

.giac-prefecture-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 16px;
    margin-bottom: 30px;
}

.giac-prefecture-card {
    position: relative;
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    background: var(--color-white);
    border: 2px solid var(--color-gray-200);
    border-radius: 16px;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}

.giac-prefecture-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: var(--color-black);
    transform: scaleY(0);
    transition: transform 0.3s ease;
}

.giac-prefecture-card:hover {
    border-color: var(--color-black);
    box-shadow: var(--shadow-lg);
    transform: translateY(-2px);
}

.giac-prefecture-card:hover::before {
    transform: scaleY(1);
}

.giac-prefecture-icon {
    flex-shrink: 0;
    width: 50px;
    height: 50px;
    background: var(--color-black);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-white);
    font-size: 20px;
    transition: all 0.3s ease;
}

.giac-prefecture-card:hover .giac-prefecture-icon {
    transform: rotate(-5deg) scale(1.1);
}

.giac-prefecture-content {
    flex: 1;
}

.giac-prefecture-name {
    font-size: 13px;
    font-weight: 700;
    color: var(--color-black);
    margin: 0 0 3px;
}

.giac-prefecture-count {
    font-size: 11px;
    font-weight: 600;
    color: var(--color-gray-600);
}

.giac-prefecture-arrow {
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    background: var(--color-gray-100);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-gray-600);
    font-size: 14px;
    transition: all 0.3s ease;
}

.giac-prefecture-card:hover .giac-prefecture-arrow {
    background: var(--color-black);
    color: var(--color-white);
    transform: translateX(4px);
}

/* 全都道府県表示ボタン */
.giac-show-all-prefectures {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 16px;
    background: var(--color-white);
    border: 2px solid var(--color-black);
    border-radius: 12px;
    font-size: 15px;
    font-weight: 700;
    color: var(--color-black);
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 20px;
}

.giac-show-all-prefectures:hover {
    background: var(--color-black);
    color: var(--color-white);
}

.giac-show-all-prefectures.active {
    background: var(--color-black);
    color: var(--color-white);
}

.giac-show-all-prefectures .button-icon {
    transition: transform 0.3s ease;
}

.giac-show-all-prefectures.active .button-icon {
    transform: rotate(180deg);
}

/* 全都道府県グリッド */
.giac-all-prefectures {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s ease;
}

.giac-all-prefectures.show {
    max-height: 1000px;
}

.giac-all-prefectures-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 8px;
    padding: 20px;
    background: var(--color-gray-50);
    border-radius: 12px;
}

.giac-prefecture-mini-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: var(--color-white);
    border: 1px solid var(--color-gray-200);
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s ease;
}

.giac-prefecture-mini-card:hover {
    border-color: var(--color-black);
    box-shadow: var(--shadow-md);
}

.giac-prefecture-mini-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--color-black);
}

.giac-prefecture-mini-count {
    font-size: 12px;
    color: var(--color-gray-600);
    background: var(--color-gray-100);
    padding: 2px 8px;
    border-radius: 12px;
}

/* 市町村セレクター */
.giac-municipality-selector {
    margin-bottom: 40px;
}

.giac-search-wrapper {
    max-width: 600px;
}

.giac-search-container {
    position: relative;
    display: flex;
    margin-bottom: 20px;
}

.giac-search-input {
    flex: 1;
    padding: 16px 20px;
    font-size: 16px;
    color: var(--color-black);
    background: var(--color-white);
    border: 2px solid var(--color-gray-300);
    border-right: none;
    border-radius: 12px 0 0 12px;
    transition: all 0.2s ease;
}

.giac-search-input:focus {
    outline: none;
    border-color: var(--color-black);
}

.giac-search-input::placeholder {
    color: var(--color-gray-500);
}

.giac-search-button {
    padding: 16px 20px;
    background: var(--color-black);
    color: var(--color-white);
    border: none;
    border-radius: 0 12px 12px 0;
    cursor: pointer;
    transition: all 0.2s ease;
}

.giac-search-button:hover {
    background: var(--color-gray-800);
}

.giac-search-button i {
    font-size: 16px;
}

/* 検索結果 */
.giac-search-results {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
    background: var(--color-white);
    border: 2px solid var(--color-gray-200);
    border-radius: 12px;
    margin-bottom: 20px;
}

.giac-search-results.show {
    max-height: 300px;
    overflow-y: auto;
}

.giac-search-result-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
    text-decoration: none;
    border-bottom: 1px solid var(--color-gray-100);
    transition: background-color 0.2s ease;
}

.giac-search-result-item:last-child {
    border-bottom: none;
}

.giac-search-result-item:hover {
    background: var(--color-gray-50);
}

.giac-search-result-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--color-black);
}

.giac-search-result-count {
    font-size: 12px;
    color: var(--color-gray-600);
}

/* 人気の市町村セクション削除済み - スタイリッシュな白黒デザインに統一 */

/* レスポンシブ対応 */
@media (max-width: 768px) {
    .giac-location-section {
        margin-top: 40px;
        padding-top: 40px;
    }
    
    .giac-location-title {
        font-size: 24px;
    }
    
    .giac-prefecture-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .giac-prefecture-card {
        padding: 16px;
    }
    
    .giac-all-prefectures-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }
    
    .giac-search-input {
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .giac-location-title {
        font-size: 20px;
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .giac-search-container {
        flex-direction: column;
    }
    
    .giac-search-input {
        border-radius: 12px 12px 0 0;
        border-right: 2px solid var(--color-gray-300);
    }
    
    .giac-search-button {
        border-radius: 0 0 12px 12px;
    }
    
    .giac-popular-grid {
        justify-content: center;
    }
}

/* パフォーマンス最適化 */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // AOS アニメーション
    const aosElements = document.querySelectorAll('[data-aos]');
    const aosObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const delay = entry.target.getAttribute('data-aos-delay') || 0;
                setTimeout(() => {
                    entry.target.classList.add('aos-animate');
                }, delay);
                aosObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    aosElements.forEach(element => {
        aosObserver.observe(element);
    });
    
    // その他のカテゴリー展開
    const moreButton = document.getElementById('giac-toggle-more');
    const moreCategories = document.getElementById('giac-more-categories');
    
    if (moreButton && moreCategories) {
        moreButton.addEventListener('click', function() {
            const isOpen = moreCategories.classList.contains('show');
            
            if (isOpen) {
                moreCategories.classList.remove('show');
                this.classList.remove('active');
                this.querySelector('span').textContent = 'すべてのカテゴリー';
            } else {
                moreCategories.classList.add('show');
                this.classList.add('active');
                this.querySelector('span').textContent = '閉じる';
                
                // スムーズスクロール
                setTimeout(() => {
                    moreCategories.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'nearest' 
                    });
                }, 100);
            }
        });
    }
    
    // カテゴリーカードのタップフィードバック
    document.querySelectorAll('.giac-category-card, .giac-mini-card').forEach(card => {
        card.addEventListener('touchstart', function() {
            this.style.opacity = '0.8';
        });
        
        card.addEventListener('touchend', function() {
            this.style.opacity = '1';
        });
    });
    
    // 地域選択機能の初期化
    initializeLocationSelector();
    
    // パフォーマンス最適化
    if ('requestIdleCallback' in window) {
        requestIdleCallback(() => {
            console.log('Categories section loaded successfully');
        });
    }
});

// 地域選択機能
function initializeLocationSelector() {
    // 全都道府県表示ボタン
    const showAllBtn = document.getElementById('giac-show-all-prefectures');
    const allPrefectures = document.getElementById('giac-all-prefectures');
    
    if (showAllBtn && allPrefectures) {
        showAllBtn.addEventListener('click', function() {
            const isOpen = allPrefectures.classList.contains('show');
            
            if (isOpen) {
                allPrefectures.classList.remove('show');
                showAllBtn.classList.remove('active');
                showAllBtn.querySelector('.button-text').textContent = 'すべての都道府県を見る';
            } else {
                allPrefectures.classList.add('show');
                showAllBtn.classList.add('active');
                showAllBtn.querySelector('.button-text').textContent = '都道府県一覧を閉じる';
            }
        });
    }
    
    // 市町村検索機能
    const searchInput = document.getElementById('giac-municipality-search');
    const searchButton = document.getElementById('giac-search-municipality');
    const searchResults = document.getElementById('giac-search-results');
    
    if (searchInput && searchButton && searchResults) {
        let searchTimeout;
        
        // 検索実行
        function performMunicipalitySearch() {
            const query = searchInput.value.trim();
            
            if (query.length < 2) {
                searchResults.classList.remove('show');
                return;
            }
            
            // ローディング表示
            searchResults.innerHTML = '<div style="padding: 20px; text-align: center; color: #666;">検索中...</div>';
            searchResults.classList.add('show');
            
            // AJAX検索リクエスト
            fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'search_municipalities',
                    query: query,
                    nonce: '<?php echo wp_create_nonce("gi_ajax_nonce"); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.results) {
                    displaySearchResults(data.data.results);
                } else {
                    searchResults.innerHTML = '<div style="padding: 20px; text-align: center; color: #666;">該当する市町村が見つかりませんでした。</div>';
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                searchResults.innerHTML = '<div style="padding: 20px; text-align: center; color: #e74c3c;">検索エラーが発生しました。</div>';
            });
        }
        
        // 検索結果表示
        function displaySearchResults(results) {
            if (results.length === 0) {
                searchResults.innerHTML = '<div style="padding: 20px; text-align: center; color: #666;">該当する市町村が見つかりませんでした。</div>';
                return;
            }
            
            const resultsHtml = results.map(result => `
                <a href="${result.url}" class="giac-search-result-item">
                    <span class="giac-search-result-name">${result.name}</span>
                    <span class="giac-search-result-count">${result.count}件</span>
                </a>
            `).join('');
            
            searchResults.innerHTML = resultsHtml;
        }
        
        // 入力イベント（デバウンス）
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(performMunicipalitySearch, 300);
        });
        
        // 検索ボタンクリック
        searchButton.addEventListener('click', performMunicipalitySearch);
        
        // Enterキー
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performMunicipalitySearch();
            }
        });
        
        // フォーカス外したら検索結果を非表示（少し遅延）
        searchInput.addEventListener('blur', function() {
            setTimeout(() => {
                searchResults.classList.remove('show');
            }, 200);
        });
        
        // フォーカス時に検索結果を再表示
        searchInput.addEventListener('focus', function() {
            if (searchResults.innerHTML.trim() && this.value.trim().length >= 2) {
                searchResults.classList.add('show');
            }
        });
    }
}
</script>

<?php
// デバッグ情報（開発環境のみ）
if (defined('WP_DEBUG') && WP_DEBUG) {
    echo '<!-- Categories Section v3.3 - Clean Style -->';
    echo '<!-- Total Categories: ' . count($all_categories) . ' -->';
    echo '<!-- Main Categories: ' . count($main_categories) . ' -->';
}
?>