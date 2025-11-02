# Total Blocking Time (TBT) Analysis & Remediation Plan

## 🔴 Critical Issue: TBT Regression

### Problem Summary
After implementing initial performance optimizations, **Total Blocking Time (TBT) has increased from 440ms to 2,140ms** - a 387% regression. This is now the primary performance bottleneck.

---

## 📊 TBT Breakdown Analysis

### Current State
- **Total Main-Thread Work**: 7.1 seconds
- **Total Blocking Time**: 2,140ms
- **Long Tasks**: 19 tasks detected
- **Performance Impact**: Despite 90 Performance Score, TBT is dragging down potential score to 95+

### Main-Thread Work Categories

Based on the Lighthouse report breakdown:

1. **"Other" Category**: Significant contributor
   - Unknown/unclassified work
   - Likely includes:
     - Third-party script execution
     - Non-optimized event handlers
     - Excessive DOM manipulation

2. **"Style & Layout" Calculations**: Major contributor
   - CSS recalculations
   - Layout thrashing
   - Forced synchronous layouts
   - Related to the **578 uncomposed animations** issue

3. **"Script Evaluation"**: JavaScript execution
   - Large JavaScript bundles being parsed
   - Third-party scripts (GTM, Ads)
   - Inline scripts in HTML

---

## 🔍 Root Causes

### 1. Third-Party Scripts (490ms)
Even with lazy loading implementation, third-party scripts are still blocking:
- **Google Tag Manager (GTM)**
- **Google/Doubleclick Ads**
- **Google FundingChoices**

**Current Implementation Issue**:
```javascript
// From performance-optimization.php line 487-522
// Scripts are delayed by 5 seconds OR user interaction
// BUT: Once loaded, they execute synchronously on main thread
setTimeout(loadThirdPartyScripts, 5000);
```

**Problem**: Scripts execute in a blocking manner once triggered.

### 2. Uncomposed Animations (578 Elements)
Dramatic increase from 43 to 578 elements with "unsupported CSS properties":
- `color`
- `tab-size`
- `line-height`
- `border-bottom-color`, `border-left-color`, `border-right-color`, `border-top-color`

**Root Cause Analysis**:
- These properties cannot be hardware-accelerated
- Browser performs style recalculation on main thread
- Each transition/animation blocks main thread
- Likely related to:
  - Tailwind CSS utility classes with transitions
  - Hover effects on cards (2,946 DOM elements)
  - Global CSS transitions applied to many elements

**Code Example** (likely culprit in critical CSS):
```css
/* From performance-optimization.php line 366 */
.search-input {
    transition: all var(--transition-base); /* ❌ 'all' includes color, border-color */
}
```

### 3. Excessive DOM Size (2,946 Elements)
Large DOM contributes to:
- Longer style recalculation times
- More elements affected by CSS transitions
- Slower JavaScript execution

**Impact on TBT**:
- Each style change affects many elements
- Browser must recalculate styles for all matching selectors
- Compounded by "uncomposed animations" issue

### 4. Unused CSS (480 KiB) & JS (271 KiB)
Despite build tools configuration:
- **Unused CSS**: 480 KiB (minimal reduction from 391 KiB)
- **Unused JavaScript**: 271 KiB (no reduction from 272 KiB)

**Root Cause**:
- Build tools (Vite, Tailwind purge) not executed
- CDN versions still being used:
  - Tailwind CSS CDN: `https://cdn.tailwindcss.com/`
  - Font Awesome CDN: `https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css`
- Full libraries loaded instead of optimized bundles

### 5. Render-Blocking Resources (320ms)
Still present:
- Google Fonts CSS
- Font Awesome CSS

**Current Implementation Gap**:
- `optimize_google_fonts()` uses `preload` but still blocks initially
- Font Awesome is loaded via `async_styles()` but `requestIdleCallback` may fire during critical path

---

## 🎯 Remediation Strategy

### Priority 1: Fix Uncomposed Animations (High Impact)

#### Action Items:
1. **Replace `transition: all`** with specific properties
   ```css
   /* ❌ BAD - includes non-composited properties */
   transition: all 0.25s ease;
   
   /* ✅ GOOD - only composited properties */
   transition: transform 0.25s ease, opacity 0.25s ease;
   ```

2. **Use GPU-accelerated properties only**:
   - `transform` (translate, scale, rotate)
   - `opacity`
   - Avoid: `color`, `background-color`, `border-color`, `width`, `height`, `margin`, `padding`

3. **Apply `will-change` strategically**:
   ```css
   .card:hover {
       will-change: transform; /* Hint browser to optimize */
       transform: translateY(-4px);
   }
   ```

4. **Reduce transition-enabled elements**:
   - Remove global transitions
   - Apply only to interactive elements (buttons, cards)
   - Limit to hover states, not all elements

#### Implementation:
- Update critical CSS in `inc/performance-optimization.php`
- Audit all CSS files for `transition: all`
- Use PostCSS plugin to automatically replace problematic transitions

---

### Priority 2: Optimize Third-Party Scripts

#### Action Items:
1. **Use Web Workers** for heavy processing:
   ```javascript
   // Offload GTM/Analytics to Web Worker
   const worker = new Worker('/assets/js/analytics-worker.js');
   ```

