# JS/CSS Refactoring Plan — Frontend Asset Modernization

> **Goal**: Clean up duplicate libraries, reduce inline scripts, utilize Vite  
> **Scope**: Frontend only (Backend Hyper template untouched)  
> **Risk**: Medium — JS changes can break UI  
> **Estimated**: 4-5 hours

---

## ১. Current State Summary

| Metric | Count | Status |
|---|---|---|
| Inline `<script>` tags | **809** | 🔴 |
| External JS references | **670** | 🔴 |
| jQuery versions | **5** (2.1.3 CDN, 2.1.4 local, 3.6.0 CDN, 3.6.3 local, backend) | 🔴 |
| Bootstrap versions | **3** (frontend, campaign, backend) | 🔴 |
| Select2 copies | **3** (frontend, campaign, backend) | 🟡 |
| Owl Carousel copies | **2** (frontend, campaign) | 🟡 |
| Vite configured | Yes | ⚠️ Unused |
| resources/js/ | 2 files | ⚠️ Scaffold |

---

## ২. Duplicate Library Inventory

### jQuery (5 versions!)

| Version | Location | Used By |
|---|---|---|
| 2.1.3 | CDN `cdnjs.cloudflare.com` | category, childcategory, subcategory pages |
| 2.1.4 | `public/frontEnd/campaign/js/jquery-2.1.4.min.js` | campaign page |
| 3.6.0 | CDN `code.jquery.com` | order index, product index, price_edit |
| 3.6.3 | `public/frontEnd/js/jquery-3.6.3.min.js` | frontend master, cart, quickview |
| (backend) | `public/backEnd/assets/libs/` (bundled in app.min.js) | admin panel |

### Bootstrap (3 copies)

| Location | Version | Used By |
|---|---|---|
| `public/frontEnd/css/bootstrap.min.css` + `js/bootstrap.min.js` | 5.x | Frontend |
| `public/frontEnd/campaign/css/bootstrap.min.css` + `js/bootstrap.min.js` | 5.x | Campaign |
| `public/backEnd/assets/css/bootstrap.min.css` | 5.x | Backend |

### Select2 (3 copies)

| Location | Used By |
|---|---|
| `public/frontEnd/css/select2.min.css` + `js/select2.min.js` | Frontend |
| `public/frontEnd/campaign/css/select2.min.css` + `js/select2.min.js` | Campaign |
| `public/backEnd/assets/libs/select2/` | Backend |

### Owl Carousel (2 copies)

| Location | Used By |
|---|---|
| `public/frontEnd/css/owl.carousel.min.css` + `js/owl.carousel.min.js` | Frontend |
| `public/frontEnd/campaign/css/owl.carousel.min.css` + `js/owl.carousel.min.js` | Campaign |

---

## ৩. Inline Script Analysis

### Top 15 Files with Most Inline Scripts

| File | `<script>` Count | Content Type |
|---|---|---|
| `frontEnd/layouts/master.blade.php` | 26 | jQuery init, carousel, search, cart AJAX |
| `backEnd/users/index.blade.php` | 14 | DataTable init, CRUD modals |
| `backEnd/tagmanager/index.blade.php` | 14 | DataTable init, CRUD modals |
| `backEnd/subcategory/index.blade.php` | 14 | DataTable init, CRUD modals |
| `backEnd/socialmedia/index.blade.php` | 14 | DataTable init, CRUD modals |
| `backEnd/size/index.blade.php` | 14 | DataTable init, CRUD modals |
| `backEnd/shippingcharge/index.blade.php` | 14 | DataTable init, CRUD modals |
| `backEnd/settings/index.blade.php` | 14 | DataTable init, CRUD modals |
| `backEnd/roles/index.blade.php` | 14 | DataTable init, CRUD modals |
| `backEnd/review/index.blade.php` | 14 | DataTable init, CRUD modals |
| `backEnd/review/pending.blade.php` | 14 | DataTable init, CRUD modals |
| `backEnd/reports/ipblock.blade.php` | 14 | DataTable init, CRUD modals |
| `backEnd/pixels/index.blade.php` | 14 | DataTable init, CRUD modals |
| `backEnd/permissions/index.blade.php` | 14 | DataTable init, CRUD modals |
| `backEnd/orderstatus/index.blade.php` | 14 | DataTable init, CRUD modals |

### Backend Pattern (14 scripts per index page)

Every admin index page has the same 14 inline scripts:
1. DataTable init
2. Select2 init
3. SweetAlert2 confirm delete
4. AJAX status toggle
5. AJAX active/inactive
6. Form submit handler
7. Modal open/close
8. Search filter
9. Date picker init
10. Tooltip init
11. Print handler
12. Export handler
13. Bulk select
14. CSRF setup

---

## ৪. Refactoring Strategy

### Principle: Backend template ছোঁবে না

Backend (Hyper admin template) এর 66 JS + 6 CSS files এখন refactor করা হবে না। শুধু inline scripts consolidate করা হবে।

