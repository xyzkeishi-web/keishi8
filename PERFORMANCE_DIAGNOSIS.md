# archive-grant.php パフォーマンス診断レポート

## 📊 Lighthouse スコア概要
- **パフォーマンス**: 55/100 ⚠️
- **FCP (First Contentful Paint)**: 1.5秒 ⚠️
- **LCP (Largest Contentful Paint)**: 2.2秒 ⚠️
- **TBT (Total Blocking Time)**: 630ms ❌
- **CLS (Cumulative Layout Shift)**: 0.09 ✅

---

## 🔴 重大な問題点

### 1. サードパーティスクリプトのブロッキング (550ms)
**影響度**: 非常に高い

#### 問題の詳細
- Google FundingChoices: 282ms (メインスレッドブロック)
- Google/Doubleclick Ads: 149ms
- Google Tag Manager: 70ms
- Tailwind CSS CDN: 45ms

#### 影響
- メインスレッドのブロッキングが合計550msに達し、ユーザーインタラクションを遅延
- JavaScript実行時間が1.5秒を超過

---

### 2. 画像の最適化不足
**影響度**: 高い

#### 問題の詳細
1. **Next-Gen形式への変換が必要** (削減可能: 345 KiB)
   - Hero画像: `名称未設定のデザイン-3.png` (326 KiB)
   - ロゴ画像: `名称未設定のデザイン.png` (110 KiB)
   - 現在PNG形式 → WebP/AVIFに変換すべき

2. **不適切な画像サイズ** (削減可能: 236 KiB)
   - Hero画像を1200x630で提供しているが、実際の表示サイズより大きい
   - ロゴ画像も同様に過剰なサイズ

3. **LCP要素の遅延**
   - Hero画像のLCP Render Delayが1,290ms (58%を占める)
   - `loading="eager"` と `fetchpriority="high"` は設定済みだが、画像サイズが原因

---

### 3. 未使用CSSの大量読み込み (361 KiB)
**影響度**: 高い

#### 問題の詳細
- Google Fonts: 327.3 KiB (複数のフォントウェイト)
- Font Awesome: 18.2 KiB
- WordPress block-library: 15.8 KiB

#### 具体的な問題
```php
// 現在の実装 (行289-296)
<img src="https://joseikin-insight.com/wp-content/uploads/2025/10/名称未設定のデザイン-3.png" 
     alt="..." 
     class="hero-image"
     width="1200"
     height="630"
     loading="eager"
     fetchpriority="high">
```

---

### 4. メインスレッド処理時間の超過 (4.5秒)
**影響度**: 非常に高い

#### 内訳
- Style & Layout: 1,947ms
- Script Evaluation: 1,538ms
- Script Parsing & Compilation: 229ms
- Rendering: 222ms
- Garbage Collection: 167ms

---

### 5. DOM要素数の過剰 (3,122要素)
**影響度**: 中程度

#### 原因
- フィルターUIの複雑さ
- カテゴリ・都道府県ボタンの大量生成
- 複数の構造化データ/FAQスキーマ

---

## ✅ 優先度別改善提案

### 🔴 最優先 (即時対応)

#### 1. 画像最適化
```bash
# WebP変換とリサイズの実行
convert 名称未設定のデザイン-3.png -resize 800x420 -quality 85 hero-image.webp
convert 名称未設定のデザイン.png -resize 200x60 -quality 85 logo.webp
```

