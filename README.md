# Laravel Service Classes Tutorial

This tutorial demonstrates the implementation of service classes in a Laravel project. The goal of this tutorial is to demonstrate how to separate business logic from controllers and make the code more maintainable and testable.

## Important Files to Check

-   [`app/Http/Controllers/OrderController.php`](https://github.com/izemomar/laravel-service-classes/blob/main/app/Http/Controllers/OrderController.php)
-   [`app/Services/OrderService.php`](https://github.com/izemomar/laravel-service-classes/blob/main/app/Services/OrderService.php)
-   [`app/Http/Requests/StoreOrderRequest.php`](https://github.com/izemomar/laravel-service-classes/blob/main/app/Http/Requests/StoreOrderRequest.php)
-   [`tests/Feature/OrderControllerTest.php`](https://github.com/izemomar/laravel-service-classes/blob/main/tests/Feature/OrderControllerTest.php)
-   [`tests/Feature/OrderServiceTest.php`](https://github.com/izemomar/laravel-service-classes/blob/main/tests/Feature/OrderServiceTest.php)

## Getting Started

1. Clone the project:

```bash
git clone https://github.com/izemomar/laravel-service-classes
```

2. Install the dependencies:

```bash
composer install
```

3. Set up the database (Note: The project is using SQLite. If you want to use a different database, you can change it in the `database` config file)

```bash
php artisan migrate
```

4. Seed the database:

```bash
php artisan db:seed
```

5. Start the server:

```bash
php artisan serve
```

To run tests, run

```bash
php artisan test
```
