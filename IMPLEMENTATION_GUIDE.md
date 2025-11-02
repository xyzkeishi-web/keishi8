# パフォーマンス最適化 実装ガイド
## 助成金・補助金インサイト

**作成日**: 2025-10-19  
**対象バージョン**: v9.2.0+  
**想定スコア改善**: 41 → 85-90

---

## 📋 実装チェックリスト

### ✅ 完了済み

1. **パフォーマンス最適化モジュールの作成**
   - ファイル: `/inc/performance-optimization.php`
   - 画像最適化、HTTPS強制、CSS/JS最適化、キャッシュ設定

2. **functions.phpへの統合**
   - パフォーマンス最適化モジュールを読み込むように更新

3. **遅延レンダリングJavaScript**
   - ファイル: `/assets/js/lazy-cards.js`
   - Intersection Observerを使用したDOM要素削減

4. **ビルド設定ファイル**
   - `package.json` - npm設定
   - `vite.config.js` - Viteビルド設定
   - `tailwind.config.js` - Tailwind CSS設定
   - `postcss.config.js` - PostCSS設定

5. **.htaccessファイル**
   - HTTPS強制リダイレクト
   - ブラウザキャッシュ設定
   - Gzip圧縮
   - セキュリティヘッダー

6. **ドキュメント**
   - `PERFORMANCE_OPTIMIZATION.md` - 詳細な最適化ガイド
   - このファイル - 実装手順

---

## 🚀 デプロイ手順

### Phase 1: 事前準備（5分）

1. **バックアップの作成**
   ```bash
   # データベースバックアップ
   wp db export backup-$(date +%Y%m%d).sql
   
   # ファイルバックアップ
   tar -czf ../joseikin-backup-$(date +%Y%m%d).tar.gz .
   ```

2. **現在のLighthouseスコアを記録**
   - URL: https://joseikin-insight.com
   - 現在のスコア: 41/100
   - スクリーンショット保存

### Phase 2: ファイルのアップロード（10分）

1. **新規ファイルをサーバーにアップロード**
   ```bash
   # FTP/SCPで以下をアップロード:
   /inc/performance-optimization.php
   /assets/js/lazy-cards.js
   package.json
   vite.config.js
   tailwind.config.js
   postcss.config.js
   .htaccess (既存ファイルがある場合は置き換え)
   ```

2. **functions.phpを更新**
   - 既存のfunctions.phpをバックアップ
   - 更新版のfunctions.phpをアップロード

3. **ファイルパーミッションの確認**
   ```bash
   chmod 644 /inc/performance-optimization.php
   chmod 644 /assets/js/lazy-cards.js
   chmod 644 .htaccess
   ```

### Phase 3: ビルドツールのセットアップ（オプション）（15分）

> **注意**: ビルドツールはローカル開発環境で実行することを推奨します。

1. **Node.jsのインストール確認**
   ```bash
   node -v  # v18以上を推奨
   npm -v
   ```

2. **依存関係のインストール**
   ```bash
   cd /path/to/theme
   npm install
   ```

3. **本番用ビルドの実行**
   ```bash
   npm run build
   ```

4. **ビルド済みファイルをサーバーにアップロード**
   ```bash
   # assets/dist/ ディレクトリをサーバーにアップロード
   rsync -avz assets/dist/ user@server:/path/to/theme/assets/dist/
   ```

### Phase 4: WordPress設定（5分）

1. **WordPressダッシュボードにログイン**

2. **パーマリンク設定の再保存**
   - 設定 → パーマリンク
   - 「変更を保存」をクリック（.htaccessの更新を反映）

3. **既存キャッシュのクリア**
   - キャッシュプラグインを使用している場合、すべてクリア
   - ブラウザのハードリフレッシュ（Ctrl+Shift+R）

### Phase 5: 画像の最適化（30分）

#### A. 既存画像のWebP変換（手動）

1. **主要画像の特定**
   ```
   /wp-content/uploads/2025/10/1.png (706 KiB)
   /wp-content/uploads/2025/09/名称未設定の*.png (110 KiB)
   ```

