<?php
/**
 * Modern Hero Section - Search Style v2.0
 * ヒーローセクション - サーチスタイル統一版
 * @package Grant_Insight_Perfect
 * @version 32.0-search-style
 * 
 * === 主要機能 ===
 * 1. サーチセクションとデザイン統一
 * 2. スマホスクロール最適化
 * 3. パフォーマンス改善
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

// ヘルパー関数
if (!function_exists('gih_safe_output')) {
    function gih_safe_output($text) {
        return esc_html($text);
    }
}

if (!function_exists('gih_get_option')) {
    function gih_get_option($key, $default = '') {
        $value = get_option('gih_' . $key, $default);
        return !empty($value) ? $value : $default;
    }
}

// 設定データ
$hero_config = array(
    'main_title' => gih_get_option('hero_main_title', '補助金・助成金を'),
    'sub_title' => gih_get_option('hero_sub_title', 'AIが効率的に検索'),
    'third_title' => gih_get_option('hero_third_title', '成功まで充実したサポート'),
    'description' => gih_get_option('hero_description', 'あなたのビジネスに最適な補助金・助成金情報を、最新AIテクノロジーが効率的に検索。専門家による申請サポートで豊富な実績を誇ります。'),
    'cta_text' => gih_get_option('hero_cta_text', '無料で助成金を探す'),
    'cta_url' => 'https://joseikin-insight.com/grants/',
    'hero_image' => 'https://joseikin-insight.com/wp-content/uploads/2025/10/1.png'
);

// 構造化データ
$schema_data = array(
    '@context' => 'https://schema.org',
    '@type' => 'WebApplication',
    'name' => '補助金インサイト - AI補助金検索',
    'applicationCategory' => 'BusinessApplication',
    'description' => '全国の補助金・助成金情報をAIが効率的に検索',
    'url' => home_url(),
    'offers' => array(
        '@type' => 'Offer',
        'price' => '0',
        'priceCurrency' => 'JPY'
    )
);
?>

<!-- SEO メタタグ -->
<meta name="description" content="補助金・助成金をAIが効率的に検索｜全国のデータベースから最適な制度を発見。業種別・地域別対応、専門家による申請サポート。完全無料。">
<link rel="canonical" href="<?php echo esc_url(home_url('/')); ?>">

<!-- 構造化データ -->
<script type="application/ld+json">
<?php echo wp_json_encode($schema_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
</script>

<section class="gih-hero-section" id="hero-section" role="banner">
    <div class="gih-container">
        
        <!-- デスクトップレイアウト -->
        <div class="gih-desktop-layout">
            <div class="gih-content-grid">
                
                <!-- 左側：テキストコンテンツ -->
                <div class="gih-content-left">
                    
                    <!-- ステータスバッジ -->
                    <div class="gih-badge">
                        <div class="gih-badge-dot"></div>
                        <span>AI POWERED PLATFORM</span>
                    </div>
                    
                    <!-- メインタイトル -->
                    <h1 class="gih-title">
                        <span class="gih-title-line-1"><?php echo gih_safe_output($hero_config['main_title']); ?></span>
                        <span class="gih-title-line-2">
                            <span class="gih-highlight"><?php echo gih_safe_output($hero_config['sub_title']); ?></span>
                        </span>
                        <span class="gih-title-line-3"><?php echo gih_safe_output($hero_config['third_title']); ?></span>
                    </h1>
                    
                    <!-- 説明文 -->
                    <p class="gih-description">
                        <?php echo gih_safe_output($hero_config['description']); ?>
                    </p>
                    
                    <!-- CTAボタン -->
                    <div class="gih-cta">
                        <a href="<?php echo esc_url($hero_config['cta_url']); ?>" class="gih-btn-primary">
                            <span><?php echo gih_safe_output($hero_config['cta_text']); ?></span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                
                <!-- 右側：画像 -->
                <div class="gih-content-right">
                    <div class="gih-image-wrapper">
                        <img src="<?php echo esc_url($hero_config['hero_image']); ?>" 
                             alt="補助金・助成金検索システム"
                             class="gih-hero-image"
                             width="1200"
                             height="800"
                             loading="eager">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- モバイルレイアウト -->
        <div class="gih-mobile-layout">
            
            <!-- バッジ -->
            <div class="gih-mobile-badge">
                <div class="gih-mobile-badge-dot"></div>
                <span>AI POWERED PLATFORM</span>
            </div>
            
            <!-- タイトル -->
            <h1 class="gih-mobile-title">
                <span class="gih-mobile-line-1"><?php echo gih_safe_output($hero_config['main_title']); ?></span>
                <span class="gih-mobile-line-2">
                    <span class="gih-mobile-highlight"><?php echo gih_safe_output($hero_config['sub_title']); ?></span>
                </span>
                <span class="gih-mobile-line-3"><?php echo gih_safe_output($hero_config['third_title']); ?></span>
            </h1>
            
            <!-- 説明 -->
            <p class="gih-mobile-description">
                最新AIテクノロジーがあなたのビジネスに最適な補助金・助成金を効率的に検索。専門家による充実したサポートで豊富な実績を誇ります。
            </p>
            
            <!-- 画像 -->
            <div class="gih-mobile-image">
                <img src="<?php echo esc_url($hero_config['hero_image']); ?>" 
                     alt="補助金・助成金検索システム"
                     width="800"
                     height="600"
                     loading="eager">
            </div>
            
            <!-- CTA -->
            <div class="gih-mobile-cta">
                <a href="<?php echo esc_url($hero_config['cta_url']); ?>" class="gih-mobile-btn">
                    <span><?php echo gih_safe_output($hero_config['cta_text']); ?></span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<style>
/* ============================================
   ヒーローセクション - サーチスタイル統一版
   ============================================ */

