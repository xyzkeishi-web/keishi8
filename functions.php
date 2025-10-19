<?php
/**
 * Grant Insight Perfect - Functions File (Consolidated & Clean Edition)
 * 
 * Simplified structure with consolidated files in single /inc/ directory
 * - Removed unused code and duplicate functionality
 * - Merged related files for better organization
 * - Eliminated folder over-organization
 * 
 * @package Grant_Insight_Perfect
 * @version 9.0.0 (Consolidated Edition)
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit;
}

// テーマバージョン定数
if (!defined('GI_THEME_VERSION')) {
    define('GI_THEME_VERSION', '9.1.0'); // Municipality slug standardization update
}
if (!defined('GI_THEME_PREFIX')) {
    define('GI_THEME_PREFIX', 'gi_');
}

// EMERGENCY: File editing temporarily disabled to prevent memory exhaustion
// All theme editor functionality removed until memory issue is resolved

// 🔧 MEMORY OPTIMIZATION
// Increase memory limit for admin area only
if (is_admin() && !wp_doing_ajax()) {
    @ini_set('memory_limit', '256M');
    
    // Limit WordPress features that consume memory
    add_action('init', function() {
        // Disable post revisions temporarily
        if (!defined('WP_POST_REVISIONS')) {
            define('WP_POST_REVISIONS', 3);
        }
        
        // Reduce autosave interval
        if (!defined('AUTOSAVE_INTERVAL')) {
            define('AUTOSAVE_INTERVAL', 300); // 5 minutes
        }
    }, 1);
}

// 統合されたファイルの読み込み（シンプルな配列）
$inc_dir = get_template_directory() . '/inc/';

$required_files = array(
    // Core files
    'theme-foundation.php',        // テーマ設定、投稿タイプ、タクソノミー
    'data-processing.php',         // データ処理・ヘルパー関数
    
    // Admin & UI
    'admin-functions.php',         // 管理画面カスタマイズ + メタボックス (統合済み)
    'acf-fields.php',              // ACF設定とフィールド定義
    
    // Core functionality
    'card-display.php',            // カードレンダリング・表示機能
    'ajax-functions.php',          // AJAX処理
    'ai-functions.php',            // AI機能・検索履歴 (統合済み)
    
    // Performance optimization
    'performance-optimization.php', // パフォーマンス最適化（v9.2.0+）
    'seo-optimization.php',         // SEO最適化（v9.2.1+）
    
    // Google Sheets integration (consolidated into one file)
    'google-sheets-integration.php', // Google Sheets統合（全機能統合版）
    'safe-sync-manager.php',         // 安全同期管理システム
    'disable-auto-sync.php'          // 自動同期無効化
);

// ファイルを安全に読み込み
foreach ($required_files as $file) {
    $file_path = $inc_dir . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
    } else {
        // デバッグモードの場合のみエラーログに記録
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Grant Insight: Missing required file: ' . $file);
        }
    }
}

// グローバルで使えるヘルパー関数
if (!function_exists('gi_render_card')) {
    function gi_render_card($post_id, $view = 'grid') {
        if (class_exists('GrantCardRenderer')) {
            $renderer = GrantCardRenderer::getInstance();
            return $renderer->render($post_id, $view);
        }
        
        // フォールバック
        return '<div class="grant-card-error">カードレンダラーが利用できません</div>';
    }
}

/**
 * エラーハンドリング強化
 */
function gi_error_handler($errno, $errstr, $errfile, $errline) {
    // 重要でないエラーはログに記録するだけで処理を続行
    if (strpos($errstr, 'Attempt to read property') !== false || 
        strpos($errstr, 'count():') !== false ||
        strpos($errstr, 'Undefined variable') !== false) {
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Grant Insight Error Handler: {$errstr} in {$errfile} on line {$errline}");
        }
        return true; // エラーを抑制
    }
    
    return false; // 通常のエラーハンドリングに委ねる
}

// Custom error handler temporarily disabled to reduce memory usage
// Re-enabled after memory optimization

/**
 * テーマの最終初期化
 */
function gi_final_init() {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Grant Insight: Theme initialized successfully v' . GI_THEME_VERSION);
    }
}
add_action('wp_loaded', 'gi_final_init', 999);

/**
 * WordPress CoreのRecovery Mode関連エラー対策
 */
function gi_fix_recovery_mode_errors() {
    // Recovery Mode Email Service の配列アクセスエラーを防ぐ
    add_filter('recovery_mode_email', function($email) {
        if (!is_array($email) || !isset($email['to'])) {
            return false; // メール送信を無効化してエラーを防ぐ
        }
        return $email;
    }, 10, 1);
    
    // PHPエラーの詳細情報を安全に処理
    add_filter('wp_php_error_message', function($message, $error) {
        if (!is_array($error)) {
            return 'PHP Error detected but details unavailable.';
        }
        return $message;
    }, 10, 2);
}
add_action('init', 'gi_fix_recovery_mode_errors', 1);

/**
 * クリーンアップ処理
 */
function gi_theme_cleanup() {
    // 不要なオプションの削除
    delete_option('gi_login_attempts');
    delete_option('gi_mobile_cache');
    delete_transient('gi_site_stats_v2');
    
    // オブジェクトキャッシュのフラッシュ（存在する場合のみ）
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
}
add_action('switch_theme', 'gi_theme_cleanup');

/**
 * コンタクトフォーム処理（admin_post方式）
 */
add_action('admin_post_nopriv_contact_form', 'gi_handle_contact_form');
add_action('admin_post_contact_form', 'gi_handle_contact_form');

function gi_handle_contact_form() {
    // Nonceの検証
    if (!wp_verify_nonce($_POST['contact_form_nonce'], 'contact_form_submit')) {
        wp_die('セキュリティエラーが発生しました。');
    }
    
    $form_errors = array();
    
    // 入力値の取得とサニタイズ
    $inquiry_type = sanitize_text_field($_POST['inquiry_type'] ?? '');
    $name = sanitize_text_field($_POST['name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $phone = sanitize_text_field($_POST['phone'] ?? '');
    $company = sanitize_text_field($_POST['company'] ?? '');
    $industry = sanitize_text_field($_POST['industry'] ?? '');
    $employees = sanitize_text_field($_POST['employees'] ?? '');
    $subject = sanitize_text_field($_POST['subject'] ?? '');
    $message = sanitize_textarea_field($_POST['message'] ?? '');
    $contact_method = sanitize_text_field($_POST['contact_method'] ?? 'email');
    $contact_time = isset($_POST['contact_time']) ? array_map('sanitize_text_field', $_POST['contact_time']) : array();
    $privacy_agree = isset($_POST['privacy_agree']) ? true : false;
    
    // バリデーション
    if (empty($inquiry_type)) {
        $form_errors[] = 'お問い合わせ種別を選択してください。';
    }
    if (empty($name)) {
        $form_errors[] = 'お名前を入力してください。';
    }
    if (empty($email)) {
        $form_errors[] = 'メールアドレスを入力してください。';
    } elseif (!is_email($email)) {
        $form_errors[] = 'メールアドレスの形式が正しくありません。';
    }
    if (empty($subject)) {
        $form_errors[] = '件名を入力してください。';
    }
    if (empty($message)) {
        $form_errors[] = 'お問い合わせ内容を入力してください。';
    } elseif (mb_strlen($message) > 500) {
        $form_errors[] = 'お問い合わせ内容は500文字以内で入力してください。';
    }
    if (!$privacy_agree) {
        $form_errors[] = '個人情報の取り扱いに同意してください。';
    }
    
    // リダイレクト先URL
    $contact_page_id = get_page_by_path('contact');
    $contact_page_url = $contact_page_id ? get_permalink($contact_page_id->ID) : home_url('/contact/');
    
    // エラーがある場合はエラーメッセージと共にリダイレクト
    if (!empty($form_errors)) {
        $redirect_url = add_query_arg(array(
            'contact_error' => '1',
            'error_msg' => urlencode(implode('|', $form_errors))
        ), $contact_page_url);
        wp_redirect($redirect_url);
        exit;
    }
    
    // メール送信処理（page-contact.phpから移行）
    
    // お問い合わせ種別の変換
    $inquiry_types = array(
        'usage' => 'サイトの使い方について',
        'grant-info' => '補助金・助成金の制度について',
        'update' => '掲載情報の修正・更新',
        'media' => '媒体掲載・取材依頼',
        'technical' => '技術的な問題・不具合',
        'other' => 'その他'
    );
    $inquiry_type_label = $inquiry_types[$inquiry_type] ?? $inquiry_type;
    
    // 業種の変換
    $industries = array(
        'manufacturing' => '製造業',
        'retail' => '小売業',
        'service' => 'サービス業',
        'it' => 'IT・通信業',
        'construction' => '建設業',
        'transport' => '運輸業',
        'healthcare' => '医療・福祉',
        'education' => '教育・学習支援',
        'agriculture' => '農林水産業',
        'other' => 'その他'
    );
    $industry_label = !empty($industry) ? ($industries[$industry] ?? $industry) : '未記入';
    
    // 従業員数の変換
    $employees_options = array(
        '1' => '1人（個人事業主）',
        '2-5' => '2-5人',
        '6-20' => '6-20人',
        '21-50' => '21-50人',
        '51-100' => '51-100人',
        '101-300' => '101-300人',
        '301+' => '301人以上'
    );
    $employees_label = !empty($employees) ? ($employees_options[$employees] ?? $employees) : '未記入';
    
    // 連絡方法の変換
    $contact_methods = array(
        'email' => 'メール',
        'phone' => '電話',
        'either' => 'どちらでも可'
    );
    $contact_method_label = $contact_methods[$contact_method] ?? $contact_method;
    
    // 連絡時間帯の変換
    $contact_times = array(
        'morning' => '9:00-12:00',
        'afternoon' => '13:00-17:00',
        'evening' => '17:00-19:00',
        'anytime' => '時間指定なし'
    );
    $contact_time_labels = array();
    foreach ($contact_time as $time) {
        if (isset($contact_times[$time])) {
            $contact_time_labels[] = $contact_times[$time];
        }
    }
    $contact_time_text = !empty($contact_time_labels) ? implode('、', $contact_time_labels) : '指定なし';
    
    // 管理者宛メール本文
    $admin_message = "補助金インサイトへ新しいお問い合わせがありました。\n\n";
    $admin_message .= "【お問い合わせ情報】\n";
    $admin_message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    $admin_message .= "お問い合わせ種別: {$inquiry_type_label}\n";
    $admin_message .= "件名: {$subject}\n\n";
    $admin_message .= "【お客様情報】\n";
    $admin_message .= "お名前: {$name}\n";
    $admin_message .= "メールアドレス: {$email}\n";
    $admin_message .= "電話番号: " . (!empty($phone) ? $phone : '未記入') . "\n";
    $admin_message .= "会社名・団体名: " . (!empty($company) ? $company : '未記入') . "\n";
    $admin_message .= "業種: {$industry_label}\n";
    $admin_message .= "従業員数: {$employees_label}\n\n";
    $admin_message .= "【連絡希望】\n";
    $admin_message .= "連絡方法: {$contact_method_label}\n";
    $admin_message .= "連絡時間帯: {$contact_time_text}\n\n";
    $admin_message .= "【お問い合わせ内容】\n";
    $admin_message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $admin_message .= $message . "\n";
    $admin_message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    $admin_message .= "送信日時: " . current_time('Y年m月d日 H:i:s') . "\n";
    $admin_message .= "送信元IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "\n";
    
    // 管理者宛メールヘッダー
    $admin_headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: 補助金インサイト <noreply@' . $_SERVER['HTTP_HOST'] . '>',
        'Reply-To: ' . $name . ' <' . $email . '>'
    );
    
    // 管理者宛メール送信
    $admin_email = get_option('admin_email');
    $admin_sent = wp_mail(
        $admin_email,
        '[補助金インサイト] ' . $subject,
        $admin_message,
        $admin_headers
    );
    
    if ($admin_sent) {
        // 成功時のリダイレクト
        $redirect_url = add_query_arg('contact_sent', '1', $contact_page_url);
        wp_redirect($redirect_url);
        exit;
    } else {
        // メール送信失敗
        error_log('Contact form mail sending failed. Admin email: ' . $admin_email);
        $redirect_url = add_query_arg(array(
            'contact_error' => '1',
            'error_msg' => urlencode('メール送信に失敗しました。時間をおいて再度お試しください。')
        ), $contact_page_url);
        wp_redirect($redirect_url);
        exit;
    }
}

/**
 * 地域名を取得するヘルパー関数
 */
if (!function_exists('gi_get_region_name')) {
    function gi_get_region_name($region_slug) {
        $regions = array(
            'hokkaido' => '北海道',
            'tohoku' => '東北',
            'kanto' => '関東',
            'chubu' => '中部',
            'kinki' => '近畿',
            'chugoku' => '中国',
            'shikoku' => '四国',
            'kyushu' => '九州・沖縄'
        );
        
        return isset($regions[$region_slug]) ? $regions[$region_slug] : '';
    }
}

/**
 * スクリプトにdefer属性を追加（最適化版）
 */
if (!function_exists('gi_add_defer_attribute')) {
    function gi_add_defer_attribute($tag, $handle, $src) {
        // 管理画面では処理しない
        if (is_admin()) {
            return $tag;
        }
        
        // WordPressコアスクリプトは除外
        if (strpos($src, 'wp-includes/js/') !== false) {
            return $tag;
        }
        
        // 既にdefer/asyncがある場合はスキップ
        if (strpos($tag, 'defer') !== false || strpos($tag, 'async') !== false) {
            return $tag;
        }
        
        // 特定のハンドルにのみdeferを追加
        $defer_handles = array(
            'gi-main-js',
            'gi-frontend-js',
            'gi-mobile-enhanced'
        );
        
        if (in_array($handle, $defer_handles)) {
            return str_replace('<script ', '<script defer ', $tag);
        }
        
        return $tag;
    }
}

// フィルターの重複登録を防ぐ
remove_filter('script_loader_tag', 'gi_add_defer_attribute', 10);
add_filter('script_loader_tag', 'gi_add_defer_attribute', 10, 3);

/**
 * モバイル用AJAX - さらに読み込み
 */
function gi_ajax_load_more_grants() {
    check_ajax_referer('gi_ajax_nonce', 'nonce');
    
    $page = intval($_POST['page'] ?? 1);
    $posts_per_page = 10;
    
    $args = [
        'post_type' => 'grant',
        'posts_per_page' => $posts_per_page,
        'post_status' => 'publish',
        'paged' => $page,
        'orderby' => 'date',
        'order' => 'DESC'
    ];
    
    $query = new WP_Query($args);
    
    if (!$query->have_posts()) {
        wp_send_json_error('No more posts found');
    }
    
    ob_start();
    
    while ($query->have_posts()): $query->the_post();
        echo gi_render_card(get_the_ID(), 'mobile');
    endwhile;
    
    wp_reset_postdata();
    
    $html = ob_get_clean();
    
    wp_send_json_success([
        'html' => $html,
        'page' => $page,
        'max_pages' => $query->max_num_pages,
        'found_posts' => $query->found_posts
    ]);
}
add_action('wp_ajax_gi_load_more_grants', 'gi_ajax_load_more_grants');
add_action('wp_ajax_nopriv_gi_load_more_grants', 'gi_ajax_load_more_grants');

/**
 * テーマのアクティベーションチェック
 */
function gi_theme_activation_check() {
    // PHP バージョンチェック
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            echo 'Grant Insight テーマはPHP 7.4以上が必要です。現在のバージョン: ' . PHP_VERSION;
            echo '</p></div>';
        });
    }
    
    // WordPress バージョンチェック
    global $wp_version;
    if (version_compare($wp_version, '5.8', '<')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-warning"><p>';
            echo 'Grant Insight テーマはWordPress 5.8以上を推奨します。';
            echo '</p></div>';
        });
    }
    
    // 必須プラグインチェック（ACFなど）
    if (!class_exists('ACF') && is_admin()) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-info"><p>';
            echo 'Grant Insight テーマの全機能を利用するには、Advanced Custom Fields (ACF) プラグインのインストールを推奨します。';
            echo '</p></div>';
        });
    }
}
add_action('after_setup_theme', 'gi_theme_activation_check');

