# GGEZ Sports

A full-stack e-commerce web app for gaming gear and peripherals, built with PHP and MySQL. It has three portals: a **customer storefront**, an **admin dashboard**, and a **courier/delivery portal**.

<!-- Add screenshots here, e.g.:
![Home page](images/screenshots/home.png)
![Admin dashboard](images/screenshots/admin.png)
-->

## Features

### Customer storefront
- Account registration and login, including **Sign in with Google** (OAuth 2.0)
- Product browsing by category, search, and quick view
- Wishlist and shopping cart with quantity updates
- Checkout with **Stripe** and **PayPal** payment integration
- Multiple saved addresses and profile management
- Order tracking (to ship / to receive / delivered), order history, and reorder
- Order cancellation and return requests
- Contact form and product ratings

### Admin dashboard
- Sales dashboard and **PDF report generation** (FPDF)
- Product management: create, update, and archive products, and manage categories
- Promotions and discounts
- Order management, including order status updates and cancelled orders
- Customer, admin, and courier account management with **role-based access**
- Customer messages, ratings, and activity logs

### Courier portal
- Courier login and assigned deliveries
- Delivery status updates and tracking
- Delivery reports and account settings

## Tech stack

- **Backend:** PHP (PDO), MySQL
- **Frontend:** HTML, CSS/SCSS, JavaScript, Swiper.js, Font Awesome
- **Integrations:** Stripe PHP SDK, PayPal REST SDK, Google OAuth 2.0 (`league/oauth2-google`), FPDF
- **Environment:** XAMPP (Apache + MySQL), Composer

## Running locally

1. Install [XAMPP](https://www.apachefriends.org/) and [Composer](https://getcomposer.org/).
2. Clone this repo into `C:\xampp\htdocs\ggezports`.
3. Install the dependencies:
   ```bash
   composer install
   ```
4. Start Apache and MySQL in XAMPP, then import `shop_db (1).sql` into a database named `shop_db` with phpMyAdmin.
5. Open http://localhost/ggezports/home.php

### API keys

The secret keys have been taken out of this repo. To enable payments and Google login, replace these placeholders with your own test credentials:

| Placeholder | File(s) |
|---|---|
| `YOUR_STRIPE_SECRET_KEY` | `stripe_checkout.php` |
| `YOUR_PAYPAL_SECRET` | `paypal_checkout.php`, `paypal_success.php` |
| `YOUR_GOOGLE_CLIENT_SECRET` | `google_login.php` |

The database connection settings are in `components/connect.php` (XAMPP defaults: user `root`, no password).
