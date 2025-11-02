<?php
/**
 * Grant Card Unified - Error Fixed Complete Edition v20.1
 * template-parts/grant-card-unified.php
 * 
 * ✅ 構文エラー完全修正
 * ✅ AI Search Section完全一致デザイン
 * ✅ AI要約表示完全実装
 * ✅ AIモーダル完全実装
 * 
 * @package Grant_Insight_Perfect
 * @version 20.1.0
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
?>

<style>
/* ============================================
   🎨 AI Search Section完全一致デザイン v20.1
   grant-card-unified.php専用CSS
============================================ */

.grants-grid,
.grant-card-unified,
.grant-ai-modal {
    --gi-color-primary: #000000;
    --gi-color-secondary: #333333;
    --gi-color-tertiary: #666666;
    --gi-color-accent: #FFEB3B;
    --gi-color-background: #FFFFFF;
    --gi-color-surface: #FAFAFA;
    --gi-color-border: #000000;
    --gi-color-text: #000000;
    --gi-color-text-muted: #666666;
    --gi-color-text-light: #999999;
    --gi-spacing-xs: 3px;
    --gi-spacing-sm: 6px;
    --gi-spacing-md: 12px;
    --gi-spacing-lg: 18px;
    --gi-spacing-xl: 24px;
    --gi-font-size-xs: 9px;
    --gi-font-size-sm: 10px;
    --gi-font-size-base: 12px;
    --gi-font-size-md: 14px;
    --gi-font-size-lg: 16px;
    --gi-radius-sm: 4px;
    --gi-radius-md: 8px;
    --gi-radius-lg: 12px;
    --gi-radius-full: 9999px;
    --gi-shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
    --gi-shadow-md: 0 4px 8px rgba(0, 0, 0, 0.08);
    --gi-shadow-lg: 0 8px 16px rgba(0, 0, 0, 0.1);
    --gi-shadow-xl: 0 12px 24px rgba(0, 0, 0, 0.12);
    --gi-shadow-2xl: 0 20px 40px rgba(0, 0, 0, 0.15);
    --gi-transition-fast: 0.15s ease;
    --gi-transition-base: 0.3s ease;
}

.grants-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: var(--gi-spacing-lg);
    padding: 0;
    background: transparent;
}

.grant-card-unified {
    position: relative;
    background: var(--gi-color-background);
    padding: var(--gi-spacing-lg);
    border: 3px solid #000000 !important;
    transition: all var(--gi-transition-base);
    cursor: pointer;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.grant-card-unified::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: var(--gi-color-accent);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform var(--gi-transition-base);
}

.grant-card-unified:hover::before {
    transform: scaleX(1);
}

.grant-card-unified::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, transparent 0%, rgba(0,0,0,0.02) 100%);
    opacity: 0;
    transition: opacity var(--gi-transition-base);
    pointer-events: none;
}

.grant-card-unified:hover::after {
    opacity: 1;
}

.grant-card-unified:hover {
    transform: translateY(-8px);
    box-shadow: var(--gi-shadow-2xl);
    border-color: #333333 !important;
}

.card-badge {
    position: absolute;
    top: 0;
    right: 0;
    padding: var(--gi-spacing-sm) var(--gi-spacing-md);
    background: var(--gi-color-primary);
    color: var(--gi-color-background);
    font-size: var(--gi-font-size-xs);
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    z-index: 10;
}

.grant-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: var(--gi-spacing-sm);
    margin-bottom: var(--gi-spacing-md);
}

.grant-card-title {
    font-size: 15px;
    font-weight: 700;
    line-height: 1.5;
    letter-spacing: 0.02em;
    margin: 0;
    flex: 1;
    color: var(--gi-color-text);
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    position: relative;
}

.grant-card-title a {
    color: inherit;
    text-decoration: none;
    transition: color var(--gi-transition-fast);
    display: block;
    padding: 2px 0;
}

.grant-card-title a:hover {
    color: var(--gi-color-secondary);
}