/**
 * エラーハンドリング用のグローバル関数
 */
if (!function_exists('gi_log_error')) {
    function gi_log_error($message, $context = array()) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $log_message = '[Grant Insight Error] ' . $message;
            if (!empty($context)) {
                $log_message .= ' | Context: ' . print_r($context, true);
            }
            error_log($log_message);
        }
    }
}

/**
 * 削除された機能のCronタスクを無効化
 */
add_action('init', function() {
    $deprecated_cron_hooks = array(
        'giji_auto_import_hook',        // J-Grants (削除済み)
        'gi_excel_auto_export_hook'     // Excel (削除済み)
    );
    
    foreach ($deprecated_cron_hooks as $hook) {
        wp_clear_scheduled_hook($hook);
    }
});

/**
 * テーマ設定のデフォルト値を取得
 */
if (!function_exists('gi_get_theme_option')) {
    function gi_get_theme_option($option_name, $default = null) {
        $theme_options = get_option('gi_theme_options', array());
        
        if (isset($theme_options[$option_name])) {
            return $theme_options[$option_name];
        }
        
        return $default;
    }
}

/**
 * テーマ設定を保存
 */
if (!function_exists('gi_update_theme_option')) {
    function gi_update_theme_option($option_name, $value) {
        $theme_options = get_option('gi_theme_options', array());
        $theme_options[$option_name] = $value;
        
        return update_option('gi_theme_options', $theme_options);
    }
}

/**
 * テーマのバージョンアップグレード処理 (デバッグ強化版)
 */
function gi_theme_version_upgrade() {
    $current_version = get_option('gi_installed_version', '0.0.0');
    
    // 必ずログ出力するデバッグ情報
    error_log('=== GRANT INSIGHT THEME UPGRADE DEBUG ===');
    error_log('Current version: ' . $current_version);
    error_log('Target version: ' . GI_THEME_VERSION);
    error_log('WP_DEBUG status: ' . (defined('WP_DEBUG') && WP_DEBUG ? 'ENABLED' : 'DISABLED'));
    error_log('Is admin: ' . (is_admin() ? 'YES' : 'NO'));
    
    if (version_compare($current_version, GI_THEME_VERSION, '<')) {
        error_log('✅ Version upgrade needed from ' . $current_version . ' to ' . GI_THEME_VERSION);
        
        // 9.0.0への統合アップグレード
        if (version_compare($current_version, '9.0.0', '<')) {
            error_log('🔧 Running 9.0.0 upgrade tasks');
            // キャッシュのクリア
            gi_theme_cleanup();
            // URLリライト更新
            flush_rewrite_rules();
            error_log('✅ 9.0.0 upgrade tasks completed');
        }
        
        // 9.1.0への市町村スラッグ統一アップグレード
        if (version_compare($current_version, '9.1.0', '<')) {
            error_log('🏙️ Starting municipality slugs standardization for v9.1.0');
            
            // 市町村ターム数をチェック
            $muni_terms = get_terms([
                'taxonomy' => 'grant_municipality',
                'hide_empty' => false,
                'number' => 0
            ]);
            
            $muni_count = is_wp_error($muni_terms) ? 0 : count($muni_terms);
            error_log('Municipality terms found: ' . $muni_count);
            
            if ($muni_count > 0) {
                // 統一処理を実行
                $result = gi_standardize_municipality_slugs();
                error_log('Standardization result: ' . ($result !== false ? $result . ' terms processed' : 'FAILED'));
            } else {
                error_log('⚠️ No municipality terms found, skipping standardization');
            }
            
            error_log('✅ Municipality slugs standardization completed for v9.1.0');
        } else {
            error_log('💡 v9.1.0 upgrade already completed, current: ' . $current_version);
        }
        
        // バージョン更新
        update_option('gi_installed_version', GI_THEME_VERSION);
        error_log('✅ Version updated to: ' . GI_THEME_VERSION);
        
        // アップグレード完了通知
        if (is_admin()) {
            add_action('admin_notices', function() use ($current_version) {
                echo '<div class="notice notice-success is-dismissible"><p>';
                echo 'Grant Insight テーマが v' . GI_THEME_VERSION . ' (Municipality Slug Standardization Edition) にアップグレードされました。';
                if (version_compare($current_version, '9.1.0', '<')) {
                    echo '<br>✅ 市町村スラッグの統一処理も完了しました。';
                    
                    // 統一結果を表示
                    $stats = get_option('gi_municipality_standardization_result', []);
                    if (!empty($stats)) {
                        echo '<br>📊 結果: ' . ($stats['total_processed'] ?? 0) . '件処理, ' . ($stats['standardized_count'] ?? 0) . '件更新';
                    }
                }
                echo '</p></div>';
            });
        }
        
        // アップグレード前のバージョンを記録
        update_option('gi_previous_version', $current_version);
        
        error_log('=== THEME UPGRADE COMPLETED ===');
    } else {
        error_log('💡 No upgrade needed, current version ' . $current_version . ' >= ' . GI_THEME_VERSION);
    }
}
add_action('init', 'gi_theme_version_upgrade');

/**
 * 市町村スラッグの統一処理
 * テーマインストール/アップグレード時に実行される関数
 */
function gi_standardize_municipality_slugs() {
    try {
        // 常に詳細ログを出力（WP_DEBUGに関係なく）
        error_log('=== MUNICIPALITY SLUGS STANDARDIZATION STARTED ===');
        error_log('Function called at: ' . current_time('Y-m-d H:i:s'));
        error_log('WP_DEBUG: ' . (defined('WP_DEBUG') && WP_DEBUG ? 'ON' : 'OFF'));
        
        // すべての市町村タームを取得
        $municipality_terms = get_terms([
            'taxonomy' => 'grant_municipality',
            'hide_empty' => false,
            'number' => 0 // 全件取得
        ]);
        
        if (is_wp_error($municipality_terms)) {
            error_log('❌ Error getting municipality terms: ' . $municipality_terms->get_error_message());
            return false;
        }
        
        error_log('✅ Found ' . count($municipality_terms) . ' municipality terms to process');
        
        $standardized_count = 0;
        $error_count = 0;
        $processed_slugs = [];
        
        foreach ($municipality_terms as $index => $term) {
            error_log("🔄 Processing term #{$index}: {$term->name} (ID: {$term->term_id})");
            
            // 既存スラッグを解析
            $current_slug = $term->slug;
            $term_name = $term->name;
            
            error_log("   Current slug: {$current_slug}");
            
            // 都道府県の情報を取得
            $prefecture_slug = get_term_meta($term->term_id, 'prefecture_slug', true);
            error_log("   Prefecture meta: " . ($prefecture_slug ?: 'NOT SET'));
            
            if (empty($prefecture_slug)) {
                // スラッグから都道府県を推定
                $prefecture_slug = gi_extract_prefecture_from_slug($current_slug);
                error_log("   Extracted prefecture: " . ($prefecture_slug ?: 'FAILED'));
            }
            
            // まだ都道府県が特定できない場合、市町村名から推定
            if (empty($prefecture_slug)) {
                $prefecture_slug = gi_guess_prefecture_from_municipality_name($term_name);
                error_log("   Prefecture from name guess: " . ($prefecture_slug ?: 'FAILED'));
            }
            
            if (empty($prefecture_slug)) {
                error_log("❌ Cannot determine prefecture for municipality: {$term_name} (slug: {$current_slug})");
                $error_count++;
                continue;
            }
            
            // 正しいスラッグを生成
            $correct_slug = gi_generate_municipality_slug($prefecture_slug, $term_name);
            error_log("   Correct slug should be: {$correct_slug}");
            
            // スラッグが異なる場合のみ更新
            if ($current_slug !== $correct_slug) {
                error_log("   🔄 NEEDS UPDATE: {$current_slug} → {$correct_slug}");
                // 重複チェック
                if (in_array($correct_slug, $processed_slugs) || term_exists($correct_slug, 'grant_municipality')) {
                    error_log("Duplicate slug detected: {$correct_slug} for term {$term_name}");
                    $error_count++;
                    continue;
                }
                
                // スラッグを更新
                $result = wp_update_term($term->term_id, 'grant_municipality', [
                    'slug' => $correct_slug
                ]);
                
                if (is_wp_error($result)) {
                    error_log("Failed to update slug for {$term_name}: " . $result->get_error_message());
                    $error_count++;
                } else {
                    // メタデータを更新
                    update_term_meta($term->term_id, 'prefecture_slug', $prefecture_slug);
                    update_term_meta($term->term_id, 'original_slug', $current_slug);
                    update_term_meta($term->term_id, 'standardized_date', current_time('mysql'));
                    
                    $processed_slugs[] = $correct_slug;
                    $standardized_count++;
                    
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log("Standardized: {$current_slug} -> {$correct_slug} ({$term_name})");
                    }
                }
            } else {
                // スラッグが正しい場合もメタデータを更新
                update_term_meta($term->term_id, 'prefecture_slug', $prefecture_slug);
                $processed_slugs[] = $current_slug;
            }
        }
        
        // 結果をログで記録（常に出力）
        error_log("=== MUNICIPALITY SLUGS STANDARDIZATION COMPLETED ===");
        error_log("📊 Statistics: {$standardized_count} updated, {$error_count} errors, " . count($municipality_terms) . " total processed");
        error_log("⏰ Completed at: " . current_time('Y-m-d H:i:s'));
        
        // 結果をオプションに保存
        update_option('gi_municipality_standardization_result', [
            'standardized_count' => $standardized_count,
            'error_count' => $error_count,
            'total_processed' => count($municipality_terms),
            'date' => current_time('mysql')
        ]);
        
        return $standardized_count;
        
    } catch (Exception $e) {
        error_log('Municipality standardization error: ' . $e->getMessage());
        return false;
    }
}

/**
 * 都道府県スラッグから正しい市町村スラッグを生成
 */
function gi_generate_municipality_slug($prefecture_slug, $municipality_name) {
    // 正規化ルール: {prefecture_slug}-{sanitized_municipality_name}
    $clean_name = sanitize_title($municipality_name);
    return $prefecture_slug . '-' . $clean_name;
}

/**
 * スラッグから都道府県を推定（URL-encoded対応版）
 */
function gi_extract_prefecture_from_slug($slug) {
    // URL-encodedの場合はデコード
    if (strpos($slug, '%') !== false) {
        $decoded_slug = urldecode($slug);
        error_log("   Decoded slug: {$slug} → {$decoded_slug}");
        $slug = $decoded_slug;
    }
    
    // 標準都道府県リストからマッチを探す
    $prefectures = gi_get_all_prefectures();
    
    foreach ($prefectures as $pref) {
        // スラッグが都道府県スラッグで始まるかチェック
        if (strpos($slug, $pref['slug'] . '-') === 0) {
            return $pref['slug'];
        }
    }
    
    // フォールバック1: 最初のハイフンまでを都道府県と推定
    $parts = explode('-', $slug);
    if (count($parts) >= 2) {
        $potential_pref = $parts[0];
        // 都道府県リストに存在するかチェック
        foreach ($prefectures as $pref) {
            if ($pref['slug'] === $potential_pref) {
                return $potential_pref;
            }
        }
    }
    
    // フォールバック2: 市町村名から都道府県を推定
    $municipality_name = sanitize_title($slug);
    $prefecture_slug = gi_guess_prefecture_from_municipality_name($municipality_name);
    if (!empty($prefecture_slug)) {
        error_log("   Prefecture guessed from municipality name: {$prefecture_slug}");
        return $prefecture_slug;
    }
    
    return '';
}

/**
 * 管理画面に市町村スラッグ統一のデバッグ情報とボタンを表示
 */
