// スクロール修正ブックマークレット - Scroll Fix Bookmarklet
// このコードをブラウザのコンソールで実行するか、ブックマークレットとして使用

(function() {
    'use strict';
    
    // デバッグ用のログ関数
    function log(message, type = 'info') {
        const styles = {
            info: 'color: #007cba; font-weight: bold;',
            success: 'color: #28a745; font-weight: bold;',
            warning: 'color: #ffc107; font-weight: bold;',
            error: 'color: #dc3545; font-weight: bold;'
        };
        console.log(`%c[スクロール修正] ${message}`, styles[type]);
    }
    
    log('スクロール修正ツールを開始します...', 'info');
    
    // 1. 基本的なoverflow修正
    function fixOverflow() {
        const originalBodyOverflow = getComputedStyle(document.body).overflow;
        const originalHtmlOverflow = getComputedStyle(document.documentElement).overflow;
        
        if (originalBodyOverflow === 'hidden') {
            document.body.style.overflow = 'auto';
            log('body要素のoverflow:hiddenを修正しました', 'success');
        }
        
        if (originalHtmlOverflow === 'hidden') {
            document.documentElement.style.overflow = 'auto';
            log('html要素のoverflow:hiddenを修正しました', 'success');
        }
        
        // no-scrollクラスの削除
        if (document.body.classList.contains('no-scroll')) {
            document.body.classList.remove('no-scroll');
            log('body要素からno-scrollクラスを削除しました', 'success');
        }
        
        if (document.documentElement.classList.contains('no-scroll')) {
            document.documentElement.classList.remove('no-scroll');
            log('html要素からno-scrollクラスを削除しました', 'success');
        }
    }
    
    // 2. ヒーローセクションの修正
    function fixHeroSections() {
        const heroSelectors = [
            '.section-hero',
            '.hero',
            '.banner',
            'section[class*="hero"]',
            '.front-page',
            '.landing-hero',
            '.page-hero'
        ];
        
        heroSelectors.forEach(selector => {
            const elements = document.querySelectorAll(selector);
            elements.forEach((element, index) => {
                const styles = getComputedStyle(element);
                let fixed = false;
                
                // height: 100vhの修正
                if (styles.height === '100vh') {
                    element.style.setProperty('min-height', '100vh', 'important');
                    element.style.setProperty('height', 'auto', 'important');
                    log(`${selector}[${index}]: height:100vh → min-height:100vh + height:auto`, 'success');
                    fixed = true;
                }
                
                // position: fixed/absoluteの修正
                if (styles.position === 'fixed' || styles.position === 'absolute') {
                    element.style.setProperty('position', 'relative', 'important');
                    log(`${selector}[${index}]: position:${styles.position} → position:relative`, 'success');
                    fixed = true;
                }
                
                // overflow: hiddenの修正
                if (styles.overflow === 'hidden') {
                    element.style.setProperty('overflow', 'visible', 'important');
                    log(`${selector}[${index}]: overflow:hidden → overflow:visible`, 'success');
                    fixed = true;
                }
                
                if (!fixed) {
                    log(`${selector}[${index}]: 修正が必要な問題は見つかりませんでした`, 'info');
                }
            });
        });
    }
    
    // 3. スムーズスクロールの有効化
    function enableSmoothScroll() {
        document.documentElement.style.scrollBehavior = 'smooth';
        log('スムーズスクロールを有効にしました', 'success');
    }
    
    // 4. 強制的なスクロール有効化
    function forceEnableScroll() {
        // 全ての要素からoverflow:hiddenを削除
        const allElements = document.querySelectorAll('*');
        let count = 0;
        
        allElements.forEach(element => {
            const styles = getComputedStyle(element);
            if (styles.overflow === 'hidden' && 
                (element === document.body || 
                 element === document.documentElement || 
                 element.classList.contains('page') ||
                 element.classList.contains('site') ||
                 element.classList.contains('wrapper'))) {
                element.style.setProperty('overflow', 'auto', 'important');
                count++;
            }
        });
        
        if (count > 0) {
            log(`${count}個の要素のoverflow:hiddenを修正しました`, 'success');
        }
    }
    
    // 5. 診断情報の表示
    function showDiagnostics() {
        const documentHeight = Math.max(
            document.body.scrollHeight,
            document.body.offsetHeight,
            document.documentElement.clientHeight,
            document.documentElement.scrollHeight,
            document.documentElement.offsetHeight
        );
        
        log('=== 診断情報 ===', 'info');
        log(`ドキュメント高さ: ${documentHeight}px`, 'info');
        log(`ウィンドウ高さ: ${window.innerHeight}px`, 'info');
        log(`body overflow: ${getComputedStyle(document.body).overflow}`, 'info');
        log(`html overflow: ${getComputedStyle(document.documentElement).overflow}`, 'info');
        log(`現在のスクロール位置: ${window.pageYOffset}px`, 'info');
        
        if (documentHeight <= window.innerHeight) {
            log('⚠️ ドキュメントの高さがウィンドウより小さいです', 'warning');
        }
    }
    
    // 6. テストスクロール
    function testScroll() {
        log('スクロールテストを実行中...', 'info');
        
        // 少し下にスクロールしてみる
        window.scrollBy({
            top: 100,
            behavior: 'smooth'
        });
        
        setTimeout(() => {
            if (window.pageYOffset > 0) {
                log('✅ スクロールが正常に動作しています！', 'success');
                // 元の位置に戻る
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            } else {
                log('❌ スクロールがまだ動作していません', 'error');
                log('手動でCSSファイルを確認する必要があります', 'warning');
            }
        }, 1000);
    }
    
    // 修正処理の実行
    try {
        showDiagnostics();
        fixOverflow();
        fixHeroSections();
        forceEnableScroll();
        enableSmoothScroll();
        
        log('=== 修正完了 ===', 'success');
        log('修正処理が完了しました。5秒後にスクロールテストを実行します。', 'info');
        
        setTimeout(() => {
            testScroll();
        }, 5000);
        
        // 修正結果の表示用のスタイル付きメッセージ
        const notification = document.createElement('div');
        notification.innerHTML = `
            <div style="
                position: fixed;
                top: 20px;
                right: 20px;
                background: #28a745;
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                z-index: 10000;
                font-family: Arial, sans-serif;
                font-size: 14px;
                max-width: 300px;
            ">
                <strong>✅ スクロール修正完了</strong><br>
                修正処理が実行されました。<br>
                <small>このメッセージは10秒後に消えます</small>
            </div>
        `;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 10000);
        
    } catch (error) {
        log(`エラーが発生しました: ${error.message}`, 'error');
    }
    
})();