.card-bookmark {
    width: 32px;
    height: 32px;
    border: 2px solid var(--gi-color-border);
    background: var(--gi-color-background);
    border-radius: var(--gi-radius-sm);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--gi-transition-fast);
    flex-shrink: 0;
}

.card-bookmark:hover {
    border-color: var(--gi-color-accent);
    background: var(--gi-color-accent);
    color: var(--gi-color-primary);
}

.card-ai-summary {
    background: linear-gradient(135deg, #fffbea 0%, #fff9e6 100%);
    border: 2px solid var(--gi-color-accent);
    border-radius: var(--gi-radius-md);
    padding: var(--gi-spacing-lg) var(--gi-spacing-md) var(--gi-spacing-md);
    margin-bottom: var(--gi-spacing-md);
    position: relative;
    box-shadow: 0 2px 8px rgba(255, 235, 59, 0.2);
}

.card-ai-summary::before {
    content: 'AI要約';
    position: absolute;
    top: -10px;
    left: var(--gi-spacing-md);
    background: var(--gi-color-accent);
    color: var(--gi-color-primary);
    padding: 4px 12px;
    font-size: var(--gi-font-size-xs);
    font-weight: 800;
    border-radius: var(--gi-radius-sm);
    letter-spacing: 0.1em;
    text-transform: uppercase;
    box-shadow: 0 2px 4px rgba(255, 235, 59, 0.4);
}

.card-ai-summary-text {
    font-size: var(--gi-font-size-base);
    line-height: 1.7;
    color: var(--gi-color-secondary);
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    font-weight: 500;
}

.card-meta {
    display: flex;
    gap: var(--gi-spacing-md);
    margin-bottom: var(--gi-spacing-md);
}

.meta-item {
    display: flex;
    flex-direction: column;
    gap: var(--gi-spacing-xs);
}

.meta-item svg {
    width: 14px;
    height: 14px;
    stroke: currentColor;
    stroke-width: 2;
    margin-bottom: var(--gi-spacing-xs);
}

.meta-label {
    font-size: var(--gi-font-size-xs);
    color: var(--gi-color-text-light);
}

.meta-value {
    font-size: var(--gi-font-size-sm);
    font-weight: 700;
    color: var(--gi-color-primary);
}

.card-org {
    font-size: var(--gi-font-size-xs);
    color: var(--gi-color-tertiary);
    margin: 0 0 var(--gi-spacing-md);
    display: flex;
    align-items: center;
    gap: var(--gi-spacing-xs);
}

.card-org svg {
    width: 12px;
    height: 12px;
    stroke: currentColor;
    stroke-width: 1.5;
}

.card-rate {
    margin-bottom: var(--gi-spacing-md);
}

.rate-bar {
    height: 4px;
    background: var(--gi-color-border);
    border-radius: var(--gi-radius-sm);
    overflow: hidden;
    margin-bottom: var(--gi-spacing-xs);
}

.rate-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981, #34d399);
    transition: width 1s ease-out;
}

.rate-text {
    font-size: var(--gi-font-size-xs);
    color: var(--gi-color-tertiary);
    display: flex;
    align-items: center;
    gap: var(--gi-spacing-xs);
}

.rate-text svg {
    width: 12px;
    height: 12px;
    stroke: currentColor;
    stroke-width: 1.5;
}

.card-actions {
    display: flex;
    align-items: center;
    gap: var(--gi-spacing-md);
    margin-top: auto;
    pointer-events: auto !important;
    position: relative;
    z-index: 10;
}

.ai-assist-btn {
    padding: var(--gi-spacing-sm) var(--gi-spacing-md);
    background: transparent;
    border: 2px solid var(--gi-color-primary);
    color: var(--gi-color-primary);
    font-size: var(--gi-font-size-xs);
    font-weight: 600;
    border-radius: var(--gi-radius-full);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all var(--gi-transition-base);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    pointer-events: auto !important;
    position: relative;
    z-index: 20;
}

.ai-assist-btn:hover {
    background: var(--gi-color-primary);
    color: var(--gi-color-background);
    transform: translateY(-1px);
    box-shadow: var(--gi-shadow-md);
}

.ai-assist-btn svg {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    stroke-width: 2;
}