add_action('admin_notices', 'gi_municipality_slug_admin_notices');
function gi_municipality_slug_admin_notices() {
    // 管理者のみ表示
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // デバッグ情報を表示
    $current_version = get_option('gi_installed_version', '0.0.0');
    $target_version = defined('GI_THEME_VERSION') ? GI_THEME_VERSION : 'UNDEFINED';
    
    echo '<div class="notice notice-info">';
    echo '<h3>🔧 Grant Insight Municipality Debug Info</h3>';
    echo '<p><strong>Current Version:</strong> ' . $current_version . ' | <strong>Target:</strong> ' . $target_version . '</p>';
    
    // 市町村統計
    $muni_terms = get_terms(['taxonomy' => 'grant_municipality', 'hide_empty' => false, 'number' => 0]);
    $muni_count = is_wp_error($muni_terms) ? 'ERROR' : count($muni_terms);
    echo '<p><strong>Municipality Terms:</strong> ' . $muni_count . '</p>';
    
    // 統一結果
    $stats = get_option('gi_municipality_standardization_result', []);
    if (!empty($stats)) {
        echo '<p><strong>Last Standardization:</strong> ' . ($stats['date'] ?? 'Unknown') . 
             ' - Processed: ' . ($stats['total_processed'] ?? 0) . 
             ', Updated: ' . ($stats['standardized_count'] ?? 0) . 
             ', Errors: ' . ($stats['error_count'] ?? 0) . '</p>';
    }
    
    // 手動実行ボタン
    echo '<p>';
    echo '<button type="button" class="button button-primary" onclick="gi_run_slug_standardization()">🔄 Run Municipality Slug Standardization</button> ';
    echo '<button type="button" class="button button-secondary" onclick="gi_check_slug_issues()">🔍 Check Slug Issues</button> ';
    echo '<button type="button" class="button button-secondary" onclick="gi_force_theme_upgrade()">⚡ Force Theme Upgrade</button>';
    echo '</p>';
    
    echo '<div id="gi-standardization-result" style="margin-top: 10px;"></div>';
    
    // JavaScript for AJAX calls
    echo '<script>
    function gi_run_slug_standardization() {
        const resultDiv = document.getElementById("gi-standardization-result");
        resultDiv.innerHTML = "<p>🔄 Running standardization...</p>";
        
        fetch(ajaxurl, {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: new URLSearchParams({
                action: "gi_standardize_municipality_slugs",
                _wpnonce: "' . wp_create_nonce('gi_standardize_slugs_nonce') . '"
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = `<div class="notice notice-success"><p>✅ ${data.data.message}<br>📊 Processed: ${data.data.total_processed || 0}, Updated: ${data.data.standardized_count || 0}, Errors: ${data.data.error_count || 0}</p></div>`;
            } else {
                resultDiv.innerHTML = `<div class="notice notice-error"><p>❌ ${data.data.message}</p></div>`;
            }
        })
        .catch(error => {
            resultDiv.innerHTML = `<div class="notice notice-error"><p>❌ Error: ${error}</p></div>`;
        });
    }
    
    function gi_force_theme_upgrade() {
        const resultDiv = document.getElementById("gi-standardization-result");
        resultDiv.innerHTML = "<p>⚡ Forcing theme upgrade...</p>";
        
        fetch(ajaxurl, {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: new URLSearchParams({
                action: "gi_force_theme_upgrade",
                _wpnonce: "' . wp_create_nonce('gi_force_upgrade_nonce') . '"
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = `<div class="notice notice-success"><p>✅ ${data.data.message}</p></div>`;
                setTimeout(() => location.reload(), 2000);
            } else {
                resultDiv.innerHTML = `<div class="notice notice-error"><p>❌ ${data.data.message}</p></div>`;
            }
        });
    }
    
    function gi_check_slug_issues() {
        const resultDiv = document.getElementById("gi-standardization-result");
        resultDiv.innerHTML = "<p>🔍 Checking slug issues...</p>";
        
        fetch(ajaxurl, {
            method: "POST", 
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: new URLSearchParams({
                action: "gi_check_municipality_slugs"
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const issues = data.data.issues || [];
                let html = `<div class="notice notice-info"><p>📊 Total: ${data.data.total_terms}, Correct: ${data.data.correct_count}, Issues: ${data.data.issues_count}</p>`;
                
                if (issues.length > 0) {
                    html += "<h4>Issues Found (first 10):</h4><ul>";
                    issues.forEach(issue => {
                        html += `<li><strong>${issue.name}</strong>: ${issue.current_slug} → ${issue.correct_slug}</li>`;
                    });
                    html += "</ul>";
                }
                html += "</div>";
                resultDiv.innerHTML = html;
            } else {
                resultDiv.innerHTML = `<div class="notice notice-error"><p>❌ ${data.data.message}</p></div>`;
            }
        });
    }
    </script>';
    echo '</div>';
}

// フラグリセット用のフック
add_action('gi_reset_result_flag', function() {
    delete_option('gi_standardization_result_shown');
});

/**
 * 市町村スラッグの問題をチェックするAJAX機能
 */
add_action('wp_ajax_gi_check_municipality_slugs', 'gi_ajax_check_municipality_slugs');
function gi_ajax_check_municipality_slugs() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => '権限が不足しています']);
        return;
    }
    
    $municipality_terms = get_terms([
        'taxonomy' => 'grant_municipality',
        'hide_empty' => false,
        'number' => 0
    ]);
    
    if (is_wp_error($municipality_terms)) {
        wp_send_json_error(['message' => 'データ取得エラー: ' . $municipality_terms->get_error_message()]);
        return;
    }
    
    $issues = [];
    $correct_count = 0;
    
    foreach ($municipality_terms as $term) {
        $current_slug = $term->slug;
        $term_name = $term->name;
        
        $prefecture_slug = get_term_meta($term->term_id, 'prefecture_slug', true);
        if (empty($prefecture_slug)) {
            $prefecture_slug = gi_extract_prefecture_from_slug($current_slug);
        }
        
        if (!empty($prefecture_slug)) {
            $correct_slug = gi_generate_municipality_slug($prefecture_slug, $term_name);
            
            if ($current_slug !== $correct_slug) {
                $issues[] = [
                    'name' => $term_name,
                    'current_slug' => $current_slug,
                    'correct_slug' => $correct_slug,
                    'prefecture' => $prefecture_slug
                ];
            } else {
                $correct_count++;
            }
        } else {
            $issues[] = [
                'name' => $term_name,
                'current_slug' => $current_slug,
                'correct_slug' => 'ERROR: 都道府県特定不可',
                'prefecture' => 'unknown'
            ];
        }
    }
    
    wp_send_json_success([
        'total_terms' => count($municipality_terms),
        'correct_count' => $correct_count,
        'issues_count' => count($issues),
        'issues' => array_slice($issues, 0, 10) // 最初の10件のみ表示
    ]);
}

/**
 * 強制テーマアップグレードAJAX機能
 */
add_action('wp_ajax_gi_force_theme_upgrade', 'gi_ajax_force_theme_upgrade');
function gi_ajax_force_theme_upgrade() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => '権限が不足しています']);
        return;
    }
    
    if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'gi_force_upgrade_nonce')) {
        wp_send_json_error(['message' => 'セキュリティチェックに失敗しました']);
        return;
    }
    
    try {
        // バージョンをリセットして強制アップグレード
        $old_version = get_option('gi_installed_version', '0.0.0');
        update_option('gi_installed_version', '9.0.0'); // 9.1.0より低くしてアップグレードをトリガー
        
        error_log('🔄 FORCE UPGRADE: Version reset from ' . $old_version . ' to 9.0.0, target: ' . GI_THEME_VERSION);
        
        // アップグレード実行
        gi_theme_version_upgrade();
        
        wp_send_json_success([
            'message' => 'テーマアップグレードを強制実行しました。バージョン: ' . $old_version . ' → ' . GI_THEME_VERSION
        ]);
        
    } catch (Exception $e) {
        wp_send_json_error(['message' => 'エラー: ' . $e->getMessage()]);
    }
}

/**
 * 手動で市町村スラッグ統一を実行するAJAX機能
 */
add_action('wp_ajax_gi_standardize_municipality_slugs', 'gi_ajax_standardize_municipality_slugs');
function gi_ajax_standardize_municipality_slugs() {
    // 権限チェック
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => '権限が不足しています']);
        return;
    }
    
    // Nonce検証
    if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'gi_standardize_slugs_nonce')) {
        wp_send_json_error(['message' => 'セキュリティチェックに失敗しました']);
        return;
    }
    
    try {
        $result = gi_standardize_municipality_slugs();
        
        if ($result !== false) {
            $stats = get_option('gi_municipality_standardization_result', []);
            wp_send_json_success([
                'message' => '市町村スラッグの統一が完了しました',
                'standardized_count' => $stats['standardized_count'] ?? 0,
                'error_count' => $stats['error_count'] ?? 0,
                'total_processed' => $stats['total_processed'] ?? 0
            ]);
        } else {
            wp_send_json_error(['message' => '統一処理中にエラーが発生しました']);
        }
    } catch (Exception $e) {
        wp_send_json_error(['message' => 'エラー: ' . $e->getMessage()]);
    }
}

/**
 * データベーステーブル作成
 */
function gi_create_database_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // AI検索履歴テーブル
    $search_history_table = $wpdb->prefix . 'gi_search_history';
    $sql1 = "CREATE TABLE IF NOT EXISTS $search_history_table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        session_id varchar(255) NOT NULL,
        user_id bigint(20) unsigned DEFAULT NULL,
        search_query text NOT NULL,
        search_filter varchar(50) DEFAULT NULL,
        results_count int(11) DEFAULT 0,
        clicked_results text DEFAULT NULL,
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY session_id (session_id),
        KEY user_id (user_id),
        KEY created_at (created_at)
    ) $charset_collate;";
    
    // ユーザー設定テーブル
    $user_preferences_table = $wpdb->prefix . 'gi_user_preferences';
    $sql2 = "CREATE TABLE IF NOT EXISTS $user_preferences_table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        user_id bigint(20) unsigned NOT NULL,
        preference_key varchar(100) NOT NULL,
        preference_value text DEFAULT NULL,
        updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY user_preference (user_id, preference_key)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql1);
    dbDelta($sql2);
    
    // バージョン管理
    update_option('gi_db_version', '1.0.0');
}

// テーマ有効化時にテーブル作成と市町村スラッグ統一
add_action('after_switch_theme', 'gi_create_database_tables');
add_action('after_switch_theme', 'gi_standardize_municipality_slugs_on_activation');

/**
 * テーマアクティベーション時の市町村スラッグ統一
 */
function gi_standardize_municipality_slugs_on_activation() {
    // アクティベーション時の統一処理をスケジュール（重い処理なので遅延実行）
    wp_schedule_single_event(time() + 10, 'gi_standardize_slugs_hook');
    
    // 管理画面通知を設定
    update_option('gi_slug_standardization_pending', true);
}

// スケジュールされた処理のフック
add_action('gi_standardize_slugs_hook', 'gi_standardize_municipality_slugs');

// 既存のインストールでもテーブル作成を確認
add_action('init', function() {
    $db_version = get_option('gi_db_version', '0');
    if (version_compare($db_version, '1.0.0', '<')) {
        gi_create_database_tables();
    }
});

// デバッグ情報の出力
if (defined('WP_DEBUG') && WP_DEBUG) {
    add_action('admin_footer', function() use ($required_files) {
        echo '<!-- Grant Insight: Consolidated version v' . GI_THEME_VERSION . ' loaded successfully -->';
        echo '<!-- Files loaded: ' . (is_array($required_files) ? count($required_files) : 0) . ' -->';
    });
}

/**
 * =============================================================================
 * カテゴリーアーカイブ用AJAX フィルター機能
 * =============================================================================
 */

/**
 * NOTE: Category AJAX functions have been moved to inc/ajax-functions.php
 * to avoid duplication and centralize AJAX handling
 */

/**
 * =============================================================================
 * お問い合わせフォーム処理
 * =============================================================================
 */

// 重複した関数定義を削除（admin_post方式を使用するため）
/**
 * 都道府県タームを持つ投稿に、自動的に市町村タームも追加（強化版）
 * 地域制限タイプに応じて適切な市町村タームを設定
 */
add_action('save_post_grant', 'gi_sync_prefecture_to_municipality', 20, 3);
function gi_sync_prefecture_to_municipality($post_id, $post, $update) {
    // 自動保存、リビジョン、自動下書きをスキップ
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }
    
    // 都道府県タームを取得
    $prefectures = wp_get_post_terms($post_id, 'grant_prefecture', ['fields' => 'all']);
    
    if (!empty($prefectures) && !is_wp_error($prefectures)) {
        // 地域制限タイプを確認
        $regional_limitation = get_post_meta($post_id, 'regional_limitation', true);
        
        $municipality_term_ids = [];
        
        foreach ($prefectures as $prefecture) {
            if ($regional_limitation === 'prefecture_only' || empty($regional_limitation) || $regional_limitation === 'nationwide') {
                // 都道府県レベルの助成金：都道府県名の市町村タームを自動設定
                $pref_level_slug = $prefecture->slug . '-prefecture-level';
                $pref_muni_term = get_term_by('slug', $pref_level_slug, 'grant_municipality');
                
                if (!$pref_muni_term) {
                    // 都道府県レベルの市町村タームを作成
                    $result = wp_insert_term(
                        $prefecture->name,
                        'grant_municipality',
                        [
                            'slug' => $pref_level_slug,
                            'description' => $prefecture->name . '全域対象の助成金'
                        ]
                    );
                    
                    if (!is_wp_error($result)) {
                        $municipality_term_ids[] = $result['term_id'];
                        // メタデータを設定
                        add_term_meta($result['term_id'], 'prefecture_slug', $prefecture->slug);
                        add_term_meta($result['term_id'], 'prefecture_name', $prefecture->name);
                        add_term_meta($result['term_id'], 'is_prefecture_level', '1');
                    }
                } else {
                    $municipality_term_ids[] = $pref_muni_term->term_id;
                    // メタデータがなければ設定
                    if (!get_term_meta($pref_muni_term->term_id, 'prefecture_slug', true)) {
                        add_term_meta($pref_muni_term->term_id, 'prefecture_slug', $prefecture->slug);
                        add_term_meta($pref_muni_term->term_id, 'prefecture_name', $prefecture->name);
                        add_term_meta($pref_muni_term->term_id, 'is_prefecture_level', '1');
                    }
                }
                
                // この都道府県の市町村データが未初期化なら初期化
                gi_ensure_municipalities_for_prefecture($prefecture->slug, $prefecture->name);
                
            } elseif ($regional_limitation === 'municipality_only') {
                // 市町村レベルの助成金：手動選択された市町村のみ保持
                // 自動では何もしない（手動選択を尊重）
                continue;
            }
        }
        
        // 既存の市町村タームとマージ（municipality_onlyの場合は手動選択を保持）
        if (!empty($municipality_term_ids)) {
            $existing_munis = wp_get_post_terms($post_id, 'grant_municipality', ['fields' => 'ids']);
            if (!is_wp_error($existing_munis)) {
                if ($regional_limitation === 'municipality_only') {
                    // 市町村限定の場合、既存の手動選択を優先
                    $manual_munis = array_filter($existing_munis, function($term_id) {
                        $term = get_term($term_id, 'grant_municipality');
                        return $term && !empty($term->parent); // 親がある＝実際の市町村
                    });
                    
                    if (!empty($manual_munis)) {
                        // 手動選択がある場合はそれを優先
                        wp_set_post_terms($post_id, $manual_munis, 'grant_municipality', false);
                    } else {
                        // 手動選択がない場合は都道府県レベルを設定
                        $all_muni_ids = array_unique(array_merge($existing_munis, $municipality_term_ids));
                        wp_set_post_terms($post_id, $all_muni_ids, 'grant_municipality', false);
                    }
                } else {
                    // 都道府県レベルの場合は自動設定とマージ
                    $all_muni_ids = array_unique(array_merge($existing_munis, $municipality_term_ids));
                    wp_set_post_terms($post_id, $all_muni_ids, 'grant_municipality', false);
                }
            } else {
                // 既存がない場合は新規のみセット
                wp_set_post_terms($post_id, $municipality_term_ids, 'grant_municipality', false);
            }
        }
    }
}

/**
 * 指定都道府県の市町村データを確実に初期化
 */
function gi_ensure_municipalities_for_prefecture($prefecture_slug, $prefecture_name) {
    $option_key = 'gi_municipalities_init_' . $prefecture_slug;
    $initialized = get_option($option_key, false);
    
    if (!$initialized) {
        gi_init_municipalities_for_prefecture($prefecture_slug, $prefecture_name);
        update_option($option_key, true);
    }
}

/**
 * 既存の投稿全てに対して都道府県→市町村の同期を実行（一度だけ実行）
 */
add_action('admin_init', 'gi_sync_all_prefecture_to_municipality_once');
function gi_sync_all_prefecture_to_municipality_once() {
    $sync_done = get_option('gi_prefecture_municipality_sync_done', false);
    
    if (!$sync_done) {
        // 全ての助成金投稿を取得
        $grants = get_posts([
            'post_type' => 'grant',
            'posts_per_page' => -1,
            'post_status' => 'any',
            'fields' => 'ids'
        ]);
        
        foreach ($grants as $grant_id) {
            gi_sync_prefecture_to_municipality($grant_id, get_post($grant_id), true);
        }
        
        // 完了フラグを保存
        update_option('gi_prefecture_municipality_sync_done', true);
    }
}

/**
 * 市町村データ初期化を強制実行する関数
 */
function gi_force_initialize_municipalities() {
    // 既存のすべての市町村データを削除
    $existing_municipalities = get_terms([
        'taxonomy' => 'grant_municipality',
        'hide_empty' => false,
        'fields' => 'ids'
    ]);
    
    if (!is_wp_error($existing_municipalities)) {
        foreach ($existing_municipalities as $term_id) {
            wp_delete_term($term_id, 'grant_municipality');
        }
    }
    
    // 市町村データを再初期化
    if (function_exists('gi_initialize_all_municipalities')) {
        return gi_initialize_all_municipalities();
    }
    
    return ['success' => false, 'message' => 'gi_initialize_all_municipalities関数が見つかりません'];
}

