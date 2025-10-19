# archive-grant.php パフォーマンス最適化実装計画

## 📋 概要

このドキュメントは、Lighthouse診断結果に基づいた具体的な実装手順を提供します。
現在のパフォーマンススコア **55** を **90+** に改善することを目標とします。

---

## 🎯 Phase 1: 即座に実装可能な改善 (推定改善: +15点)

### 1.1 Hero画像の最適化

#### 現在の問題
```php
<!-- 行289-296: 現在のコード -->
<img src="https://joseikin-insight.com/wp-content/uploads/2025/10/名称未設定のデザイン-3.png" 
     alt="助成金・補助金検索 | 2025年度最新情報 - 助成金・補助金検索サービス | 2025年度最新情報" 
     class="hero-image"
     width="1200"
     height="630"
     loading="eager"
     fetchpriority="high"
     itemprop="image">
```

#### 改善コード
```php
<!-- Phase 1改善版: WebP対応のpictureタグ -->
<picture>
    <source 
        type="image/webp" 
        srcset="https://joseikin-insight.com/wp-content/uploads/2025/10/hero-image-400.webp 400w,
                https://joseikin-insight.com/wp-content/uploads/2025/10/hero-image-800.webp 800w,
                https://joseikin-insight.com/wp-content/uploads/2025/10/hero-image-1200.webp 1200w"
        sizes="(max-width: 768px) 100vw, 50vw">
    <source 
        type="image/png" 
        srcset="https://joseikin-insight.com/wp-content/uploads/2025/10/名称未設定のデザイン-3-800.png 800w,
                https://joseikin-insight.com/wp-content/uploads/2025/10/名称未設定のデザイン-3.png 1200w"
        sizes="(max-width: 768px) 100vw, 50vw">
    <img src="https://joseikin-insight.com/wp-content/uploads/2025/10/hero-image-800.webp" 
         alt="助成金・補助金検索 | 2025年度最新情報" 
         class="hero-image"
         width="800"
         height="420"
         loading="eager"
         fetchpriority="high"
         itemprop="image">
</picture>
```

#### 画像生成コマンド
```bash
# WebP形式に変換 (ImageMagickまたはcwebpツール使用)
convert 名称未設定のデザイン-3.png -resize 400x210 -quality 85 hero-image-400.webp
convert 名称未設定のデザイン-3.png -resize 800x420 -quality 85 hero-image-800.webp
convert 名称未設定のデザイン-3.png -resize 1200x630 -quality 85 hero-image-1200.webp

# フォールバック用PNG (リサイズのみ)
convert 名称未設定のデザイン-3.png -resize 800x420 -quality 90 名称未設定のデザイン-3-800.png
```

**期待される効果**:
- LCP改善: 2.2秒 → 1.5秒 (-32%)
- 画像サイズ削減: 326 KiB → 80 KiB (-75%)

---

### 1.2 ロゴ画像の最適化

#### 現在のコード
```php
<!-- 不明な行番号: ヘッダーロゴ -->
<img src="https://joseikin-insight.com/wp-content/uploads/2025/09/名称未設定のデザイン.png" 
     alt="助成金・補助金インサイト" 
     class="stylish-logo-image" 
     width="200" 
     height="60" 
     loading="eager">
```

#### 改善コード
```php
<picture>
    <source 
        type="image/webp" 
        srcset="https://joseikin-insight.com/wp-content/uploads/2025/09/logo.webp">
    <img src="https://joseikin-insight.com/wp-content/uploads/2025/09/logo.webp" 
         alt="助成金・補助金インサイト" 
         class="stylish-logo-image" 
         width="200" 
         height="60" 
         loading="eager">
</picture>
```

#### 画像生成コマンド
```bash
convert 名称未設定のデザイン.png -resize 200x60 -quality 85 logo.webp
```

**期待される効果**:
- 画像サイズ削減: 110 KiB → 15 KiB (-86%)

---

### 1.3 サードパーティスクリプトの遅延読み込み

#### 実装場所: 行3567の前に追加

```php
<!-- サードパーティスクリプト最適化 -->
<script>
(function() {
    'use strict';
    
    // Critical Performance Enhancement
    // Defer non-critical third-party scripts
    
    function deferThirdPartyScripts() {
        // Tailwind CSS - defer until after first paint
        if (!document.querySelector('script[src*="tailwindcss"]')) {
            setTimeout(() => {
                const tailwind = document.createElement('script');
                tailwind.src = 'https://cdn.tailwindcss.com/3.4.17';
                tailwind.defer = true;
                document.head.appendChild(tailwind);
            }, 500);
        }
        
        // Google Fonts - defer stylesheet loading
        setTimeout(() => {
            const fontLink = document.createElement('link');
            fontLink.rel = 'stylesheet';
            fontLink.href = 'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap';
            document.head.appendChild(fontLink);
        }, 300);
    }
    
    // Execute after initial page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', deferThirdPartyScripts);
    } else {
        deferThirdPartyScripts();
    }
})();
</script>
```