/* ベース設定 */
.gih-hero-section {
    position: relative;
    min-height: 100vh;
    height: auto;
    display: flex;
    align-items: center;
    padding: 120px 0 80px;
    background: #ffffff;
    font-family: 'Inter', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, sans-serif;
    overflow: visible;
    
    /* スマホスクロール最適化 */
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: auto;
}

/* コンテナ */
.gih-container {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px;
}

/* デスクトップレイアウト */
.gih-desktop-layout {
    display: none;
}

@media (min-width: 1024px) {
    .gih-desktop-layout {
        display: block;
    }
    
    .gih-hero-section {
        min-height: 100vh;
        height: auto;
        overflow: visible;
    }
}

.gih-content-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: center;
}

/* 左側コンテンツ */
.gih-content-left {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

/* ステータスバッジ */
.gih-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #000000;
    color: #ffffff;
    padding: 8px 16px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    width: fit-content;
    transition: all 0.3s ease;
}

.gih-badge:hover {
    background: #333333;
    transform: translateY(-2px);
}

.gih-badge-dot {
    width: 6px;
    height: 6px;
    background: #ffeb3b;
    border-radius: 50%;
    animation: gih-pulse 2s ease-in-out infinite;
}

/* メインタイトル */
.gih-title {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.gih-title-line-1 {
    font-size: 40px;
    font-weight: 300;
    color: #666666;
    line-height: 1.2;
    letter-spacing: -0.02em;
}

.gih-title-line-2 {
    font-size: 56px;
    font-weight: 900;
    line-height: 1.1;
    letter-spacing: -0.03em;
}

.gih-highlight {
    color: #000000;
    position: relative;
}

.gih-highlight::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 12px;
    background: #ffeb3b;
    z-index: -1;
}

.gih-title-line-3 {
    font-size: 32px;
    font-weight: 300;
    color: #000000;
    line-height: 1.3;
}

/* 説明文 */
.gih-description {
    font-size: 17px;
    line-height: 1.7;
    color: #666666;
    font-weight: 400;
}

/* CTAボタン */
.gih-cta {
    display: flex;
    gap: 16px;
}

.gih-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    padding: 18px 32px;
    background: #ffeb3b;
    color: #000000;
    border-radius: 12px;
    font-size: 17px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.gih-btn-primary:hover {
    background: #ffc107;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
}

.gih-btn-primary:active {
    transform: translateY(0);
}

.gih-btn-primary i {
    transition: transform 0.3s ease;
}

.gih-btn-primary:hover i {
    transform: translateX(4px);
}

/* 右側画像 */
.gih-content-right {
    position: relative;
}

.gih-image-wrapper {
    position: relative;
    width: 100%;
}

.gih-hero-image {
    width: 100%;
    height: auto;
    display: block;
    object-fit: contain;
    transition: transform 0.3s ease;
}

.gih-hero-image:hover {
    transform: scale(1.02);
}

/* モバイルレイアウト */
.gih-mobile-layout {
    display: block;
    text-align: center;
}

@media (min-width: 1024px) {
    .gih-mobile-layout {
        display: none;
    }
}

/* モバイルバッジ */
.gih-mobile-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #000000;
    color: #ffffff;
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    margin-bottom: 24px;
}

.gih-mobile-badge-dot {
    width: 6px;
    height: 6px;
    background: #ffeb3b;
    border-radius: 50%;
    animation: gih-pulse 2s ease-in-out infinite;
}

/* モバイルタイトル */
.gih-mobile-title {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 20px;
}

.gih-mobile-line-1 {
    font-size: 28px;
    font-weight: 300;
    color: #666666;
    line-height: 1.2;
}

.gih-mobile-line-2 {
    font-size: 36px;
    font-weight: 900;
    line-height: 1.1;
}

.gih-mobile-highlight {
    color: #000000;
    position: relative;
}

.gih-mobile-highlight::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 8px;
    background: #ffeb3b;
    z-index: -1;
}

.gih-mobile-line-3 {
    font-size: 24px;
    font-weight: 300;
    color: #000000;
    line-height: 1.3;
}

/* モバイル説明 */
.gih-mobile-description {
    font-size: 15px;
    line-height: 1.6;
    color: #666666;
    margin-bottom: 24px;
}

/* モバイル画像 */
.gih-mobile-image {
    width: 100%;
    margin: 24px 0;
    
    /* スクロール最適化 */
    transform: translateZ(0);
    will-change: transform;
}