/**
 * 市町村データ強制初期化AJAXハンドラー（スラッグ統一機能付き）
 */
add_action('wp_ajax_gi_force_initialize_municipalities', 'gi_ajax_force_initialize_municipalities');
function gi_ajax_force_initialize_municipalities() {
    if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'gi_municipality_init_nonce') && !current_user_can('manage_options')) {
        wp_send_json_error(['message' => '権限が不足しています']);
        return;
    }
    
    try {
        // 1. 既存の市町村データを初期化
        $result = gi_force_initialize_municipalities();
        
        // 2. スラッグを統一
        $standardize_result = gi_standardize_municipality_slugs();
        
        if ($result['success']) {
            wp_send_json_success([
                'message' => '市町村データの強制初期化とスラッグ統一が完了しました',
                'initialization_result' => $result,
                'standardization_count' => $standardize_result ?: 0
            ]);
        } else {
            wp_send_json_error([
                'message' => '初期化に失敗しました: ' . $result['message']
            ]);
        }
    } catch (Exception $e) {
        wp_send_json_error([
            'message' => 'エラーが発生しました: ' . $e->getMessage()
        ]);
    }
}

/**
 * 都道府県レベル助成金の市町村を一括修正するAJAX関数
 */
add_action('wp_ajax_gi_bulk_fix_prefecture_municipalities', 'gi_ajax_bulk_fix_prefecture_municipalities');
function gi_ajax_bulk_fix_prefecture_municipalities() {
    // nonce確認
    if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'gi_bulk_fix_nonce')) {
        wp_send_json_error(['message' => 'セキュリティチェックに失敗しました']);
        return;
    }
    
    // 管理者権限チェック
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => '権限が不足しています']);
        return;
    }
    
    try {
        error_log('Bulk Fix Prefecture Municipalities: Starting process');
        
        // 1. 都道府県・市町村データの初期化
        if (function_exists('gi_initialize_all_municipalities')) {
            error_log('Bulk Fix: Calling gi_initialize_all_municipalities');
            $init_result = gi_initialize_all_municipalities();
            error_log('Bulk Fix: Initialization result: ' . json_encode($init_result));
        } else {
            error_log('Bulk Fix: gi_initialize_all_municipalities function not found');
            $init_result = ['success' => false, 'message' => '初期化関数が見つかりません'];
        }
        
        // 2. 都道府県レベル助成金を取得
        error_log('Bulk Fix: Searching for prefecture-level grants');
        $grants_query = new WP_Query([
            'post_type' => 'grant',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => 'regional_limitation',
                    'value' => ['prefecture_only', 'nationwide', ''],
                    'compare' => 'IN'
                ]
            ]
        ]);
        
        error_log('Bulk Fix: Found ' . $grants_query->found_posts . ' prefecture-level grants');
        
        $fixed_count = 0;
        $error_count = 0;
        $details = [];
        
        if ($grants_query->have_posts()) {
            while ($grants_query->have_posts()) {
                $grants_query->the_post();
                $post_id = get_the_ID();
                $post_title = get_the_title();
                
                // 都道府県を取得
                $prefectures = wp_get_post_terms($post_id, 'grant_prefecture');
                if (!empty($prefectures) && !is_wp_error($prefectures)) {
                    $municipality_term_ids = [];
                    
                    foreach ($prefectures as $prefecture) {
                        // 都道府県レベルの市町村タームを取得または作成
                        $pref_level_slug = $prefecture->slug . '-prefecture-level';
                        $pref_muni_term = get_term_by('slug', $pref_level_slug, 'grant_municipality');
                        
                        if (!$pref_muni_term) {
                            // 都道府県レベルの市町村タームを作成
                            $result = wp_insert_term(
                                $prefecture->name,
                                'grant_municipality',
                                [
                                    'slug' => $pref_level_slug,
                                    'description' => $prefecture->name . '全域対象の助成金'
                                ]
                            );
                            
                            if (!is_wp_error($result)) {
                                $municipality_term_ids[] = $result['term_id'];
                                // メタデータ設定
                                add_term_meta($result['term_id'], 'prefecture_slug', $prefecture->slug);
                                add_term_meta($result['term_id'], 'prefecture_name', $prefecture->name);
                                add_term_meta($result['term_id'], 'is_prefecture_level', '1');
                            }
                        } else {
                            $municipality_term_ids[] = $pref_muni_term->term_id;
                            // メタデータがなければ追加
                            if (!get_term_meta($pref_muni_term->term_id, 'prefecture_slug', true)) {
                                add_term_meta($pref_muni_term->term_id, 'prefecture_slug', $prefecture->slug);
                                add_term_meta($pref_muni_term->term_id, 'prefecture_name', $prefecture->name);
                                add_term_meta($pref_muni_term->term_id, 'is_prefecture_level', '1');
                            }
                        }
                    }
                    
                    // 市町村を設定
                    if (!empty($municipality_term_ids)) {
                        $existing_munis = wp_get_post_terms($post_id, 'grant_municipality', ['fields' => 'ids']);
                        if (!is_wp_error($existing_munis)) {
                            $all_muni_ids = array_unique(array_merge($existing_munis, $municipality_term_ids));
                            wp_set_post_terms($post_id, $all_muni_ids, 'grant_municipality', false);
                            $fixed_count++;
                            $details[] = "✅ {$post_title} - 市町村設定完了";
                        } else {
                            wp_set_post_terms($post_id, $municipality_term_ids, 'grant_municipality', false);
                            $fixed_count++;
                            $details[] = "✅ {$post_title} - 市町村新規設定";
                        }
                    } else {
                        $error_count++;
                        $details[] = "❌ {$post_title} - 市町村タームの作成に失敗";
                    }
                } else {
                    $error_count++;
                    $details[] = "❌ {$post_title} - 都道府県タームが見つかりません";
                }
            }
            wp_reset_postdata();
        }
        
        error_log("Bulk Fix: Completed - Fixed: {$fixed_count}, Errors: {$error_count}");
        
        wp_send_json_success([
            'message' => "一括修正完了: 修正 {$fixed_count} 件, エラー {$error_count} 件",
            'fixed_count' => $fixed_count,
            'error_count' => $error_count,
            'initialization' => $init_result,
            'details' => array_slice($details, 0, 10) // 最初の10件のみ
        ]);
        
    } catch (Exception $e) {
        wp_send_json_error([
            'message' => '一括修正中にエラーが発生しました: ' . $e->getMessage()
        ]);
    }
}


/**
 * 市町村名から都道府県を推定する包括的な関数
 */