2. **WebP変換ツールの使用**
   
   **方法1: オンラインツール**
   - https://squoosh.app/
   - 品質: 85%
   - 形式: WebP
   
   **方法2: コマンドライン（ImageMagick）**
   ```bash
   convert input.png -quality 85 output.webp
   ```
   
   **方法3: WordPress プラグイン**
   - WebP Converter for Media
   - EWWW Image Optimizer

3. **変換後のファイルをアップロード**
   ```
   /wp-content/uploads/2025/10/1.webp
   /wp-content/uploads/2025/09/名称未設定の*.webp
   ```

#### B. 今後の画像アップロード

- 新規アップロード画像は自動的にWebPに変換されます
- `/inc/performance-optimization.php` の `generate_webp_on_upload()` が自動処理

### Phase 6: テストと検証（15分）

1. **機能テスト**
   ```
   ✓ トップページの表示
   ✓ 助成金カードの表示
   ✓ 検索機能
   ✓ フィルター機能
   ✓ 画像の表示（WebP対応ブラウザとフォールバック）
   ✓ モバイル表示
   ```

2. **パフォーマンステスト**
   - Google PageSpeed Insights: https://pagespeed.web.dev/
   - URL: https://joseikin-insight.com
   - 目標: 85以上

3. **ブラウザ互換性テスト**
   ```
   ✓ Chrome（最新）
   ✓ Firefox（最新）
   ✓ Safari（最新）
   ✓ Edge（最新）
   ✓ モバイルブラウザ
   ```

4. **エラーログの確認**
   ```bash
   tail -f /var/log/apache2/error.log
   # または
   tail -f /var/log/nginx/error.log
   ```

---

## 🔧 トラブルシューティング

### 問題1: 画像が表示されない

**原因**: WebP非対応ブラウザでフォールバック画像が見つからない

**解決策**:
```php
// functions.php に追加
add_filter('wp_get_attachment_image_src', function($image) {
    if (is_array($image) && isset($image[0])) {
        // WebPファイルが存在しない場合は元の画像を返す
        $webp_url = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $image[0]);
        if (!file_exists(str_replace(home_url(), ABSPATH, $webp_url))) {
            return $image; // 元の画像を使用
        }
    }
    return $image;
});
```

### 問題2: CSSが読み込まれない

**原因**: ファイルパスが正しくない、または権限の問題

**解決策**:
```bash
# ファイルの存在確認
ls -la /inc/performance-optimization.php

# パーミッション確認
chmod 644 /inc/performance-optimization.php

# WordPress管理画面でテーマを再度有効化
```

### 問題3: .htaccessが効かない

**原因**: Apacheのmod_rewriteが無効

**解決策**:
```bash
# mod_rewriteを有効化
sudo a2enmod rewrite
sudo systemctl restart apache2

# または .htaccessを許可
# /etc/apache2/sites-available/000-default.conf に追加:
<Directory /var/www/html>
    AllowOverride All
</Directory>
```

### 問題4: パフォーマンススコアが上がらない

**チェックリスト**:
- [ ] .htaccessが正しくアップロードされているか
- [ ] WebP画像が生成されているか
- [ ] ブラウザキャッシュがクリアされているか
- [ ] サーバーのGzip圧縮が有効か
- [ ] サードパーティスクリプト（広告）が遅延読み込みされているか

**検証方法**:
```bash
# Gzip圧縮の確認
curl -H "Accept-Encoding: gzip" -I https://joseikin-insight.com

# キャッシュヘッダーの確認
curl -I https://joseikin-insight.com/wp-content/uploads/2025/10/1.png
```

---

## 📊 期待される結果

### Before（現在）

| 指標 | 値 |
|------|-----|
| パフォーマンススコア | 41 |
| FCP | 8.9秒 |
| LCP | 16.1秒 |
| TBT | 440ms |
| CLS | 0.114 |

### After（実装後）

