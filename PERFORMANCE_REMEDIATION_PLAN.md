# Performance Remediation Plan - Phase 2
## Addressing Remaining Issues from New Lighthouse Report

**Date**: 2025-10-19  
**Version**: 2.0  
**Status**: Ready for Implementation

---

## 📊 Executive Summary

### Overall Progress
Our Phase 1 optimizations achieved **significant success**:
- Performance Score: **41 → 90** (+119%)
- FCP: **8.9s → 1.2s** (86% faster)
- LCP: **16.1s → 3.6s** (78% faster)
- Speed Index: **11.0s → 4.6s** (58% faster)

### Critical Issues Requiring Attention
However, new bottlenecks have emerged:
1. **🔴 TBT Regression**: 440ms → **2,140ms** (+387%) - CRITICAL
2. **🔴 Uncomposed Animations**: 43 → **578 elements** (13x increase)
3. **⚠️ Accessibility**: 94 → **78** (-17%)
4. **⚠️ SEO**: 41 → **38** (-8%)

### Persistent Issues
Despite Phase 1 implementation, several issues remain:
- Image optimization not fully effective
- Unused CSS/JS still high
- Third-party scripts still blocking (490ms)
- Render-blocking resources present (320ms)
- DOM size unchanged (2,946 elements)

---

## 🎯 Phase 2 Objectives

### Performance Goals
- **TBT**: 2,140ms → **< 300ms** (Target: 85% reduction)
- **Performance Score**: 90 → **98+**
- **Uncomposed Animations**: 578 → **< 50 elements**
- **Unused CSS**: 480 KiB → **< 50 KiB**
- **Unused JS**: 271 KiB → **< 100 KiB**

### Other Metrics
- **Accessibility**: 78 → **94+** (restore previous level)
- **SEO**: 38 → **50+** (improve beyond initial)
- **Best Practices**: **100** (maintain)

---

## 📋 Implementation Roadmap

### 🔴 Priority 1: Fix TBT & Uncomposed Animations (CRITICAL)

#### Task 1.1: Replace Non-Composited CSS Transitions
**Problem**: 578 elements with transitions on non-GPU-accelerated properties (`color`, `border-color`, `line-height`).

**Solution**:
```css
/* ❌ BEFORE - triggers layout/paint on main thread */
.card {
    transition: all 0.25s ease;
}

.search-input {
    transition: all var(--transition-base);
}

/* ✅ AFTER - GPU-accelerated only */
.card {
    transition: transform 0.25s ease, opacity 0.25s ease;
    will-change: transform;
}

.search-input {
    transition: border-color 0.25s ease, box-shadow 0.25s ease;
}
```

**Files to Modify**:
1. `/inc/performance-optimization.php` (lines 310-393)
   - Update critical CSS inline styles
   - Replace all `transition: all`
   - Add `will-change` to interactive elements

2. `/assets/css/*.css` (if exists)
   - Audit all transition declarations
   - Use PostCSS plugin to auto-convert

**Expected Impact**:
- Uncomposed animations: 578 → **< 50**
- TBT: 2,140ms → **~1,000ms** (50% reduction)
- Style & Layout work: Significantly reduced

---

#### Task 1.2: Optimize Third-Party Scripts with `requestIdleCallback`
**Problem**: Third-party scripts (GTM, Ads) blocking main thread for 490ms.

**Current Implementation** (lines 487-522 in `performance-optimization.php`):
```javascript
// ❌ PROBLEM: Scripts load synchronously after delay
setTimeout(loadThirdPartyScripts, 5000);
```

**Improved Solution**:
```javascript
function loadThirdPartyScripts() {
    if (thirdPartyScriptsLoaded) return;
    thirdPartyScriptsLoaded = true;
    
    // Use requestIdleCallback to load during idle time
    if ('requestIdleCallback' in window) {
        requestIdleCallback(() => {
            loadGTM();
            loadAds();
        }, { timeout: 3000 });
    } else {
        // Fallback for older browsers
        setTimeout(() => {
            loadGTM();
            loadAds();
        }, 3000);
    }
}

function loadGTM() {
    const script = document.createElement('script');
    script.src = 'https://www.googletagmanager.com/gtm.js?id=GTM-XXXX';
    script.async = true; // Still async even after idle
    document.head.appendChild(script);
}

function loadAds() {
    // Similar pattern for ads
}
```

