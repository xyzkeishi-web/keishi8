<?php
/**
 * Grant Insight Perfect - Front Page Template
 * テンプレートパーツを活用したシンプル構成
 *
 * @package Grant_Insight_Perfect
 * @version 7.1-optimized
 */

get_header(); ?>

<style>
/* フロントページ専用スタイル */
.site-main {
    padding: 0;
    background: #ffffff;
}

/* セクション間のスペーシング調整 */
.front-page-section {
    position: relative;
}

.front-page-section + .front-page-section {
    margin-top: -1px; /* セクション間の隙間を削除 */
}

/* スムーススクロール */
html {
    scroll-behavior: smooth;
}

/* セクションアニメーション */
.section-animate {
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 0.8s ease, transform 0.8s ease;
}

.section-animate.visible {
    opacity: 1;
    transform: translateY(0);
}

/* モバイル最適化 - スクロール問題完全解決 */
@media (max-width: 768px) {
    .site-main {
        overflow-x: hidden;
        overflow-y: auto;
        /* タッチスクロール最適化 */
        -webkit-overflow-scrolling: touch;
        overscroll-behavior-y: contain;
    }
    
    /* フロントページ専用のスクロール修正 */
    .front-page-section {
        min-height: auto !important;
        height: auto !important;
        overflow: visible !important;
    }
    
    /* iOS Safari対応 */
    html {
        height: 100%;
        -webkit-text-size-adjust: 100%;
    }
    
    body {
        height: auto;
        min-height: 100vh;
        overflow-y: auto !important;
    }
}

/* スクロールプログレスバー */
.scroll-progress {
    position: fixed;
    top: 0;
    left: 0;
    height: 3px;
    /* ===== 変更点1: プログレスバーの色を白黒（グレー）に変更 ===== */
    background: #333333;
    z-index: 9999;
    transition: width 0.1s ease;
    width: 0%;
}
</style>

<main id="main" class="site-main" role="main">

    <?php
    /**
     * 1. Hero Section
     * メインビジュアルとキャッチコピー
     */
    ?>
    <section class="front-page-section section-animate" id="hero-section">
        <?php get_template_part('template-parts/front-page/section', 'hero'); ?>
    </section>

    <?php
    /**
     * 2. Search Section
     * AI検索セクション
     */
    ?>
    <section class="front-page-section section-animate" id="search-section">
        <?php get_template_part('template-parts/front-page/section', 'search'); ?>
    </section>

    <?php
    /**
     * 3. Categories Section
     * カテゴリーセクション
     */
    ?>
    <section class="front-page-section section-animate" id="categories-section">
        <?php get_template_part('template-parts/front-page/section', 'categories'); ?>
    </section>

</main>

<div class="scroll-progress" id="scroll-progress"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    // セクションアニメーション
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const sectionObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                sectionObserver.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.section-animate').forEach(section => {
        sectionObserver.observe(section);
    });

    /* ===== 変更点2: スクロール処理をrequestAnimationFrameで最適化 ===== */
    const progressBar = document.getElementById('scroll-progress');
    let ticking = false;

    function updateProgressBar() {
        const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
        const scrolled = window.scrollY;
        // スクロール量が0未満またはscrollHeightが0の場合の計算エラーを防ぐ
        const progress = scrollHeight > 0 ? (scrolled / scrollHeight) * 100 : 0;
        
        if (progressBar) {
            progressBar.style.width = Math.min(progress, 100) + '%';
        }
        ticking = false;
    }

    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(function() {
                updateProgressBar();
            });
            ticking = true;
        }
    });
    
    // 初期表示時にもプログレスバーを更新
    window.requestAnimationFrame(updateProgressBar);
    
    // パフォーマンス監視
    if ('performance' in window) {
        window.addEventListener('load', function() {
            const perfData = performance.getEntriesByType('navigation')[0];
            if (perfData) {
                console.log('[パフォーマンス] ページ読み込み時間:', perfData.loadEventEnd - perfData.loadEventStart, 'ms');
            }
        });
    }
    
    // ページ内リンクのスムーススクロール
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href && href !== '#' && href !== '#0') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    const offset = 80; // ヘッダーの高さ分調整
                    const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - offset;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
    
    console.log('[OK] Grant Insight Perfect - フロントページ初期化完了 (v7.1-optimized)');
});
</script>

<?php get_footer(); ?>