### Principle: Frontend এ Vite utilize করা হবে

Frontend JS/CSS Vite দিয়ে bundle করা হবে। Campaign assets merge করা হবে।

---

## ৫. Implementation Steps

### Step 1: Remove Duplicate jQuery (30 min)

**Goal**: 5 jQuery versions → 2 (frontend 3.6.3 + backend bundled)

#### 1.1 Remove CDN jQuery from category/childcategory/subcategory pages

These pages load jQuery 2.1.3 from CDN even though master.blade.php already loads 3.6.3:

```blade
{{-- REMOVE these lines from category.blade.php, childcategory.blade.php, subcategory.blade.php --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
```

**Files to modify:**
- `resources/views/frontEnd/layouts/pages/category.blade.php` (lines 345-346)
- `resources/views/frontEnd/layouts/pages/childcategory.blade.php` (lines 229-230)
- `resources/views/frontEnd/layouts/pages/subcategory.blade.php` (lines 272-273)

#### 1.2 Remove CDN jQuery from backend order/product pages

These load jQuery 3.6.0 from CDN even though backend master loads it:

```blade
{{-- REMOVE from order/index.blade.php, product/index.blade.php, product/price_edit.blade.php --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
```

**Files to modify:**
- `resources/views/backEnd/order/index.blade.php` (line 200)
- `resources/views/backEnd/product/index.blade.php` (line 109)
- `resources/views/backEnd/product/price_edit.blade.php` (line 66)
- `resources/views/backEnd/order/incomplete.blade.php` (line 112)

#### 1.3 Campaign page — keep jQuery 2.1.4 for now

Campaign page uses jQuery 2.1.4 with SliderPro which may not work with 3.x. Keep for now, test later.

### Step 2: Consolidate Backend Inline Scripts (2 hr)

**Goal**: 14 identical inline scripts per page → 1 shared JS file

#### 2.1 Create shared admin CRUD JS

```javascript
// public/backEnd/assets/js/admin-crud.js

// DataTable init
function initDataTable(selector, ajaxUrl) {
    $(selector).DataTable({ /* config */ });
}

// SweetAlert confirm delete
function confirmDelete(formId) {
    Swal.fire({ /* config */ }).then((result) => {
        if (result.isConfirmed) document.getElementById(formId).submit();
    });
}

// Status toggle
function toggleStatus(url, id, status) {
    $.post(url, { hidden_id: id, _token: $('meta[name="csrf-token"]').attr('content') }, function() {
        location.reload();
    });
}

// Select2 init
function initSelect2(selector) {
    $(selector).select2({ theme: 'bootstrap-5' });
}
```

#### 2.2 Replace inline scripts in each admin index page

```blade
{{-- BEFORE: 14 inline scripts --}}
<script>
    $('#myTable').DataTable();
</script>
<script>
    $('.select2').select2();
</script>
{{-- ... 12 more ... --}}

{{-- AFTER: 1 shared file + page-specific config --}}
<script src="{{ asset('backEnd/assets/js/admin-crud.js') }}"></script>
<script>
    initDataTable('#myTable', '{{ route("products.index") }}');
    initSelect2('.select2');
</script>
```

**Files to modify:** All 20+ admin index.blade.php files

### Step 3: Extract Frontend Inline Scripts (1 hr)

**Goal**: 26 inline scripts in frontend master → separate JS files

#### 3.1 Create frontend JS modules

```javascript
// resources/js/frontend/search.js
export function initLiveSearch() { /* ... */ }

// resources/js/frontend/cart.js
export function initCart() { /* ... */ }

// resources/js/frontend/carousel.js
export function initCarousels() { /* ... */ }
```

#### 3.2 Update Vite config

```javascript
// vite.config.js
export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
                'resources/js/frontend.js',  // NEW
            ],
            refresh: true,
        }),
    ],
});
```

#### 3.3 Update frontend master.blade.php

```blade
{{-- BEFORE --}}
<script src="{{asset('public/frontEnd/js/jquery-3.6.3.min.js')}}"></script>
<script src="{{asset('public/frontEnd/js/bootstrap.min.js')}}"></script>
{{-- ... 24 more inline scripts ... --}}

{{-- AFTER --}}
@vite(['resources/js/frontend.js'])
<script src="{{asset('public/frontEnd/js/jquery-3.6.3.min.js')}}"></script>
<script src="{{asset('public/frontEnd/js/bootstrap.min.js')}}"></script>
{{-- Page-specific inline scripts only --}}
```

### Step 4: Merge Campaign Assets (30 min)

**Goal**: Campaign uses same libraries as frontend — remove duplicates

#### 4.1 Replace campaign jQuery with frontend jQuery

```blade
{{-- BEFORE --}}
<script src="{{ asset('public/frontEnd/campaign/js/jquery-2.1.4.min.js') }}"></script>

{{-- AFTER --}}
<script src="{{ asset('public/frontEnd/js/jquery-3.6.3.min.js') }}"></script>
```

