# Phase 2 Quick Start Guide
## Immediate Actions to Fix TBT Regression

**🎯 Goal**: Reduce Total Blocking Time from 2,140ms to <300ms

---

## 📊 Current Status

### ✅ What's Working Well (Phase 1 Success)
- Performance Score: **90** (was 41) ⬆️ +119%
- FCP: **1.2s** (was 8.9s) ⬆️ 86% faster
- LCP: **3.6s** (was 16.1s) ⬆️ 78% faster
- Speed Index: **4.6s** (was 11.0s) ⬆️ 58% faster
- Best Practices: **100** (was 71) ✅

### 🔴 Critical Issues (Phase 2 Focus)
1. **TBT**: 2,140ms (was 440ms) ⬇️ **387% WORSE** - CRITICAL
2. **Uncomposed Animations**: 578 elements (was 43) ⬇️ 13x increase
3. **Accessibility**: 78 (was 94) ⬇️ -17%
4. **SEO**: 38 (was 41) ⬇️ -8%

---

## 🚀 3-Step Quick Fix (2-3 Hours Work)

### Step 1: Fix Uncomposed Animations (60 min) ⚡ HIGHEST IMPACT

**Problem**: 578 elements using `transition: all` which triggers layout/paint on main thread.

**Solution**: Edit `/inc/performance-optimization.php`

**Find and replace in the `inline_critical_css()` method (lines 310-393)**:

```php
// ❌ FIND THIS (around line 366):
.search-input {
    width: 100%;
    padding: 16px 20px;
    font-size: 16px;
    border: 1px solid rgba(0,0,0,0.2);
    border-radius: 8px;
    transition: all var(--transition-base);
}

// ✅ REPLACE WITH:
.search-input {
    width: 100%;
    padding: 16px 20px;
    font-size: 16px;
    border: 1px solid rgba(0,0,0,0.2);
    border-radius: 8px;
    transition: border-color var(--transition-base), box-shadow var(--transition-base);
}
```

**Add this new CSS rule before the closing `</style>` tag (around line 392)**:

```php
        /* GPU-accelerated animations only */
        .card, .card-item {
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), 
                        opacity 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card:hover, .card-item:hover {
            will-change: transform;
            transform: translateY(-4px);
        }
        
        /* Remove transitions from all other elements */
        * {
            transition: none !important;
        }
        
        /* Re-enable for specific interactive elements */
        button, a, input, textarea, select, .search-input, .card, .card-item {
            transition: revert !important;
        }
        </style>
```

**Expected Impact**:
- Uncomposed animations: 578 → <50 elements
- TBT: 2,140ms → ~1,000ms (50% reduction)
- Implementation time: 30-60 minutes

---

### Step 2: Optimize Third-Party Scripts (45 min)

**Problem**: GTM and Ads blocking main thread for 490ms.

**Solution**: Edit `/inc/performance-optimization.php`

**Replace the `lazy_load_third_party_scripts()` method (lines 487-522) with**:

```php
    /**
     * サードパーティスクリプトをユーザー操作後に読み込む
     */
    public function lazy_load_third_party_scripts() {
        ?>
        <script>
        (function() {
            let thirdPartyScriptsLoaded = false;
            
            // GTMを読み込む
            function loadGTM() {
                const script = document.createElement('script');
                script.src = 'https://www.googletagmanager.com/gtm.js?id=GTM-XXXX'; // Update GTM-XXXX with actual ID
                script.async = true;
                document.head.appendChild(script);
            }
            
            // Adsを読み込む
            function loadAds() {
                // Add your ads loading code here
                console.log('Ads loading deferred');
            }
            
            // サードパーティスクリプトを読み込む関数
            function loadThirdPartyScripts() {
                if (thirdPartyScriptsLoaded) return;
                thirdPartyScriptsLoaded = true;
                
                console.log('Loading third-party scripts in idle time...');
                
                // requestIdleCallbackを使用してアイドル時に読み込む
                if ('requestIdleCallback' in window) {
                    requestIdleCallback(function() {
                        loadGTM();
                        loadAds();
                    }, { timeout: 3000 });
                } else {
                    // Fallback for browsers without requestIdleCallback
                    setTimeout(function() {
                        loadGTM();
                        loadAds();
                    }, 3000);
                }
            }
            
            // ユーザー操作を検知したら読み込む
            const events = ['scroll', 'click', 'mousemove', 'touchstart'];
            const triggerLoad = function() {
                loadThirdPartyScripts();
                events.forEach(event => {
                    window.removeEventListener(event, triggerLoad);
                });
            };
            
            // 各イベントにリスナーを設定（passive: trueでパフォーマンス向上）
            events.forEach(event => {
                window.addEventListener(event, triggerLoad, { once: true, passive: true });
            });
            
            // 3秒経過したら自動的に読み込む（以前は5秒だった）
            setTimeout(loadThirdPartyScripts, 3000);
        })();
        </script>
        <?php
    }
```

**Expected Impact**:
- Third-party blocking: 490ms → <200ms
- TBT: Additional 300ms reduction
- Implementation time: 30-45 minutes

---

### Step 3: Test & Validate (30 min)

