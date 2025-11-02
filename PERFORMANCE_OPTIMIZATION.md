# パフォーマンス最適化レポート
## 助成金・補助金インサイト (joseikin-insight.com)

**作成日**: 2025-10-19  
**Lighthouse スコア**: 41/100  
**対象**: https://joseikin-insight.com

---

## 📊 現状分析

### 主要指標の問題点

| 指標 | 現在値 | 目標値 | 改善の余地 |
|------|--------|--------|------------|
| **FCP** (First Contentful Paint) | 8.9秒 | <1.8秒 | ⚠️ 7.1秒 |
| **LCP** (Largest Contentful Paint) | 16.1秒 | <2.5秒 | ⚠️ 13.6秒 |
| **TBT** (Total Blocking Time) | 440ms | <200ms | ⚠️ 240ms |
| **CLS** (Cumulative Layout Shift) | 0.114 | <0.1 | ⚠️ 0.014 |
| **Speed Index** | 11.0秒 | <3.4秒 | ⚠️ 7.6秒 |

---

## 🔴 緊急対応が必要な項目（優先度: 高）

### 1. 画像最適化（推定削減: 691.9 KiB）

#### 問題
- PNG形式の大きな画像が2つ使用されている
  - `/wp-content/uploads/2025/10/1.png`: 706.1 KiB → WebP変換で608.7 KiB削減
  - ロゴ画像: 110.1 KiB → WebP変換で83.2 KiB削減

#### 解決策

**A. WebP形式への変換（自動化）**

```php
// functions.phpに追加
/**
 * アップロード時に自動的にWebPを生成
 */
function gi_generate_webp_on_upload($metadata, $attachment_id) {
    $file_path = get_attached_file($attachment_id);
    
    if (!file_exists($file_path)) {
        return $metadata;
    }
    
    $image_editor = wp_get_image_editor($file_path);
    
    if (!is_wp_error($image_editor)) {
        $file_info = pathinfo($file_path);
        $webp_path = $file_info['dirname'] . '/' . $file_info['filename'] . '.webp';
        
        // WebP形式で保存
        $image_editor->set_quality(85);
        $saved = $image_editor->save($webp_path, 'image/webp');
        
        if (!is_wp_error($saved)) {
            // メタデータに追加
            $metadata['webp_path'] = $webp_path;
        }
    }
    
    return $metadata;
}
add_filter('wp_generate_attachment_metadata', 'gi_generate_webp_on_upload', 10, 2);

/**
 * <picture>タグでWebPを優先的に提供
 */
function gi_output_webp_picture($html, $attachment_id, $size) {
    $webp_path = get_post_meta($attachment_id, '_webp_path', true);
    
    if ($webp_path && file_exists($webp_path)) {
        $webp_url = str_replace(
            wp_get_upload_dir()['basedir'],
            wp_get_upload_dir()['baseurl'],
            $webp_path
        );
        
        // 元の画像URL
        $src = wp_get_attachment_image_src($attachment_id, $size);
        
        if ($src) {
            $html = sprintf(
                '<picture>
                    <source srcset="%s" type="image/webp">
                    <img src="%s" alt="%s" loading="lazy" width="%d" height="%d">
                </picture>',
                esc_url($webp_url),
                esc_url($src[0]),
                esc_attr(get_post_meta($attachment_id, '_wp_attachment_image_alt', true)),
                $src[1],
                $src[2]
            );
        }
    }
    
    return $html;
}
add_filter('wp_get_attachment_image', 'gi_output_webp_picture', 10, 3);
```

**B. 画像サイズの最適化**