**期待される効果**:
- TBT削減: 630ms → 400ms (-36%)
- メインスレッドブロッキング削減: 550ms → 300ms (-45%)

---

### 1.4 プリロードヒントの追加

#### 実装場所: `<head>`セクション (行77の前)

```php
<!-- Performance Optimization: Resource Hints -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="dns-prefetch" href="//www.googletagmanager.com">
<link rel="dns-prefetch" href="//pagead2.googlesyndication.com">
<link rel="dns-prefetch" href="//cdn.tailwindcss.com">

<!-- Preload critical resources -->
<link rel="preload" 
      as="image" 
      href="https://joseikin-insight.com/wp-content/uploads/2025/10/hero-image-800.webp" 
      type="image/webp"
      fetchpriority="high">
```

**期待される効果**:
- FCP改善: 1.5秒 → 1.2秒 (-20%)
- DNSルックアップ時間短縮

---

## 🎯 Phase 2: CSS最適化 (推定改善: +10点)

### 2.1 クリティカルCSSの抽出とインライン化

#### 実装方法

1. **クリティカルCSSの抽出**
```bash
# Critical CSS Generator (Node.js)
npm install -g critical
critical https://joseikin-insight.com/grants/ --base=. --inline --minify
```

2. **実装コード** (行1033の`<style>`タグを置換)

```php
<!-- Critical CSS (Above-the-fold only) -->
<style>
/* Minified Critical CSS - Generated by Critical tool */
.grant-archive-optimized{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Noto Sans JP',sans-serif;color:#1a1a1a;background:#fff;min-height:100vh}
.container{max-width:1400px;margin:0 auto;padding:0 20px}
.breadcrumb-nav{background:#f8f9fa;border-bottom:1px solid #e5e7eb;padding:12px 0;margin-bottom:0}
.archive-hero-section{position:relative;min-height:450px;background:#fff;overflow:hidden;margin-bottom:0}
.hero-background{position:absolute;top:0;right:0;width:50%;height:100%;z-index:1}
.hero-image{width:100%;height:100%;object-fit:contain;object-position:center}
.hero-content{position:relative;z-index:2;padding:80px 20px;display:flex;align-items:center;min-height:450px}
.hero-title{font-size:48px;font-weight:700;color:#000;margin:0 0 20px 0;line-height:1.2;letter-spacing:-.02em}
.hero-description{font-size:18px;color:#333;line-height:1.7;margin:0 0 40px 0}
.filter-section-enhanced{background:linear-gradient(135deg,#f8f9ff 0%,#fff 50%,#f0f4ff 100%);padding:50px 0;border-bottom:3px solid #e2e8f0;position:relative}
.grants-grid-optimized{display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:24px;min-height:400px}
/* Add only essential above-the-fold styles */
</style>

<!-- Defer non-critical CSS -->
<link rel="preload" 
      href="<?php echo get_stylesheet_directory_uri(); ?>/css/non-critical.css" 
      as="style" 
      onload="this.onload=null;this.rel='stylesheet'">
<noscript>
  <link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/css/non-critical.css">
</noscript>
```

3. **非クリティカルCSSファイル作成**
```css
/* /css/non-critical.css - Below-the-fold styles */
/* ===== FAQセクション ===== */
.faq-section { /* ... */ }
/* ===== 関連リンクセクション ===== */
.related-links-section { /* ... */ }
/* ===== 詳細フィルター ===== */
.advanced-filters-box { /* ... */ }
/* 残りの非クリティカルスタイル */
```

**期待される効果**:
- 初期CSS読み込み削減: 361 KiB → 50 KiB
- FCP改善: 1.2秒 → 1.0秒

---

### 2.2 Google Fontsの最適化

#### 現在の問題
- 不要なフォントウェイト読み込み (400, 500, 700)
- Font Awesome全体の読み込み

#### 改善コード

```php
<!-- Font loading optimization -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<!-- Optimized font loading - only used weights -->
<link rel="preload" 
      as="style" 
      href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap"
      onload="this.onload=null;this.rel='stylesheet'">
<noscript>
  <link rel="stylesheet" 
        href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap">
</noscript>

<!-- Font Awesome - load specific icons only (if possible) -->
<!-- または SVG icons に置き換え -->
```

