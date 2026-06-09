# E-Commerce API

A robust **Laravel REST API** for an e-commerce platform with full-text search and clean architecture.

## Tech Stack

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=flat&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat&logo=mysql&logoColor=white)
![REST API](https://img.shields.io/badge/REST_API-009688?style=flat)

## Features

- Full-text site-wide search
- Products, categories, orders endpoints
- Authentication with Laravel Sanctum
- CORS configured for frontend separation
- Clean RESTful resource routing

## Getting Started

```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed && php artisan serve
```

## License
MIT