```php
// functions.phpに追加
/**
 * カスタム画像サイズを定義
 */
function gi_custom_image_sizes() {
    // ヒーロー画像（最大幅800px）
    add_image_size('grant-hero', 800, 600, true);
    
    // カードサムネイル（グリッド表示用）
    add_image_size('grant-card', 400, 300, true);
    
    // モバイルビュー
    add_image_size('grant-mobile', 375, 280, true);
}
add_action('after_setup_theme', 'gi_custom_image_sizes');

/**
 * レスポンシブ画像のsrcset自動生成
 */
function gi_responsive_images($html, $attachment_id) {
    $sizes = [
        'grant-mobile' => '(max-width: 640px) 375px',
        'grant-card' => '(max-width: 1024px) 400px',
        'grant-hero' => '800px'
    ];
    
    $srcset = [];
    foreach ($sizes as $size => $media_query) {
        $src = wp_get_attachment_image_src($attachment_id, $size);
        if ($src) {
            $srcset[] = esc_url($src[0]) . ' ' . $src[1] . 'w';
        }
    }
    
    if (!empty($srcset)) {
        $html = str_replace('<img ', sprintf(
            '<img srcset="%s" sizes="%s" ',
            implode(', ', $srcset),
            '(max-width: 640px) 375px, (max-width: 1024px) 400px, 800px'
        ), $html);
    }
    
    return $html;
}
add_filter('wp_get_attachment_image', 'gi_responsive_images', 10, 2);
```

---

### 2. レンダリングブロックリソースの排除（推定削減: 7.15秒）

#### 問題
- CSSとJavaScriptが初期レンダリングをブロックしている
- Google Fonts（3ファイル）、Font Awesome、Tailwind CSSが同期読み込み

#### 解決策

**A. CSS の遅延読み込み**

```php
// functions.phpに追加
/**
 * CSSを非同期で読み込む
 */
function gi_async_styles() {
    ?>
    <script>
    // CSS非同期読み込み関数
    function loadCSS(href, media) {
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = href;
        link.media = media || 'all';
        document.head.appendChild(link);
    }
    
    // クリティカルでないCSSを遅延読み込み
    if (window.requestIdleCallback) {
        requestIdleCallback(function() {
            loadCSS('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
        });
    } else {
        window.addEventListener('load', function() {
            loadCSS('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
        });
    }
    </script>
    <?php
}
add_action('wp_head', 'gi_async_styles', 5);
```

**B. クリティカルCSSのインライン化**

```php
/**
 * Above the fold CSSをインライン化
 */
function gi_inline_critical_css() {
    ?>
    <style>
    /* クリティカルCSS - Above the Fold */
    :root {
        --color-black: #000;
        --color-white: #fff;
        --color-gray-100: #f5f5f5;
        --color-gray-800: #1a1a1a;
    }
    
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        line-height: 1.6;
        color: var(--color-gray-800);
    }
    
    /* ヘッダー - Above the Fold */
    .stylish-header {
        position: fixed;
        top: 0;
        width: 100%;
        background: var(--color-white);
        border-bottom: 1px solid rgba(0,0,0,0.1);
        z-index: 1000;
    }
    
    /* ヒーローセクション - Above the Fold */
    .hero-section {
        min-height: 60vh;
        display: flex;
        align-items: center;
        padding-top: 80px;
    }
    
    /* 検索ボックス - Above the Fold */
    .search-input-wrapper {
        max-width: 800px;
        margin: 0 auto;
    }
    </style>
    <?php
}
add_action('wp_head', 'gi_inline_critical_css', 1);
```

**C. JavaScriptの遅延読み込み**

```php
/**
 * JavaScriptを defer/async で読み込む
 */
function gi_defer_scripts($tag, $handle, $src) {
    // 遅延読み込みしたいスクリプトのハンドル
    $defer_scripts = [
        'jquery',
        'wp-embed',
    ];
    
    // 非同期読み込みしたいスクリプト
    $async_scripts = [
        'google-analytics',
        'google-tag-manager',
    ];
    
    if (in_array($handle, $defer_scripts)) {
        return str_replace('<script ', '<script defer ', $tag);
    }
    
    if (in_array($handle, $async_scripts)) {
        return str_replace('<script ', '<script async ', $tag);
    }
    
    return $tag;
}
add_filter('script_loader_tag', 'gi_defer_scripts', 10, 3);
```