**期待される効果**:
- フォント読み込み削減: 327 KiB → 150 KiB
- ウェイト500を削除することでリクエスト数削減

---

## 🎯 Phase 3: JavaScript最適化 (推定改善: +10点)

### 3.1 JavaScriptのコード分割

#### 実装方法

**ステップ1**: メインスクリプトを分割 (行3568以降)

```javascript
<!-- archive-grant-core.js (インライン: 必須機能のみ) -->
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
        isLoading: false
    };
    
    // Core functionality only
    function init() {
        console.log('🚀 Core initialized');
        setupCoreListeners();
        loadGrants();
    }
    
    function setupCoreListeners() {
        // Category filters
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.addEventListener('click', handleCategoryClick);
        });
        
        // Prefecture filters
        document.querySelectorAll('.prefecture-btn').forEach(btn => {
            btn.addEventListener('click', handlePrefectureClick);
        });
        
        // View toggle
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', handleViewChange);
        });
    }
    
    function loadGrants() {
        // AJAX grant loading logic
        // ... simplified version
    }
    
    // Helper functions
    function handleCategoryClick(e) { /* ... */ }
    function handlePrefectureClick(e) { /* ... */ }
    function handleViewChange(e) { /* ... */ }
    
    // Initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Expose core API
    window.GrantArchive = {
        state,
        loadGrants
    };
})();
</script>

<!-- Load non-critical features after initial render -->
<script>
window.addEventListener('load', function() {
    // Defer non-critical feature loading
    setTimeout(() => {
        // AI Search module
        const aiScript = document.createElement('script');
        aiScript.src = '<?php echo get_stylesheet_directory_uri(); ?>/js/ai-search.js';
        aiScript.defer = true;
        document.body.appendChild(aiScript);
        
        // Advanced filters module
        const advFiltersScript = document.createElement('script');
        advFiltersScript.src = '<?php echo get_stylesheet_directory_uri(); ?>/js/advanced-filters.js';
        advFiltersScript.defer = true;
        document.body.appendChild(advFiltersScript);
    }, 2000); // 2秒後に読み込み
});
</script>
```

**ステップ2**: AI検索を外部ファイルに分離

```javascript
// /js/ai-search.js
(function() {
    'use strict';
    
    if (!window.GrantArchive) {
        console.error('GrantArchive core not loaded');
        return;
    }
    
    // AI検索機能の実装
    function setupAISearch() {
        const aiSearchBtn = document.getElementById('ai-search-btn');
        if (aiSearchBtn) {
            aiSearchBtn.addEventListener('click', handleAISearch);
        }
        // ... 残りのAI検索ロジック
    }
    
    function handleAISearch() { /* ... */ }
    
    // Initialize AI search
    setupAISearch();
    console.log('✅ AI Search module loaded');
})();
```

**期待される効果**:
- JavaScript実行時間: 1.5秒 → 0.8秒 (-47%)
- TBT削減: 400ms → 250ms
- Time to Interactive改善

---

### 3.2 Intersection Observerによる遅延レンダリング

```javascript
// その他カテゴリの遅延表示
function setupLazyRendering() {
    const otherCategoriesBtn = document.getElementById('toggle-other-categories');
    if (!otherCategoriesBtn) return;
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // その他カテゴリボタンを動的生成
                renderOtherCategories();
                observer.disconnect();
            }
        });
    }, { rootMargin: '200px' }); // 200px手前で読み込み開始
    
    observer.observe(otherCategoriesBtn);
}

function renderOtherCategories() {
    // カテゴリボタンを動的に生成
    const container = document.getElementById('other-categories-section');
    const categories = <?php echo json_encode(array_slice($other_categories, 0, 50)); ?>;
    
    const html = categories.map(cat => `
        <button class="category-btn small" data-category="${cat.slug}">
            <span class="btn-text">${cat.name}</span>
            <span class="btn-count">${cat.count}</span>
        </button>
    `).join('');
    
    container.querySelector('.category-buttons-other').innerHTML = html;
}
```

**期待される効果**:
- 初期DOM要素数削減: 3,122 → 2,200
- 初期レンダリング時間短縮

---

## 🎯 Phase 4: レイアウトシフト完全解消

### 4.1 Hero画像のアスペクト比固定