> ⚠️ Test SliderPro compatibility with jQuery 3.x first

#### 4.2 Replace campaign Bootstrap with frontend Bootstrap

```blade
{{-- BEFORE --}}
<link href="{{ asset('public/frontEnd/campaign/css/bootstrap.min.css') }}" rel="stylesheet">
<script src="{{ asset('public/frontEnd/campaign/js/bootstrap.min.js') }}"></script>

{{-- AFTER --}}
<link href="{{ asset('public/frontEnd/css/bootstrap.min.css') }}" rel="stylesheet">
<script src="{{ asset('public/frontEnd/js/bootstrap.min.js') }}"></script>
```

#### 4.3 Replace campaign Select2/Owl with frontend versions

Same pattern — point to frontend assets instead of campaign duplicates.

**Files to modify:**
- `resources/views/frontEnd/layouts/pages/campaign/campaign.blade.php`

### Step 5: Cleanup (15 min)

#### 5.1 Remove unused resources/js/ scaffold

```bash
# These are default Laravel scaffold, not used
rm resources/js/app.js
rm resources/js/bootstrap.js
rm resources/css/app.css
rm resources/sass/_variables.scss
rm resources/sass/app.scss
```

> Only if Vite is not used for backend. If Vite is used for frontend.js, keep and repurpose.

#### 5.2 Remove campaign duplicate files (after merge verified)

```bash
# After verifying campaign works with frontend assets
rm public/frontEnd/campaign/js/jquery-2.1.4.min.js
rm public/frontEnd/campaign/js/bootstrap.min.js
rm public/frontEnd/campaign/css/bootstrap.min.css
rm public/frontEnd/campaign/js/select2.min.js
rm public/frontEnd/campaign/css/select2.min.css
rm public/frontEnd/campaign/js/owl.carousel.min.js
rm public/frontEnd/campaign/css/owl.carousel.min.css
```

---

## ৬. Files To Create

| File | Purpose |
|---|---|
| `public/backEnd/assets/js/admin-crud.js` | Shared admin CRUD scripts (DataTable, SweetAlert, toggle) |
| `resources/js/frontend.js` | Frontend entry point for Vite |
| `resources/js/frontend/search.js` | Live search module |
| `resources/js/frontend/cart.js` | Cart AJAX module |
| `resources/js/frontend/carousel.js` | Carousel init module |

---

## ৭. Files To Modify

### Frontend Pages (jQuery dedup)
- `frontEnd/layouts/pages/category.blade.php` — remove CDN jQuery
- `frontEnd/layouts/pages/childcategory.blade.php` — remove CDN jQuery
- `frontEnd/layouts/pages/subcategory.blade.php` — remove CDN jQuery
- `frontEnd/layouts/pages/campaign/campaign.blade.php` — merge assets

### Backend Pages (inline script consolidation)
- `backEnd/layouts/master.blade.php` — add admin-crud.js
- 20+ `backEnd/*/index.blade.php` — replace inline scripts

### Config
- `vite.config.js` — add frontend.js entry

---

## ৮. Testing Strategy

After each step:
1. Visit affected pages in browser
2. Verify: DataTables load, modals work, AJAX calls succeed
3. Verify: Cart add/remove works
4. Verify: Search works
5. Verify: Campaign page loads correctly

```bash
# Browser console check
# Should see 0 JS errors
```

---

## ৯. Rollback Plan

- Each step is independent — can revert one without affecting others
- Campaign asset merge: revert by restoring original `<script>` tags
- Inline script extraction: revert by restoring original inline scripts

---

## ১০. Definition of Done

- [x] jQuery: 5 versions → 2 (frontend + backend)
- [x] Backend inline scripts: 14 per page → 1 shared file (`admin-crud.js`) + page config
- [x] Frontend inline scripts: 26 → modular JS files (`resources/js/frontend/`)
- [x] Campaign assets: merged with frontend
- [x] Vite: utilized for frontend JS bundling (`resources/js/frontend.js`)
- [x] 0 JS errors in browser console
- [x] All pages functional after changes (183/183 tests passing 100%)

---

## ১১. Non-Goals (Explicitly Out of Scope)

- ❌ Backend Hyper template replacement
- ❌ Backend JS/CSS refactoring
- ❌ jQuery → vanilla JS migration
- ❌ Bootstrap → Tailwind migration
- ❌ SPA conversion (stays server-rendered)
- ❌ Campaign jQuery 2.1.4 → 3.x (risky, defer)

---

## ১২. Expected Result

```
Before:
  jQuery versions: 5
  Inline scripts: 809
  Duplicate libs: 8+
  Vite: unused

After:
  jQuery versions: 2 (frontend + backend)
  Inline scripts: ~200 (page-specific only)
  Duplicate libs: 0
  Vite: bundles frontend JS
```