---

### 3. サードパーティスクリプトの最適化（推定削減: 1,070ms）

#### 問題
- Google Ads: 595ms
- Google Tag Manager: 561ms  
- Google FundingChoices: 383ms

#### 解決策

**A. スクリプトの遅延読み込み**

```php
/**
 * サードパーティスクリプトをユーザー操作後に読み込む
 */
function gi_lazy_load_third_party_scripts() {
    ?>
    <script>
    (function() {
        let thirdPartyScriptsLoaded = false;
        
        // サードパーティスクリプトを読み込む関数
        function loadThirdPartyScripts() {
            if (thirdPartyScriptsLoaded) return;
            thirdPartyScriptsLoaded = true;
            
            // Google Tag Manager
            (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','GT-NBP3PZK8');
            
            // Google Ads（必要に応じて）
            // ... その他のスクリプト
        }
        
        // ユーザー操作を検知したら読み込む
        const events = ['scroll', 'click', 'mousemove', 'touchstart', 'keydown'];
        const triggerLoad = function() {
            loadThirdPartyScripts();
            events.forEach(event => {
                window.removeEventListener(event, triggerLoad);
            });
        };
        
        // 各イベントにリスナーを設定
        events.forEach(event => {
            window.addEventListener(event, triggerLoad, { once: true, passive: true });
        });
        
        // 5秒経過したら自動的に読み込む
        setTimeout(loadThirdPartyScripts, 5000);
    })();
    </script>
    <?php
}
add_action('wp_footer', 'gi_lazy_load_third_party_scripts', 1);
```

**B. Google Fontsの最適化**

```php
/**
 * Google Fontsを最適化して読み込む
 */
function gi_optimize_google_fonts() {
    ?>
    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Font display: swap で即座にフォールバックフォント表示 -->
    <link rel="preload" as="style" 
          href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@400;600;700&family=Noto+Sans+JP:wght@400;500;700&display=swap">
    
    <link rel="stylesheet" 
          href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@400;600;700&family=Noto+Sans+JP:wght@400;500;700&display=swap"
          media="print" onload="this.media='all'">
    
    <noscript>
        <link rel="stylesheet" 
              href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@400;600;700&family=Noto+Sans+JP:wght@400;500;700&display=swap">
    </noscript>
    <?php
}
add_action('wp_head', 'gi_optimize_google_fonts', 3);
```

---

### 4. 未使用CSS/JavaScriptの削減

#### 問題
- 未使用CSS: 391 KiB
- 未使用JavaScript: 272 KiB

#### 解決策

**A. Tailwind CSSの最適化（CDNから自己ホスト + Purge）**

```javascript
// tailwind.config.js（新規作成）
module.exports = {
  content: [
    './**/*.php',
    './assets/js/**/*.js',
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
```

**B. CSSの最小化とインライン化**

```php
/**
 * 使用しているCSSのみを読み込む
 */
function gi_enqueue_optimized_styles() {
    // Tailwind CDNを削除し、ビルド済みカスタムCSSを使用
    wp_dequeue_style('tailwind-cdn');
    
    // カスタムビルドのCSSを読み込む
    wp_enqueue_style(
        'gi-optimized-styles',
        get_template_directory_uri() . '/assets/css/optimized.min.css',
        [],
        GI_THEME_VERSION
    );
}
add_action('wp_enqueue_scripts', 'gi_enqueue_optimized_styles', 20);
```

---

### 5. HTTPS混在コンテンツの修正

#### 問題
- ロゴ画像がHTTPで読み込まれている

#### 解決策