.gih-mobile-image img {
    width: 100%;
    height: auto;
    display: block;
    object-fit: contain;
}

/* モバイルCTA */
.gih-mobile-cta {
    margin-top: 24px;
}

.gih-mobile-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    width: 100%;
    padding: 16px 24px;
    background: #ffeb3b;
    color: #000000;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.gih-mobile-btn:active {
    transform: scale(0.98);
    background: #ffc107;
}

.gih-mobile-btn i {
    transition: transform 0.3s ease;
}

.gih-mobile-btn:active i {
    transform: translateX(4px);
}

/* アニメーション */
@keyframes gih-pulse {
    0%, 100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.7;
        transform: scale(1.2);
    }
}

/* タブレット */
@media (min-width: 768px) and (max-width: 1023px) {
    .gih-hero-section {
        padding: 100px 0 60px;
    }
    
    .gih-mobile-line-1 {
        font-size: 32px;
    }
    
    .gih-mobile-line-2 {
        font-size: 42px;
    }
    
    .gih-mobile-line-3 {
        font-size: 28px;
    }
    
    .gih-mobile-description {
        font-size: 16px;
    }
}

/* スマホ最適化 */
@media (max-width: 640px) {
    .gih-hero-section {
        min-height: auto;
        padding: 80px 0 40px;
        
        /* スクロール問題解決 */
        height: auto;
        overflow: visible;
    }
    
    .gih-container {
        padding: 0 16px;
    }
    
    .gih-mobile-badge {
        padding: 5px 12px;
        font-size: 9px;
        margin-bottom: 20px;
    }
    
    .gih-mobile-title {
        gap: 4px;
        margin-bottom: 16px;
    }
    
    .gih-mobile-line-1 {
        font-size: 24px;
    }
    
    .gih-mobile-line-2 {
        font-size: 32px;
    }
    
    .gih-mobile-line-3 {
        font-size: 20px;
    }
    
    .gih-mobile-highlight::after {
        height: 6px;
    }
    
    .gih-mobile-description {
        font-size: 14px;
        margin-bottom: 20px;
    }
    
    .gih-mobile-image {
        margin: 20px 0;
        
        /* パフォーマンス最適化 */
        contain: layout style paint;
    }
    
    .gih-mobile-cta {
        margin-top: 20px;
    }
    
    .gih-mobile-btn {
        padding: 14px 20px;
        font-size: 15px;
    }
}

/* 極小スマホ */
@media (max-width: 375px) {
    .gih-mobile-line-1 {
        font-size: 22px;
    }
    
    .gih-mobile-line-2 {
        font-size: 28px;
    }
    
    .gih-mobile-line-3 {
        font-size: 18px;
    }
    
    .gih-mobile-description {
        font-size: 13px;
    }
    
    .gih-mobile-btn {
        padding: 12px 18px;
        font-size: 14px;
    }
}

/* デスクトップ大画面 */
@media (min-width: 1400px) {
    .gih-content-grid {
        gap: 80px;
    }
    
    .gih-title-line-1 {
        font-size: 48px;
    }
    
    .gih-title-line-2 {
        font-size: 64px;
    }
    
    .gih-title-line-3 {
        font-size: 36px;
    }
    
    .gih-description {
        font-size: 18px;
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

/* フォーカススタイル */
.gih-btn-primary:focus,
.gih-mobile-btn:focus {
    outline: 2px solid #ffeb3b;
    outline-offset: 2px;
}

/* タッチデバイス最適化 */
@media (hover: none) and (pointer: coarse) {
    .gih-btn-primary,
    .gih-mobile-btn {
        -webkit-tap-highlight-color: transparent;
    }
    
    .gih-hero-image:hover {
        transform: none;
    }
}
</style>

<script>
/**
 * ヒーローセクション JavaScript - 最適化版
 */
class GrantHeroSystem {
    constructor() {
        this.init();
    }
    
    init() {
        this.setupImageOptimization();
        this.setupScrollOptimization();
        this.setupAccessibility();
    }
    
    setupImageOptimization() {
        const images = document.querySelectorAll('.gih-hero-image, .gih-mobile-image img');
        
        images.forEach(img => {
            if (img.complete) {
                this.onImageLoad(img);
            } else {
                img.addEventListener('load', () => this.onImageLoad(img), { once: true });
            }
        });
    }
    
    onImageLoad(img) {
        img.style.opacity = '1';
    }
    
    setupScrollOptimization() {
        // スマホスクロール最適化
        const heroSection = document.querySelector('.gih-hero-section');
        if (!heroSection) return;
        
        // パッシブイベントリスナー
        let ticking = false;
        
        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(() => {
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });
        
        // iOS Safari対応
        if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
            heroSection.style.webkitOverflowScrolling = 'touch';
        }
    }
    
    setupAccessibility() {
        // キーボードナビゲーション
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                document.body.classList.add('keyboard-nav');
            }
        });
        
        document.addEventListener('mousedown', () => {
            document.body.classList.remove('keyboard-nav');
        });
    }
}

// 初期化
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.grantHeroSystem = new GrantHeroSystem();
    });
} else {
    window.grantHeroSystem = new GrantHeroSystem();
}
</script>