2. **Implement `requestIdleCallback` properly**:
   ```javascript
   function loadThirdPartyScripts() {
       requestIdleCallback(() => {
           // Load scripts in idle time
           const script = document.createElement('script');
           script.src = 'https://www.googletagmanager.com/gtm.js?id=GTM-XXXX';
           script.async = true; // Still async after idle
           document.head.appendChild(script);
       }, { timeout: 2000 });
   }
   ```

3. **Use Facade Pattern** for ads:
   - Show placeholder initially
   - Load actual ad only when in viewport
   - Defer script execution until user scrolls to ad area

4. **Partytown Integration** (advanced):
   - Run third-party scripts in Web Worker
   - Offload from main thread entirely
   - Library: https://partytown.builder.io/

---

### Priority 3: Execute Build Process

#### Action Items:
1. **Run Vite Build**:
   ```bash
   cd /home/user/webapp
   npm install
   npm run build
   ```

2. **Enable Tailwind Purge**:
   - Ensure `tailwind.config.js` has correct content paths
   - Build CSS bundle instead of using CDN

3. **Update `functions.php`** to enqueue built assets:
   ```php
   // Replace CDN links with built assets
   wp_enqueue_style('main-styles', get_template_directory_uri() . '/assets/dist/css/main.css');
   ```

4. **Verify Purge Results**:
   - Check built CSS file size
   - Should be < 50 KiB (from 480 KiB)

---

### Priority 4: Advanced TBT Optimizations

#### 4.1 Code Splitting
```javascript
// vite.config.js
rollupOptions: {
    output: {
        manualChunks: {
            'vendor': ['./node_modules/**'], // Separate vendor code
            'critical': ['./assets/js/critical.js'], // Critical path
            'lazy': ['./assets/js/lazy-cards.js'], // Lazy features
        }
    }
}
```

#### 4.2 JavaScript Deferral Strategy
```html
<!-- Critical JavaScript inline -->
<script>
// Minimal code for initial interaction
</script>

<!-- Non-critical deferred -->
<script src="main.js" defer></script>
```

#### 4.3 CSS Containment
```css
/* Reduce layout recalculation scope */
.card {
    contain: layout style paint; /* Isolate element */
}
```

#### 4.4 Virtual Scrolling for Large Lists
- Implement virtual scrolling library (e.g., `react-window`, `vue-virtual-scroller`)
- Render only visible DOM elements
- Reduce DOM size from 2,946 to ~50-100 active elements

---

## 📈 Expected Impact

### After Implementing Priority 1-2:
- **TBT**: 2,140ms → **~600ms** (72% reduction)
- **Performance Score**: 90 → **95+**
- **Uncomposed Animations**: 578 → **< 50 elements**

### After Implementing Priority 3:
- **Unused CSS**: 480 KiB → **< 50 KiB** (90% reduction)
- **Unused JS**: 271 KiB → **< 100 KiB** (63% reduction)
- **Performance Score**: 95 → **97+**

### After Implementing Priority 4:
- **TBT**: 600ms → **< 300ms** (85% total reduction)
- **Performance Score**: 97 → **98+**
- **Main-thread work**: 7.1s → **< 3.0s**

---

## 🛠️ Implementation Order

### Phase 1: Quick Wins (1-2 hours)
1. Fix uncomposed animations (replace `transition: all`)
2. Update critical CSS
3. Test TBT improvement

### Phase 2: Third-Party Optimization (2-3 hours)
1. Implement proper `requestIdleCallback` pattern
2. Add facade pattern for ads
3. Test third-party script impact

### Phase 3: Build Process (1-2 hours)
1. Run `npm run build`
2. Update asset enqueuing
3. Verify unused CSS/JS reduction

### Phase 4: Advanced Optimizations (Optional, 4-6 hours)
1. Implement code splitting
2. Add virtual scrolling
3. Optimize DOM size
4. Add CSS containment

---

## 🔬 Testing & Validation

### TBT-Specific Testing
1. **Chrome DevTools Performance Tab**:
   - Record page load
   - Check "Bottom-Up" tab for main-thread tasks
   - Identify long tasks (> 50ms)

2. **Lighthouse CI**:
   ```bash
   npm install -g @lhci/cli
   lhci autorun --collect.numberOfRuns=5
   ```

3. **Real User Monitoring (RUM)**:
   - Use Google Analytics with Web Vitals
   - Monitor TBT in production

### Success Criteria
- ✅ TBT < 300ms (Good)
- ✅ Long tasks < 5 occurrences
- ✅ Main-thread work < 3.0s
- ✅ Uncomposed animations < 50 elements
- ✅ Performance Score > 95

---

## 📚 Resources

- [Web.dev: Optimize TBT](https://web.dev/tbt/)
- [Web.dev: Long Tasks](https://web.dev/long-tasks-devtools/)
- [CSS Triggers (Compositing)](https://csstriggers.com/)
- [Partytown Documentation](https://partytown.builder.io/)
- [Virtual Scrolling Techniques](https://web.dev/virtualize-lists-with-windowing/)

---

## 🔄 Next Steps

1. Review this document
2. Confirm priority order
3. Begin implementation (Phase 1)
4. Test and measure after each phase
5. Iterate based on results

---

**Document Created**: 2025-10-19
**Status**: Ready for Implementation
**Priority**: 🔴 Critical - TBT is blocking Performance Score improvement beyond 90