```css
/* 行1096以降のスタイルに追加/修正 */
.archive-hero-section {
    position: relative;
    min-height: 450px;
    background: #ffffff;
    overflow: hidden;
    margin-bottom: 0;
    /* アスペクト比を保持 */
    contain: layout; /* レイアウト最適化 */
}

.hero-background {
    position: absolute;
    top: 0;
    right: 0;
    width: 50%;
    height: 100%;
    z-index: 1;
    /* アスペクト比を明示 */
    aspect-ratio: 1200 / 630;
}

.hero-image {
    width: 100%;
    height: auto; /* heightを固定値からautoに */
    object-fit: contain;
    object-position: center;
    /* ブラウザのレイアウト最適化 */
    content-visibility: auto;
}
```

### 4.2 スペース確保による CLS 防止

```css
/* フィルターセクションの事前スペース確保 */
.filter-section-enhanced {
    min-height: 400px; /* 最小高さを確保 */
}

.grants-grid-optimized {
    min-height: 800px; /* グリッド領域を確保 */
}
```

**期待される効果**:
- CLS: 0.09 → 0.00 (完全解消)

---

## 📊 実装後の測定指標

### 目標スコア

| 指標 | 現在 | Phase 1後 | Phase 2後 | Phase 3後 | 最終目標 |
|------|------|-----------|-----------|-----------|----------|
| **Performance** | 55 | 70 | 80 | 88 | 90+ |
| **FCP** | 1.5s | 1.2s | 1.0s | 0.9s | 0.8s |
| **LCP** | 2.2s | 1.5s | 1.3s | 1.1s | 1.0s |
| **TBT** | 630ms | 400ms | 300ms | 200ms | 150ms |
| **CLS** | 0.09 | 0.05 | 0.02 | 0.00 | 0.00 |
| **SI** | 1.8s | 1.5s | 1.3s | 1.1s | 1.0s |

---

## 🚀 実装チェックリスト

### Phase 1 (1-2日) - 即座の改善
- [ ] Hero画像のWebP変換 (3サイズ)
- [ ] ロゴ画像のWebP変換
- [ ] `<picture>`タグ実装
- [ ] サードパーティスクリプト遅延読み込み
- [ ] プリロードヒント追加
- [ ] Lighthouseで再測定

### Phase 2 (3-5日) - CSS最適化
- [ ] Critical CSS抽出ツールのセットアップ
- [ ] クリティカルCSSのインライン化
- [ ] 非クリティカルCSS外部ファイル化
- [ ] Google Fontsウェイト削減
- [ ] 遅延読み込みスクリプト実装
- [ ] Lighthouseで再測定

### Phase 3 (1週間) - JavaScript最適化
- [ ] コアJavaScriptとAI機能を分離
- [ ] 外部JSファイル作成 (ai-search.js, advanced-filters.js)
- [ ] Intersection Observer実装
- [ ] 遅延レンダリング実装
- [ ] Lighthouseで再測定

### Phase 4 (1-2日) - 最終調整
- [ ] アスペクト比CSS追加
- [ ] スペース確保CSS追加
- [ ] ブラウザキャッシュ設定確認
- [ ] 最終Lighthouse測定
- [ ] Real User Monitoring設定

---

## 📝 追加の推奨事項

### キャッシュ設定 (.htaccess)

```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/webp "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType font/woff2 "access plus 1 year"
</IfModule>

<IfModule mod_headers.c>
    <FilesMatch "\.(webp|png|jpg|jpeg)$">
        Header set Cache-Control "public, max-age=31536000, immutable"
    </FilesMatch>
</IfModule>
```

### Cloudflare設定推奨

1. **Auto Minify**: HTML/CSS/JS有効化
2. **Brotli圧縮**: 有効化
3. **Early Hints**: 有効化 (プリロードヒント高速化)
4. **Polish**: Lossless または Lossy (画像自動最適化)
5. **Mirage**: 有効化 (画像遅延読み込み)

---

## 🔍 モニタリングとテスト

### 実装後の確認項目

1. **Lighthouse CI セットアップ**
```bash
npm install -g @lhci/cli
lhci autorun --url=https://joseikin-insight.com/grants/
```

2. **PageSpeed Insights定期測定**
   - 毎週月曜日に測定
   - Mobile / Desktop両方

3. **Real User Monitoring**
   - Google Analytics 4 Web Vitals測定
   - Search ConsoleのCore Web Vitalsレポート

4. **エラー監視**
   - ブラウザコンソールエラーチェック
   - 404エラー監視 (画像パス変更後)

---

## 📞 サポート

質問や問題が発生した場合:
1. `/PERFORMANCE_DIAGNOSIS.md` を参照
2. Lighthouseレポートを再実行
3. ブラウザDevToolsでネットワークタブを確認

**実装完了日**: _____ / _____ / _____  
**最終スコア**: _____