.card-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: var(--gi-font-size-sm);
    font-weight: 600;
    color: var(--gi-color-primary);
    text-decoration: none;
    transition: all var(--gi-transition-fast);
    pointer-events: auto !important;
    position: relative;
    z-index: 20;
}

.card-link:hover {
    gap: 10px;
    color: var(--gi-color-accent);
}

.card-link svg {
    width: 14px;
    height: 14px;
    stroke: currentColor;
    stroke-width: 2;
}

.grant-ai-modal {
    position: fixed;
    inset: 0;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.grant-ai-modal-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(4px);
}

.grant-ai-modal-container {
    position: relative;
    width: 90vw;
    max-width: 600px;
    height: auto;
    max-height: 85vh;
    background: var(--gi-color-background);
    border-radius: 20px;
    box-shadow: var(--gi-shadow-2xl);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transform: scale(0.9);
    transition: transform 0.3s ease;
}

.grant-ai-modal-header {
    padding: var(--gi-spacing-lg);
    border-bottom: 2px solid #000000;
    display: flex;
    align-items: center;
    gap: var(--gi-spacing-md);
    position: relative;
    background: var(--gi-color-surface);
}

.assistant-avatar {
    position: relative;
    width: 48px;
    height: 48px;
    flex-shrink: 0;
}

.avatar-ring {
    position: absolute;
    inset: 0;
    border: 2px solid var(--gi-color-primary);
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.05); }
}

.avatar-icon {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--gi-color-primary);
    color: var(--gi-color-background);
    border-radius: 50%;
    font-size: var(--gi-font-size-sm);
    font-weight: 700;
}

.assistant-info {
    flex: 1;
    min-width: 0;
}

.assistant-name {
    font-size: var(--gi-font-size-sm);
    font-weight: 600;
    margin: 0 0 var(--gi-spacing-xs);
    color: var(--gi-color-text);
}

.assistant-status {
    font-size: var(--gi-font-size-xs);
    color: #10b981;
    display: flex;
    align-items: center;
    gap: var(--gi-spacing-xs);
}

.status-dot {
    width: 6px;
    height: 6px;
    background: #10b981;
    border-radius: 50%;
    animation: blink 2s infinite;
}

@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}

.grant-ai-modal-close {
    margin-left: auto;
    width: 32px;
    height: 32px;
    border: 2px solid var(--gi-color-primary);
    background: var(--gi-color-background);
    color: var(--gi-color-primary);
    border-radius: var(--gi-radius-sm);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--gi-transition-base);
}

.grant-ai-modal-close:hover {
    background: var(--gi-color-primary);
    color: var(--gi-color-background);
}

.grant-ai-modal-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.grant-ai-chat-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background: var(--gi-color-surface);
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.grant-ai-chat-messages::-webkit-scrollbar {
    width: 6px;
}

.grant-ai-chat-messages::-webkit-scrollbar-track {
    background: #e0e0e0;
    border-radius: 3px;
}

.grant-ai-chat-messages::-webkit-scrollbar-thumb {
    background: #9e9e9e;
    border-radius: 3px;
}

.grant-ai-message {
    display: flex;
    gap: 12px;
    max-width: 85%;
    animation: messageSlideIn 0.3s ease;
}

@keyframes messageSlideIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.grant-ai-message--assistant {
    align-self: flex-start;
}