**Files to Modify**:
- `/inc/performance-optimization.php` (lines 483-523)

**Expected Impact**:
- Third-party blocking: 490ms → **< 200ms**
- TBT: Additional 300ms reduction

---

#### Task 1.3: Implement Partytown for Third-Party Scripts (Advanced)
**What is Partytown?**
Library that runs third-party scripts in a Web Worker, completely offloading them from the main thread.

**Installation**:
```bash
cd /home/user/webapp
npm install @builder.io/partytown
```

**Implementation**:
```html
<!-- In header.php -->
<script>
partytown = {
    forward: ['dataLayer.push', 'gtag']
};
</script>
<script src="/assets/js/partytown/partytown.js"></script>

<!-- Third-party scripts with type="text/partytown" -->
<script type="text/partytown" src="https://www.googletagmanager.com/gtm.js?id=GTM-XXXX"></script>
```

**Expected Impact**:
- Third-party scripts: **0ms main-thread blocking**
- TBT: Additional 500ms reduction
- **Note**: Advanced implementation, optional if Tasks 1.1-1.2 achieve target

---

### 🔴 Priority 2: Image Optimization (Remaining Issues)

#### Task 2.1: Manually Optimize Flagged Images
**Problem**: Two specific images still not optimized:
- `1.png` (692 KiB) - likely the LCP element
- `名称未設定の.png` (669 KiB) - logo image

**Solution - Manual WebP Conversion**:
```bash
# Install ImageMagick if not available
cd /home/user/webapp

# Convert and resize LCP image
convert assets/images/1.png -resize 800x600 -quality 85 assets/images/1.webp

# Convert logo
convert assets/images/名称未設定の.png -resize 400x300 -quality 90 assets/images/名称未設定の.webp
```

**Alternative - Online Tool**:
1. Download images locally
2. Use https://squoosh.app/ or https://tinypng.com/
3. Generate WebP versions
4. Upload back to server

**Files to Modify**:
- Update image references in templates to use WebP with fallback
- Ensure `<picture>` tags are used

**Expected Impact**:
- Image savings: ~1,300 KiB total
- LCP: Potential further improvement

---

#### Task 2.2: Add Explicit Dimensions to Logo Image
**Problem**: `stylish-logo-image` lacks explicit width/height.

**Solution**:
Find logo rendering code (likely in `header.php` or `inc/theme-foundation.php`):

```php
// ❌ BEFORE
<img src="<?php echo $logo_url; ?>" alt="Logo" class="stylish-logo-image">

// ✅ AFTER
<img src="<?php echo $logo_url; ?>" 
     alt="Logo" 
     class="stylish-logo-image"
     width="200" 
     height="60"
     loading="eager">
```

**Files to Search/Modify**:
- `header.php`
- `inc/theme-foundation.php`
- Search for "stylish-logo-image" class

**Expected Impact**:
- CLS: Potential further reduction
- Layout shift: Resolved for logo

---

### 🟡 Priority 3: Execute Build Process & Reduce Unused Assets

#### Task 3.1: Run Build Process
**Current Issue**: Build tools configured but not executed. CDN versions still in use.

**Steps**:
```bash
cd /home/user/webapp

# Install dependencies
npm install

# Build production assets
npm run build

# Verify output
ls -lh assets/dist/
```

**Expected Output**:
```
assets/dist/
├── css/
│   └── main.[hash].css  # ~40-50 KiB (vs 480 KiB unused)
└── js/
    ├── main.[hash].js   # ~100-150 KiB (vs 271 KiB unused)
    └── lazyCards.[hash].js
```

---

#### Task 3.2: Update Asset Enqueuing in `functions.php`
**Problem**: Currently using CDN for Tailwind and Font Awesome.

**Solution**:
```php
// Find and update in functions.php or theme-foundation.php

// ❌ REMOVE CDN versions
// wp_enqueue_style('tailwind-cdn', 'https://cdn.tailwindcss.com/');
// wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/.../all.min.css');

// ✅ ADD built versions
wp_enqueue_style(
    'main-styles',
    get_template_directory_uri() . '/assets/dist/css/main.css',
    array(),
    filemtime(get_template_directory() . '/assets/dist/css/main.css') // Cache busting
);

wp_enqueue_script(
    'main-scripts',
    get_template_directory_uri() . '/assets/dist/js/main.js',
    array(),
    filemtime(get_template_directory() . '/assets/dist/js/main.js'),
    true // Load in footer
);
```

