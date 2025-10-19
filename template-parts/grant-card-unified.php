<?php
/**
 * Grant Card Unified - Improved Design Edition v17.0
 * template-parts/grant-card-unified.php
 * 
 * グレートーン & イエローアクセント版
 * 機能はそのまま、デザインを改善
 * 
 * @package Grant_Insight_Improved
 * @version 17.0.0
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

// グローバル変数から必要データを取得
global $post, $current_view, $display_mode;

$post_id = get_the_ID();
if (!$post_id) {
    if (WP_DEBUG) {
        error_log('grant-card-unified.php: No post ID available');
    }
    return;
}

// 表示モードの判定
$display_mode = $display_mode ?? (isset($_GET['view']) ? sanitize_text_field($_GET['view']) : 'card');
$view_class = 'grant-view-' . $display_mode;

// 基本データ取得
$title = get_the_title($post_id);
$permalink = get_permalink($post_id);
$excerpt = get_the_excerpt($post_id);

// ACFフィールド取得
$grant_data = array(
    'organization' => get_field('organization', $post_id) ?: '',
    'max_amount' => get_field('max_amount', $post_id) ?: '',
    'max_amount_numeric' => intval(get_field('max_amount_numeric', $post_id)),
    'deadline' => get_field('deadline', $post_id) ?: '',
    'deadline_date' => get_field('deadline_date', $post_id) ?: '',
    'application_status' => get_field('application_status', $post_id) ?: 'open',
    'grant_target' => get_field('grant_target', $post_id) ?: '',
    'adoption_rate' => floatval(get_field('adoption_rate', $post_id)),
    'grant_difficulty' => get_field('grant_difficulty', $post_id) ?: 'normal',
    'official_url' => get_field('official_url', $post_id) ?: '',
    'is_featured' => get_field('is_featured', $post_id) ?: false,
    'ai_summary' => get_field('ai_summary', $post_id) ?: get_post_meta($post_id, 'ai_summary', true),
);

extract($grant_data);

// タクソノミーデータ
$taxonomies = array(
    'categories' => get_the_terms($post_id, 'grant_category'),
    'prefectures' => get_the_terms($post_id, 'grant_prefecture'),
);

$main_category = ($taxonomies['categories'] && !is_wp_error($taxonomies['categories'])) ? $taxonomies['categories'][0]->name : '';

// 地域表示
$region_display = '全国';
if ($taxonomies['prefectures'] && !is_wp_error($taxonomies['prefectures'])) {
    $prefectures = $taxonomies['prefectures'];
    $prefecture_count = count($prefectures);
    
    if ($prefecture_count >= 47 || $prefecture_count >= 20) {
        $region_display = '全国';
    } elseif ($prefecture_count > 3) {
        $region_display = $prefecture_count . '都道府県';
    } elseif ($prefecture_count > 1) {
        $region_names = array_map(function($p) { return $p->name; }, array_slice($prefectures, 0, 2));
        $region_display = implode('・', $region_names);
        if ($prefecture_count > 2) {
            $region_display .= '他' . ($prefecture_count - 2);
        }
    } else {
        $region_display = $prefectures[0]->name;
    }
}

// 金額フォーマット
$formatted_amount = '';
if ($max_amount_numeric > 0) {
    if ($max_amount_numeric >= 100000000) {
        $formatted_amount = number_format($max_amount_numeric / 100000000, 1) . '億円';
    } elseif ($max_amount_numeric >= 10000) {
        $formatted_amount = number_format($max_amount_numeric / 10000) . '万円';
    } else {
        $formatted_amount = number_format($max_amount_numeric) . '円';
    }
} elseif ($max_amount) {
    $formatted_amount = $max_amount;
}

// ステータス表示
$status_labels = array(
    'open' => '募集中',
    'closed' => '終了',
    'planned' => '予定',
);
$status_display = $status_labels[$application_status] ?? '募集中';

// 締切日情報
$deadline_info = array();
$deadline_timestamp = 0;
$days_remaining = 0;

if ($deadline_date) {
    $deadline_timestamp = strtotime($deadline_date);
    if ($deadline_timestamp && $deadline_timestamp > 0) {
        $current_time = current_time('timestamp');
        $days_remaining = ceil(($deadline_timestamp - $current_time) / (60 * 60 * 24));
        
        if ($days_remaining <= 0) {
            $deadline_info = array('class' => 'expired', 'text' => '終了');
        } elseif ($days_remaining <= 7) {
            $deadline_info = array('class' => 'critical', 'text' => '残り'.$days_remaining.'日');
        } elseif ($days_remaining <= 30) {
            $deadline_info = array('class' => 'warning', 'text' => '残り'.$days_remaining.'日');
        } else {
            $deadline_info = array('class' => 'normal', 'text' => date('n/j', $deadline_timestamp));
        }
    }
} elseif ($deadline) {
    $deadline_info = array('class' => 'normal', 'text' => $deadline);
}

// 難易度設定
$difficulty_configs = array(
    'easy' => array('label' => '易', 'icon' => '●'),
    'normal' => array('label' => '中', 'icon' => '●●'),
    'hard' => array('label' => '難', 'icon' => '●●●'),
);
$difficulty_data = $difficulty_configs[$grant_difficulty] ?? $difficulty_configs['normal'];

$assets_loaded = false;
?>

<?php if (!$assets_loaded): ?>

<style>
/* ============================================
   🎨 Improved Design System - グレー & イエロー
============================================ */