**Run Lighthouse Test**:

```bash
# Option 1: Chrome DevTools
# 1. Open site in Chrome
# 2. Open DevTools (F12)
# 3. Lighthouse tab → Analyze page load

# Option 2: Lighthouse CI (if npm installed)
cd /home/user/webapp
npx lighthouse https://joseikin-insight.com --view
```

**Check These Metrics**:
- ✅ TBT should be <1,200ms (target: <300ms eventually)
- ✅ Uncomposed animations should be <100 elements
- ✅ Performance Score should be 92-95
- ✅ Third-party impact reduced

**Expected Results After Steps 1-3**:
| Metric | Before | After | Improvement |
|--------|--------|-------|-------------|
| TBT | 2,140ms | ~700-900ms | 60-65% better |
| Performance | 90 | 94-96 | +4-6 points |
| Uncomposed Animations | 578 | <100 | 80% reduction |

---

## 📝 Implementation Checklist

### Before You Start
- [ ] Backup current site (`mysqldump` + file backup)
- [ ] Create new branch: `git checkout -b performance-phase2-tbt-fix`
- [ ] Note current Lighthouse scores for comparison

### Implementation
- [ ] **Step 1**: Fix uncomposed animations in `performance-optimization.php`
- [ ] **Step 2**: Optimize third-party scripts in `performance-optimization.php`
- [ ] Test locally (if possible)
- [ ] Deploy to staging/production
- [ ] **Step 3**: Run Lighthouse test
- [ ] Verify TBT improvement

### After Implementation
- [ ] Commit changes: `git commit -m "fix(performance): Reduce TBT by fixing uncomposed animations"`
- [ ] Push to remote: `git push origin performance-phase2-tbt-fix`
- [ ] Monitor for 24 hours for any issues
- [ ] Document actual improvement in metrics

---

## 🔍 Troubleshooting

### If TBT Doesn't Improve
1. **Check CSS is actually updated**:
   - View page source, look for the updated critical CSS
   - Verify `transition: all` is replaced

2. **Check browser cache**:
   - Hard refresh (Ctrl+Shift+R or Cmd+Shift+R)
   - Clear browser cache completely

3. **Check for other CSS files**:
   - Search for `transition: all` in other CSS files
   - May need to update theme's main stylesheet too

### If Uncomposed Animations Still High
- Inspect which elements are flagged in Lighthouse report
- May need to add more specific selectors to the fix
- Consider using browser DevTools Performance tab to identify specific elements

---

## 📊 Next Steps After Quick Fix

Once TBT is <1,000ms, proceed with remaining optimizations:

### Priority 2 (Week 2)
- [ ] Run build process: `npm run build`
- [ ] Update asset enqueuing (use built CSS/JS, not CDN)
- [ ] Optimize images (1.png, logo)
- [ ] Self-host Google Fonts

**Expected**: Performance Score 96-98, TBT <400ms

### Priority 3 (Week 3)
- [ ] Fix accessibility issues (restore to 94+)
- [ ] Add SEO meta tags and structured data
- [ ] Final optimization polish

**Expected**: All scores >95, TBT <300ms

---

## 📚 Full Documentation

For complete details, see:
- **[TBT_ANALYSIS.md](./TBT_ANALYSIS.md)** - Deep technical analysis
- **[PERFORMANCE_REMEDIATION_PLAN.md](./PERFORMANCE_REMEDIATION_PLAN.md)** - Complete 3-week plan
- **[IMPLEMENTATION_GUIDE.md](./IMPLEMENTATION_GUIDE.md)** - Deployment instructions

---

## 💡 Pro Tips

1. **Use Chrome DevTools Performance Tab**:
   - Record page load
   - Check "Bottom-Up" tab
   - Look for long tasks (red bars >50ms)
   - This shows exactly what's blocking main thread

2. **Test on Real Mobile Device**:
   - TBT is often worse on mobile
   - Use Chrome Remote Debugging

3. **Monitor Core Web Vitals**:
   - Add Web Vitals library to track real user data
   - See how actual users experience the site

---

## 🎯 Success Criteria

### Minimum Viable Improvement (After Quick Fix)
- ✅ TBT < 1,000ms
- ✅ Performance Score > 93
- ✅ No new errors or issues

### Target Goal (After Full Phase 2)
- ✅ TBT < 300ms
- ✅ Performance Score > 98
- ✅ All metrics in "green" zone

---

## 🚨 Need Help?

### If Issues Occur
1. **Check error logs**: Look for PHP errors in WordPress debug log
2. **Revert changes**: Use git to rollback if needed
3. **Test in stages**: Implement Step 1 first, test, then Step 2

### Common Issues
- **Styles broken**: Check CSS syntax, ensure closing braces
- **Scripts not loading**: Check browser console for JavaScript errors
- **GTM not working**: Update GTM-XXXX with actual GTM ID

---

**Last Updated**: 2025-10-19  
**Estimated Time**: 2-3 hours for quick fix  
**Expected Impact**: TBT reduction of 60-65%  
**Risk Level**: Low (CSS/JS changes only, easily reversible)

---

*Ready to start? Begin with Step 1 - it has the highest impact! 🚀*