**Files to Modify**:
- `functions.php`
- `inc/theme-foundation.php` (look for `wp_enqueue_style/script` calls)

**Expected Impact**:
- Unused CSS: 480 KiB → **< 50 KiB** (90% reduction)
- Unused JS: 271 KiB → **< 120 KiB** (56% reduction)
- Render-blocking: Font Awesome no longer an issue
- Performance Score: +3-5 points

---

#### Task 3.3: Verify Tailwind Purge Configuration
**Ensure `tailwind.config.js` is correctly configured**:

```javascript
// tailwind.config.js
module.exports = {
  content: [
    './pages/**/*.php',
    './inc/**/*.php',
    './assets/js/**/*.js',
    './header.php',
    './footer.php',
    './index.php',
    './single.php',
    './archive.php',
    // Add all PHP template files
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
```

**Verification**:
```bash
# After build, check CSS size
cd /home/user/webapp
ls -lh assets/dist/css/main.*.css

# Should be < 100 KiB
# If larger, audit content paths in tailwind.config.js
```

---

### 🟡 Priority 4: Further Optimize Render-Blocking Resources

#### Task 4.1: Self-Host Google Fonts
**Problem**: Google Fonts still render-blocking (320ms).

**Solution - Use `fontsource` package**:
```bash
cd /home/user/webapp
npm install @fontsource/inter @fontsource/outfit @fontsource/noto-sans-jp
```

**Update CSS**:
```css
/* In main CSS file */
@import '@fontsource/inter/400.css';
@import '@fontsource/inter/600.css';
@import '@fontsource/inter/700.css';
@import '@fontsource/outfit/400.css';
@import '@fontsource/outfit/600.css';
@import '@fontsource/outfit/700.css';
@import '@fontsource/noto-sans-jp/400.css';
@import '@fontsource/noto-sans-jp/500.css';
@import '@fontsource/noto-sans-jp/700.css';
```

**Remove from `performance-optimization.php`**:
```php
// DELETE lines 399-419 (optimize_google_fonts method usage)
// Fonts now bundled in main.css
```

**Expected Impact**:
- Render-blocking: 320ms → **< 100ms**
- Fonts load faster (local, cacheable)
- FCP: Potential slight improvement

---

#### Task 4.2: Inline Font Awesome Icons (Optional)
**Alternative to loading full Font Awesome**:
Use only the specific icons needed.

**Steps**:
1. Identify icons in use:
   ```bash
   cd /home/user/webapp
   grep -roh 'fa-[a-z-]*' --include="*.php" | sort | uniq
   ```

2. Use Font Awesome subsetting:
   - https://fontawesome.com/docs/web/setup/optimize-performance

3. Or replace with SVG sprites:
   ```html
   <svg><use xlink:href="#icon-search"></use></svg>
   ```

**Expected Impact**:
- CSS size: Additional ~100 KiB savings
- Render-blocking: Further reduced

---

### 🟡 Priority 5: Improve Accessibility (78 → 94+)

#### Task 5.1: Run Accessibility Audit
**Likely Issues** (based on score drop):
- Missing ARIA labels
- Insufficient color contrast
- Missing alt text on new images
- Keyboard navigation issues

**Tools**:
```bash
# Install axe-core CLI
npm install -g @axe-core/cli

# Run audit (after deploying to accessible URL)
axe https://joseikin-insight.com --save results.json
```

**Common Fixes**:
```html
<!-- Add ARIA labels -->
<button aria-label="Search">
    <i class="fa fa-search"></i>
</button>

<!-- Ensure proper heading hierarchy -->
<h1>Main Title</h1>
<h2>Section</h2>  <!-- Not skipping to h3 -->

<!-- Add alt text -->
<img src="image.jpg" alt="Descriptive text">

<!-- Proper form labels -->
<label for="search-input">Search:</label>
<input id="search-input" type="text">
```

**Files to Audit**:
- `header.php`
- `pages/*.php` templates
- `inc/card-display.php`