:root {
    /* カラーパレット - 改善版 */
    --gi-black: #1a1a1a;
    --gi-white: #ffffff;
    --gi-yellow: #ffeb3b;
    --gi-yellow-dark: #ffc107;
    
    /* グレースケール - 目に優しい */
    --gi-gray-50: #fafafa;
    --gi-gray-100: #f5f5f5;
    --gi-gray-200: #eeeeee;
    --gi-gray-300: #e0e0e0;
    --gi-gray-400: #bdbdbd;
    --gi-gray-500: #9e9e9e;
    --gi-gray-600: #757575;
    --gi-gray-700: #616161;
    --gi-gray-800: #424242;
    --gi-gray-900: #212121;
    
    /* シャドウ */
    --gi-shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.08);
    --gi-shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
    --gi-shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
    --gi-shadow-xl: 0 20px 25px rgba(0, 0, 0, 0.15);
    
    /* ボーダー */
    --gi-border: 1px solid var(--gi-gray-300);
    --gi-border-hover: 1px solid var(--gi-gray-500);
    
    /* トランジション */
    --gi-transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Grid Container */
.grants-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(min(100%, 280px), 1fr));
    gap: 1.25rem;
    padding: 1.25rem;
    background: var(--gi-gray-50);
}

/* ============================================
   📦 Card Structure - 改善版
============================================ */
.grant-view-card .grant-card-unified {
    position: relative;
    width: 100%;
    background: var(--gi-white);
    border: var(--gi-border);
    border-radius: 12px;
    overflow: hidden;
    transition: var(--gi-transition);
    display: flex;
    flex-direction: column;
    box-shadow: var(--gi-shadow-sm);
}

.grant-view-card .grant-card-unified:hover {
    transform: translateY(-3px);
    box-shadow: var(--gi-shadow-md);
    border-color: var(--gi-gray-400);
}

/* ============================================
   🎯 Header - グレー & イエロー
============================================ */
.grant-compact-header {
    background: var(--gi-gray-800);
    padding: 0.625rem 0.875rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
}

.grant-compact-header.status--closed {
    background: var(--gi-gray-600);
}

.grant-compact-header.status--urgent {
    background: var(--gi-gray-900);
}

.grant-status-compact {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--gi-white);
    font-size: 0.6875rem;
    font-weight: 700;
}

.grant-status-dot {
    width: 7px;
    height: 7px;
    background: var(--gi-yellow);
    border-radius: 50%;
    box-shadow: 0 0 4px var(--gi-yellow);
}

.grant-deadline-compact {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.625rem;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 6px;
    color: var(--gi-white);
    font-size: 0.6875rem;
    font-weight: 700;
}

.grant-deadline-compact.critical {
    background: var(--gi-yellow);
    color: var(--gi-black);
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.9; transform: scale(1.02); }
}

/* ============================================
   📝 Content - 改善版
============================================ */
.grant-card-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 1rem;
    gap: 0.75rem;
}

