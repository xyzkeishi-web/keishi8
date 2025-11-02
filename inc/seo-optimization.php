<?php
/**
 * SEO Optimization Module
 * 
 * SEOスコア改善のためのメタタグと構造化データ
 * 
 * @package Grant_Insight_Perfect
 * @version 1.0.0
 * @since 9.2.1
 */

if (!defined('ABSPATH')) {
    exit;
}

class GI_SEO_Optimizer {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // メタタグ追加
        add_action('wp_head', [$this, 'add_seo_meta_tags'], 5);
        
        // Open Graphタグ追加
        add_action('wp_head', [$this, 'add_open_graph_tags'], 6);
        
        // 構造化データ追加
        add_action('wp_head', [$this, 'add_structured_data'], 10);
        
        // カノニカルURL追加
        add_action('wp_head', [$this, 'add_canonical_url'], 7);
    }
    
    /**
     * ========================================
     * SEOメタタグ
     * ========================================
     */
    
    /**
     * 基本的なSEOメタタグを追加
     */
    public function add_seo_meta_tags() {
        // 文字コード
        echo '<meta charset="' . get_bloginfo('charset') . '">' . "\n";
        
        // ビューポート（モバイル対応）
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">' . "\n";
        
        // テーマカラー
        echo '<meta name="theme-color" content="#000000">' . "\n";
        
        // 説明文
        $description = $this->get_page_description();
        if ($description) {
            echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
        }
        
        // キーワード（個別投稿の場合はタグから生成）
        if (is_singular()) {
            $keywords = $this->get_page_keywords();
            if ($keywords) {
                echo '<meta name="keywords" content="' . esc_attr($keywords) . '">' . "\n";
            }
        }
        
        // robots（インデックス制御）
        if (is_search() || is_404()) {
            echo '<meta name="robots" content="noindex, follow">' . "\n";
        } else {
            echo '<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">' . "\n";
        }
    }
    
    /**
     * Open Graphタグを追加
     */
    public function add_open_graph_tags() {
        // サイト名
        echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
        
        // タイトル
        $title = $this->get_page_title();
        echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
        
        // 説明文
        $description = $this->get_page_description();
        if ($description) {
            echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
        }
        
        // タイプ
        $og_type = is_singular() ? 'article' : 'website';
        echo '<meta property="og:type" content="' . $og_type . '">' . "\n";
        
        // URL
        $url = $this->get_current_url();
        echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
        
        // 画像
        $image = $this->get_page_image();
        if ($image) {
            echo '<meta property="og:image" content="' . esc_url($image) . '">' . "\n";
            echo '<meta property="og:image:width" content="1200">' . "\n";
            echo '<meta property="og:image:height" content="630">' . "\n";
        }
        
        // ロケール
        echo '<meta property="og:locale" content="ja_JP">' . "\n";
        
        // Twitter Card
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
        if ($description) {
            echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
        }
        if ($image) {
            echo '<meta name="twitter:image" content="' . esc_url($image) . '">' . "\n";
        }
    }
    
    /**
     * カノニカルURLを追加
     */
    public function add_canonical_url() {
        $canonical = $this->get_current_url();
        echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";
    }
    
    /**
     * ========================================
     * 構造化データ（JSON-LD）
     * ========================================
     */
    
    /**
     * 構造化データを追加
     */
    public function add_structured_data() {
        // 組織情報（全ページ共通）
        $this->output_organization_schema();
        
        // WebSiteスキーマ（ホームページ）
        if (is_front_page()) {
            $this->output_website_schema();
        }
        
        // 記事スキーマ（個別投稿）
        if (is_singular('grant')) {
            $this->output_article_schema();
        }
        
        // パンくずリストスキーマ
        if (!is_front_page()) {
            $this->output_breadcrumb_schema();
        }
    }
    
    /**
     * 組織情報スキーマ
     */
    private function output_organization_schema() {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => get_bloginfo('name'),
            'url' => home_url('/'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => 'https://joseikin-insight.com/wp-content/uploads/2025/09/名称未設定のデザイン.png',
                'width' => 200,
                'height' => 60
            ],
            'description' => get_bloginfo('description'),
            'sameAs' => []
        ];
        
        $this->output_json_ld($schema);
    }
    
    /**
     * WebSiteスキーマ
     */
    private function output_website_schema() {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => get_bloginfo('name'),
            'url' => home_url('/'),
            'description' => get_bloginfo('description'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => home_url('/?s={search_term_string}'),
                'query-input' => 'required name=search_term_string'
            ]
        ];
        
        $this->output_json_ld($schema);
    }
    
    /**
     * 記事スキーマ
     */
    private function output_article_schema() {
        if (!is_singular()) {
            return;
        }
        
        $post_id = get_the_ID();
        $post = get_post($post_id);
        
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => get_the_title(),
            'description' => $this->get_page_description(),
            'image' => $this->get_page_image(),
            'datePublished' => get_the_date('c', $post_id),
            'dateModified' => get_the_modified_date('c', $post_id),
            'author' => [
                '@type' => 'Organization',
                'name' => get_bloginfo('name')
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => get_bloginfo('name'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => 'https://joseikin-insight.com/wp-content/uploads/2025/09/名称未設定のデザイン.png',
                    'width' => 200,
                    'height' => 60
                ]
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => get_permalink()
            ]
        ];
        
        $this->output_json_ld($schema);
    }
    
    /**
     * パンくずリストスキーマ
     */
    private function output_breadcrumb_schema() {
        $breadcrumbs = [
            [
                'name' => 'ホーム',
                'url' => home_url('/')
            ]
        ];
        
        // アーカイブページの場合
        if (is_archive()) {
            $breadcrumbs[] = [
                'name' => get_the_archive_title(),
                'url' => get_permalink()
            ];
        }
        
        // 個別投稿の場合
        if (is_singular()) {
            // カスタム投稿タイプのアーカイブ
            $post_type = get_post_type();
            $post_type_obj = get_post_type_object($post_type);
            
            if ($post_type_obj && $post_type !== 'post' && $post_type !== 'page') {
                $breadcrumbs[] = [
                    'name' => $post_type_obj->label,
                    'url' => get_post_type_archive_link($post_type)
                ];
            }
            
            // 現在のページ
            $breadcrumbs[] = [
                'name' => get_the_title(),
                'url' => get_permalink()
            ];
        }
        
        // スキーマ生成
        $items = [];
        foreach ($breadcrumbs as $index => $crumb) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $crumb['name'],
                'item' => $crumb['url']
            ];
        }
        
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items
        ];
        
        $this->output_json_ld($schema);
    }
    
    /**
     * JSON-LDを出力
     */
    private function output_json_ld($schema) {
        echo '<script type="application/ld+json">' . "\n";
        echo json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        echo "\n" . '</script>' . "\n";
    }
    
    /**
     * ========================================
     * ヘルパー関数
     * ========================================
     */
    
    /**
     * ページタイトルを取得
     */
    private function get_page_title() {
        if (is_singular()) {
            return get_the_title();
        } elseif (is_archive()) {
            return get_the_archive_title();
        } elseif (is_search()) {
            return '検索結果: ' . get_search_query();
        } elseif (is_404()) {
            return 'ページが見つかりません';
        } else {
            return get_bloginfo('name');
        }
    }
    
    /**
     * ページ説明文を取得
     */
    private function get_page_description() {
        if (is_singular()) {
            $post_id = get_the_ID();
            
            // 抜粋があればそれを使用
            $excerpt = get_the_excerpt($post_id);
            if ($excerpt) {
                return wp_trim_words($excerpt, 30, '...');
            }
            
            // コンテンツから生成
            $content = get_the_content(null, false, $post_id);
            $content = wp_strip_all_tags($content);
            return wp_trim_words($content, 30, '...');
        } elseif (is_archive()) {
            $description = get_the_archive_description();
            return $description ? wp_trim_words($description, 30, '...') : get_bloginfo('description');
        } else {
            return get_bloginfo('description');
        }
    }
    
    /**
     * ページキーワードを取得
     */
    private function get_page_keywords() {
        if (!is_singular()) {
            return '';
        }
        
        $keywords = [];
        
        // タグから取得
        $tags = get_the_tags();
        if ($tags) {
            foreach ($tags as $tag) {
                $keywords[] = $tag->name;
            }
        }
        
        // カスタムタクソノミーから取得
        $taxonomies = get_object_taxonomies(get_post_type());
        foreach ($taxonomies as $taxonomy) {
            if ($taxonomy === 'post_tag') {
                continue; // 既に処理済み
            }
            
            $terms = get_the_terms(get_the_ID(), $taxonomy);
            if ($terms && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $keywords[] = $term->name;
                }
            }
        }
        
        return implode(', ', array_unique($keywords));
    }
    
    /**
     * ページ画像を取得
     */
    private function get_page_image() {
        if (is_singular()) {
            $post_id = get_the_ID();
            
            // アイキャッチ画像
            if (has_post_thumbnail($post_id)) {
                $image = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'large');
                if ($image) {
                    return $image[0];
                }
            }
            
            // コンテンツ内の最初の画像
            $content = get_the_content(null, false, $post_id);
            preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);
            if (!empty($matches[1])) {
                return $matches[1];
            }
        }
        
        // デフォルト画像（ロゴ）
        return 'https://joseikin-insight.com/wp-content/uploads/2025/09/名称未設定のデザイン.png';
    }
    
    /**
     * 現在のURLを取得
     */
    private function get_current_url() {
        global $wp;
        return home_url(add_query_arg([], $wp->request));
    }
}

// インスタンス化
GI_SEO_Optimizer::get_instance();