**Expected Impact**:
- Accessibility Score: 78 → **94+**

---

### 🟢 Priority 6: Improve SEO (38 → 50+)

#### Task 6.1: Add Missing Meta Tags
**Likely Issues**:
- Missing meta descriptions
- Missing Open Graph tags
- Missing structured data

**Solution**:
```php
// In header.php or new inc/seo.php file

function gi_add_seo_meta() {
    if (is_singular()) {
        $post_id = get_the_ID();
        $description = get_the_excerpt() ?: wp_trim_words(get_the_content(), 20);
        $image = get_the_post_thumbnail_url($post_id, 'large');
        
        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
        echo '<meta property="og:title" content="' . esc_attr(get_the_title()) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
        echo '<meta property="og:image" content="' . esc_url($image) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url(get_permalink()) . '">' . "\n";
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    }
}
add_action('wp_head', 'gi_add_seo_meta', 5);
```

**Expected Impact**:
- SEO Score: 38 → **45-50**

---

#### Task 6.2: Add Structured Data (JSON-LD)
**Implementation**:
```php
function gi_add_structured_data() {
    if (is_singular('grant')) { // Assuming 'grant' is custom post type
        $post_id = get_the_ID();
        
        $structured_data = array(
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => get_the_title(),
            'description' => get_the_excerpt(),
            'image' => get_the_post_thumbnail_url($post_id, 'large'),
            'datePublished' => get_the_date('c'),
            'dateModified' => get_the_modified_date('c'),
            'author' => array(
                '@type' => 'Organization',
                'name' => get_bloginfo('name')
            )
        );
        
        echo '<script type="application/ld+json">' . "\n";
        echo json_encode($structured_data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        echo "\n</script>\n";
    }
}
add_action('wp_head', 'gi_add_structured_data', 10);
```

**Expected Impact**:
- SEO Score: Additional +3-5 points
- Rich snippets in search results

---

## 🗓️ Implementation Timeline

### Week 1: Critical Issues (Priority 1-2)
**Days 1-2**: Fix TBT & Uncomposed Animations
- Modify critical CSS (Task 1.1)
- Update third-party script loading (Task 1.2)
- **Goal**: TBT < 1,000ms

**Days 3-4**: Image Optimization
- Convert flagged images to WebP (Task 2.1)
- Add logo dimensions (Task 2.2)
- **Goal**: Image optimization complete

**Day 5**: Testing & Validation
- Run Lighthouse tests
- Verify TBT improvements
- Adjust if needed

### Week 2: Build Process & Asset Optimization (Priority 3-4)
**Days 1-2**: Execute Build Process
- Run `npm run build` (Task 3.1)
- Update asset enqueuing (Task 3.2)
- Verify Tailwind purge (Task 3.3)
- **Goal**: Unused CSS/JS < 120 KiB total

**Days 3-4**: Font & Render-Blocking Optimization
- Self-host Google Fonts (Task 4.1)
- Optimize Font Awesome (Task 4.2)
- **Goal**: Render-blocking < 100ms

**Day 5**: Testing & Validation
- Run Lighthouse tests
- Verify Performance Score > 95

### Week 3: Accessibility & SEO (Priority 5-6)
**Days 1-3**: Accessibility Fixes
- Run axe audit (Task 5.1)
- Fix identified issues
- **Goal**: Accessibility Score > 94

**Days 4-5**: SEO Enhancements
- Add meta tags (Task 6.1)
- Add structured data (Task 6.2)
- **Goal**: SEO Score > 50

---

## 📊 Success Metrics

### Target Lighthouse Scores (Phase 2 Completion)
| Metric | Current | Target | Stretch Goal |
|--------|---------|--------|--------------|
| **Performance** | 90 | 95 | 98 |
| **Accessibility** | 78 | 94 | 97 |
| **Best Practices** | 100 | 100 | 100 |
| **SEO** | 38 | 50 | 60 |

### Core Web Vitals Targets
| Metric | Current | Target | Status |
|--------|---------|--------|--------|
| **FCP** | 1.2s | < 1.0s | Good |
| **LCP** | 3.6s | < 2.5s | Needs improvement |
| **TBT** | 2,140ms | **< 300ms** | 🔴 Critical |
| **CLS** | 0.068 | < 0.05 | Good |
| **SI** | 4.6s | < 4.0s | Good |

