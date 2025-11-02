/**
 * Lazy Card Rendering
 * DOM要素削減のための遅延レンダリング
 * 
 * @package Grant_Insight_Perfect
 * @version 1.0.0
 */

(function() {
    'use strict';
    
    /**
     * Intersection Observerを使用したカードの遅延レンダリング
     */
    function initLazyCards() {
        // Intersection Observer非対応ブラウザの場合は即座にすべて表示
        if (!('IntersectionObserver' in window)) {
            console.log('IntersectionObserver not supported, rendering all cards immediately');
            renderAllCards();
            return;
        }
        
        const cards = document.querySelectorAll('.grant-card[data-lazy]');
        
        if (cards.length === 0) {
            return;
        }
        
        // Observer設定
        const observerOptions = {
            root: null,
            rootMargin: '200px', // ビューポートの200px前に読み込み開始
            threshold: 0.01
        };
        
        // Observerコールバック
        const observerCallback = function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const card = entry.target;
                    renderCard(card);
                    observer.unobserve(card);
                }
            });
        };
        
        // Observerを作成
        const cardObserver = new IntersectionObserver(observerCallback, observerOptions);
        
        // すべてのカードを監視
        cards.forEach(function(card) {
            cardObserver.observe(card);
        });
        
        console.log('Lazy card rendering initialized for ' + cards.length + ' cards');
    }
    
    /**
     * 単一のカードをレンダリング
     */
    function renderCard(card) {
        const template = card.querySelector('template');
        
        if (!template) {
            console.warn('Template not found for card:', card);
            return;
        }
        
        // テンプレートからコンテンツを取得
        const content = template.content.cloneNode(true);
        
        // カードの内容を置き換え
        card.innerHTML = '';
        card.appendChild(content);
        
        // data-lazy属性を削除
        card.removeAttribute('data-lazy');
        
        // レンダリング完了イベントを発火
        card.dispatchEvent(new CustomEvent('cardRendered', {
            bubbles: true,
            detail: { cardId: card.dataset.id }
        }));
    }
    
    /**
     * すべてのカードを即座にレンダリング（フォールバック用）
     */
    function renderAllCards() {
        const cards = document.querySelectorAll('.grant-card[data-lazy]');
        
        cards.forEach(function(card) {
            renderCard(card);
        });
    }
    
    /**
     * 無限スクロール（Load More）機能
     */
    function initInfiniteScroll() {
        const loadMoreTrigger = document.querySelector('.load-more-trigger');
        
        if (!loadMoreTrigger) {
            return;
        }
        
        // Intersection Observer非対応ブラウザの場合は手動ボタンを表示
        if (!('IntersectionObserver' in window)) {
            const loadMoreBtn = document.createElement('button');
            loadMoreBtn.className = 'load-more-btn';
            loadMoreBtn.textContent = 'さらに読み込む';
            loadMoreBtn.addEventListener('click', loadMoreGrants);
            loadMoreTrigger.appendChild(loadMoreBtn);
            return;
        }
        
        const observerOptions = {
            root: null,
            rootMargin: '100px',
            threshold: 0
        };
        
        const observerCallback = function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting && !loadMoreTrigger.dataset.loading) {
                    loadMoreGrants();
                }
            });
        };
        
        const loadMoreObserver = new IntersectionObserver(observerCallback, observerOptions);
        loadMoreObserver.observe(loadMoreTrigger);
    }
    
    /**
     * AJAXで追加の助成金を読み込む
     */
    function loadMoreGrants() {
        const loadMoreTrigger = document.querySelector('.load-more-trigger');
        
        if (!loadMoreTrigger || loadMoreTrigger.dataset.loading === 'true') {
            return;
        }
        
        const currentPage = parseInt(loadMoreTrigger.dataset.page || '1');
        const nextPage = currentPage + 1;
        
        // ローディング状態を設定
        loadMoreTrigger.dataset.loading = 'true';
        loadMoreTrigger.classList.add('loading');
        
        // ローディングインジケーターを表示
        showLoadingIndicator(loadMoreTrigger);
        
        // AJAX リクエスト
        const formData = new FormData();
        formData.append('action', 'load_more_grants');
        formData.append('page', nextPage);
        
        fetch(gi_ajax_object.ajax_url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success && data.data.html) {
                // 新しいカードを追加
                const container = document.querySelector('.grants-container');
                if (container) {
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data.data.html;
                    
                    while (tempDiv.firstChild) {
                        container.appendChild(tempDiv.firstChild);
                    }
                    
                    // 遅延レンダリングを再初期化
                    initLazyCards();
                }
                
                // ページ番号を更新
                loadMoreTrigger.dataset.page = nextPage;
                
                // もう読み込むものがない場合は非表示
                if (!data.data.has_more) {
                    loadMoreTrigger.style.display = 'none';
                }
            } else {
                console.error('Failed to load more grants:', data);
                loadMoreTrigger.style.display = 'none';
            }
        })
        .catch(function(error) {
            console.error('Error loading more grants:', error);
        })
        .finally(function() {
            // ローディング状態を解除
            loadMoreTrigger.dataset.loading = 'false';
            loadMoreTrigger.classList.remove('loading');
            hideLoadingIndicator(loadMoreTrigger);
        });
    }
    
    /**
     * ローディングインジケーターを表示
     */
    function showLoadingIndicator(container) {
        const indicator = document.createElement('div');
        indicator.className = 'loading-indicator';
        indicator.innerHTML = '<div class="loading-spinner"></div><p>読み込み中...</p>';
        container.appendChild(indicator);
    }
    
    /**
     * ローディングインジケーターを非表示
     */
    function hideLoadingIndicator(container) {
        const indicator = container.querySelector('.loading-indicator');
        if (indicator) {
            indicator.remove();
        }
    }
    
    /**
     * 初期化
     */
    document.addEventListener('DOMContentLoaded', function() {
        initLazyCards();
        initInfiniteScroll();
        
        console.log('Lazy card rendering and infinite scroll initialized');
    });
    
    // グローバルに公開（必要に応じて）
    window.GI_LazyCards = {
        init: initLazyCards,
        renderCard: renderCard,
        loadMore: loadMoreGrants
    };
    
})();