.grant-ai-message--user {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.grant-ai-message-avatar {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border: 1px solid var(--gi-color-border);
}

.grant-ai-message--assistant .grant-ai-message-avatar {
    background: var(--gi-color-primary);
    color: var(--gi-color-background);
    border-color: var(--gi-color-primary);
}

.grant-ai-message--user .grant-ai-message-avatar {
    background: var(--gi-color-accent);
    color: var(--gi-color-primary);
    border-color: var(--gi-color-accent);
}

.grant-ai-message-content {
    background: var(--gi-color-background);
    padding: 14px 16px;
    border-radius: 10px;
    border: 1px solid var(--gi-color-border);
    font-size: 14px;
    line-height: 1.6;
    box-shadow: var(--gi-shadow-sm);
}

.grant-ai-message--user .grant-ai-message-content {
    background: var(--gi-color-primary);
    color: var(--gi-color-background);
    border-color: var(--gi-color-primary);
}

.grant-ai-chat-input-container {
    padding: var(--gi-spacing-md);
    border-top: 1px solid var(--gi-color-border);
    position: relative;
    background: var(--gi-color-background);
}

.typing-indicator {
    position: absolute;
    top: calc(-24px - var(--gi-spacing-sm));
    left: var(--gi-spacing-lg);
    display: none;
    gap: var(--gi-spacing-xs);
}

.typing-indicator.active {
    display: flex;
}

.typing-indicator span {
    width: 8px;
    height: 8px;
    background: var(--gi-color-text-light);
    border-radius: 50%;
    animation: typing 1.4s infinite;
}

.typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
.typing-indicator span:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-10px); }
}

.grant-ai-chat-input-wrapper {
    display: flex;
    gap: var(--gi-spacing-sm);
    align-items: flex-end;
    margin-bottom: var(--gi-spacing-md);
}

.grant-ai-chat-input {
    flex: 1;
    padding: var(--gi-spacing-md);
    background: var(--gi-color-background);
    border: 2px solid var(--gi-color-primary);
    border-radius: var(--gi-radius-sm);
    font-size: var(--gi-font-size-base);
    resize: none;
    outline: none;
    transition: all var(--gi-transition-fast);
    min-height: 44px;
    max-height: 120px;
    font-family: inherit;
}

.grant-ai-chat-input:focus {
    border-color: var(--gi-color-accent);
    box-shadow: 0 0 0 2px rgba(255, 235, 59, 0.2);
}

.grant-ai-chat-send {
    height: 44px;
    padding: 0 var(--gi-spacing-lg);
    background: var(--gi-color-primary);
    color: var(--gi-color-background);
    border: 2px solid var(--gi-color-primary);
    border-radius: var(--gi-radius-sm);
    cursor: pointer;
    transition: all var(--gi-transition-fast);
    font-weight: 600;
    font-size: var(--gi-font-size-base);
    display: flex;
    align-items: center;
    gap: var(--gi-spacing-sm);
    flex-shrink: 0;
}

.grant-ai-chat-send:hover:not(:disabled) {
    background: var(--gi-color-background);
    color: var(--gi-color-primary);
}

.grant-ai-chat-send:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-text-desktop {
    display: inline;
}

.grant-ai-chat-suggestions {
    display: flex;
    flex-wrap: wrap;
    gap: var(--gi-spacing-sm);
}

.grant-ai-suggestion {
    padding: var(--gi-spacing-sm) var(--gi-spacing-md);
    background: var(--gi-color-background);
    border: 1px solid var(--gi-color-border);
    border-radius: var(--gi-radius-full);
    font-size: var(--gi-font-size-xs);
    font-weight: 500;
    color: var(--gi-color-tertiary);
    cursor: pointer;
    transition: all var(--gi-transition-fast);
    display: flex;
    align-items: center;
    gap: 6px;
}

.grant-ai-suggestion:hover {
    background: var(--gi-color-primary);
    color: var(--gi-color-background);
    border-color: var(--gi-color-primary);
}

.grant-ai-suggestion svg {
    width: 14px;
    height: 14px;
    stroke: currentColor;
    stroke-width: 2;
}

.grant-icon-compact {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    stroke-width: 2.5;
}

.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@media (min-width: 768px) and (max-width: 1023px) {
    .grants-grid {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    }
    .grant-card-title {
        font-size: 14px;
    }
}

@media (max-width: 767px) {
    .grants-grid {
        grid-template-columns: 1fr;
        gap: var(--gi-spacing-md);
    }
    .grant-card-title {
        font-size: 13px;
    }
    .card-meta {
        flex-direction: column;
        gap: var(--gi-spacing-sm);
    }
    .card-actions {
        flex-direction: column;
        gap: var(--gi-spacing-sm);
        align-items: stretch;
    }
    .ai-assist-btn {
        justify-content: center;
    }
    .card-link {
        text-align: center;
        justify-content: center;
    }
    .grant-ai-modal-container {
        width: 100vw !important;
        height: 100vh !important;
        max-height: 100vh !important;
        border-radius: 0 !important;
        transform: translateY(100%);
    }
    .btn-text-desktop {
        display: none;
    }
    .grant-ai-chat-send {
        padding: 0 var(--gi-spacing-md);
    }
}