---

## 🧪 Testing Strategy

### 1. Local Development Testing
```bash
# Lighthouse CI (multiple runs for consistency)
cd /home/user/webapp
npm install -g @lhci/cli
lhci autorun --collect.numberOfRuns=5 --collect.url=http://localhost
```

### 2. Chrome DevTools Performance
1. Open DevTools (F12)
2. Performance tab → Record
3. Reload page
4. Analyze:
   - Long tasks (> 50ms)
   - Main-thread work breakdown
   - TBT contributors

### 3. Real Device Testing
- Test on actual mobile devices (Android, iOS)
- Use throttling (Slow 4G, 3G Fast)
- Verify Core Web Vitals

### 4. Production Monitoring
```javascript
// Add Web Vitals monitoring
import {getCLS, getFID, getFCP, getLCP, getTTFB} from 'web-vitals';

function sendToAnalytics(metric) {
    gtag('event', metric.name, {
        event_category: 'Web Vitals',
        value: Math.round(metric.value),
        event_label: metric.id,
    });
}

getCLS(sendToAnalytics);
getFID(sendToAnalytics);
getFCP(sendToAnalytics);
getLCP(sendToAnalytics);
getTTFB(sendToAnalytics);
```

---

## 🚀 Quick Start Guide

### For Immediate TBT Reduction (Highest Priority)
1. **Fix Uncomposed Animations** (Task 1.1):
   ```bash
   cd /home/user/webapp
   # Edit inc/performance-optimization.php
   # Replace all "transition: all" with specific properties
   ```

2. **Test Impact**:
   ```bash
   # Run Lighthouse
   npm run lighthouse
   # Check TBT - should see 30-50% reduction immediately
   ```

3. **Optimize Third-Party Scripts** (Task 1.2):
   ```bash
   # Edit inc/performance-optimization.php
   # Update lazy_load_third_party_scripts() method
   ```

### For Quick Wins (Medium Impact, Low Effort)
1. **Add Logo Dimensions** (Task 2.2)
2. **Convert Images to WebP** (Task 2.1)
3. **Add SEO Meta Tags** (Task 6.1)

---

## 📝 Notes & Considerations

### Deployment Strategy
1. **Staging First**: Test all changes in staging environment
2. **Incremental Rollout**: Deploy one priority at a time
3. **Monitor**: Watch for regressions or new issues
4. **Rollback Plan**: Keep backups, use Git tags

### Potential Trade-offs
- **Self-hosted fonts**: Slight increase in initial bundle size, but better caching
- **Partytown**: Advanced setup, may have compatibility issues with some scripts
- **Virtual scrolling**: Requires more JavaScript, but dramatically reduces DOM size

### Browser Compatibility
- **`requestIdleCallback`**: Polyfill included for older browsers
- **WebP**: Fallback to PNG/JPG via `<picture>` tags
- **CSS Containment**: Progressive enhancement, gracefully degrades

---

## 🔗 Related Documents
- [TBT_ANALYSIS.md](./TBT_ANALYSIS.md) - Detailed TBT issue analysis
- [PERFORMANCE_OPTIMIZATION.md](./PERFORMANCE_OPTIMIZATION.md) - Phase 1 documentation
- [IMPLEMENTATION_GUIDE.md](./IMPLEMENTATION_GUIDE.md) - Deployment instructions
- [PERFORMANCE_SUMMARY.md](./PERFORMANCE_SUMMARY.md) - Phase 1 summary

---

## ✅ Pre-Implementation Checklist
- [ ] Review TBT_ANALYSIS.md for technical details
- [ ] Backup current site and database
- [ ] Create new Git branch for Phase 2
- [ ] Set up local development environment
- [ ] Install required npm packages
- [ ] Configure testing tools (Lighthouse CI, axe)
- [ ] Identify all template files requiring updates
- [ ] Plan staging deployment

---

**Status**: ⏳ Ready for Implementation  
**Next Action**: Begin Priority 1 - Task 1.1 (Fix Uncomposed Animations)  
**Expected Completion**: 3 weeks  
**Risk Level**: Medium (requires testing, potential for regressions)

---

*This document will be updated as implementation progresses.*
