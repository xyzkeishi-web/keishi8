# 画像変換ガイド - Phase 1 実装

## 📋 概要

このガイドは、archive-grant.phpのHero画像をWebP形式に変換し、パフォーマンスを最適化するための手順を提供します。

---

## 🎯 目標

- **画像サイズ削減**: 326 KiB → 80 KiB (-75%)
- **LCP改善**: 2.2秒 → 1.5秒以下
- **パフォーマンススコア**: 55 → 70 (+15点)

---

## 📁 対象画像

### 1. Hero画像
**元画像**: `名称未設定のデザイン-3.png`
- 現在のURL: `https://joseikin-insight.com/wp-content/uploads/2025/10/名称未設定のデザイン-3.png`
- サイズ: 1200×630px
- 容量: 326 KiB

**生成する画像**:
1. `hero-image-400.webp` (400×210px) - モバイル用
2. `hero-image-800.webp` (800×420px) - タブレット/デスクトップ用
3. `hero-image-1200.webp` (1200×630px) - 大画面用
4. `名称未設定のデザイン-3-800.png` (800×420px) - PNG フォールバック

---

## 🛠️ 画像変換方法

### 方法1: ImageMagick（推奨）

```bash
# インストール確認
convert --version

# Hero画像の変換
cd /path/to/wp-content/uploads/2025/10/

# WebP形式で3サイズ生成
convert 名称未設定のデザイン-3.png -resize 400x210 -quality 85 hero-image-400.webp
convert 名称未設定のデザイン-3.png -resize 800x420 -quality 85 hero-image-800.webp
convert 名称未設定のデザイン-3.png -resize 1200x630 -quality 85 hero-image-1200.webp

# PNG フォールバック (リサイズのみ)
convert 名称未設定のデザイン-3.png -resize 800x420 -quality 90 名称未設定のデザイン-3-800.png
```

### 方法2: cwebp（Google公式ツール）

```bash
# インストール (Ubuntu/Debian)
sudo apt-get install webp

# 変換
cwebp -q 85 -resize 400 210 名称未設定のデザイン-3.png -o hero-image-400.webp
cwebp -q 85 -resize 800 420 名称未設定のデザイン-3.png -o hero-image-800.webp
cwebp -q 85 -resize 1200 630 名称未設定のデザイン-3.png -o hero-image-1200.webp
```

### 方法3: オンラインツール

以下のツールを使用して手動変換:

1. **Squoosh** (推奨)
   - URL: https://squoosh.app/
   - PNG→WebPに変換
   - 品質: 85%
   - リサイズ: 各サイズに

2. **CloudConvert**
   - URL: https://cloudconvert.com/png-to-webp
   - バッチ変換可能

3. **TinyPNG + Manual WebP Conversion**
   - まずPNG最適化: https://tinypng.com/
   - その後Squooshでさらに最適化

---

## 📤 アップロード手順

### FTP/SFTPでアップロード

```bash
# 接続先
Host: joseikin-insight.com
Directory: /wp-content/uploads/2025/10/

# アップロードするファイル
hero-image-400.webp
hero-image-800.webp
hero-image-1200.webp
名称未設定のデザイン-3-800.png
```

### WordPressメディアライブラリ経由

1. WordPressダッシュボードにログイン
2. メディア → 新規追加
3. 生成した4つのファイルをアップロード
4. アップロード後、URLを確認:
   - `https://joseikin-insight.com/wp-content/uploads/2025/10/hero-image-800.webp`

---

## ✅ 実装済みコード

archive-grant.phpには**既に実装済み**です:

### 1. リソースヒント（行77前に追加）

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

### 2. Picture タグ実装（行286-298を置換）

```php
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
         alt="<?php echo esc_attr($archive_title . ' - 助成金・補助金検索サービス | ' . date('Y') . '年度最新情報'); ?>" 
         class="hero-image"
         width="800"
         height="420"
         loading="eager"
         fetchpriority="high"
         itemprop="image">
</picture>
```

### 3. サードパーティスクリプト遅延（行3567前に追加）