function gi_guess_prefecture_from_municipality_name($municipality_name) {
    // 包括的な市町村→都道府県マッピング（全1741市区町村対応版）
    $municipality_prefecture_map = array(
        // === 北海道 ===
        'えりも' => 'hokkaido', '旭川' => 'hokkaido', '芦別' => 'hokkaido', '網走' => 'hokkaido',
        '石狩' => 'hokkaido', '岩見沢' => 'hokkaido', '歌志内' => 'hokkaido', '恵庭' => 'hokkaido',
        '江別' => 'hokkaido', '小樽' => 'hokkaido', '帯広' => 'hokkaido', '北広島' => 'hokkaido',
        '北見' => 'hokkaido', '釧路' => 'hokkaido', '札幌' => 'hokkaido', '士別' => 'hokkaido',
        '砂川' => 'hokkaido', '滝川' => 'hokkaido', '伊達' => 'hokkaido', '千歳' => 'hokkaido',
        '苫小牧' => 'hokkaido', '名寄' => 'hokkaido', '根室' => 'hokkaido', '登別' => 'hokkaido',
        '函館' => 'hokkaido', '美唄' => 'hokkaido', '富良野' => 'hokkaido', '北斗' => 'hokkaido',
        '紋別' => 'hokkaido', '夕張' => 'hokkaido', '留萌' => 'hokkaido', '稚内' => 'hokkaido',
        '音更' => 'hokkaido', '鹿追' => 'hokkaido', '上士幌' => 'hokkaido', '士幌' => 'hokkaido',
        '新得' => 'hokkaido', '清水' => 'hokkaido', '芽室' => 'hokkaido', '中札内' => 'hokkaido',
        '更別' => 'hokkaido', '大樹' => 'hokkaido', '広尾' => 'hokkaido', '幕別' => 'hokkaido',
        '池田' => 'hokkaido', '豊頃' => 'hokkaido', '本別' => 'hokkaido', '足寄' => 'hokkaido',
        '陸別' => 'hokkaido', '浦幌' => 'hokkaido', '釧路町' => 'hokkaido', '厚岸' => 'hokkaido',
        '浜中' => 'hokkaido', '標茶' => 'hokkaido', '弟子屈' => 'hokkaido', '鶴居' => 'hokkaido',
        '白糠' => 'hokkaido', '別海' => 'hokkaido', '中標津' => 'hokkaido', '標津' => 'hokkaido',
        '羅臼' => 'hokkaido', '森' => 'hokkaido', '八雲' => 'hokkaido', '長万部' => 'hokkaido',
        '江差' => 'hokkaido', '上ノ国' => 'hokkaido', '厚沢部' => 'hokkaido', '乙部' => 'hokkaido',
        '奥尻' => 'hokkaido', '今金' => 'hokkaido', 'せたな' => 'hokkaido', '島牧' => 'hokkaido',
        '寿都' => 'hokkaido', '黒松内' => 'hokkaido', '蘭越' => 'hokkaido', 'ニセコ' => 'hokkaido',
        '真狩' => 'hokkaido', '留寿都' => 'hokkaido', '喜茂別' => 'hokkaido', '京極' => 'hokkaido',
        '倶知安' => 'hokkaido', '共和' => 'hokkaido', '岩内' => 'hokkaido', '泊' => 'hokkaido',
        '神恵内' => 'hokkaido', '積丹' => 'hokkaido', '古平' => 'hokkaido', '仁木' => 'hokkaido',
        '余市' => 'hokkaido', '赤井川' => 'hokkaido', '南幌' => 'hokkaido', '奈井江' => 'hokkaido',
        '上砂川' => 'hokkaido', '由仁' => 'hokkaido', '長沼' => 'hokkaido', '栗山' => 'hokkaido',
        '月形' => 'hokkaido', '浦臼' => 'hokkaido', '新十津川' => 'hokkaido', '妹背牛' => 'hokkaido',
        '秩父別' => 'hokkaido', '雨竜' => 'hokkaido', '北竜' => 'hokkaido', '沼田' => 'hokkaido',
        '鷹栖' => 'hokkaido', '東神楽' => 'hokkaido', '当麻' => 'hokkaido', '比布' => 'hokkaido',
        '愛別' => 'hokkaido', '上川' => 'hokkaido', '東川' => 'hokkaido', '美瑛' => 'hokkaido',
        '上富良野' => 'hokkaido', '中富良野' => 'hokkaido', '南富良野' => 'hokkaido', '占冠' => 'hokkaido',
        '和寒' => 'hokkaido', '剣淵' => 'hokkaido', '下川' => 'hokkaido', '美深' => 'hokkaido',
        '音威子府' => 'hokkaido', '中川' => 'hokkaido', '幌加内' => 'hokkaido', '増毛' => 'hokkaido',
        '小平' => 'hokkaido', '苫前' => 'hokkaido', '羽幌' => 'hokkaido', '初山別' => 'hokkaido',
        '遠別' => 'hokkaido', '天塩' => 'hokkaido', '猿払' => 'hokkaido', '浜頓別' => 'hokkaido',
        '中頓別' => 'hokkaido', '枝幸' => 'hokkaido', '豊富' => 'hokkaido', '礼文' => 'hokkaido',
        '利尻' => 'hokkaido', '利尻富士' => 'hokkaido', '幌延' => 'hokkaido', '美幌' => 'hokkaido',
        '津別' => 'hokkaido', '斜里' => 'hokkaido', '小清水' => 'hokkaido', '訓子府' => 'hokkaido',
        '置戸' => 'hokkaido', '佐呂間' => 'hokkaido', '遠軽' => 'hokkaido', '湧別' => 'hokkaido',
        '滝上' => 'hokkaido', '興部' => 'hokkaido', '西興部' => 'hokkaido', '雄武' => 'hokkaido',
        '大空' => 'hokkaido', '豊浦' => 'hokkaido', '壮瞥' => 'hokkaido', '白老' => 'hokkaido',
        '厚真' => 'hokkaido', '洞爺湖' => 'hokkaido', '安平' => 'hokkaido', 'むかわ' => 'hokkaido',
        '日高' => 'hokkaido', '平取' => 'hokkaido', '新冠' => 'hokkaido', '浦河' => 'hokkaido',
        '様似' => 'hokkaido', '新ひだか' => 'hokkaido', '鹿部' => 'hokkaido',
        
        // === 青森県 ===
        '青森' => 'aomori', '弘前' => 'aomori', '八戸' => 'aomori', '黒石' => 'aomori', '五所川原' => 'aomori',
        '十和田' => 'aomori', '三沢' => 'aomori', 'むつ' => 'aomori', 'つがる' => 'aomori', '平川' => 'aomori',
        '平内' => 'aomori', '今別' => 'aomori', '蓬田' => 'aomori', '外ヶ浜' => 'aomori', '鰺ヶ沢' => 'aomori',
        '深浦' => 'aomori', '西目屋' => 'aomori', '藤崎' => 'aomori', '大鰐' => 'aomori', '田舎館' => 'aomori',
        '板柳' => 'aomori', '鶴田' => 'aomori', '中泊' => 'aomori', '野辺地' => 'aomori', '七戸' => 'aomori',
        '六戸' => 'aomori', '横浜' => 'aomori', '東北' => 'aomori', '六ヶ所' => 'aomori', 'おいらせ' => 'aomori',
        '大間' => 'aomori', '東通' => 'aomori', '風間浦' => 'aomori', '佐井' => 'aomori', '三戸' => 'aomori',
        '五戸' => 'aomori', '田子' => 'aomori', '南部' => 'aomori', '階上' => 'aomori', '新郷' => 'aomori',
        
        // === 岩手県 ===
        '盛岡' => 'iwate', '宮古' => 'iwate', '大船渡' => 'iwate', '花巻' => 'iwate', '北上' => 'iwate',
        '久慈' => 'iwate', '遠野' => 'iwate', '一関' => 'iwate', '陸前高田' => 'iwate', '釜石' => 'iwate',
        '二戸' => 'iwate', '八幡平' => 'iwate', '奥州' => 'iwate', '滝沢' => 'iwate', '雫石' => 'iwate',
        '葛巻' => 'iwate', '岩手町' => 'iwate', '紫波' => 'iwate', '矢巾' => 'iwate', '西和賀' => 'iwate',
        '金ケ崎' => 'iwate', '平泉' => 'iwate', '住田' => 'iwate', '大槌' => 'iwate', '山田' => 'iwate',
        '岩泉' => 'iwate', '田野畑' => 'iwate', '普代' => 'iwate', '軽米' => 'iwate', '野田' => 'iwate',
        '九戸' => 'iwate', '洋野' => 'iwate', '一戸' => 'iwate',
        
        // === 宮城県 ===
        '仙台' => 'miyagi', '石巻' => 'miyagi', '塩竈' => 'miyagi', '気仙沼' => 'miyagi', '白石' => 'miyagi',
        '名取' => 'miyagi', '角田' => 'miyagi', '多賀城' => 'miyagi', '岩沼' => 'miyagi', '登米' => 'miyagi',
        '栗原' => 'miyagi', '東松島' => 'miyagi', '大崎' => 'miyagi', '富谷' => 'miyagi', '蔵王' => 'miyagi',
        '七ヶ宿' => 'miyagi', '大河原' => 'miyagi', '村田' => 'miyagi', '柴田' => 'miyagi', '川崎' => 'miyagi',
        '丸森' => 'miyagi', '亘理' => 'miyagi', '山元' => 'miyagi', '松島' => 'miyagi', '七ヶ浜' => 'miyagi',
        '利府' => 'miyagi', '大和' => 'miyagi', '大郷' => 'miyagi', '大衡' => 'miyagi', '色麻' => 'miyagi',
        '加美' => 'miyagi', '涌谷' => 'miyagi', '美里' => 'miyagi', '女川' => 'miyagi', '南三陸' => 'miyagi',
        
        // === 秋田県 ===
        '秋田' => 'akita', '能代' => 'akita', '横手' => 'akita', '大館' => 'akita', '男鹿' => 'akita',
        '湯沢' => 'akita', '鹿角' => 'akita', '由利本荘' => 'akita', '潟上' => 'akita', '大仙' => 'akita',
        '北秋田' => 'akita', 'にかほ' => 'akita', '仙北' => 'akita', '小坂' => 'akita', '上小阿仁' => 'akita',
        '藤里' => 'akita', '三種' => 'akita', '八峰' => 'akita', '五城目' => 'akita', '八郎潟' => 'akita',
        '井川' => 'akita', '大潟' => 'akita', '美郷' => 'akita', '羽後' => 'akita', '東成瀬' => 'akita',
        
        // === 山形県 ===
        '山形' => 'yamagata', '米沢' => 'yamagata', '鶴岡' => 'yamagata', '酒田' => 'yamagata', '新庄' => 'yamagata',
        '寒河江' => 'yamagata', '上山' => 'yamagata', '村山' => 'yamagata', '長井' => 'yamagata', '天童' => 'yamagata',
        '東根' => 'yamagata', '尾花沢' => 'yamagata', '南陽' => 'yamagata', '山辺' => 'yamagata', '中山' => 'yamagata',
        '河北' => 'yamagata', '西川' => 'yamagata', '朝日' => 'yamagata', '大江' => 'yamagata', '大石田' => 'yamagata',
        '金山' => 'yamagata', '最上' => 'yamagata', '舟形' => 'yamagata', '真室川' => 'yamagata', '大蔵' => 'yamagata',
        '鮭川' => 'yamagata', '戸沢' => 'yamagata', '高畠' => 'yamagata', '川西' => 'yamagata', '小国' => 'yamagata',
        '白鷹' => 'yamagata', '飯豊' => 'yamagata', '三川' => 'yamagata', '庄内' => 'yamagata', '遊佐' => 'yamagata',
        
        // === 福島県 ===
        '福島' => 'fukushima', '会津若松' => 'fukushima', '郡山' => 'fukushima', 'いわき' => 'fukushima', '白河' => 'fukushima',
        '須賀川' => 'fukushima', '喜多方' => 'fukushima', '相馬' => 'fukushima', '二本松' => 'fukushima', '田村' => 'fukushima',
        '南相馬' => 'fukushima', '伊達' => 'fukushima', '本宮' => 'fukushima', '桑折' => 'fukushima', '国見' => 'fukushima',
        '川俣' => 'fukushima', '大玉' => 'fukushima', '鏡石' => 'fukushima', '天栄' => 'fukushima', '下郷' => 'fukushima',
        '檜枝岐' => 'fukushima', '只見' => 'fukushima', '南会津' => 'fukushima', '北塩原' => 'fukushima', '西会津' => 'fukushima',
        '磐梯' => 'fukushima', '猪苗代' => 'fukushima', '会津坂下' => 'fukushima', '湯川' => 'fukushima', '柳津' => 'fukushima',
        '三島' => 'fukushima', '金山' => 'fukushima', '昭和' => 'fukushima', '会津美里' => 'fukushima', '西郷' => 'fukushima',
        '泉崎' => 'fukushima', '中島' => 'fukushima', '矢吹' => 'fukushima', '棚倉' => 'fukushima', '矢祭' => 'fukushima',
        '塙' => 'fukushima', '鮫川' => 'fukushima', '石川' => 'fukushima', '玉川' => 'fukushima', '平田' => 'fukushima',
        '浅川' => 'fukushima', '古殿' => 'fukushima', '三春' => 'fukushima', '小野' => 'fukushima', '広野' => 'fukushima',
        '楢葉' => 'fukushima', '富岡' => 'fukushima', '川内' => 'fukushima', '大熊' => 'fukushima', '双葉' => 'fukushima',
        '浪江' => 'fukushima', '葛尾' => 'fukushima', '新地' => 'fukushima', '飯舘' => 'fukushima',
        
        // === 茨城県 ===
        'かすみがうら' => 'ibaraki', '笠間' => 'ibaraki', '鹿嶋' => 'ibaraki', '北茨城' => 'ibaraki',
        '古河' => 'ibaraki', '小美玉' => 'ibaraki', '桜川' => 'ibaraki', '下妻' => 'ibaraki',
        '常総' => 'ibaraki', '高萩' => 'ibaraki', '筑西' => 'ibaraki', 'つくば' => 'ibaraki',
        'つくばみらい' => 'ibaraki', '土浦' => 'ibaraki', '取手' => 'ibaraki', '那珂' => 'ibaraki',
        '行方' => 'ibaraki', '坂東' => 'ibaraki', '常陸太田' => 'ibaraki', '常陸大宮' => 'ibaraki',
        '日立' => 'ibaraki', 'ひたちなか' => 'ibaraki', '鉾田' => 'ibaraki', '水戸' => 'ibaraki',
        '守谷' => 'ibaraki', '結城' => 'ibaraki', '龍ケ崎' => 'ibaraki', '石岡' => 'ibaraki', '牛久' => 'ibaraki',
        '潮来' => 'ibaraki', '稲敷' => 'ibaraki', '茨城' => 'ibaraki', '大洗' => 'ibaraki', '城里' => 'ibaraki',
        '東海' => 'ibaraki', '大子' => 'ibaraki', '美浦' => 'ibaraki', '阿見' => 'ibaraki', '河内' => 'ibaraki',
        '八千代' => 'ibaraki', '五霞' => 'ibaraki', '境' => 'ibaraki', '利根' => 'ibaraki',
        
        // === 栃木県 ===
        'さくら' => 'tochigi', '足利' => 'tochigi', '市貝' => 'tochigi', '宇都宮' => 'tochigi',
        '大田原' => 'tochigi', '小山' => 'tochigi', '鹿沼' => 'tochigi', '上三川' => 'tochigi',
        '佐野' => 'tochigi', '下野' => 'tochigi', '高根沢' => 'tochigi', '栃木' => 'tochigi',
        '那須烏山' => 'tochigi', '那須塩原' => 'tochigi', '日光' => 'tochigi', '野木' => 'tochigi',
        '芳賀' => 'tochigi', '益子' => 'tochigi', '壬生' => 'tochigi', '真岡' => 'tochigi',
        '矢板' => 'tochigi', '那珂川' => 'tochigi', '塩谷' => 'tochigi', '那須' => 'tochigi',
        
        // === 群馬県 ===
        '前橋' => 'gunma', '高崎' => 'gunma', '桐生' => 'gunma', '伊勢崎' => 'gunma', '太田' => 'gunma',
        '沼田' => 'gunma', '館林' => 'gunma', '渋川' => 'gunma', '藤岡' => 'gunma', '富岡' => 'gunma',
        '安中' => 'gunma', 'みどり' => 'gunma', '榛東' => 'gunma', '吉岡' => 'gunma', '上野' => 'gunma',
        '神流' => 'gunma', '下仁田' => 'gunma', '南牧' => 'gunma', '甘楽' => 'gunma', '中之条' => 'gunma',
        '長野原' => 'gunma', '嬬恋' => 'gunma', '草津' => 'gunma', '高山' => 'gunma', '東吾妻' => 'gunma',
        '片品' => 'gunma', '川場' => 'gunma', '昭和' => 'gunma', 'みなかみ' => 'gunma', '玉村' => 'gunma',
        '板倉' => 'gunma', '明和' => 'gunma', '千代田' => 'gunma', '大泉' => 'gunma', '邑楽' => 'gunma',
        
        // === 埼玉県 ===
        'さいたま' => 'saitama', '川越' => 'saitama', '熊谷' => 'saitama', '川口' => 'saitama', '行田' => 'saitama',
        '秩父' => 'saitama', '所沢' => 'saitama', '飯能' => 'saitama', '加須' => 'saitama', '本庄' => 'saitama',
        '東松山' => 'saitama', '春日部' => 'saitama', '狭山' => 'saitama', '羽生' => 'saitama', '鴻巣' => 'saitama',
        '深谷' => 'saitama', '上尾' => 'saitama', '草加' => 'saitama', '越谷' => 'saitama', '蕨' => 'saitama',
        '戸田' => 'saitama', '入間' => 'saitama', '朝霞' => 'saitama', '志木' => 'saitama', '和光' => 'saitama',
        '新座' => 'saitama', '桶川' => 'saitama', '久喜' => 'saitama', '北本' => 'saitama', '八潮' => 'saitama',
        '富士見' => 'saitama', '三郷' => 'saitama', '蓮田' => 'saitama', '坂戸' => 'saitama', '幸手' => 'saitama',
        '鶴ヶ島' => 'saitama', '日高' => 'saitama', '吉川' => 'saitama', 'ふじみ野' => 'saitama', '白岡' => 'saitama',
        '伊奈' => 'saitama', '三芳' => 'saitama', '毛呂山' => 'saitama', '越生' => 'saitama', '滑川' => 'saitama',
        '嵐山' => 'saitama', '小川' => 'saitama', '川島' => 'saitama', '吉見' => 'saitama', '鳩山' => 'saitama',
        'ときがわ' => 'saitama', '横瀬' => 'saitama', '皆野' => 'saitama', '長瀞' => 'saitama', '小鹿野' => 'saitama',
        '東秩父' => 'saitama', '美里' => 'saitama', '神川' => 'saitama', '上里' => 'saitama', '寄居' => 'saitama',
        '宮代' => 'saitama', '杉戸' => 'saitama', '松伏' => 'saitama',
        
        // === 千葉県 ===
        'いすみ' => 'chiba', '市川' => 'chiba', '市原' => 'chiba', '印西' => 'chiba', '浦安' => 'chiba',
        '大網白里' => 'chiba', '柏' => 'chiba', '勝浦' => 'chiba', '香取' => 'chiba', '鎌ケ谷' => 'chiba',
        '鴨川' => 'chiba', '木更津' => 'chiba', '君津' => 'chiba', '佐倉' => 'chiba', '山武' => 'chiba',
        '白井' => 'chiba', '匝瑳' => 'chiba', '袖ケ浦' => 'chiba', '館山' => 'chiba', '千葉' => 'chiba',
        '銚子' => 'chiba', '東金' => 'chiba', '富津' => 'chiba', '流山' => 'chiba', '習志野' => 'chiba',
        '成田' => 'chiba', '野田' => 'chiba', '富里' => 'chiba', '船橋' => 'chiba', '松戸' => 'chiba',
        '南房総' => 'chiba', '茂原' => 'chiba', '八街' => 'chiba', '八千代' => 'chiba', '四街道' => 'chiba',
        '酒々井' => 'chiba', '栄' => 'chiba', '神崎' => 'chiba', '多古' => 'chiba', '東庄' => 'chiba',
        '九十九里' => 'chiba', '芝山' => 'chiba', '横芝光' => 'chiba', '一宮' => 'chiba', '睦沢' => 'chiba',
        '長生' => 'chiba', '白子' => 'chiba', '長柄' => 'chiba', '長南' => 'chiba', '大多喜' => 'chiba',
        '御宿' => 'chiba', '鋸南' => 'chiba',
        
        // === 東京都 ===
        '千代田区' => 'tokyo', '中央区' => 'tokyo', '港区' => 'tokyo', '新宿区' => 'tokyo', '文京区' => 'tokyo',
        '台東区' => 'tokyo', '墨田区' => 'tokyo', '江東区' => 'tokyo', '品川区' => 'tokyo', '目黒区' => 'tokyo',
        '大田区' => 'tokyo', '世田谷区' => 'tokyo', '渋谷区' => 'tokyo', '中野区' => 'tokyo', '杉並区' => 'tokyo',
        '豊島区' => 'tokyo', '北区' => 'tokyo', '荒川区' => 'tokyo', '板橋区' => 'tokyo', '練馬区' => 'tokyo',
        '足立区' => 'tokyo', '葛飾区' => 'tokyo', '江戸川区' => 'tokyo', '八王子' => 'tokyo', '立川' => 'tokyo',
        '武蔵野' => 'tokyo', '三鷹' => 'tokyo', '青梅' => 'tokyo', '府中' => 'tokyo', '昭島' => 'tokyo',
        '調布' => 'tokyo', '町田' => 'tokyo', '小金井' => 'tokyo', '小平' => 'tokyo', '日野' => 'tokyo',
        '東村山' => 'tokyo', '国分寺' => 'tokyo', '国立' => 'tokyo', '福生' => 'tokyo', '狛江' => 'tokyo',
        '東大和' => 'tokyo', '清瀬' => 'tokyo', '東久留米' => 'tokyo', '武蔵村山' => 'tokyo', '多摩' => 'tokyo',
        '稲城' => 'tokyo', '羽村' => 'tokyo', 'あきる野' => 'tokyo', '西東京' => 'tokyo', '瑞穂' => 'tokyo',
        '日の出' => 'tokyo', '檜原' => 'tokyo', '奥多摩' => 'tokyo', '大島' => 'tokyo', '利島' => 'tokyo',
        '新島' => 'tokyo', '神津島' => 'tokyo', '三宅' => 'tokyo', '御蔵島' => 'tokyo', '八丈' => 'tokyo',
        '青ヶ島' => 'tokyo', '小笠原' => 'tokyo',
        
        // === 神奈川県 ===
        '横浜' => 'kanagawa', '川崎' => 'kanagawa', '相模原' => 'kanagawa', '横須賀' => 'kanagawa', '平塚' => 'kanagawa',
        '鎌倉' => 'kanagawa', '藤沢' => 'kanagawa', '小田原' => 'kanagawa', '茅ヶ崎' => 'kanagawa', '逗子' => 'kanagawa',
        '三浦' => 'kanagawa', '秦野' => 'kanagawa', '厚木' => 'kanagawa', '大和' => 'kanagawa', '伊勢原' => 'kanagawa',
        '海老名' => 'kanagawa', '座間' => 'kanagawa', '南足柄' => 'kanagawa', '綾瀬' => 'kanagawa', '葉山' => 'kanagawa',
        '寒川' => 'kanagawa', '大磯' => 'kanagawa', '二宮' => 'kanagawa', '中井' => 'kanagawa', '大井' => 'kanagawa',
        '松田' => 'kanagawa', '山北' => 'kanagawa', '開成' => 'kanagawa', '箱根' => 'kanagawa', '真鶴' => 'kanagawa',
        '湯河原' => 'kanagawa', '愛川' => 'kanagawa', '清川' => 'kanagawa',
        
        // === 新潟県 ===
        '新潟' => 'niigata', '長岡' => 'niigata', '三条' => 'niigata', '柏崎' => 'niigata', '新発田' => 'niigata',
        '小千谷' => 'niigata', '加茂' => 'niigata', '十日町' => 'niigata', '見附' => 'niigata', '村上' => 'niigata',
        '燕' => 'niigata', '糸魚川' => 'niigata', '妙高' => 'niigata', '五泉' => 'niigata', '上越' => 'niigata',
        '阿賀野' => 'niigata', '佐渡' => 'niigata', '魚沼' => 'niigata', '南魚沼' => 'niigata', '胎内' => 'niigata',
        '聖籠' => 'niigata', '弥彦' => 'niigata', '田上' => 'niigata', '阿賀' => 'niigata', '出雲崎' => 'niigata',
        '湯沢' => 'niigata', '津南' => 'niigata', '刈羽' => 'niigata', '関川' => 'niigata', '粟島浦' => 'niigata',
        
        // === 富山県 ===
        '富山' => 'toyama', '高岡' => 'toyama', '魚津' => 'toyama', '氷見' => 'toyama', '滑川' => 'toyama',
        '黒部' => 'toyama', '砺波' => 'toyama', '小矢部' => 'toyama', '南砺' => 'toyama', '射水' => 'toyama',
        '舟橋' => 'toyama', '上市' => 'toyama', '立山' => 'toyama', '入善' => 'toyama', '朝日' => 'toyama',
        
        // === 石川県 ===
        '金沢' => 'ishikawa', '七尾' => 'ishikawa', '小松' => 'ishikawa', '輪島' => 'ishikawa', '珠洲' => 'ishikawa',
        '加賀' => 'ishikawa', '羽咋' => 'ishikawa', 'かほく' => 'ishikawa', '白山' => 'ishikawa', '能美' => 'ishikawa',
        '野々市' => 'ishikawa', '川北' => 'ishikawa', '津幡' => 'ishikawa', '内灘' => 'ishikawa', '志賀' => 'ishikawa',
        '宝達志水' => 'ishikawa', '中能登' => 'ishikawa', '穴水' => 'ishikawa', '能登' => 'ishikawa',
        
        // === 福井県 ===
        '福井' => 'fukui', '敦賀' => 'fukui', 'つるが' => 'fukui', '小浜' => 'fukui', '大野' => 'fukui', '勝山' => 'fukui',
        '鯖江' => 'fukui', 'あわら' => 'fukui', '越前' => 'fukui', '坂井' => 'fukui', '永平寺' => 'fukui',
        '池田' => 'fukui', '南越前' => 'fukui', '越前町' => 'fukui', '美浜' => 'fukui', '高浜' => 'fukui',
        'おおい' => 'fukui', '若狭' => 'fukui',
        
        // === 山梨県 ===
        '甲府' => 'yamanashi', '富士吉田' => 'yamanashi', '都留' => 'yamanashi', '山梨' => 'yamanashi', '大月' => 'yamanashi',
        '韮崎' => 'yamanashi', '南アルプス' => 'yamanashi', '北杜' => 'yamanashi', '甲斐' => 'yamanashi', '笛吹' => 'yamanashi',
        '上野原' => 'yamanashi', '甲州' => 'yamanashi', '中央' => 'yamanashi', '市川三郷' => 'yamanashi', '早川' => 'yamanashi',
        '身延' => 'yamanashi', '南部' => 'yamanashi', '富士川' => 'yamanashi', '昭和' => 'yamanashi', '道志' => 'yamanashi',
        '西桂' => 'yamanashi', '忍野' => 'yamanashi', '山中湖' => 'yamanashi', '鳴沢' => 'yamanashi', '富士河口湖' => 'yamanashi',
        '小菅' => 'yamanashi', '丹波山' => 'yamanashi',
        
        // === 長野県 ===
        '長野' => 'nagano', '松本' => 'nagano', '上田' => 'nagano', '岡谷' => 'nagano', '飯田' => 'nagano',
        '諏訪' => 'nagano', '須坂' => 'nagano', '小諸' => 'nagano', '伊那' => 'nagano', '駒ヶ根' => 'nagano',
        '中野' => 'nagano', '大町' => 'nagano', '飯山' => 'nagano', '茅野' => 'nagano', '塩尻' => 'nagano',
        '佐久' => 'nagano', '千曲' => 'nagano', '東御' => 'nagano', '安曇野' => 'nagano', '小海' => 'nagano',
        '川上' => 'nagano', '南牧' => 'nagano', '南相木' => 'nagano', '北相木' => 'nagano', '佐久穂' => 'nagano',
        '軽井沢' => 'nagano', '御代田' => 'nagano', '立科' => 'nagano', '青木' => 'nagano', '長和' => 'nagano',
        '下諏訪' => 'nagano', '富士見' => 'nagano', '原' => 'nagano', '辰野' => 'nagano', '箕輪' => 'nagano',
        '飯島' => 'nagano', '南箕輪' => 'nagano', '中川' => 'nagano', '宮田' => 'nagano', '松川' => 'nagano',
        '高森' => 'nagano', '阿南' => 'nagano', '阿智' => 'nagano', '平谷' => 'nagano', '根羽' => 'nagano',
        '下條' => 'nagano', '売木' => 'nagano', '天龍' => 'nagano', '泰阜' => 'nagano', '喬木' => 'nagano',
        '豊丘' => 'nagano', '大鹿' => 'nagano', '上松' => 'nagano', '南木曽' => 'nagano', '木祖' => 'nagano',
        '王滝' => 'nagano', '大桑' => 'nagano', '木曽' => 'nagano', '麻績' => 'nagano', '生坂' => 'nagano',
        '筑北' => 'nagano', '坂城' => 'nagano', '小布施' => 'nagano', '高山' => 'nagano', '山ノ内' => 'nagano',
        '木島平' => 'nagano', '野沢温泉' => 'nagano', '信濃町' => 'nagano', '小川' => 'nagano', '飯綱' => 'nagano',
        
        // === 岐阜県 ===
        '岐阜' => 'gifu', '大垣' => 'gifu', '高山' => 'gifu', '多治見' => 'gifu', '関' => 'gifu',
        '中津川' => 'gifu', '美濃' => 'gifu', '瑞浪' => 'gifu', '羽島' => 'gifu', '恵那' => 'gifu',
        '美濃加茂' => 'gifu', '土岐' => 'gifu', '各務原' => 'gifu', '可児' => 'gifu', '山県' => 'gifu',
        '瑞穂' => 'gifu', '飛騨' => 'gifu', '本巣' => 'gifu', '郡上' => 'gifu', '下呂' => 'gifu',
        '海津' => 'gifu', '岐南' => 'gifu', '笠松' => 'gifu', '養老' => 'gifu', '垂井' => 'gifu',
        '関ヶ原' => 'gifu', '神戸' => 'gifu', '輪之内' => 'gifu', '安八' => 'gifu', '揖斐川' => 'gifu',
        '大野' => 'gifu', '北方' => 'gifu', '坂祝' => 'gifu', '富加' => 'gifu', '川辺' => 'gifu',
        '七宗' => 'gifu', '八百津' => 'gifu', '白川' => 'gifu', '東白川' => 'gifu', '御嵩' => 'gifu',
        '白川村' => 'gifu',
        
        // === 静岡県 ===
        '静岡' => 'shizuoka', '浜松' => 'shizuoka', '沼津' => 'shizuoka', '熱海' => 'shizuoka', '三島' => 'shizuoka',
        '富士宮' => 'shizuoka', '伊東' => 'shizuoka', '島田' => 'shizuoka', '富士' => 'shizuoka', '磐田' => 'shizuoka',
        '焼津' => 'shizuoka', '掛川' => 'shizuoka', '藤枝' => 'shizuoka', '御殿場' => 'shizuoka', '袋井' => 'shizuoka',
        '下田' => 'shizuoka', '裾野' => 'shizuoka', '湖西' => 'shizuoka', '伊豆' => 'shizuoka', '御前崎' => 'shizuoka',
        '菊川' => 'shizuoka', '伊豆の国' => 'shizuoka', '牧之原' => 'shizuoka', '東伊豆' => 'shizuoka', '河津' => 'shizuoka',
        '南伊豆' => 'shizuoka', '松崎' => 'shizuoka', '西伊豆' => 'shizuoka', '函南' => 'shizuoka', '清水' => 'shizuoka',
        '長泉' => 'shizuoka', '小山' => 'shizuoka', '吉田' => 'shizuoka', '川根本' => 'shizuoka', '森' => 'shizuoka',
        
        // === 愛知県 ===
        'あま' => 'aichi', '愛西' => 'aichi', '安城' => 'aichi', '一宮' => 'aichi', '稲沢' => 'aichi',
        '犬山' => 'aichi', '岩倉' => 'aichi', '大府' => 'aichi', '尾張旭' => 'aichi', '春日井' => 'aichi',
        '蒲郡' => 'aichi', '刈谷' => 'aichi', '北名古屋' => 'aichi', '清須' => 'aichi', '江南' => 'aichi',
        '小牧' => 'aichi', '新城' => 'aichi', '瀬戸' => 'aichi', '高浜' => 'aichi', '田原' => 'aichi',
        '知多' => 'aichi', '知立' => 'aichi', '津島' => 'aichi', '常滑' => 'aichi', '豊明' => 'aichi',
        '豊川' => 'aichi', '豊田' => 'aichi', '豊橋' => 'aichi', '名古屋' => 'aichi', '西尾' => 'aichi',
        '日進' => 'aichi', '半田' => 'aichi', '碧南' => 'aichi', 'みよし' => 'aichi', '弥富' => 'aichi',
        '豊山' => 'aichi', '大口' => 'aichi', '扶桑' => 'aichi', '大治' => 'aichi', '蟹江' => 'aichi',
        '飛島' => 'aichi', '阿久比' => 'aichi', '東浦' => 'aichi', '南知多' => 'aichi', '美浜' => 'aichi',
        '武豊' => 'aichi', '幸田' => 'aichi', '設楽' => 'aichi', '東栄' => 'aichi', '豊根' => 'aichi',
        
        // === 三重県 ===
        'いなべ' => 'mie', '伊賀' => 'mie', '伊勢' => 'mie', '尾鷲' => 'mie', '亀山' => 'mie',
        '熊野' => 'mie', '桑名' => 'mie', '志摩' => 'mie', '鈴鹿' => 'mie', '津' => 'mie',
        '鳥羽' => 'mie', '名張' => 'mie', '松阪' => 'mie', '四日市' => 'mie', '木曽岬' => 'mie',
        '東員' => 'mie', '菰野' => 'mie', '朝日' => 'mie', '川越' => 'mie', '多気' => 'mie',
        '明和' => 'mie', '大台' => 'mie', '玉城' => 'mie', '度会' => 'mie', '大紀' => 'mie',
        '南伊勢' => 'mie', '紀北' => 'mie', '御浜' => 'mie', '紀宝' => 'mie',
        
        // === 滋賀県 ===
        '大津' => 'shiga', '彦根' => 'shiga', '長浜' => 'shiga', '近江八幡' => 'shiga', '草津' => 'shiga',
        '守山' => 'shiga', '栗東' => 'shiga', '甲賀' => 'shiga', '野洲' => 'shiga', '湖南' => 'shiga',
        '高島' => 'shiga', '東近江' => 'shiga', '米原' => 'shiga', '日野' => 'shiga', '竜王' => 'shiga',
        '愛荘' => 'shiga', '豊郷' => 'shiga', '甲良' => 'shiga', '多賀' => 'shiga',
        
        // === 京都府 ===
        '京都' => 'kyoto', '福知山' => 'kyoto', '舞鶴' => 'kyoto', '綾部' => 'kyoto', '宇治' => 'kyoto',
        '宮津' => 'kyoto', '亀岡' => 'kyoto', '城陽' => 'kyoto', '向日' => 'kyoto', '長岡京' => 'kyoto',
        '八幡' => 'kyoto', '京田辺' => 'kyoto', '京丹後' => 'kyoto', '南丹' => 'kyoto', '木津川' => 'kyoto',
        '大山崎' => 'kyoto', '久御山' => 'kyoto', '井手' => 'kyoto', '宇治田原' => 'kyoto', '笠置' => 'kyoto',
        '和束' => 'kyoto', '精華' => 'kyoto', '南山城' => 'kyoto', '京丹波' => 'kyoto', '伊根' => 'kyoto',
        '与謝野' => 'kyoto',
        
        // === 大阪府 ===
        '大阪' => 'osaka', '堺' => 'osaka', '岸和田' => 'osaka', '豊中' => 'osaka', '吹田' => 'osaka',
        '泉大津' => 'osaka', '高槻' => 'osaka', '貝塚' => 'osaka', '守口' => 'osaka', '枚方' => 'osaka',
        '茨木' => 'osaka', '八尾' => 'osaka', '泉佐野' => 'osaka', '富田林' => 'osaka', '寝屋川' => 'osaka',
        '河内長野' => 'osaka', '松原' => 'osaka', '大東' => 'osaka', '和泉' => 'osaka', '箕面' => 'osaka',
        '柏原' => 'osaka', '羽曳野' => 'osaka', '門真' => 'osaka', '摂津' => 'osaka', '高石' => 'osaka',
        '藤井寺' => 'osaka', '東大阪' => 'osaka', '泉南' => 'osaka', '四條畷' => 'osaka', '交野' => 'osaka',
        '大阪狭山' => 'osaka', '阪南' => 'osaka', '島本' => 'osaka', '豊能' => 'osaka', '能勢' => 'osaka',
        '忠岡' => 'osaka', '熊取' => 'osaka', '田尻' => 'osaka', '岬' => 'osaka', '太子' => 'osaka',
        '河南' => 'osaka', '千早赤阪' => 'osaka',
        
        // === 兵庫県 ===
        '神戸' => 'hyogo', '姫路' => 'hyogo', '尼崎' => 'hyogo', '明石' => 'hyogo', '西宮' => 'hyogo',
        '洲本' => 'hyogo', '芦屋' => 'hyogo', '伊丹' => 'hyogo', '相生' => 'hyogo', '豊岡' => 'hyogo',
        '加古川' => 'hyogo', '赤穂' => 'hyogo', '西脇' => 'hyogo', '宝塚' => 'hyogo', '三木' => 'hyogo',
        '高砂' => 'hyogo', '川西' => 'hyogo', '小野' => 'hyogo', '三田' => 'hyogo', '加西' => 'hyogo',
        '篠山' => 'hyogo', '養父' => 'hyogo', '丹波' => 'hyogo', '南あわじ' => 'hyogo', '朝来' => 'hyogo',
        '淡路' => 'hyogo', '宍粟' => 'hyogo', '加東' => 'hyogo', 'たつの' => 'hyogo', '猪名川' => 'hyogo',
        '多可' => 'hyogo', '稲美' => 'hyogo', '播磨' => 'hyogo', '市川' => 'hyogo', '福崎' => 'hyogo',
        '神河' => 'hyogo', '太子' => 'hyogo', '上郡' => 'hyogo', '佐用' => 'hyogo', '香美' => 'hyogo',
        '新温泉' => 'hyogo',
        
        // === 奈良県 ===
        '奈良' => 'nara', '橿原' => 'nara', '生駒' => 'nara', '大和郡山' => 'nara', '天理' => 'nara',
        '桜井' => 'nara', '五條' => 'nara', '御所' => 'nara', '大和高田' => 'nara', 'いかるが' => 'nara',
        '王寺' => 'nara', '上牧' => 'nara', '河合' => 'nara', '吉野' => 'nara', '大淀' => 'nara',
        '下市' => 'nara', '黒滝' => 'nara', '天川' => 'nara', '野迫川' => 'nara', '十津川' => 'nara',
        '下北山' => 'nara', '上北山' => 'nara', '川上' => 'nara', '東吉野' => 'nara', '山添' => 'nara',
        '平群' => 'nara', '三郷' => 'nara', '斑鳩' => 'nara', '安堵' => 'nara', '三宅' => 'nara',
        '田原本' => 'nara', '曽爾' => 'nara', '御杖' => 'nara', '高取' => 'nara', '明日香' => 'nara',
        '広陵' => 'nara',
        
        // === 和歌山県 ===
        '和歌山' => 'wakayama', '海南' => 'wakayama', '橋本' => 'wakayama', '有田' => 'wakayama', '御坊' => 'wakayama',
        '田辺' => 'wakayama', '新宮' => 'wakayama', '紀の川' => 'wakayama', '岩出' => 'wakayama', '紀美野' => 'wakayama',
        'かつらぎ' => 'wakayama', '九度山' => 'wakayama', '高野' => 'wakayama', '湯浅' => 'wakayama', '広川' => 'wakayama',
        '有田川' => 'wakayama', '美浜' => 'wakayama', '日高' => 'wakayama', '由良' => 'wakayama', '印南' => 'wakayama',
        'みなべ' => 'wakayama', '日高川' => 'wakayama', '白浜' => 'wakayama', '上富田' => 'wakayama', 'すさみ' => 'wakayama',
        '那智勝浦' => 'wakayama', '太地' => 'wakayama', '古座川' => 'wakayama', '北山' => 'wakayama', '串本' => 'wakayama',
        
        // === 鳥取県 ===
        '鳥取' => 'tottori', '米子' => 'tottori', '倉吉' => 'tottori', '境港' => 'tottori', '岩美' => 'tottori',
        '若桜' => 'tottori', '智頭' => 'tottori', '八頭' => 'tottori', '三朝' => 'tottori', '湯梨浜' => 'tottori',
        '琴浦' => 'tottori', '北栄' => 'tottori', '日吉津' => 'tottori', '大山' => 'tottori', '南部' => 'tottori',
        '伯耆' => 'tottori', '日南' => 'tottori', '日野' => 'tottori', '江府' => 'tottori',
        
        // === 島根県 ===
        '松江' => 'shimane', '浜田' => 'shimane', '出雲' => 'shimane', '益田' => 'shimane', '大田' => 'shimane',
        '安来' => 'shimane', '江津' => 'shimane', '雲南' => 'shimane', '奥出雲' => 'shimane', '飯南' => 'shimane',
        '川本' => 'shimane', '美郷' => 'shimane', '邑南' => 'shimane', '津和野' => 'shimane', '吉賀' => 'shimane',
        '海士' => 'shimane', '西ノ島' => 'shimane', '知夫' => 'shimane', '隠岐の島' => 'shimane',
        
        // === 岡山県 ===
        '岡山' => 'okayama', '倉敷' => 'okayama', '津山' => 'okayama', '玉野' => 'okayama', '笠岡' => 'okayama',
        '井原' => 'okayama', '総社' => 'okayama', '高梁' => 'okayama', '新見' => 'okayama', '備前' => 'okayama',
        '瀬戸内' => 'okayama', '赤磐' => 'okayama', '真庭' => 'okayama', '美作' => 'okayama', '浅口' => 'okayama',
        '和気' => 'okayama', '早島' => 'okayama', '里庄' => 'okayama', '矢掛' => 'okayama', '新庄' => 'okayama',
        '鏡野' => 'okayama', '勝央' => 'okayama', '奈義' => 'okayama', '西粟倉' => 'okayama', '久米南' => 'okayama',
        '美咲' => 'okayama', '吉備中央' => 'okayama',
        
        // === 広島県 ===
        '広島' => 'hiroshima', '呉' => 'hiroshima', '竹原' => 'hiroshima', '三原' => 'hiroshima', '尾道' => 'hiroshima',
        '福山' => 'hiroshima', '府中' => 'hiroshima', '三次' => 'hiroshima', '庄原' => 'hiroshima', '大竹' => 'hiroshima',
        '東広島' => 'hiroshima', '廿日市' => 'hiroshima', '安芸高田' => 'hiroshima', '江田島' => 'hiroshima', '府中町' => 'hiroshima',
        '海田' => 'hiroshima', '熊野' => 'hiroshima', '坂' => 'hiroshima', '安芸太田' => 'hiroshima', '北広島' => 'hiroshima',
        '大崎上島' => 'hiroshima', '世羅' => 'hiroshima', '神石高原' => 'hiroshima',
        
        // === 山口県 ===
        '下関' => 'yamaguchi', '宇部' => 'yamaguchi', '山口' => 'yamaguchi', '萩' => 'yamaguchi', '防府' => 'yamaguchi',
        '下松' => 'yamaguchi', '岩国' => 'yamaguchi', '光' => 'yamaguchi', '長門' => 'yamaguchi', '柳井' => 'yamaguchi',
        '美祢' => 'yamaguchi', '周南' => 'yamaguchi', '山陽小野田' => 'yamaguchi', '周防大島' => 'yamaguchi', '和木' => 'yamaguchi',
        '上関' => 'yamaguchi', '田布施' => 'yamaguchi', '平生' => 'yamaguchi', '阿武' => 'yamaguchi',
        
        // === 徳島県 ===
        '徳島' => 'tokushima', '鳴門' => 'tokushima', '小松島' => 'tokushima', '阿南' => 'tokushima', '吉野川' => 'tokushima',
        '阿波' => 'tokushima', '美馬' => 'tokushima', '三好' => 'tokushima', '勝浦' => 'tokushima', '上勝' => 'tokushima',
        '佐那河内' => 'tokushima', '石井' => 'tokushima', '神山' => 'tokushima', '那賀' => 'tokushima', '牟岐' => 'tokushima',
        '美波' => 'tokushima', '海陽' => 'tokushima', '松茂' => 'tokushima', '北島' => 'tokushima', '藍住' => 'tokushima',
        '板野' => 'tokushima', '上板' => 'tokushima', 'つるぎ' => 'tokushima', '東みよし' => 'tokushima',
        
        // === 香川県 ===
        '高松' => 'kagawa', '丸亀' => 'kagawa', '坂出' => 'kagawa', '善通寺' => 'kagawa', '観音寺' => 'kagawa',
        'さぬき' => 'kagawa', '東かがわ' => 'kagawa', '三豊' => 'kagawa', '土庄' => 'kagawa', '小豆島' => 'kagawa',
        '三木' => 'kagawa', '直島' => 'kagawa', '宇多津' => 'kagawa', '綾川' => 'kagawa', '琴平' => 'kagawa',
        '多度津' => 'kagawa', 'まんのう' => 'kagawa',
        
        // === 愛媛県 ===
        '松山' => 'ehime', '今治' => 'ehime', '宇和島' => 'ehime', '八幡浜' => 'ehime', '新居浜' => 'ehime',
        '西条' => 'ehime', '大洲' => 'ehime', '伊予' => 'ehime', '四国中央' => 'ehime', '西予' => 'ehime',
        '東温' => 'ehime', '上島' => 'ehime', '久万高原' => 'ehime', '松前' => 'ehime', '砥部' => 'ehime',
        '内子' => 'ehime', '伊方' => 'ehime', '松野' => 'ehime', '鬼北' => 'ehime', '愛南' => 'ehime',
        
        // === 高知県 ===
        'いの' => 'kochi', '安芸' => 'kochi', '香美' => 'kochi', '香南' => 'kochi', '高知' => 'kochi',
        '四万十' => 'kochi', '宿毛' => 'kochi', '須崎' => 'kochi', '土佐' => 'kochi', '土佐清水' => 'kochi',
        '南国' => 'kochi', '室戸' => 'kochi', '東洋' => 'kochi', '奈半利' => 'kochi', '田野' => 'kochi',
        '安田' => 'kochi', '北川' => 'kochi', '馬路' => 'kochi', '芸西' => 'kochi', '本山' => 'kochi',
        '大豊' => 'kochi', '土佐町' => 'kochi', '大川' => 'kochi', '仁淀川' => 'kochi', '中土佐' => 'kochi',
        '佐川' => 'kochi', '越知' => 'kochi', '檮原' => 'kochi', '津野' => 'kochi', '大月' => 'kochi',
        '三原' => 'kochi', '黒潮' => 'kochi',
        
        // === 福岡県 ===
        'うきは' => 'fukuoka', '大川' => 'fukuoka', '大野城' => 'fukuoka', '大牟田' => 'fukuoka',
        '小郡' => 'fukuoka', '春日' => 'fukuoka', '嘉麻' => 'fukuoka', '北九州' => 'fukuoka',
        '久留米' => 'fukuoka', '古賀' => 'fukuoka', '田川' => 'fukuoka', '太宰府' => 'fukuoka',
        '筑紫野' => 'fukuoka', '筑後' => 'fukuoka', '中間' => 'fukuoka', '直方' => 'fukuoka',
        '福岡' => 'fukuoka', '福津' => 'fukuoka', '豊前' => 'fukuoka', 'みやま' => 'fukuoka',
        '宗像' => 'fukuoka', '柳川' => 'fukuoka', '八女' => 'fukuoka', '行橋' => 'fukuoka',
        '飯塚' => 'fukuoka', '朝倉' => 'fukuoka', '糸島' => 'fukuoka', '那珂川' => 'fukuoka',
        '宇美' => 'fukuoka', '篠栗' => 'fukuoka', '志免' => 'fukuoka', '須恵' => 'fukuoka',
        '新宮' => 'fukuoka', '久山' => 'fukuoka', '粕屋' => 'fukuoka', '芦屋' => 'fukuoka',
        '水巻' => 'fukuoka', '岡垣' => 'fukuoka', '遠賀' => 'fukuoka', '小竹' => 'fukuoka',
        '鞍手' => 'fukuoka', '桂川' => 'fukuoka', '筑前' => 'fukuoka', '東峰' => 'fukuoka',
        '大刀洗' => 'fukuoka', '大木' => 'fukuoka', '広川' => 'fukuoka', '香春' => 'fukuoka',
        '添田' => 'fukuoka', '糸田' => 'fukuoka', '川崎' => 'fukuoka', '大任' => 'fukuoka',
        '赤村' => 'fukuoka', '福智' => 'fukuoka', '苅田' => 'fukuoka', 'みやこ' => 'fukuoka',
        '吉富' => 'fukuoka', '上毛' => 'fukuoka', '築上' => 'fukuoka',
        
        // === 佐賀県 ===
        '佐賀' => 'saga', '唐津' => 'saga', '鳥栖' => 'saga', '多久' => 'saga', '伊万里' => 'saga',
        '武雄' => 'saga', '鹿島' => 'saga', '小城' => 'saga', '嬉野' => 'saga', '神埼' => 'saga',
        '吉野ヶ里' => 'saga', '基山' => 'saga', '上峰' => 'saga', 'みやき' => 'saga', '玄海' => 'saga',
        '有田' => 'saga', '大町' => 'saga', '江北' => 'saga', '白石' => 'saga', '太良' => 'saga',
        
        // === 長崎県 ===
        '長崎' => 'nagasaki', '佐世保' => 'nagasaki', '島原' => 'nagasaki', '諫早' => 'nagasaki', '大村' => 'nagasaki',
        '平戸' => 'nagasaki', '松浦' => 'nagasaki', '対馬' => 'nagasaki', '壱岐' => 'nagasaki', '五島' => 'nagasaki',
        '西海' => 'nagasaki', '雲仙' => 'nagasaki', '南島原' => 'nagasaki', '長与' => 'nagasaki', '時津' => 'nagasaki',
        '東彼杵' => 'nagasaki', '川棚' => 'nagasaki', '波佐見' => 'nagasaki', '小値賀' => 'nagasaki', '佐々' => 'nagasaki',
        '新上五島' => 'nagasaki',
        
        // === 熊本県 ===
        '熊本' => 'kumamoto', '八代' => 'kumamoto', '人吉' => 'kumamoto', '荒尾' => 'kumamoto', '水俣' => 'kumamoto',
        '玉名' => 'kumamoto', '山鹿' => 'kumamoto', '菊池' => 'kumamoto', '宇土' => 'kumamoto', '上天草' => 'kumamoto',
        '宇城' => 'kumamoto', '阿蘇' => 'kumamoto', '天草' => 'kumamoto', '合志' => 'kumamoto', '美里' => 'kumamoto',
        '玉東' => 'kumamoto', '南関' => 'kumamoto', '長洲' => 'kumamoto', '和水' => 'kumamoto', '大津' => 'kumamoto',
        '菊陽' => 'kumamoto', '南小国' => 'kumamoto', '小国' => 'kumamoto', '産山' => 'kumamoto', '高森' => 'kumamoto',
        '西原' => 'kumamoto', '南阿蘇' => 'kumamoto', '御船' => 'kumamoto', '嘉島' => 'kumamoto', '益城' => 'kumamoto',
        '甲佐' => 'kumamoto', '山都' => 'kumamoto', '氷川' => 'kumamoto', '芦北' => 'kumamoto', '津奈木' => 'kumamoto',
        '錦' => 'kumamoto', '多良木' => 'kumamoto', '湯前' => 'kumamoto', '水上' => 'kumamoto', '相良' => 'kumamoto',
        '五木' => 'kumamoto', '山江' => 'kumamoto', '球磨' => 'kumamoto', 'あさぎり' => 'kumamoto', '苓北' => 'kumamoto',
        
        // === 大分県 ===
        '大分' => 'oita', '別府' => 'oita', '中津' => 'oita', '日田' => 'oita', '佐伯' => 'oita',
        '臼杵' => 'oita', '津久見' => 'oita', '竹田' => 'oita', '豊後高田' => 'oita', '杵築' => 'oita',
        '宇佐' => 'oita', '豊後大野' => 'oita', '由布' => 'oita', '国東' => 'oita', '姫島' => 'oita',
        '日出' => 'oita', '九重' => 'oita', '玖珠' => 'oita',
        
        // === 宮崎県 ===
        '宮崎' => 'miyazaki', '都城' => 'miyazaki', '延岡' => 'miyazaki', '日南' => 'miyazaki', '小林' => 'miyazaki',
        '日向' => 'miyazaki', '串間' => 'miyazaki', '西都' => 'miyazaki', 'えびの' => 'miyazaki', '三股' => 'miyazaki',
        '高原' => 'miyazaki', '国富' => 'miyazaki', '綾' => 'miyazaki', '高鍋' => 'miyazaki', '新富' => 'miyazaki',
        '西米良' => 'miyazaki', '木城' => 'miyazaki', '川南' => 'miyazaki', '都農' => 'miyazaki', '門川' => 'miyazaki',
        '諸塚' => 'miyazaki', '椎葉' => 'miyazaki', '美郷' => 'miyazaki', '高千穂' => 'miyazaki', '日之影' => 'miyazaki',
        '五ヶ瀬' => 'miyazaki',
        
        // === 鹿児島県 ===
        'いちき串木野' => 'kagoshima', '阿久根' => 'kagoshima', '奄美' => 'kagoshima', '伊佐' => 'kagoshima',
        '出水' => 'kagoshima', '指宿' => 'kagoshima', '大崎' => 'kagoshima', '鹿児島' => 'kagoshima',
        '鹿屋' => 'kagoshima', '枕崎' => 'kagoshima', '南九州' => 'kagoshima', '南さつま' => 'kagoshima',
        '日置' => 'kagoshima', '曽於' => 'kagoshima', '霧島' => 'kagoshima', '薩摩川内' => 'kagoshima',
        '西之表' => 'kagoshima', '垂水' => 'kagoshima', '志布志' => 'kagoshima', 'さつま' => 'kagoshima',
        '三島' => 'kagoshima', '十島' => 'kagoshima', '龍郷' => 'kagoshima', '喜界' => 'kagoshima',
        '徳之島' => 'kagoshima', '天城' => 'kagoshima', '伊仙' => 'kagoshima', '和泊' => 'kagoshima',
        '知名' => 'kagoshima', '与論' => 'kagoshima', '長島' => 'kagoshima', '湧水' => 'kagoshima',
        '東串良' => 'kagoshima', '錦江' => 'kagoshima', '南大隅' => 'kagoshima', '肝付' => 'kagoshima',
        '中種子' => 'kagoshima', '南種子' => 'kagoshima', '屋久島' => 'kagoshima',
        
        // === 沖縄県 ===
        '那覇' => 'okinawa', '宜野湾' => 'okinawa', '石垣' => 'okinawa', '浦添' => 'okinawa', '名護' => 'okinawa',
        '糸満' => 'okinawa', '沖縄' => 'okinawa', '豊見城' => 'okinawa', 'うるま' => 'okinawa', '宮古島' => 'okinawa',
        '南城' => 'okinawa', '国頭' => 'okinawa', '大宜味' => 'okinawa', '東' => 'okinawa', '今帰仁' => 'okinawa',
        '本部' => 'okinawa', '恩納' => 'okinawa', '宜野座' => 'okinawa', '金武' => 'okinawa', '伊江' => 'okinawa',
        '読谷' => 'okinawa', '嘉手納' => 'okinawa', '北谷' => 'okinawa', '北中城' => 'okinawa', '中城' => 'okinawa',
        '西原' => 'okinawa', '与那原' => 'okinawa', '南風原' => 'okinawa', '渡嘉敷' => 'okinawa', '座間味' => 'okinawa',
        '粟国' => 'okinawa', '渡名喜' => 'okinawa', '南大東' => 'okinawa', '北大東' => 'okinawa', '伊平屋' => 'okinawa',
        '伊是名' => 'okinawa', '久米島' => 'okinawa', '八重瀬' => 'okinawa', '多良間' => 'okinawa', '竹富' => 'okinawa',
        '与那国' => 'okinawa'
    );
    
    // 市町村名から都道府県名の文字を除去してクリーンアップ
    $clean_name = preg_replace('/[都道府県市町村区町]/u', '', $municipality_name);
    
    // 完全一致を試行
    if (isset($municipality_prefecture_map[$clean_name])) {
        return $municipality_prefecture_map[$clean_name];
    }
    
    // 部分一致を試行（市町村名が含まれているかチェック）
    foreach ($municipality_prefecture_map as $key => $prefecture) {
        if (strpos($clean_name, $key) !== false || strpos($key, $clean_name) !== false) {
            return $prefecture;
        }
    }
    
    // 地域特有の命名パターンから推定
    $patterns = array(
        // 北海道パターン
        '/^(上|下|北|南|東|西).*(川|別|内|幌|沢|岳|岸|浦|丘|野|見)/' => 'hokkaido',
        '/^.*(幌|別|内|岳|沢|川|浦|丘|野|見|滝|岸)$/' => 'hokkaido',
        
        // 東北パターン  
        '/^.*(沢|田|川|内|山|野|町|森|原)$/' => function($name) {
            // より具体的な地域判定が必要
            if (strpos($name, '津軽') !== false) return 'aomori';
            if (strpos($name, '平泉') !== false) return 'iwate';
            if (strpos($name, '仙台') !== false || strpos($name, '石巻') !== false) return 'miyagi';
            if (strpos($name, '横手') !== false || strpos($name, '大館') !== false) return 'akita';
            if (strpos($name, '米沢') !== false || strpos($name, '鶴岡') !== false) return 'yamagata';
            if (strpos($name, '会津') !== false || strpos($name, 'いわき') !== false) return 'fukushima';
            return '';
        },
        
        // 九州パターン
        '/^(薩摩|大隅|肝属|曽於|出水|伊佐)/' => 'kagoshima',
        '/^(筑前|筑後|豊前|豊後)/' => 'fukuoka',
        '/^(肥前|肥後)/' => function($name) {
            if (strpos($name, '佐賀') !== false) return 'saga';
            if (strpos($name, '長崎') !== false) return 'nagasaki'; 
            if (strpos($name, '熊本') !== false) return 'kumamoto';
            return '';
        }
    );
    
    foreach ($patterns as $pattern => $result) {
        if (is_callable($result)) {
            if (preg_match($pattern, $clean_name)) {
                $prefecture = $result($clean_name);
                if ($prefecture) return $prefecture;
            }
        } else {
            if (preg_match($pattern, $clean_name)) {
                return $result;
            }
        }
    }
    
    // 郡名パターンから推定
    if (strpos($municipality_name, '郡') !== false) {
        $common_gun_patterns = array(
            '雨竜郡' => 'hokkaido', '石狩郡' => 'hokkaido', '勇払郡' => 'hokkaido', '上川郡' => 'hokkaido',
            '空知郡' => 'hokkaido', 'sorachi' => 'hokkaido', '留萌郡' => 'hokkaido', '宗谷郡' => 'hokkaido',
            '網走郡' => 'hokkaido', '斜里郡' => 'hokkaido', '常呂郡' => 'hokkaido', '紋別郡' => 'hokkaido',
            '河東郡' => 'hokkaido', '上士幌' => 'hokkaido', '河西郡' => 'hokkaido', '広尾郡' => 'hokkaido',
            '足寄郡' => 'hokkaido', '十勝郡' => 'hokkaido', '中川郡' => 'hokkaido', '天塩郡' => 'hokkaido',
            '苫前郡' => 'hokkaido', '羽幌' => 'hokkaido', '増毛郡' => 'hokkaido', '留萌市' => 'hokkaido',
            
            '津軽郡' => 'aomori', '北津軽郡' => 'aomori', '西津軽郡' => 'aomori', '中津軽郡' => 'aomori',
            '南津軽郡' => 'aomori', '上北郡' => 'aomori', '下北郡' => 'aomori', '三戸郡' => 'aomori',
            
            '二戸郡' => 'iwate', '九戸郡' => 'iwate', '一戸' => 'iwate', '岩手郡' => 'iwate',
            '紫波郡' => 'iwate', '稗貫郡' => 'iwate', '花巻' => 'iwate', '和賀郡' => 'iwate',
            '胆沢郡' => 'iwate', '江刺' => 'iwate', '気仙郡' => 'iwate', '上閉伊郡' => 'iwate',
            '下閉伊郡' => 'iwate', '九戸郡' => 'iwate',
            
            '黒川郡' => 'miyagi', '加美郡' => 'miyagi', '遠田郡' => 'miyagi', '牡鹿郡' => 'miyagi',
            '本吉郡' => 'miyagi', '刈田郡' => 'miyagi', '柴田郡' => 'miyagi', '伊具郡' => 'miyagi',
            '亘理郡' => 'miyagi', '宮城郡' => 'miyagi',
            
            '鹿角郡' => 'akita', '北秋田郡' => 'akita', '山本郡' => 'akita', '南秋田郡' => 'akita',
            '河辺郡' => 'akita', '由利郡' => 'akita', '仙北郡' => 'akita', '平鹿郡' => 'akita',
            '雄勝郡' => 'akita',
            
            '最上郡' => 'yamagata', '村山郡' => 'yamagata', '西村山郡' => 'yamagata', '北村山郡' => 'yamagata',
            '東村山郡' => 'yamagata', '西置賜郡' => 'yamagata', '東置賜郡' => 'yamagata', '南置賜郡' => 'yamagata',
            '西田川郡' => 'yamagata', '東田川郡' => 'yamagata', '飽海郡' => 'yamagata',
            
            '伊達郡' => 'fukushima', '安達郡' => 'fukushima', '岩瀬郡' => 'fukushima', '南会津郡' => 'fukushima',
            '耶麻郡' => 'fukushima', '河沼郡' => 'fukushima', '大沼郡' => 'fukushima', '西白河郡' => 'fukushima',
            '東白川郡' => 'fukushima', '石川郡' => 'fukushima', '田村郡' => 'fukushima', '双葉郡' => 'fukushima',
            '相馬郡' => 'fukushima'
        );
        
        foreach ($common_gun_patterns as $gun => $pref) {
            if (strpos($municipality_name, $gun) !== false) {
                return $pref;
            }
        }
    }
    
    return '';
}

