# E-Commerce REST API

Laravel 8 JWT-authenticated REST API serving the complete back-end for the e-commerce platform — product catalogue, vendor onboarding (KYC), orders, cart, wishlist, shipping, collections, reviews, admin panel, and mobile app. Shared by both the `ecommerce-website` Blade frontend and a mobile application.

![Laravel](https://img.shields.io/badge/Laravel-8.x-FF2D20?style=flat&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat&logo=php&logoColor=white)
![JWT](https://img.shields.io/badge/JWT--Auth-tymon-000000?style=flat&logo=jsonwebtokens)
![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?style=flat&logo=mysql&logoColor=white)
![Pusher](https://img.shields.io/badge/Pusher-Realtime-300D4F?style=flat&logo=pusher&logoColor=white)
![Google Translate](https://img.shields.io/badge/Google-Translate_API-4285F4?style=flat&logo=googletranslate)

## Features

**Authentication (JWT)** — Register, login, logout, refresh, social login (Google/Facebook), OTP email/mobile verification, vendor registration.

**Storefront (Public)**
- Homepage: banners, categories, featured/sale/mega-deals/top-selling products, featured sellers
- Product detail: full info, reviews, Q&A, like tracking
- Brands, delivery slots, product search and refinement

**Buyer (Authenticated)**
- Profile, password, delivery addresses (country/city lookup)
- Reviews — submit pending/past reviews with images
- Q&A CRUD, product likes
- Cart — add/remove/update, transfer to wishlist
- Wishlist — add/remove/bulk operations
- Orders — place (web + app + guest), list, detail, cancel
- User store + collection management

**Vendor**
- Full onboarding: business info, KYC documents, mobile/email verification
- Custom messages CRUD

**Shipping**
- Pending delivery requests, status updates

**Admin Panel** (200+ routes)
- Dashboard stats
- Products CRUD + bulk Excel upload + stock management
- 3-level category tree CRUD
- Brands, attributes, variants CRUD
- Orders: list, status update, commission management
- Vendors: approve/reject, store management, user management
- Customers and buyer management
- Coupons CRUD
- Banners, partners, mobile covers CRUD
- Commission rates management
- Translation management (Google Translate — EN/AR bilingual content)
- Sub-roles and permissions CRUD

## Database Schema (60+ tables)

| Table | Key Columns | Purpose |
|---|---|---|
| `users` | `id`, `name`, `role_id`, `email`, `mobile`, `vendor_profile_status`, `provider_id` | All users (buyers, vendors, admins) |
| `stores` | `id`, `user_id`, `store_name`, `logo_image`, `commission_rate`, `holiday_mode` | Vendor stores |
| `categories` / `sub_categories` / `child_categories` | `id`, `title`, `slug`, `featured`, `popular`, `status` | 3-level product category tree |
| `brands` | `id`, `name`, `logo`, `slug` | Product brands |
| `products` | `id`, `store_id`, `category_id`, `name`, `slug`, `primary_image`, `avg_rating`, `likes`, `views`, `sales` | Product catalogue |
| `product_variants` | `product_id`, `price`, `special_price`, `quantity`, `seller_sku`, `image` | Variant pricing and stock |
| `product_attributes` | `product_id`, `variant_id`, `key_id`, `value` | Attribute values per variant |
| `product_reviews` | `product_id`, `user_id`, `rating`, `review` | Customer reviews |
| `product_questions` | `product_id`, `user_id`, `question`, `answer` | Product Q&A |
| `cart_items` | `user_id`, `product_variant_id`, `quantity` | Shopping cart |
| `wishlist_items` | `user_id`, `product_variant_id` | Wishlist |
| `orders` | `id`, `user_id`, `total`, `commission`, `delivery_slot_id`, `address_id` | Orders |
| `order_packages` | `order_id`, `store_id`, `subtotal`, `commission` | Per-vendor order split |
| `order_package_items` | `order_package_id`, `product_variant_id`, `quantity`, `price` | Line items |
| `order_package_history` | `order_package_id`, `status`, `note` | Status audit trail |
| `business_information` | `user_id`, `company_name`, `person_id_type`, `person_id_no` | Vendor KYC |
| `business_documents` | `user_id`, `document_id`, `file_path`, `status` | KYC documents |
| `user_addresses` | `user_id`, `address_type_id`, `country_id`, `city_id`, `street` | Delivery addresses |
| `user_store` / `collections` | `user_id`, `name`, `slug`, `likes`, `followers` | User-created stores and collections |
| `commissions` | `store_id`, `category_id`, `rate` | Vendor commission rates |
| `coupons` | `code`, `discount_type`, `discount_value`, `min_order`, `expires_at` | Discount coupons |
| `delivery_slots` | `name`, `start_time`, `end_time`, `capacity` | Delivery time windows |
| `translations` | `model_type`, `model_id`, `field`, `locale`, `value` | EN/AR bilingual content |
| `site_settings` / `app_settings` | `key`, `value` | Platform configuration |
| `stock_histories` | `product_variant_id`, `quantity_change`, `reason` | Stock audit log |

## Architecture

```
ecommerce-website (Blade)  ─┐
Mobile App                  ├──► ecommerence-api (Laravel 8 REST)
Admin SPA / direct calls    ─┘          │
                                        ├── MySQL (60+ tables)
                                        ├── Pusher (real-time)
                                        ├── Google Translate (EN/AR)
                                        ├── AWS S3 (product images)
                                        └── Shipping webhooks
```

Three consumer groups: (1) public/guest for browsing, (2) JWT buyer-authenticated for cart/orders, (3) admin with sub-role permission gates.

## Getting Started

```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate
php artisan jwt:secret
php artisan serve
```

## Environment Variables

| Variable | Purpose |
|---|---|
| `DB_*` | MySQL connection |
| `JWT_SECRET` | JWT signing secret (generate with `php artisan jwt:secret`) |
| `AWS_*` | S3 for product images |
| `PUSHER_*` | Real-time notifications |
| `MAIL_*` | SMTP configuration |

## License
MIT