/* ============================================
   🏷️ Category & Title - イエローアクセント
============================================ */
.grant-category-compact {
    display: inline-flex;
    align-items: center;
    padding: 0.375rem 0.625rem;
    background: var(--gi-yellow);
    color: var(--gi-black);
    border-radius: 6px;
    font-size: 0.6875rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    align-self: flex-start;
    box-shadow: 0 2px 4px rgba(255, 235, 59, 0.3);
}

.grant-title-compact {
    font-size: 0.9375rem;
    font-weight: 800;
    line-height: 1.4;
    color: var(--gi-gray-900);
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.grant-title-compact a {
    color: inherit;
    text-decoration: none;
    transition: var(--gi-transition);
}

.grant-title-compact a:hover {
    color: var(--gi-gray-700);
}

/* ============================================
   📄 Summary
============================================ */
.grant-summary-compact {
    font-size: 0.75rem;
    line-height: 1.5;
    color: var(--gi-gray-600);
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* ============================================
   📊 Info Grid - グレー背景
============================================ */
.grant-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
}

.grant-info-item {
    padding: 0.625rem;
    background: var(--gi-gray-100);
    border: 1px solid var(--gi-gray-200);
    border-radius: 8px;
    text-align: center;
    transition: var(--gi-transition);
}

.grant-info-item:hover {
    background: var(--gi-gray-200);
    border-color: var(--gi-gray-300);
}

.grant-info-label {
    display: block;
    font-size: 0.625rem;
    font-weight: 700;
    color: var(--gi-gray-600);
    margin-bottom: 0.25rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.grant-info-value {
    display: block;
    font-size: 0.8125rem;
    font-weight: 800;
    color: var(--gi-gray-900);
}

/* ============================================
   📋 Detail Items
============================================ */
.grant-details-compact {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding: 0.75rem;
    background: var(--gi-gray-50);
    border-radius: 8px;
    border: 1px solid var(--gi-gray-200);
}

.grant-detail-item {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    font-size: 0.75rem;
    line-height: 1.4;
}

.grant-detail-icon {
    width: 1rem;
    height: 1rem;
    flex-shrink: 0;
    stroke: var(--gi-gray-600);
    stroke-width: 2.5;
    margin-top: 0.125rem;
}

.grant-detail-label {
    font-weight: 700;
    color: var(--gi-gray-700);
    min-width: 3rem;
}

.grant-detail-text {
    color: var(--gi-gray-800);
    flex: 1;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* ============================================
   🎬 Actions - 改善版
============================================ */
.grant-actions-compact {
    display: grid;
    grid-template-columns: 1fr auto auto;
    gap: 0.5rem;
    padding: 0.875rem;
    background: var(--gi-gray-50);
    border-top: 1px solid var(--gi-gray-200);
}

/* ============================================
   🔘 Buttons - グレー & イエロー
============================================ */
.grant-btn-compact {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.375rem;
    padding: 0.625rem 0.875rem;
    min-height: 38px;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 700;
    cursor: pointer;
    transition: var(--gi-transition);
    text-decoration: none;
    white-space: nowrap;
}

.grant-btn-compact--primary {
    background: var(--gi-gray-900);
    color: var(--gi-white);
    border: 1px solid var(--gi-gray-900);
}

.grant-btn-compact--primary:hover {
    background: var(--gi-gray-800);
    transform: translateY(-2px);
    box-shadow: var(--gi-shadow-sm);
}

.grant-btn-compact--secondary {
    background: var(--gi-white);
    color: var(--gi-gray-700);
    border: 1px solid var(--gi-gray-300);
    min-width: 38px;
    padding: 0.625rem;
}

.grant-btn-compact--secondary:hover {
    background: var(--gi-gray-100);
    border-color: var(--gi-gray-400);
}

.grant-btn-compact--ai {
    background: var(--gi-yellow);
    color: var(--gi-black);
    border: 1px solid var(--gi-yellow);
    min-width: 38px;
    padding: 0.625rem;
    box-shadow: 0 2px 4px rgba(255, 235, 59, 0.3);
}

.grant-btn-compact--ai:hover {
    background: var(--gi-yellow-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(255, 235, 59, 0.4);
}

.grant-icon-compact {
    width: 1rem;
    height: 1rem;
    stroke-width: 2.5;
}

/* ============================================
   🤖 AI Modal - 改善版
============================================ */
.grant-ai-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    animation: fadeIn 0.3s ease forwards;
}

@keyframes fadeIn {
    to { opacity: 1; }
}

.grant-ai-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(4px);
}

.grant-ai-modal-container {
    position: relative;
    width: 90%;
    max-width: 550px;
    height: 75vh;
    max-height: 650px;
    background: var(--gi-white);
    border: 2px solid var(--gi-gray-300);
    border-radius: 16px;
    box-shadow: var(--gi-shadow-xl);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.grant-ai-modal-header {
    padding: 1.25rem;
    background: var(--gi-gray-900);
    color: var(--gi-white);
    position: relative;
}

.grant-ai-modal-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
}

.grant-ai-modal-title-icon {
    width: 1.25rem;
    height: 1.25rem;
    stroke-width: 2.5;
}

.grant-ai-modal-subtitle {
    font-size: 0.8125rem;
    opacity: 0.9;
    max-width: 85%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.grant-ai-modal-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 2rem;
    height: 2rem;
    border: 1px solid rgba(255, 255, 255, 0.3);
    background: transparent;
    color: var(--gi-white);
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--gi-transition);
}

.grant-ai-modal-close:hover {
    background: var(--gi-yellow);
    color: var(--gi-black);
    border-color: var(--gi-yellow);
    transform: rotate(90deg);
}

.grant-ai-modal-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

/* ============================================
   💬 Chat Messages - 改善版
============================================ */
.grant-ai-chat-messages {
    flex: 1;
    padding: 1.25rem;
    overflow-y: auto;
    background: var(--gi-gray-50);
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.grant-ai-chat-messages::-webkit-scrollbar {
    width: 6px;
}

.grant-ai-chat-messages::-webkit-scrollbar-track {
    background: var(--gi-gray-200);
    border-radius: 3px;
}

.grant-ai-chat-messages::-webkit-scrollbar-thumb {
    background: var(--gi-gray-400);
    border-radius: 3px;
}

.grant-ai-chat-messages::-webkit-scrollbar-thumb:hover {
    background: var(--gi-gray-500);
}

.grant-ai-message {
    display: flex;
    gap: 0.75rem;
    max-width: 85%;
    animation: messageSlideIn 0.3s ease;
}

@keyframes messageSlideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.grant-ai-message--assistant {
    align-self: flex-start;
}

.grant-ai-message--user {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.grant-ai-message--error {
    align-self: flex-start;
}

.grant-ai-message-avatar {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border: 1px solid var(--gi-gray-300);
}

.grant-ai-message--assistant .grant-ai-message-avatar {
    background: var(--gi-gray-800);
    color: var(--gi-white);
    border-color: var(--gi-gray-800);
}

.grant-ai-message--user .grant-ai-message-avatar {
    background: var(--gi-yellow);
    color: var(--gi-black);
    border-color: var(--gi-yellow);
}

.grant-ai-message--error .grant-ai-message-avatar {
    background: var(--gi-gray-600);
    color: var(--gi-white);
    border-color: var(--gi-gray-600);
}

.grant-ai-message-content {
    background: var(--gi-white);
    padding: 0.875rem;
    border-radius: 10px;
    border: 1px solid var(--gi-gray-300);
    font-size: 0.875rem;
    line-height: 1.6;
    box-shadow: var(--gi-shadow-sm);
}

.grant-ai-message--user .grant-ai-message-content {
    background: var(--gi-gray-800);
    color: var(--gi-white);
    border-color: var(--gi-gray-800);
}

.grant-ai-message--error .grant-ai-message-content {
    background: var(--gi-gray-100);
    border-color: var(--gi-gray-400);
    color: var(--gi-gray-800);
}

/* ============================================
   ⌨️ Input Area - 改善版
============================================ */
.grant-ai-chat-input-container {
    padding: 1.25rem;
    background: var(--gi-white);
    border-top: 1px solid var(--gi-gray-300);
}

.grant-ai-chat-input-wrapper {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 0.875rem;
}

.grant-ai-chat-input {
    flex: 1;
    padding: 0.875rem;
    border: 1px solid var(--gi-gray-300);
    border-radius: 10px;
    font-family: inherit;
    font-size: 0.875rem;
    line-height: 1.5;
    resize: none;
    transition: var(--gi-transition);
    min-height: 3rem;
    max-height: 6rem;
    background: var(--gi-gray-50);
}

.grant-ai-chat-input:focus {
    outline: none;
    border-color: var(--gi-gray-500);
    background: var(--gi-white);
    box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.05);
}

.grant-ai-chat-send {
    width: 3rem;
    height: 3rem;
    background: var(--gi-yellow);
    color: var(--gi-black);
    border: 1px solid var(--gi-yellow);
    border-radius: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--gi-transition);
    flex-shrink: 0;
    box-shadow: 0 2px 4px rgba(255, 235, 59, 0.3);
}

.grant-ai-chat-send:hover:not(:disabled) {
    background: var(--gi-yellow-dark);
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(255, 235, 59, 0.4);
}

.grant-ai-chat-send:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* ============================================
   💡 Suggestions - 改善版
============================================ */
.grant-ai-chat-suggestions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.grant-ai-suggestion {
    padding: 0.5rem 0.875rem;
    background: var(--gi-white);
    border: 1px solid var(--gi-gray-300);
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--gi-gray-700);
    cursor: pointer;
    transition: var(--gi-transition);
    white-space: nowrap;
}