@media (max-width: 374px) {
    .grant-card-unified {
        padding: var(--gi-spacing-md);
    }
    .grant-card-title {
        font-size: 12px;
    }
}

@media (min-width: 1440px) {
    .grants-grid {
        grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        gap: var(--gi-spacing-xl);
    }
    .grant-card-title {
        font-size: 16px;
    }
}

.ai-assist-btn:focus,
.card-link:focus,
.card-bookmark:focus,
.grant-ai-chat-send:focus,
.grant-ai-suggestion:focus,
.grant-ai-modal-close:focus {
    outline: 2px solid var(--gi-color-accent);
    outline-offset: 2px;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
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

@media print {
    .grant-card-unified {
        break-inside: avoid;
        page-break-inside: avoid;
        border: 1px solid var(--gi-color-border);
    }
    .ai-assist-btn,
    .card-bookmark,
    .grant-ai-modal {
        display: none !important;
    }
}

.grant-card-unified * {
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

.grant-card-title a,
.card-org,
.card-ai-summary-text,
.grant-ai-message-content {
    -webkit-user-select: text;
    -moz-user-select: text;
    -ms-user-select: text;
    user-select: text;
}
</style>

<script>
(function() {
    'use strict';
    
    console.log('🚀 Grant AI Chat Script v20.1');
    
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
        
        const isMobileView = window.innerWidth < 768;
        
        const modalHTML = `
            <div class="grant-ai-modal" id="grant-ai-modal">
                <div class="grant-ai-modal-overlay"></div>
                <div class="grant-ai-modal-container">
                    <div class="grant-ai-modal-header">
                        <div class="assistant-avatar">
                            <div class="avatar-ring"></div>
                            <span class="avatar-icon">AI</span>
                        </div>
                        <div class="assistant-info">
                            <h3 class="assistant-name">補助金AIアシスタント</h3>
                            <span class="assistant-status">
                                <span class="status-dot"></span>
                                オンライン
                            </span>
                        </div>
                        <button class="grant-ai-modal-close" aria-label="閉じる">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <line x1="15" y1="5" x2="5" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <line x1="5" y1="5" x2="15" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
                    <div class="grant-ai-modal-body">
                        <div class="grant-ai-chat-messages" id="ai-chat-messages-${postId}">
                            <div class="grant-ai-message grant-ai-message--assistant">
                                <div class="grant-ai-message-avatar">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <rect x="4" y="6" width="16" height="12" rx="2" stroke="currentColor" stroke-width="2"/>
                                        <path d="M9 10h6M9 14h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        <circle cx="12" cy="3" r="1" fill="currentColor"/>
                                    </svg>
                                </div>
                                <div class="grant-ai-message-content">
                                    <p style="margin: 0 0 12px 0;">こんにちは！「<strong>${escapeHtml(grantTitle)}</strong>」について、どのようなことをお聞きしたいですか？</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="grant-ai-chat-input-container">
                            <div class="typing-indicator" id="typing-indicator-${postId}">
                                <span></span><span></span><span></span>
                            </div>
                            <div class="grant-ai-chat-input-wrapper">
                                <textarea 
                                    class="grant-ai-chat-input" 
                                    id="ai-chat-input-${postId}"
                                    placeholder="質問を入力してください"
                                    rows="1"
                                    aria-label="質問を入力"></textarea>
                                <button 
                                    class="grant-ai-chat-send" 
                                    id="ai-chat-send-${postId}"
                                    aria-label="送信">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                        <path d="M18 2L9 11M18 2l-6 16-3-7-7-3 16-6z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span class="btn-text-desktop">送信</span>
                                </button>
                            </div>
                            
                            <div class="grant-ai-chat-suggestions">
                                <button class="grant-ai-suggestion" data-question="申請の流れを教えて">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                        <path d="M1 7h12M7 1v12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    申請の流れ
                                </button>
                                <button class="grant-ai-suggestion" data-question="必要書類は？">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                        <rect x="2" y="1" width="10" height="12" rx="1" stroke="currentColor" stroke-width="2"/>
                                        <path d="M4 4h6M4 7h6M4 10h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    必要書類
                                </button>
                                <button class="grant-ai-suggestion" data-question="締切はいつ？">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                        <circle cx="7" cy="7" r="5" stroke="currentColor" stroke-width="2"/>
                                        <path d="M7 4v3l2 1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    締切確認
                                </button>
                                <button class="grant-ai-suggestion" data-question="採択率は？">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                        <path d="M1 10l3-3 3 3 5-8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    採択率
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        const modal = document.getElementById('grant-ai-modal');
        setTimeout(() => {
            modal.style.opacity = '1';
            modal.style.visibility = 'visible';
            const container = modal.querySelector('.grant-ai-modal-container');
            if (container) {
                container.style.transform = isMobileView ? 'translateY(0)' : 'scale(1)';
            }
        }, 10);
        
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
            
            input.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            });
        }
        
        modal.querySelectorAll('.grant-ai-suggestion').forEach(btn => {
            btn.addEventListener('click', function() {
                selectSuggestion(postId, this.getAttribute('data-question'));
            });
        });
        
        if (window.innerWidth < 768) {
            let startY = 0;
            let currentY = 0;
            const container = modal.querySelector('.grant-ai-modal-container');
            
            container.addEventListener('touchstart', (e) => {
                startY = e.touches[0].clientY;
            }, { passive: true });
            
            container.addEventListener('touchmove', (e) => {
                currentY = e.touches[0].clientY;
                const diff = currentY - startY;
                if (diff > 0) {
                    container.style.transform = `translateY(${diff}px)`;
                }
            }, { passive: true });
            
            container.addEventListener('touchend', () => {
                const diff = currentY - startY;
                if (diff > 100) {
                    closeAIChatModal();
                } else {
                    container.style.transform = 'translateY(0)';
                }
            }, { passive: true });
        }
        
        currentEscHandler = (e) => {
            if (e.key === 'Escape') closeAIChatModal();
        };
        document.addEventListener('keydown', currentEscHandler);
    }
    
    function closeAIChatModal() {
        const modal = document.querySelector('.grant-ai-modal');
        if (modal) {
            modal.style.opacity = '0';
            modal.style.visibility = 'hidden';
            const container = modal.querySelector('.grant-ai-modal-container');
            if (container) {
                const isMobileView = window.innerWidth < 768;
                container.style.transform = isMobileView ? 'translateY(100%)' : 'scale(0.9)';
            }
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
        const typingIndicator = document.getElementById(`typing-indicator-${postId}`);
        
        if (!input || !messagesContainer) return;
        
        const question = input.value.trim();
        if (!question) return;
        
        if (sendBtn) {
            sendBtn.disabled = true;
        }
        
        const userMessage = document.createElement('div');
        userMessage.className = 'grant-ai-message grant-ai-message--user';
        userMessage.innerHTML = `
            <div class="grant-ai-message-avatar">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2"/>
                    <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
                </svg>
            </div>
            <div class="grant-ai-message-content">${escapeHtml(question)}</div>
        `;
        messagesContainer.appendChild(userMessage);
        input.value = '';
        input.style.height = 'auto';
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        
        if (typingIndicator) {
            typingIndicator.classList.add('active');
        }
        
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
            if (typingIndicator) {
                typingIndicator.classList.remove('active');
            }
            
            const assistantMessage = document.createElement('div');
            assistantMessage.className = 'grant-ai-message grant-ai-message--assistant';
            assistantMessage.innerHTML = `
                <div class="grant-ai-message-avatar">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <rect x="4" y="6" width="16" height="12" rx="2" stroke="currentColor" stroke-width="2"/>
                        <path d="M9 10h6M9 14h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <circle cx="12" cy="3" r="1" fill="currentColor"/>
                    </svg>
                </div>
                <div class="grant-ai-message-content">${data.success ? escapeHtml(data.data.response) : '申し訳ございません。エラーが発生しました。'}</div>
            `;
            messagesContainer.appendChild(assistantMessage);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        })
        .catch(error => {
            console.error('Error:', error);
            if (typingIndicator) {
                typingIndicator.classList.remove('active');
            }
            const errorMessage = document.createElement('div');
            errorMessage.className = 'grant-ai-message grant-ai-message--assistant';
            errorMessage.innerHTML = `
                <div class="grant-ai-message-avatar">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                        <line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
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
            }
            input.focus();
        });
    }
    
    document.addEventListener('click', function(e) {
        const aiBtn = e.target.closest('.ai-assist-btn');
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

<article class="grant-card-unified <?php echo esc_attr($view_class); ?>" 
         data-post-id="<?php echo esc_attr($post_id); ?>"
         role="article"
         aria-label="<?php echo esc_attr($title); ?>">
    
    <?php if ($is_featured): ?>
    <div class="card-badge">注目</div>
    <?php endif; ?>
    
    <div class="grant-card-header">
        <h3 class="grant-card-title">
            <a href="<?php echo esc_url($permalink); ?>" aria-label="<?php echo esc_attr($title); ?>の詳細">
                <?php echo esc_html($title); ?>
            </a>
        </h3>
        <button class="card-bookmark" aria-label="ブックマーク">
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                <path d="M3 2h12v14l-6-3-6 3V2z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
            </svg>
        </button>
    </div>
    
    <?php if ($ai_summary): ?>
    <div class="card-ai-summary">
        <p class="card-ai-summary-text"><?php echo esc_html($ai_summary); ?></p>
    </div>
    <?php endif; ?>
    
    <div class="card-meta">
        <span class="meta-item">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                <circle cx="7" cy="7" r="5" stroke="currentColor" stroke-width="2"/>
                <path d="M7 4v3h3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span class="meta-label">最大</span>
            <span class="meta-value"><?php echo $formatted_amount ? esc_html($formatted_amount) : '未定'; ?></span>
        </span>
        <span class="meta-item">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                <rect x="2" y="3" width="10" height="9" rx="1" stroke="currentColor" stroke-width="2"/>
                <path d="M4 1v2M10 1v2M2 6h10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span class="meta-label">締切</span>
            <span class="meta-value"><?php echo !empty($deadline_info) ? esc_html($deadline_info['text']) : '随時'; ?></span>
        </span>
    </div>
    
    <?php if ($organization): ?>
    <p class="card-org">
        <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
            <rect x="1" y="2" width="10" height="8" rx="1" stroke="currentColor" stroke-width="1.5"/>
            <path d="M3 5h6M3 7h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        <?php echo esc_html($organization); ?>
    </p>
    <?php endif; ?>
    
    <?php if ($adoption_rate > 0): ?>
    <div class="card-rate">
        <div class="rate-bar">
            <div class="rate-fill" style="width: <?php echo $adoption_rate; ?>%"></div>
        </div>
        <span class="rate-text">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                <path d="M1 8l2.5-2.5L5 7l4-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            採択率 <?php echo $adoption_rate; ?>%
        </span>
    </div>
    <?php endif; ?>
    
    <div class="card-actions">
        <button class="ai-assist-btn" 
                data-post-id="<?php echo esc_attr($post_id); ?>" 
                data-grant-title="<?php echo esc_attr($title); ?>"
                type="button"
                role="button"
                aria-label="AIに質問する">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                <rect x="2" y="4" width="12" height="9" rx="1.5" stroke="currentColor" stroke-width="2"/>
                <path d="M5 7h6M5 10h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <circle cx="8" cy="2" r="1" fill="currentColor"/>
            </svg>
            <span>AI質問</span>
        </button>
        <a href="<?php echo esc_url($permalink); ?>" class="card-link" aria-label="詳細を見る">
            <span>詳細を見る</span>
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                <path d="M5 3l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
    </div>
</article>