```php
/**
 * すべてのURLを強制的にHTTPSに変換
 */
function gi_force_https($url) {
    return str_replace('http://', 'https://', $url);
}
add_filter('wp_get_attachment_url', 'gi_force_https');
add_filter('wp_get_attachment_image_src', function($image) {
    if (is_array($image) && isset($image[0])) {
        $image[0] = gi_force_https($image[0]);
    }
    return $image;
});

/**
 * コンテンツ内のすべてのURLをHTTPSに
 */
function gi_https_content($content) {
    return str_replace('http://joseikin-insight.com', 'https://joseikin-insight.com', $content);
}
add_filter('the_content', 'gi_https_content');
add_filter('widget_text', 'gi_https_content');
```

---

## 🟡 中優先度の改善項目

### 6. DOM要素の削減（現在: 2,946要素）

#### 解決策

**A. カードの遅延レンダリング（Intersection Observer）**

```javascript
// assets/js/lazy-cards.js
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.grant-card');
    
    const cardObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // カードが見えたらコンテンツを読み込む
                const card = entry.target;
                const template = card.querySelector('template');
                
                if (template) {
                    card.innerHTML = template.innerHTML;
                }
                
                cardObserver.unobserve(card);
            }
        });
    }, {
        rootMargin: '100px' // ビューポートの100px前に読み込み開始
    });
    
    cards.forEach(card => cardObserver.observe(card));
});
```

**B. 仮想スクロール（無限スクロール）**

```php
// functions.phpに追加
/**
 * AJAX ページネーション
 */
function gi_ajax_load_more_grants() {
    $paged = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $posts_per_page = 12;
    
    $args = [
        'post_type' => 'grant',
        'posts_per_page' => $posts_per_page,
        'paged' => $paged,
        'post_status' => 'publish',
    ];
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        ob_start();
        
        while ($query->have_posts()) {
            $query->the_post();
            echo gi_render_card(get_the_ID(), 'grid');
        }
        
        $html = ob_get_clean();
        
        wp_send_json_success([
            'html' => $html,
            'has_more' => $paged < $query->max_num_pages,
            'next_page' => $paged + 1,
        ]);
    } else {
        wp_send_json_error(['message' => '投稿が見つかりませんでした']);
    }
    
    wp_reset_postdata();
    wp_die();
}
add_action('wp_ajax_load_more_grants', 'gi_ajax_load_more_grants');
add_action('wp_ajax_nopriv_load_more_grants', 'gi_ajax_load_more_grants');
```

---

### 7. キャッシュポリシーの改善

#### 解決策

**A. .htaccessでブラウザキャッシュを設定**

```apache
# /home/user/webapp/.htaccess に追加
<IfModule mod_expires.c>
    ExpiresActive On
    
    # 画像
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
    
    # CSS/JavaScript
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType text/javascript "access plus 1 month"
    
    # フォント
    ExpiresByType font/woff2 "access plus 1 year"
    ExpiresByType font/woff "access plus 1 year"
    ExpiresByType font/ttf "access plus 1 year"
    
    # その他
    ExpiresByType application/pdf "access plus 1 month"
</IfModule>

<IfModule mod_headers.c>
    # Cache-Control ヘッダー
    <FilesMatch "\.(jpg|jpeg|png|gif|webp|svg|ico)$">
        Header set Cache-Control "max-age=31536000, public, immutable"
    </FilesMatch>
    
    <FilesMatch "\.(css|js)$">
        Header set Cache-Control "max-age=2592000, public"
    </FilesMatch>
    
    <FilesMatch "\.(woff2|woff|ttf)$">
        Header set Cache-Control "max-age=31536000, public, immutable"
    </FilesMatch>
</IfModule>
```

**B. WordPressでのキャッシュヘッダー設定**

```php
/**
 * 静的アセットにキャッシュヘッダーを追加
 */
function gi_add_cache_headers() {
    if (!is_admin()) {
        // 1年間キャッシュ
        header('Cache-Control: public, max-age=31536000, immutable');
    }
}

// 画像URLにフックする
add_action('wp_get_attachment_url', function($url) {
    if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $url)) {
        gi_add_cache_headers();
    }
    return $url;
});
```