```javascript
<!-- Performance Optimization: Defer Third-Party Scripts -->
<script>
(function() {
    'use strict';
    
    // Defer non-critical third-party scripts
    function deferThirdPartyScripts() {
        // Tailwind CSS - defer until after first paint
        if (!document.querySelector('script[src*="tailwindcss"]')) {
            setTimeout(function() {
                var tailwind = document.createElement('script');
                tailwind.src = 'https://cdn.tailwindcss.com/3.4.17';
                tailwind.defer = true;
                document.head.appendChild(tailwind);
            }, 500);
        }
        
        // Google Fonts - defer stylesheet loading
        setTimeout(function() {
            var fontLinks = [
                'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap'
            ];
            fontLinks.forEach(function(href) {
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = href;
                document.head.appendChild(link);
            });
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

---

## 🧪 テスト手順

### 1. 画像アップロード後の確認

```bash
# 各URLにアクセスして画像が表示されることを確認
https://joseikin-insight.com/wp-content/uploads/2025/10/hero-image-400.webp
https://joseikin-insight.com/wp-content/uploads/2025/10/hero-image-800.webp
https://joseikin-insight.com/wp-content/uploads/2025/10/hero-image-1200.webp
https://joseikin-insight.com/wp-content/uploads/2025/10/名称未設定のデザイン-3-800.png
```

### 2. ブラウザでの表示確認

1. **Chrome DevTools**
   ```
   1. F12でDevToolsを開く
   2. Networkタブを選択
   3. Imgフィルターを適用
   4. ページをリロード (Ctrl+Shift+R)
   5. hero-image-800.webpが読み込まれていることを確認
   6. サイズが約80KB以下であることを確認
   ```

2. **レスポンシブ確認**
   ```
   1. DevToolsでデバイスツールバーを有効化 (Ctrl+Shift+M)
   2. iPhone SE (375px): hero-image-400.webp が読み込まれる
   3. iPad (768px): hero-image-800.webp が読み込まれる
   4. Desktop (1200px+): hero-image-1200.webp が読み込まれる
   ```

3. **WebP非対応ブラウザ**
   ```
   - IE11などで名称未設定のデザイン-3-800.pngにフォールバック
   ```

### 3. Lighthouse測定

```bash
# Chrome DevTools → Lighthouse
1. URLを開く: https://joseikin-insight.com/grants/
2. Lighthouseタブを選択
3. Desktop / Mobileを選択
4. "Analyze page load" をクリック
```

**期待される結果**:
- Performance: 55 → 70 (+15点)
- LCP: 2.2秒 → 1.5秒以下
- FCP: 1.5秒 → 1.2秒以下

### 4. PageSpeed Insights

```
URL: https://pagespeed.web.dev/
入力: https://joseikin-insight.com/grants/
```

---

## 📊 期待される改善効果

### Before (現在)
| 指標 | 値 |
|------|-----|
| Performance Score | 55/100 |
| LCP | 2.2秒 |
| TBT | 630ms |
| FCP | 1.5秒 |
| Hero画像サイズ | 326 KiB |

### After (Phase 1完了後)
| 指標 | 値 | 改善 |
|------|-----|------|
| Performance Score | 70/100 | +15点 |
| LCP | 1.5秒 | -32% |
| TBT | 400ms | -36% |
| FCP | 1.2秒 | -20% |
| Hero画像サイズ | 80 KiB | -75% |

---

## 🔍 トラブルシューティング

### 問題1: WebP画像が表示されない

**原因**: ブラウザがWebPをサポートしていない
**解決**: PNGフォールバックが正常に機能しているか確認

### 問題2: 画像が404エラー

**原因**: ファイルパスが間違っている
**解決**: 
```bash
# 正しいパスを確認
ls -la /path/to/wp-content/uploads/2025/10/hero-image-*.webp
```

### 問題3: 画像サイズが期待より大きい

**原因**: 品質設定が高すぎる
**解決**: 
```bash
# 品質を下げて再変換 (quality 85 → 75)
convert input.png -resize 800x420 -quality 75 output.webp
```

### 問題4: サードパーティスクリプトがまだブロックしている

**確認方法**:
```javascript
// Consoleで確認
console.log('Tailwind loaded:', !!document.querySelector('script[src*="tailwindcss"]'));
```

**解決**: 遅延時間を調整 (500ms → 1000ms)

---

## 📝 追加の最適化（オプション）

### ロゴ画像の最適化

同様に、ヘッダーロゴも最適化可能です:

```bash
# 元画像
名称未設定のデザイン.png (110 KiB)

# 変換
convert 名称未設定のデザイン.png -resize 200x60 -quality 85 logo.webp
# 期待サイズ: 15 KiB (-86%)
```

---

## ✅ 完了チェックリスト

### 画像準備
- [ ] Hero画像を3サイズのWebPに変換
- [ ] PNGフォールバック画像を生成
- [ ] 各画像のファイルサイズを確認 (80KB以下)

### アップロード
- [ ] 4つのファイルをサーバーにアップロード
- [ ] 各URLが正常にアクセス可能

### テスト
- [ ] Chrome DevToolsでWebP画像が読み込まれることを確認
- [ ] レスポンシブ動作確認 (3サイズ)
- [ ] IE11等でPNGフォールバック確認
- [ ] Lighthouse測定 (スコア70以上)

### デプロイ
- [ ] archive-grant.phpの変更をデプロイ
- [ ] 本番環境で動作確認
- [ ] PageSpeed Insights測定

---

## 🚀 次のステップ

Phase 1完了後、以下を実施:

1. **Lighthouse再測定**
   - 目標スコア70達成確認

2. **Phase 2準備**
   - CSS最適化（クリティカルCSS抽出）
   - 目標: +10点 (70 → 80)

3. **モニタリング**
   - Google Analytics Web Vitals確認
   - Search Console Core Web Vitals確認

---

**作成日**: 2025-10-19  
**対象ファイル**: archive-grant.php  
**実装フェーズ**: Phase 1