/**
 * =============================================================================
 * 市町村検索・フィルタリング用AJAX機能
 * =============================================================================
 */

/**
 * 市町村検索のAJAX処理
 * カテゴリーセクションの地域選択で使用
 */
add_action('wp_ajax_search_municipalities', 'gi_ajax_search_municipalities');
add_action('wp_ajax_nopriv_search_municipalities', 'gi_ajax_search_municipalities');

function gi_ajax_search_municipalities() {
    // Nonce検証
    if (!wp_verify_nonce($_POST['nonce'], 'gi_ajax_nonce')) {
        wp_send_json_error(['message' => 'セキュリティチェックに失敗しました']);
        return;
    }

    $query = sanitize_text_field($_POST['query'] ?? '');
    
    if (strlen($query) < 2) {
        wp_send_json_error(['message' => '検索キーワードは2文字以上入力してください']);
        return;
    }

    $results = [];
    
    // 都道府県から検索
    $prefecture_terms = get_terms([
        'taxonomy' => 'grant_prefecture',
        'hide_empty' => true,
        'name__like' => $query,
        'number' => 5
    ]);
    
    if (!is_wp_error($prefecture_terms)) {
        foreach ($prefecture_terms as $term) {
            $results[] = [
                'name' => $term->name,
                'type' => 'prefecture',
                'count' => $term->count,
                'url' => get_term_link($term)
            ];
        }
    }
    
    // 市町村から検索
    $municipality_terms = get_terms([
        'taxonomy' => 'grant_municipality',
        'hide_empty' => true,
        'name__like' => $query,
        'number' => 10
    ]);
    
    if (!is_wp_error($municipality_terms)) {
        foreach ($municipality_terms as $term) {
            // 都道府県情報を取得
            $prefecture_slug = get_term_meta($term->term_id, 'prefecture_slug', true);
            $prefecture_name = '';
            
            if ($prefecture_slug) {
                $prefecture_term = get_term_by('slug', $prefecture_slug, 'grant_prefecture');
                if ($prefecture_term) {
                    $prefecture_name = $prefecture_term->name;
                }
            }
            
            $display_name = $term->name;
            if ($prefecture_name && strpos($term->name, $prefecture_name) === false) {
                $display_name = $prefecture_name . ' ' . $term->name;
            }
            
            $results[] = [
                'name' => $display_name,
                'type' => 'municipality', 
                'count' => $term->count,
                'url' => get_term_link($term)
            ];
        }
    }
    
    // 結果を名前でソート
    usort($results, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
    
    // 最大15件に制限
    $results = array_slice($results, 0, 15);
    
    wp_send_json_success([
        'results' => $results,
        'total' => count($results)
    ]);
}

/**
 * NOTE: Municipality AJAX functions have been moved to inc/ajax-functions.php 
 * to avoid duplication and centralize AJAX handling
 */

/**
 * =============================================================================
 * ページテンプレートルーティング用ヘルパー関数
 * =============================================================================
 */

/**
 * ページテンプレートファイルを読み込むヘルパー関数
 * 
 * @param string $template_name テンプレートファイル名（拡張子なし）
 * @param string $fallback_title フォールバック時のタイトル
 */
function gi_load_page_template($template_name, $fallback_title = 'Page') {
    $template_path = get_template_directory() . '/pages/templates/page-' . $template_name . '.php';
    
    if (file_exists($template_path)) {
        include $template_path;
    } else {
        // フォールバック: 基本的なページテンプレート
        get_header();
        ?>
        <div class="container" style="padding: 40px 20px; max-width: 1200px; margin: 0 auto;">
            <h1><?php echo esc_html($fallback_title); ?></h1>
            <p><?php echo esc_html($fallback_title . ' template not found. Please check pages/templates/page-' . $template_name . '.php'); ?></p>
        </div>
        <?php
        get_footer();
    }
}

/**
 * Note: AJAX filter functions are defined in inc/ajax-functions.php
 * - gi_ajax_filter_category_grants()
 * - gi_ajax_filter_prefecture_grants()
 * - gi_ajax_filter_municipality_grants()
 * 
 * Note: gi_render_grant_card() function is defined in inc/card-display.php
 * We use the existing functions from those files instead of redefining them here.
 */

/**
 * Helper function: Generate pagination HTML
 * Used by AJAX handlers to create consistent pagination
 */
function gi_generate_pagination_html($max_pages, $current_page) {
    if ($max_pages <= 1) {
        return '';
    }
    
    ob_start();
    ?>
    <div class="pagination-nav">
        <?php if ($current_page > 1) : ?>
            <a href="?page=<?php echo ($current_page - 1); ?>" class="pagination-link pagination-prev" aria-label="前のページ">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                </svg>
                前へ
            </a>
        <?php endif; ?>
        
        <div class="pagination-numbers">
            <?php
            $range = 2;
            $start = max(1, $current_page - $range);
            $end = min($max_pages, $current_page + $range);
            
            if ($start > 1) {
                echo '<a href="?page=1" class="pagination-number">1</a>';
                if ($start > 2) {
                    echo '<span class="pagination-dots">...</span>';
                }
            }
            
            for ($i = $start; $i <= $end; $i++) {
                if ($i == $current_page) {
                    echo '<span class="pagination-number active" aria-current="page">' . $i . '</span>';
                } else {
                    echo '<a href="?page=' . $i . '" class="pagination-number">' . $i . '</a>';
                }
            }
            
            if ($end < $max_pages) {
                if ($end < $max_pages - 1) {
                    echo '<span class="pagination-dots">...</span>';
                }
                echo '<a href="?page=' . $max_pages . '" class="pagination-number">' . $max_pages . '</a>';
            }
            ?>
        </div>
        
        <?php if ($current_page < $max_pages) : ?>
            <a href="?page=<?php echo ($current_page + 1); ?>" class="pagination-link pagination-next" aria-label="次のページ">
                次へ
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                </svg>
            </a>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * EMERGENCY: Theme editor code temporarily disabled due to memory issues
 * Will be restored with lighter implementation after site recovery
 */

// ALL theme editor code temporarily removed to resolve memory exhaustion

// Theme editor functionality completely removed to resolve critical memory issues
// Site stability is priority - editor will be restored with safer approach later