| 指標 | 目標値 | 改善率 |
|------|--------|--------|
| パフォーマンススコア | 85-90 | +107-119% |
| FCP | <1.8秒 | -80% |
| LCP | <2.5秒 | -84% |
| TBT | <200ms | -55% |
| CLS | <0.1 | -12% |

### データ削減

| カテゴリ | 削減量 |
|----------|--------|
| 画像 | ~692 KiB |
| CSS | ~391 KiB |
| JavaScript | ~272 KiB |
| **合計** | **~1.3 MB** |

---

## 🔄 継続的な最適化

### 週次タスク

1. **Lighthouseスコアの測定**
   ```bash
   npm install -g lighthouse
   lighthouse https://joseikin-insight.com --output html --output-path ./reports/lighthouse-$(date +%Y%m%d).html
   ```

2. **エラーログの確認**
   - PHPエラー
   - JavaScriptエラー
   - 404エラー

3. **画像最適化状況の確認**
   ```bash
   # WebP画像の数を確認
   find wp-content/uploads -name "*.webp" | wc -l
   
   # 未変換のPNG/JPG画像を確認
   find wp-content/uploads -name "*.png" -o -name "*.jpg" | while read file; do
       webp="${file%.*}.webp"
       if [ ! -f "$webp" ]; then
           echo "未変換: $file"
       fi
   done
   ```

### 月次タスク

1. **依存関係の更新**
   ```bash
   npm outdated
   npm update
   npm run build
   ```

2. **未使用CSS/JSの確認**
   - Chrome DevTools → Coverage タブ
   - 使用率が低いファイルを特定

3. **サードパーティスクリプトの見直し**
   - Google Tag Manager
   - Google Ads
   - その他のサービス

### 四半期タスク

1. **Core Web Vitalsの分析**
   - Google Search Console
   - リアルユーザーデータの確認

2. **新しい最適化技術の調査**
   - HTTP/3 対応
   - Brotli圧縮
   - Service Worker

3. **A/Bテストの実施**
   - 最適化前後の比較
   - コンバージョン率への影響

---

## 📚 参考資料

### 公式ドキュメント

- [Web Vitals](https://web.dev/vitals/)
- [Lighthouse Performance Scoring](https://web.dev/performance-scoring/)
- [WebP Image Format](https://developers.google.com/speed/webp)
- [Critical Rendering Path](https://developers.google.com/web/fundamentals/performance/critical-rendering-path)

### ツール

- [Google PageSpeed Insights](https://pagespeed.web.dev/)
- [WebPageTest](https://www.webpagetest.org/)
- [GTmetrix](https://gtmetrix.com/)
- [Lighthouse CI](https://github.com/GoogleChrome/lighthouse-ci)

### WordPress最適化

- [WordPress Performance Guide](https://developer.wordpress.org/advanced-administration/performance/)
- [WordPress Caching](https://developer.wordpress.org/advanced-administration/performance/caching/)

---

## ✅ デプロイ完了後のチェックリスト

- [ ] すべてのファイルがアップロードされた
- [ ] functions.phpが正しく更新された
- [ ] .htaccessが適用された
- [ ] 既存画像がWebPに変換された
- [ ] パーマリンクが再保存された
- [ ] キャッシュがクリアされた
- [ ] 機能テストが完了した
- [ ] Lighthouseスコアが改善された（目標: 85以上）
- [ ] エラーログに問題がない
- [ ] モバイル表示が正常
- [ ] すべてのブラウザで動作確認完了

---

## 🎯 次のステップ

デプロイが完了したら:

1. **スコアの記録**
   - Before/Afterのスクリーンショット
   - 数値データの保存

2. **チームへの共有**
   - 改善内容の報告
   - 今後のメンテナンス計画

3. **ユーザーフィードバックの収集**
   - ページ読み込み速度の体感
   - エラーや問題の報告

4. **継続的な監視**
   - 週次レポートの設定
   - アラートの設定

---

**作成者**: GenSpark AI Developer  
**更新日**: 2025-10-19  
**バージョン**: 1.0