.grant-ai-suggestion:hover {
    background: var(--gi-yellow);
    color: var(--gi-black);
    border-color: var(--gi-yellow);
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(255, 235, 59, 0.3);
}

/* ============================================
   ⏳ Loading Animation
============================================ */
.grant-ai-typing {
    display: flex;
    gap: 4px;
    padding: 0.375rem 0;
}

.grant-ai-typing span {
    width: 7px;
    height: 7px;
    background: var(--gi-gray-500);
    border-radius: 50%;
    animation: typing 1.4s infinite ease-in-out;
}

.grant-ai-typing span:nth-child(1) { animation-delay: 0s; }
.grant-ai-typing span:nth-child(2) { animation-delay: 0.2s; }
.grant-ai-typing span:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing {
    0%, 80%, 100% { 
        transform: scale(0.8); 
        opacity: 0.5; 
    }
    40% { 
        transform: scale(1); 
        opacity: 1; 
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* ============================================
   📱 Responsive Design
============================================ */
@media (max-width: 768px) {
    .grants-grid {
        grid-template-columns: 1fr;
        padding: 1rem;
        gap: 1rem;
    }
    
    .grant-info-grid {
        grid-template-columns: 1fr;
    }
    
    .grant-actions-compact {
        grid-template-columns: 1fr;
    }
    
    .grant-btn-compact {
        width: 100%;
    }
    
    .grant-ai-modal-container {
        width: 95%;
        height: 85vh;
        max-height: none;
    }
}

@media (min-width: 769px) and (max-width: 1024px) {
    .grants-grid {
        grid-template-columns: repeat(auto-fill, minmax(min(100%, 240px), 1fr));
    }
}

@media (min-width: 1025px) and (max-width: 1440px) {
    .grants-grid {
        grid-template-columns: repeat(auto-fill, minmax(min(100%, 260px), 1fr));
    }
}

@media (min-width: 1441px) {
    .grants-grid {
        grid-template-columns: repeat(auto-fill, minmax(min(100%, 280px), 1fr));
        padding: 1.5rem;
    }
}

/* ============================================
   ♿ Accessibility
============================================ */
.grant-btn-compact:focus,
.grant-ai-chat-send:focus,
.grant-ai-suggestion:focus,
.grant-ai-modal-close:focus {
    outline: 2px solid var(--gi-yellow);
    outline-offset: 2px;
}

/* ============================================
   🎨 Card Animations
============================================ */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.grant-card-unified {
    animation: slideIn 0.3s ease-out;
    animation-fill-mode: both;
}

.grant-card-unified:nth-child(1) { animation-delay: 0.03s; }
.grant-card-unified:nth-child(2) { animation-delay: 0.06s; }
.grant-card-unified:nth-child(3) { animation-delay: 0.09s; }
.grant-card-unified:nth-child(4) { animation-delay: 0.12s; }
.grant-card-unified:nth-child(5) { animation-delay: 0.15s; }
.grant-card-unified:nth-child(6) { animation-delay: 0.18s; }
.grant-card-unified:nth-child(7) { animation-delay: 0.21s; }
.grant-card-unified:nth-child(8) { animation-delay: 0.24s; }

/* ============================================
   🖨️ Print Styles
============================================ */
@media print {
    .grant-card-unified {
        break-inside: avoid;
        page-break-inside: avoid;
        border: 1px solid var(--gi-gray-300);
    }
    
    .grant-btn-compact,
    .grant-ai-modal {
        display: none !important;
    }
}

/* ============================================
   🎯 User Selection
============================================ */
.grant-card-unified * {
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

.grant-title-compact a,
.grant-summary-compact,
.grant-detail-text,
.grant-ai-message-content {
    -webkit-user-select: text;
    -moz-user-select: text;
    -ms-user-select: text;
    user-select: text;
}
</style>

<script>
// ============================================
// AI機能 - 機能はそのまま維持
// ============================================
(function() {
    'use strict';
    
    console.log('🚀 Grant AI Chat Script Loaded v17.0');
    
    let currentEscHandler = null;
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function showAIChatModal(postId, grantTitle) {
        console.log('📱 Opening AI Chat Modal:', postId, grantTitle);
        
        const existingModal = document.querySelector('.grant-ai-modal');
        if (existingModal) {
            existingModal.remove();
        }
        
        const modalHTML = `
            <div class="grant-ai-modal" id="grant-ai-modal">
                <div class="grant-ai-modal-overlay"></div>
                <div class="grant-ai-modal-container">
                    <div class="grant-ai-modal-header">
                        <div class="grant-ai-modal-title">
                            <svg class="grant-ai-modal-title-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                <circle cx="9" cy="10" r="1"/>
                                <circle cx="15" cy="10" r="1"/>
                            </svg>
                            <span>AI助成金アシスタント</span>
                        </div>
                        <div class="grant-ai-modal-subtitle">${escapeHtml(grantTitle)}</div>
                        <button class="grant-ai-modal-close" aria-label="閉じる">
                            <svg class="grant-icon-compact" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"/>
                                <line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>
                    <div class="grant-ai-modal-body">
                        <div class="grant-ai-chat-messages" id="ai-chat-messages-${postId}">
                            <div class="grant-ai-message grant-ai-message--assistant">
                                <div class="grant-ai-message-avatar">
                                    <svg class="grant-icon-compact" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 2v20M2 12h20"/>
                                    </svg>
                                </div>
                                <div class="grant-ai-message-content">
                                    こんにちは！この助成金について何でもお聞きください。申請条件、必要書類、申請方法、対象経費など、詳しくお答えします。
                                </div>
                            </div>
                        </div>
                        <div class="grant-ai-chat-input-container">
                            <div class="grant-ai-chat-input-wrapper">
                                <textarea 
                                    class="grant-ai-chat-input" 
                                    id="ai-chat-input-${postId}"
                                    placeholder="例：申請条件は何ですか？"
                                    rows="2"
                                    aria-label="質問を入力"></textarea>
                                <button 
                                    class="grant-ai-chat-send" 
                                    id="ai-chat-send-${postId}"
                                    aria-label="送信">
                                    <svg class="grant-icon-compact" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="22" y1="2" x2="11" y2="13"/>
                                        <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="grant-ai-chat-suggestions">
                                <button class="grant-ai-suggestion" data-question="申請条件を詳しく教えてください">
                                    申請条件は？
                                </button>
                                <button class="grant-ai-suggestion" data-question="必要な書類を教えてください">
                                    必要書類は？
                                </button>
                                <button class="grant-ai-suggestion" data-question="どんな費用が対象になりますか？">
                                    対象経費は？
                                </button>
                                <button class="grant-ai-suggestion" data-question="申請方法を教えてください">
                                    申請方法は？
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        setupModalEventListeners(postId);
        
        setTimeout(() => {
            const input = document.getElementById(`ai-chat-input-${postId}`);
            if (input) input.focus();
        }, 100);
    }
    
    function setupModalEventListeners(postId) {
        const modal = document.getElementById('grant-ai-modal');
        if (!modal) return;
        
        modal.querySelector('.grant-ai-modal-overlay')?.addEventListener('click', closeAIChatModal);
        modal.querySelector('.grant-ai-modal-close')?.addEventListener('click', closeAIChatModal);
        
        const sendBtn = document.getElementById(`ai-chat-send-${postId}`);
        if (sendBtn) {
            sendBtn.addEventListener('click', () => sendAIQuestion(postId));
        }
        
        const input = document.getElementById(`ai-chat-input-${postId}`);
        if (input) {
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendAIQuestion(postId);
                }
            });
        }
        
        modal.querySelectorAll('.grant-ai-suggestion').forEach(btn => {
            btn.addEventListener('click', function() {
                selectSuggestion(postId, this.getAttribute('data-question'));
            });
        });
        
        currentEscHandler = (e) => {
            if (e.key === 'Escape') closeAIChatModal();
        };
        document.addEventListener('keydown', currentEscHandler);
    }
    
    function closeAIChatModal() {
        const modal = document.querySelector('.grant-ai-modal');
        if (modal) {
            modal.style.opacity = '0';
            setTimeout(() => {
                modal.remove();
                if (currentEscHandler) {
                    document.removeEventListener('keydown', currentEscHandler);
                    currentEscHandler = null;
                }
            }, 300);
        }
    }
    
    function selectSuggestion(postId, question) {
        const input = document.getElementById(`ai-chat-input-${postId}`);
        if (input) {
            input.value = question;
            input.focus();
            setTimeout(() => sendAIQuestion(postId), 300);
        }
    }
    
    function sendAIQuestion(postId) {
        const input = document.getElementById(`ai-chat-input-${postId}`);
        const sendBtn = document.getElementById(`ai-chat-send-${postId}`);
        const messagesContainer = document.getElementById(`ai-chat-messages-${postId}`);
        
        if (!input || !messagesContainer) return;
        
        const question = input.value.trim();
        if (!question) return;
        
        if (sendBtn) {
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<svg class="grant-icon-compact animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>';
        }
        
        const userMessage = document.createElement('div');
        userMessage.className = 'grant-ai-message grant-ai-message--user';
        userMessage.innerHTML = `
            <div class="grant-ai-message-avatar">
                <svg class="grant-icon-compact" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <div class="grant-ai-message-content">${escapeHtml(question)}</div>
        `;
        messagesContainer.appendChild(userMessage);
        input.value = '';
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        
        const formData = new FormData();
        formData.append('action', 'handle_grant_ai_question');
        formData.append('post_id', postId);
        formData.append('question', question);
        formData.append('nonce', '<?php echo wp_create_nonce('gi_ajax_nonce'); ?>');
        
        const ajaxUrl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
        
        fetch(ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const loadingMessage = document.createElement('div');
            loadingMessage.className = 'grant-ai-message grant-ai-message--assistant';
            loadingMessage.innerHTML = `
                <div class="grant-ai-message-avatar">
                    <svg class="grant-icon-compact animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                    </svg>
                </div>
                <div class="grant-ai-message-content">
                    <div class="grant-ai-typing">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            `;
            messagesContainer.appendChild(loadingMessage);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            
            setTimeout(() => {
                loadingMessage.remove();
                
                const assistantMessage = document.createElement('div');
                assistantMessage.className = data.success ? 'grant-ai-message grant-ai-message--assistant' : 'grant-ai-message grant-ai-message--error';
                assistantMessage.innerHTML = `
                    <div class="grant-ai-message-avatar">
                        <svg class="grant-icon-compact" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            ${data.success ? '<path d="M12 2v20M2 12h20"/>' : '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>'}
                        </svg>
                    </div>
                    <div class="grant-ai-message-content">${data.success ? escapeHtml(data.data.response) : '申し訳ございません。エラーが発生しました。'}</div>
                `;
                messagesContainer.appendChild(assistantMessage);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }, 1500);
        })
        .catch(error => {
            console.error('Error:', error);
            const errorMessage = document.createElement('div');
            errorMessage.className = 'grant-ai-message grant-ai-message--error';
            errorMessage.innerHTML = `
                <div class="grant-ai-message-avatar">
                    <svg class="grant-icon-compact" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                </div>
                <div class="grant-ai-message-content">エラーが発生しました。</div>
            `;
            messagesContainer.appendChild(errorMessage);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        })
        .finally(() => {
            if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<svg class="grant-icon-compact" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>';
            }
            input.focus();
        });
    }
    
    document.addEventListener('click', function(e) {
        const aiBtn = e.target.closest('.grant-btn-compact--ai');
        if (aiBtn) {
            e.preventDefault();
            e.stopPropagation();
            const postId = aiBtn.getAttribute('data-post-id');
            const grantTitle = aiBtn.getAttribute('data-grant-title');
            if (postId && grantTitle) {
                showAIChatModal(postId, grantTitle);
            }
        }
    });
    
})();
</script>

<?php endif; ?>

<!-- Card HTML - 機能はそのまま -->
<article class="grant-card-unified <?php echo esc_attr($view_class); ?>" 
         data-post-id="<?php echo esc_attr($post_id); ?>"
         role="article"
         aria-label="<?php echo esc_attr($title); ?>">
    
    <header class="grant-compact-header <?php echo $application_status === 'closed' ? 'status--closed' : ''; ?> <?php echo !empty($deadline_info) && $deadline_info['class'] === 'critical' ? 'status--urgent' : ''; ?>">
        <div class="grant-status-compact">
            <span class="grant-status-dot"></span>
            <span><?php echo esc_html($status_display); ?></span>
        </div>
        <?php if (!empty($deadline_info)): ?>
        <div class="grant-deadline-compact <?php echo esc_attr($deadline_info['class']); ?>">
            <span><?php echo esc_html($deadline_info['text']); ?></span>
        </div>
        <?php endif; ?>
    </header>
    
    <div class="grant-card-content">
        <?php if ($main_category): ?>
        <span class="grant-category-compact"><?php echo esc_html($main_category); ?></span>
        <?php endif; ?>
        
        <h3 class="grant-title-compact">
            <a href="<?php echo esc_url($permalink); ?>" aria-label="<?php echo esc_attr($title); ?>の詳細">
                <?php echo esc_html($title); ?>
            </a>
        </h3>
        
        <?php if ($ai_summary || $excerpt): ?>
        <p class="grant-summary-compact">
            <?php echo esc_html(wp_trim_words($ai_summary ?: $excerpt, 20, '...')); ?>
        </p>
        <?php endif; ?>
        
        <div class="grant-info-grid">
            <div class="grant-info-item">
                <span class="grant-info-label">助成額</span>
                <span class="grant-info-value"><?php echo $formatted_amount ? esc_html($formatted_amount) : '要確認'; ?></span>
            </div>
            <div class="grant-info-item">
                <span class="grant-info-label">地域</span>
                <span class="grant-info-value"><?php echo esc_html($region_display); ?></span>
            </div>
        </div>
        
        <div class="grant-details-compact">
            <?php if ($organization): ?>
            <div class="grant-detail-item">
                <svg class="grant-detail-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                </svg>
                <span class="grant-detail-label">実施:</span>
                <span class="grant-detail-text"><?php echo esc_html(wp_trim_words($organization, 8, '...')); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($grant_target): ?>
            <div class="grant-detail-item">
                <svg class="grant-detail-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                </svg>
                <span class="grant-detail-label">対象:</span>
                <span class="grant-detail-text"><?php echo esc_html(wp_trim_words($grant_target, 12, '...')); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($grant_difficulty): ?>
            <div class="grant-detail-item">
                <svg class="grant-detail-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                </svg>
                <span class="grant-detail-label">難易度:</span>
                <span class="grant-detail-text"><?php echo esc_html($difficulty_data['label']); ?> <?php echo esc_html($difficulty_data['icon']); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <footer class="grant-actions-compact">
        <a href="<?php echo esc_url($permalink); ?>" class="grant-btn-compact grant-btn-compact--primary" role="button" aria-label="<?php echo esc_attr($title); ?>の詳細を見る">
            <svg class="grant-icon-compact" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M9 18l6-6-6-6"/>
            </svg>
            <span>詳細</span>
        </a>
        
        <button class="grant-btn-compact grant-btn-compact--ai" 
                data-post-id="<?php echo esc_attr($post_id); ?>" 
                data-grant-title="<?php echo esc_attr($title); ?>"
                type="button"
                role="button"
                aria-label="AIに質問する"
                title="AIに質問">
            <svg class="grant-icon-compact" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
        </button>
        
        <?php if ($official_url): ?>
        <a href="<?php echo esc_url($official_url); ?>" 
           class="grant-btn-compact grant-btn-compact--secondary" 
           target="_blank" 
           rel="noopener noreferrer" 
           role="button" 
           aria-label="公式サイトを開く"
           title="公式サイト">
            <svg class="grant-icon-compact" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                <polyline points="15 3 21 3 21 9"/>
                <line x1="10" y1="14" x2="21" y2="3"/>
            </svg>
        </a>
        <?php endif; ?>
    </footer>
</article>