**実装案 (archive-grant.php 修正)**
```php
<!-- Hero画像の最適化 (行289-296を置換) -->
<picture>
    <source 
        type="image/webp" 
        srcset="https://joseikin-insight.com/wp-content/uploads/2025/10/hero-image-800.webp 800w,
                https://joseikin-insight.com/wp-content/uploads/2025/10/hero-image-1200.webp 1200w"
        sizes="(max-width: 768px) 100vw, 50vw">
    <source 
        type="image/png" 
        srcset="https://joseikin-insight.com/wp-content/uploads/2025/10/名称未設定のデザイン-3.png">
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

**期待される効果**
- 画像サイズ: 326 KiB → 80 KiB (75%削減)
- LCP改善: 2.2秒 → 1.5秒以下
- 帯域幅削減: 約250 KiB

---

#### 2. サードパーティスクリプトの遅延読み込み

**問題のあるコード箇所の特定**
```php
<!-- これらがhead内で同期的に読み込まれている可能性 -->
<!-- Tailwind CDN (行内) -->
<!-- Google Fonts (行内) -->
<!-- Google Ads / Tag Manager (プラグインによる挿入) -->
```

**実装案**
```php
<!-- 行3567以降のスクリプト部分の前に追加 -->
<script>
// サードパーティスクリプトの遅延読み込み
document.addEventListener('DOMContentLoaded', function() {
    // Tailwind CSS を遅延読み込み
    if (!document.querySelector('script[src*="tailwindcss"]')) {
        const tailwind = document.createElement('script');
        tailwind.src = 'https://cdn.tailwindcss.com/3.4.17';
        tailwind.defer = true;
        document.head.appendChild(tailwind);
    }
    
    // Google Fonts を遅延読み込み (preconnectは残す)
    setTimeout(() => {
        const fontLinks = [
            'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap'
            // 他のフォントは実際に使用されている場合のみ追加
        ];
        fontLinks.forEach(href => {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = href;
            document.head.appendChild(link);
        });
    }, 100);
});
</script>
```

**期待される効果**
- TBT削減: 630ms → 400ms以下
- メインスレッドブロッキング: 550ms → 250ms以下

---

### 🟡 高優先度 (短期対応)

#### 3. 未使用CSSの削除とインライン化

**実装案: クリティカルCSSのインライン化**
```php
<!-- 行1033の<style>タグ内容を以下のように最適化 -->
<style>
/* クリティカルCSSのみ抽出 (Above-the-fold content) */
.grant-archive-optimized{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Noto Sans JP',sans-serif;color:#1a1a1a;background:#fff;min-height:100vh}
.container{max-width:1400px;margin:0 auto;padding:0 20px}
.breadcrumb-nav{background:#f8f9fa;border-bottom:1px solid #e5e7eb;padding:12px 0}
.archive-hero-section{position:relative;min-height:450px;background:#fff;overflow:hidden}
.hero-image{width:100%;height:100%;object-fit:contain;object-position:center}
.hero-title{font-size:48px;font-weight:700;color:#000;margin:0 0 20px 0;line-height:1.2}
/* ... 続く ... */
</style>

<!-- 非クリティカルCSSを遅延読み込み -->
<link rel="preload" href="/path/to/non-critical.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="/path/to/non-critical.css"></noscript>
```

**期待される効果**
- 初期CSS読み込み: 361 KiB → 50 KiB
- FCP改善: 1.5秒 → 1.0秒以下

---

#### 4. Google Fontsの最適化

**現在の問題**
```php
<!-- 複数のフォントウェイトを読み込んでいる -->
/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap
```

**改善案**
```php
<!-- 実際に使用しているウェイトのみ読み込み -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style" 
      href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap">
<link rel="stylesheet" 
      href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap" 
      media="print" 
      onload="this.media='all'">
<noscript>
  <link rel="stylesheet" 
        href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap">
</noscript>
```

**または、フォントサブセット化**
```php
<!-- 日本語サブセットのみ使用 -->
&text=助成金補助金検索最新情報都道府県カテゴリ...
```

**期待される効果**
- フォント読み込み: 327 KiB → 150 KiB
- 未使用CSS削減

---

### 🟢 中優先度 (中期対応)

#### 5. JavaScriptの最適化とコード分割

**現在の問題**
- 単一の大きなインラインスクリプト (行3568-4000+)
- 全機能を初期読み込み

**実装案: 遅延実行とコード分割**
```javascript
// 行3569以降を以下のように変更
<script>
(function() {
    'use strict';
    
    // 必須機能のみ即座に実行
    const AJAX_URL = '<?php echo admin_url("admin-ajax.php"); ?>';
    const NONCE = '<?php echo wp_create_nonce("gi_ajax_nonce"); ?>';
    
    // 軽量な初期化
    function init() {
        console.log('🚀 Archive page initialized');
        setupCriticalListeners(); // 最小限のイベントリスナー
        loadGrants(); // 初期データ読み込み
    }
    
    // 非クリティカル機能は遅延読み込み
    function loadNonCriticalFeatures() {
        // AI検索機能
        import('./ai-search.js').then(module => {
            module.setupAISearch(AJAX_URL, NONCE);
        });
        
        // 高度なフィルター機能
        import('./advanced-filters.js').then(module => {
            module.setupAdvancedFilters();
        });
    }
    
    // DOMContentLoaded後に非クリティカル機能を読み込み
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            init();
            setTimeout(loadNonCriticalFeatures, 1000);
        });
    } else {
        init();
        setTimeout(loadNonCriticalFeatures, 1000);
    }
})();
</script>
```

**期待される効果**
- JavaScript実行時間: 1.5秒 → 0.8秒
- TBT削減: 30-40%

---

#### 6. レイアウトシフトの完全解消

**問題箇所の特定**
```php
<!-- 行300-376: Hero section でCLSが発生 -->
<main id="main-content" class="stylish-main-content" style="margin-top: 94px;">
```

**改善案**
```php
<!-- スペースを事前に確保 -->
<style>
.archive-hero-section {
    min-height: 450px; /* 既存 */
    /* Hero imageのアスペクト比を維持 */
}
.hero-background {
    aspect-ratio: 1200 / 630; /* 画像の比率を明示 */
}
.hero-image {
    width: 100%;
    height: auto; /* heightを固定値からautoに変更 */
    object-fit: contain;
}
</style>
```

**期待される効果**
- CLS: 0.09 → 0.00 (完全解消)

---

#### 7. DOM要素数の削減

**問題箇所**
- カテゴリボタン: 50+個
- 都道府県ボタン: 47個
- 構造化データの重複

**実装案: 仮想スクロール/遅延レンダリング**
```javascript
// その他カテゴリの遅延レンダリング
function renderOtherCategories() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // その他カテゴリボタンを動的に生成
                renderCategoryButtons();
                observer.disconnect();
            }
        });
    });
    
    const trigger = document.getElementById('toggle-other-categories');
    if (trigger) observer.observe(trigger);
}
```

**期待される効果**
- 初期DOM要素数: 3,122 → 2,000以下
- 初期レンダリング時間短縮

---

## 📝 実装優先順位とロードマップ

### Phase 1: 即時対応 (1-2日)
1. ✅ 画像のWebP変換とリサイズ
2. ✅ Hero画像の`<picture>`タグ実装
3. ✅ サードパーティスクリプトの遅延読み込み

**期待されるスコア改善**: 55 → 70

---

### Phase 2: 短期対応 (3-5日)
1. ⏳ クリティカルCSSの抽出とインライン化
2. ⏳ Google Fontsの最適化
3. ⏳ 未使用CSSの削除

**期待されるスコア改善**: 70 → 80

---

### Phase 3: 中期対応 (1-2週間)
1. ⏳ JavaScriptのコード分割と遅延読み込み
2. ⏳ レイアウトシフトの完全解消
3. ⏳ DOM要素数の削減

**期待されるスコア改善**: 80 → 90+

---

## 🎯 目標パフォーマンススコア

| 指標 | 現在 | Phase 1 | Phase 2 | Phase 3 (目標) |
|------|------|---------|---------|----------------|
| Performance | 55 | 70 | 80 | 90+ |
| FCP | 1.5s | 1.2s | 1.0s | 0.8s |
| LCP | 2.2s | 1.6s | 1.3s | 1.0s |
| TBT | 630ms | 400ms | 250ms | 150ms |
| CLS | 0.09 | 0.05 | 0.02 | 0.00 |

---

## 🔧 追加の技術的推奨事項

### 1. キャッシュ戦略の改善
```php
// wp-config.php または .htaccess
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/webp "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