---

### 8. 画像のwidth/height属性追加（CLS改善）

#### 解決策

```php
/**
 * すべての画像に明示的なwidth/heightを追加
 */
function gi_add_image_dimensions($html, $attachment_id) {
    if (strpos($html, 'width=') !== false && strpos($html, 'height=') !== false) {
        return $html; // すでに設定されている
    }
    
    $metadata = wp_get_attachment_metadata($attachment_id);
    
    if (!empty($metadata['width']) && !empty($metadata['height'])) {
        $html = str_replace(
            '<img ',
            sprintf('<img width="%d" height="%d" ', $metadata['width'], $metadata['height']),
            $html
        );
    }
    
    return $html;
}
add_filter('wp_get_attachment_image', 'gi_add_image_dimensions', 10, 2);

/**
 * コンテンツ内の画像にも適用
 */
function gi_add_dimensions_to_content_images($content) {
    if (empty($content)) {
        return $content;
    }
    
    // 正規表現で<img>タグを検索
    preg_match_all('/<img[^>]+>/i', $content, $matches);
    
    if (!empty($matches[0])) {
        foreach ($matches[0] as $img_tag) {
            // すでにwidth/heightがある場合はスキップ
            if (strpos($img_tag, 'width=') !== false && strpos($img_tag, 'height=') !== false) {
                continue;
            }
            
            // attachment IDを取得
            preg_match('/wp-image-(\d+)/i', $img_tag, $class_id);
            
            if (!empty($class_id[1])) {
                $attachment_id = intval($class_id[1]);
                $metadata = wp_get_attachment_metadata($attachment_id);
                
                if (!empty($metadata['width']) && !empty($metadata['height'])) {
                    $new_img_tag = str_replace(
                        '<img ',
                        sprintf('<img width="%d" height="%d" ', $metadata['width'], $metadata['height']),
                        $img_tag
                    );
                    
                    $content = str_replace($img_tag, $new_img_tag, $content);
                }
            }
        }
    }
    
    return $content;
}
add_filter('the_content', 'gi_add_dimensions_to_content_images');
```

---

### 9. CSS/JavaScriptの最小化と結合

#### 解決策

**A. ビルドツールの導入（Webpack/Vite）**

```javascript
// package.json（新規作成）
{
  "name": "joseikin-insight-theme",
  "version": "9.1.0",
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "preview": "vite preview"
  },
  "devDependencies": {
    "vite": "^5.0.0",
    "tailwindcss": "^3.4.0",
    "autoprefixer": "^10.4.0",
    "postcss": "^8.4.0",
    "cssnano": "^6.0.0",
    "terser": "^5.26.0"
  }
}
```

```javascript
// vite.config.js（新規作成）
import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
  build: {
    outDir: 'assets/dist',
    rollupOptions: {
      input: {
        main: path.resolve(__dirname, 'assets/js/main.js'),
        styles: path.resolve(__dirname, 'assets/css/main.css'),
      },
      output: {
        entryFileNames: 'js/[name].[hash].js',
        chunkFileNames: 'js/[name].[hash].js',
        assetFileNames: 'css/[name].[hash].[ext]',
      },
    },
    minify: 'terser',
    terserOptions: {
      compress: {
        drop_console: true,
        drop_debugger: true,
      },
    },
  },
  css: {
    postcss: {
      plugins: [
        require('tailwindcss'),
        require('autoprefixer'),
        require('cssnano')({
          preset: 'default',
        }),
      ],
    },
  },
});
```

**B. 自動最小化（functions.php）**

