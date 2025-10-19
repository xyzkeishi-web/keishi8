# ビルド設定ファイル / Build Configuration Files

このディレクトリにはフロントエンドビルドツールの設定ファイルを格納しています。

## ファイル一覧

### 1. vite.config.js
**役割**: モダンなフロントエンドビルドツールの設定

**処理内容**:
- JavaScriptとCSSのバンドル（複数ファイルを1つに結合）
- コードの圧縮・最適化（ファイルサイズ削減）
- キャッシュバスティング（ファイル名にハッシュ追加）
- 開発サーバー起動（HMR対応）

**エントリーポイント**:
- `assets/js/main.js` → `assets/dist/js/main.[hash].js`
- `assets/js/lazy-cards.js` → `assets/dist/js/lazy-cards.[hash].js`

**コマンド**:
```bash
npm run dev      # 開発サーバー起動
npm run build    # 本番ビルド（圧縮・最適化）
npm run preview  # ビルド結果のプレビュー
```

---

### 2. tailwind.config.js
**役割**: Tailwind CSSフレームワークのデザインシステム定義

**処理内容**:
- プロジェクト全体の色・フォント・余白・影などの定義
- 未使用のCSSクラスを自動削除（purge機能）
- ユーティリティクラス生成（`bg-gray-50`, `text-primary`, `font-japanese`など）

**カスタマイズ項目**:
- カラーパレット（gray, white, black）
- フォントファミリー（Outfit, Inter, Noto Sans JP）
- スペーシング（1〜12の間隔）
- ボーダー半径、シャドウ、トランジション

**スキャン対象**:
- すべてのPHPファイル（`../**/*.php`）
- JavaScriptファイル（`../assets/js/**/*.js`）

---

### 3. postcss.config.js
**役割**: CSS処理パイプラインの設定

**処理内容**:
- **Tailwind CSS**: ユーティリティクラスを実際のCSSに変換
- **Autoprefixer**: ブラウザ互換性のためベンダープレフィックスを自動追加
  - 例: `display: flex` → `-webkit-box-flex`, `-ms-flexbox`, `flex`
- **cssnano**: 本番環境でCSSを圧縮・最適化
  - コメント削除、空白削除、色の最適化

**実行タイミング**:
- `npm run build` 時に自動実行
- Viteビルドプロセスの一部として動作

---

## ディレクトリ構成

```
config/
├── README.md             # このファイル
├── vite.config.js        # Viteビルドツール設定
├── tailwind.config.js    # Tailwind CSSデザインシステム
└── postcss.config.js     # PostCSS処理パイプライン
```

---

## パス設定の重要性

設定ファイルは `config/` ディレクトリに配置されているため、相対パス解決に注意:

- **vite.config.js**: `__dirname` から親ディレクトリを参照
- **tailwind.config.js**: `path.resolve(__dirname, '..')` で親ディレクトリを参照
- **postcss.config.js**: `path.resolve(__dirname, 'tailwind.config.js')` で同階層を参照

---

## package.json との連携

各ビルドコマンドは設定ファイルのパスを明示的に指定:

```json
{
  "scripts": {
    "dev": "vite --config config/vite.config.js",
    "build": "vite build --config config/vite.config.js",
    "build:css": "postcss ... --config config/postcss.config.js"
  }
}
```

---

## 設定変更時の注意点

### 1. パス変更時
- 相対パス参照を必ず確認
- ビルドが通るかテスト: `npm run build`

### 2. 依存関係追加時
- `package.json` に追加してインストール: `npm install`

### 3. Tailwind設定変更時
- `purge.content` パスを確認（未使用クラス削除の対象）
- 動的クラスは `safelist` に追加

### 4. 本番デプロイ前
- 必ず `npm run build` を実行
- `assets/dist/` ディレクトリが生成されることを確認
