# Multi-Vendor eCommerce REST API

> **Laravel 8 JWT-authenticated REST API** powering a full-featured multi-vendor marketplace. Serves three frontend applications (admin panel, vendor portal, customer storefront) and mobile apps. Features a 3-level category hierarchy, vendor KYC pipeline, multi-vendor order splitting with per-package commissions, Google Translate auto-translation, Pusher real-time events, Laravel Scout search, and 115+ database tables.

![Laravel](https://img.shields.io/badge/Laravel-8-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.0-777BB4?style=flat-square&logo=php&logoColor=white)
![JWT](https://img.shields.io/badge/JWT_Auth-1.0-000000?style=flat-square)
![Pusher](https://img.shields.io/badge/Pusher-Real--time-300D4F?style=flat-square&logo=pusher&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-115+_tables-4479A1?style=flat-square&logo=mysql&logoColor=white)

---

## Table of Contents
- [Architecture](#architecture)
- [Tech Stack](#tech-stack)
- [Route Structure](#route-structure)
- [Database Schema](#database-schema)
- [Multi-Vendor Order System](#multi-vendor-order-system)
- [Vendor KYC Pipeline](#vendor-kyc-pipeline)
- [Category Hierarchy](#category-hierarchy)
- [Multi-Language System](#multi-language-system)
- [Commission System](#commission-system)
- [User Stores (Customer Marketplace)](#user-stores-customer-marketplace)
- [Getting Started](#getting-started)

---

## Architecture

```
Mobile Apps  ←———+
                  |
Customer SPA ←———+———→ ecommerence-api (THIS REPO — Laravel 8 JWT API)
                  |           |
Vendor Panel ←———+           +→ MySQL (115+ tables)
                  |           +→ Pusher (real-time)
Admin Panel  ←———+           +→ Google Translate (Arabic auto-translation)
                              +→ Laravel Scout (full-text search)
```

All three frontend apps (`ecommerence-admin`, `ecommerence-vendor`, `ecommerce-website`) proxy their data operations through this API using JWT tokens stored in session.

---

## Tech Stack

| Package | Version | Purpose |
|---|---|---|
| `laravel/framework` | ^8.40 | Core framework |
| `tymon/jwt-auth` | ^1.0 | JWT authentication (buyers, vendors, admins) |
| `stichoza/google-translate-php` | ^4.1 | Auto-translate content to Arabic |
| `maatwebsite/excel` | ^3.1 | Bulk product import/export |
| `intervention/image` | ^2.7 | Image resize + processing |
| `pusher/pusher-php-server` | ^7.0 | Real-time notifications |
| `laravel/scout` | ^9.4 | Full-text search indexing |
| `spatie/laravel-json-api-paginate` | ^1.10 | JSON:API pagination |
| `mavinoo/laravel-batch` | ^2.3 | Batch DB update operations |
| `staudenmeir/belongs-to-through` | ^2.11 | Deep Eloquent relationships |

**Auth:** `tymon/jwt-auth` — stateless JWT. `User` model implements `JWTSubject`. Middleware: `jwt.verify`, `isBuyer`, `isVendor`, `isAdmin`.

---

## Route Structure

Routes split across 4 files: `api.php` (public + buyer), `vendor.php`, `admin.php`, `web.php`.

### Authentication & OTP (`api.php`)
POST `/api/login`, `/api/register`, `/api/vendor/register`, `/api/social/login`, `/api/refresh`, `/api/logout`, `/api/me`, `/api/account/delete`
OTP: `/api/otp/send`, `/api/otp/verify`, email verification codes, mobile verification, password reset via OTP/email.

### Public Catalog Endpoints
```
GET /api/categories-with-subcategories   — Full tree for navigation
GET /api/categories/featured             — Featured categories
GET /api/banners                         — Homepage banners
GET /api/products/recommended            — Curated products
GET /api/products/sale-of-day            — Flash sale items
GET /api/products/featured               — Featured products
GET /api/products/mega-deals             — Mega deal items
GET /api/products/top-selling            — Top sellers
GET /api/sellers/featured                — Featured vendors
POST /api/category/{id}/products         — Filtered category products
GET /api/product/{id}                    — Product detail by ID
GET /api/product/detail/{slug}           — Product detail by slug
GET /api/product/{id}/reviews            — Product reviews
GET /api/product/{id}/questions          — Product Q&A
```

### Search
```
GET /api/search/{key}                    — Full-text search (Laravel Scout)
GET /api/search/refine/{type}/{key}      — Search refinement by type
```

### Mobile App (`/api/app/*`)
Homescreen, categories, top-selling, most-selling, filter, product detail — optimized mobile response shapes.

### Authenticated Buyer (`isBuyer` middleware)
Profile CRUD, address CRUD, reviews (pending + past + submit), questions CRUD, product likes, orders (place + list + detail + cancel), cart (add/remove/quantity/empty), wishlist (add/remove/empty), user stores, collections.

### Vendor Routes (`vendor.php`, `isVendor`)
Profile multi-step (basic → business → documents → bank → warehouse → return → request-approval), products (CRUD + variants + translation + status + bulk), orders (filter by status + update), coupons, reviews/questions (reply + report), notifications, commissions, sub-user management.

### Admin Routes (`admin.php`)
All 3-level categories, vendor lifecycle, product management (variants, stock, mass upload), order management, brand/attribute/key CRUD, review/question moderation, banner management, commission config, site settings, city management, user management.

---

## Database Schema (115+ Tables)

### Identity & Auth
| Table | Key Columns |
|---|---|
| `users` | id, name, `role_id`, email, is_email_verified, country_code, mobile, is_mobile_verified, profile_image, status, `vendor_profile_status` |
| `subroles` | id, name, `owner_id` (vendor sub-roles) |
| `permissions` | id, name, slug, `permission_type` (admin/vendor) |
| `subrole_permissions` | pivot |
| `activity_logs` | user_id, module, action, description |

### Catalog
| Table | Key Columns |
|---|---|
| `categories` | id, title, title_ar, image, status, featured, order |
| `sub_categories` | id, category_id, title, title_ar, status, order |
| `child_categories` | id, sub_category_id, title, title_ar, status, order |
| `brands` | id, name, name_ar, image, status |
| `attributes` | id, name, name_ar, status |
| `keys` | id, name, name_ar, status |
| `attribute_key` / `attribute_subcategory` / `attribute_childcategory` | pivot tables |
| `brand_category` / `brand_subcategory` / `brand_childcategory` | pivot tables |

### Products
| Table | Key Columns |
|---|---|
| `products` | id, name, name_ar, category_id, subcategory_id, childcategory_id, brand_id, store_id, primary_image, video_url, description (EN+AR), warranty, package dimensions, status, featured, likes, views, sales, avg_rating, slug, tags, specifications; softDeletes |
| `product_variants` | id, product_id, price, special_price, quantity, seller_sku, availability, image; softDeletes |
| `product_images` | id, product_id, image (multiple images) |
| `product_attributes` / `variant_attributes` | product-attribute value storage |
| `stock_histories` | product_variant_id, quantity_before, quantity_after, action |
| `product_reviews` | id, user_id, product_id, order_package_item_id, rating, review, is_reported |
| `product_questions` | id, user_id, product_id, question, answer, is_reported |

### Orders (Multi-Vendor Splitting)
| Table | Key Columns |
|---|---|
| `orders` | id, order_no, user_id, billing/shipping address, payment_method (card/cod/bank_transfer), billing_status, delivery_slot_id; softDeletes |
| `order_packages` | id, order_id, package_no, store_id, fulfillment_id, status, fulfillment_charges, package_bill, `commission`; softDeletes |
| `order_package_items` | id, order_package_id, product_variant_id, quantity, unit_price, total_price, `commission` |
| `order_package_histories` | id, order_package_id, status, note, created_by |
| `order_status` | id, name, name_ar, color |
| `shipping_companies` | id, name, logo |
| `delivery_slots` | id, name, name_ar, time_from, time_to |

### Vendor KYC
| Table | Key Columns |
|---|---|
| `stores` | id, user_id, store_name, tag_line, category_id, logo, cover, holiday_mode, holiday_start/end_date, slug; softDeletes |
| `business_information` | user_id, company_name, country_id, city_id, company_address, zone/street/building/floor/apt_no, person_incharge name/mobile/email, person_id_type/no, person_id_front/back_image |
| `business_documents` | id, name, description |
| `document_inputs` | id, document_id, label, type, required (dynamic KYC form builder) |
| `bank_accounts` | user_id, account_title, account_no, bank_name, branch_code, iban, bank_letter_doc |
| `warehouse_addresses` / `return_addresses` | store_id, address fields |
| `vendor_requests` | user_id, status, notes |

### Customer Marketplace
| Table | Key Columns |
|---|---|
| `user_stores` | customer-owned stores with visibility settings |
| `user_store_likers` / `user_store_followers` | social pivot tables |
| `user_store_social_links` | id, user_store_id, platform, url |
| `collections` | id, user_store_id, name, visibility |
| `collection_product` / `collection_likers` / `collection_followers` | pivot tables |

### Commerce & Content
| Table | Key Columns |
|---|---|
| `coupons` | id, store_id, code (unique), apply_to (store/sku), discount_type (percent/amount), discount_value, minimum_order, quantity, remaining_coupons, per_user_limit, start_at, expire_at |
| `commissions` | id, child_category_id, store_id, storak_commission, user_stores_commission, status |
| `cart_items` | user_id, product_variant_id, quantity |
| `wishlist_items` | user_id, product_variant_id |
| `website_banners` | id, image, link, order, status |
| `translations` | table_name, column_name, record_id, lang, translation |

---

## Multi-Vendor Order System

Each `Order` can contain multiple `OrderPackage`s — one per vendor store. Each package tracks its own status, fulfillment method, commission, and shipping independently.

```
Order (master)
  |
  +— OrderPackage (Vendor A products)
  |     |— OrderPackageItem (Product 1, qty=2, commission=X)
  |     |— OrderPackageItem (Product 2, qty=1, commission=Y)
  |     |— OrderPackageHistory (status changes)
  |
  +— OrderPackage (Vendor B products)
        |— OrderPackageItem (Product 3, qty=1, commission=Z)
```

Status can be updated per-package (Vendor A ships while Vendor B still processes). Admin sees the full order; each vendor sees only their own packages.

---

## Vendor KYC Pipeline

```
1. Vendor registers (vendor_profile_status = 'incomplete')
2. Multi-step profile completion:
   a. Basic info
   b. Business information + company/person-in-charge details
   c. Business documents (dynamic form defined by admin via document_inputs)
   d. Bank account details
   e. Warehouse address
   f. Return/refund address
   g. Submit for review (vendor_profile_status = 'under-review')
3. Admin reviews:
   - Approve: vendor_profile_status = 'approved' → can start selling
   - Reject: vendor_profile_status = 'rejected' + notes
```

**Dynamic document forms:** Admin creates `business_documents` entries with configurable `document_inputs` (label, type, required). Each vendor's submission fills these fields — fully configurable KYC requirements without code changes.

---

## Category Hierarchy

Three-level: Categories → SubCategories → ChildCategories (separate tables).

- **Attributes** scoped at sub-category and child-category level
- **Brands** scoped at category, sub-category, and child-category level
- **Commissions** configured at child-category level (with optional store override)
- Products linked at child-category level for maximum specificity

---

## Multi-Language System

**Arabic (EN/AR):** All translatable models have `_ar` columns (`title_ar`, `name_ar`, `description_ar`).

**Auto-translation:** `stichoza/google-translate-php ^4.1` — `TranslationController` auto-translates EN content to Arabic and saves to `_ar` columns and the `translations` table.

**Generic `translations` table:** Stores `(table_name, column_name, record_id, lang, translation)` — extensible to any language without schema changes.

---

## Commission System

Commissions are configured at child-category level, with optional per-store override:

```
commissions
  child_category_id = 5 (Electronics)
  store_id = null        -> applies to ALL stores selling in this category
  storak_commission = 10%
  user_stores_commission = 5%
```

On order placement, commission amounts are calculated and stored in `order_packages.commission` and `order_package_items.commission` (stored as absolute values, not percentages) for permanent audit trail.

---

## User Stores (Customer Marketplace)

Buyers can create their own stores and curate product collections — a marketplace-within-a-marketplace:

- `user_stores` — buyer-owned stores with public/private visibility
- `collections` — curated product lists within a user store
- Like + follow on both stores and collections
- Social links per user store (Instagram, Twitter, etc.)

---

## Getting Started

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan jwt:secret
php artisan serve
```

**Required environment variables:**
```env
JWT_SECRET=
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=
```

## Related Repositories

| Repo | Purpose |
|---|---|
| `ecommerence-admin` | Laravel 8 Blade super-admin panel |
| `ecommerence-vendor` | Laravel 8 Blade vendor portal |
| `ecommerce-website` | Laravel 8 customer storefront |