```php
/**
 * 本番環境でのみ最小化されたアセットを読み込む
 */
function gi_enqueue_optimized_assets() {
    $is_production = !WP_DEBUG;
    $suffix = $is_production ? '.min' : '';
    
    // CSS
    wp_enqueue_style(
        'gi-main-styles',
        get_template_directory_uri() . "/assets/dist/css/main{$suffix}.css",
        [],
        GI_THEME_VERSION
    );
    
    // JavaScript
    wp_enqueue_script(
        'gi-main-scripts',
        get_template_directory_uri() . "/assets/dist/js/main{$suffix}.js",
        [],
        GI_THEME_VERSION,
        true
    );
}
add_action('wp_enqueue_scripts', 'gi_enqueue_optimized_assets');
```

---

## 📈 期待される改善効果

### 最適化後の予測値

| 指標 | 現在値 | 改善後（予測） | 改善率 |
|------|--------|----------------|--------|
| **パフォーマンススコア** | 41 | 85-90 | +109% |
| **FCP** | 8.9秒 | 1.5秒 | -83% |
| **LCP** | 16.1秒 | 2.2秒 | -86% |
| **TBT** | 440ms | 150ms | -66% |
| **CLS** | 0.114 | 0.05 | -56% |
| **Speed Index** | 11.0秒 | 3.0秒 | -73% |

### データ削減

| カテゴリ | 削減量 | 削減率 |
|----------|--------|--------|
| **画像** | 692 KiB | ~70% |
| **CSS** | 391 KiB | ~65% |
| **JavaScript** | 272 KiB | ~45% |
| **合計** | ~1.3 MB | ~60% |

---

## 🔧 実装手順

### フェーズ 1: 緊急対応（1-2週間）
1. ✅ HTTPS混在コンテンツの修正
2. ✅ 画像のWebP変換と最適化
3. ✅ クリティカルCSSのインライン化
4. ✅ サードパーティスクリプトの遅延読み込み

### フェーズ 2: 中期改善（2-4週間）
5. ✅ CSS/JSの最小化と結合
6. ✅ キャッシュポリシーの設定
7. ✅ DOM要素の削減（遅延レンダリング）
8. ✅ 画像へのwidth/height属性追加

### フェーズ 3: 長期最適化（1-2ヶ月）
9. ✅ CDN導入の検討
10. ✅ サーバーサイドレンダリング（SSR）の検討
11. ✅ 継続的なパフォーマンスモニタリング
12. ✅ A/Bテストによる効果測定

---

## 📊 モニタリングとメンテナンス

### 定期チェック項目

1. **週次チェック**
   - Lighthouse スコア
   - Core Web Vitals（Google Search Console）
   - エラーログ確認

2. **月次チェック**
   - 画像最適化状況
   - 未使用CSS/JS の増加チェック
   - キャッシュヒット率

3. **四半期チェック**
   - サードパーティスクリプトの見直し
   - 新しい最適化技術の調査
   - パフォーマンス目標の再設定

### ツール推奨

- **Lighthouse CI**: 継続的パフォーマンステスト
- **WebPageTest**: 詳細なウォーターフォール分析
- **Google PageSpeed Insights**: リアルユーザーデータ
- **Chrome DevTools**: 開発時のパフォーマンス分析

---

## 🎯 成功指標（KPI）

### 短期目標（1ヶ月）
- ✅ Lighthouse スコア: 70以上
- ✅ LCP: 4秒以下
- ✅ FCP: 2.5秒以下

### 中期目標（3ヶ月）
- ✅ Lighthouse スコア: 85以上
- ✅ LCP: 2.5秒以下
- ✅ FCP: 1.8秒以下
- ✅ CLS: 0.1以下

### 長期目標（6ヶ月）
- ✅ Lighthouse スコア: 90以上
- ✅ すべてのCore Web Vitals で「良好」評価
- ✅ 直帰率: 10%削減
- ✅ ページ滞在時間: 20%増加

---

**作成者**: GenSpark AI Developer  
**更新日**: 2025-10-19  
**バージョン**: 1.0