### 2. プリロード・プリコネクトの最適化
```php
<!-- header.php またはget_header()の後に追加 -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="dns-prefetch" href="//www.googletagmanager.com">
<link rel="dns-prefetch" href="//pagead2.googlesyndication.com">
<link rel="preload" as="image" href="<?php echo esc_url($og_image_webp); ?>" fetchpriority="high">
```

### 3. サービスワーカーの実装 (PWA化)
```javascript
// service-worker.js
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open('grant-archive-v1').then((cache) => {
            return cache.addAll([
                '/grants/',
                '/wp-content/themes/keishi8-genspark_ai_developer/style.css',
                // その他の重要リソース
            ]);
        })
    );
});
```

---

## 📊 測定とモニタリング

### 継続的なパフォーマンス監視
1. **Lighthouse CI の導入**
   ```bash
   npm install -g @lhci/cli
   lhci autorun --config=lighthouserc.json
   ```

2. **Real User Monitoring (RUM)**
   - Google Analytics 4のCore Web Vitals測定
   - Cloudflare Web Analyticsの活用

3. **定期的なベンチマーク**
   - 週次でLighthouseレポート実行
   - PageSpeed Insightsでの検証

---

## 🚀 即座に実装可能なクイックウィン

以下は、コード変更なしで即座に実装可能な改善:

1. **Cloudflare Auto Minify有効化**
   - HTML/CSS/JSの自動圧縮

2. **画像CDNの活用**
   - Cloudflare ImagesまたはImageKit.io

3. **Brotli圧縮の有効化**
   ```apache
   # .htaccess
   <IfModule mod_brotli.c>
       AddOutputFilterByType BROTLI_COMPRESS text/html text/css application/javascript
   </IfModule>
   ```

---

## 📞 サポートとリソース

- **Lighthouse公式ドキュメント**: https://developer.chrome.com/docs/lighthouse/
- **Web.dev Performance**: https://web.dev/performance/
- **WordPress Performance**: https://make.wordpress.org/core/handbook/testing/performance/

---

**レポート作成日**: 2025年10月19日  
**対象URL**: https://joseikin-insight.com/grants/  
**Lighthouse Version**: 12.8.2
