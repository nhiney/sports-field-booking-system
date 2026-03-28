# Sports Field Booking System

A full-stack web application for browsing, booking, and managing sports fields. Built with Laravel, the system serves two primary roles: **Users** who search and reserve fields, and **Administrators** who manage field listings, bookings, pricing, and reporting.

> **Course**: Open Source Software Development  
> **Tech Stack**: Laravel 12, PHP 8.2, MySQL, Blade, TailwindCSS  
> **Architecture**: MVC (Model-View-Controller)

---

## Table of Contents

1. [Project Overview](#project-overview)  
2. [Features](#features)  
3. [Tech Stack](#tech-stack)  
4. [System Architecture](#system-architecture)  
5. [Project Structure](#project-structure)  
6. [Installation Guide](#installation-guide)  
7. [Future Improvements](#future-improvements)  
8. [Conclusion](#conclusion)  

---

## Project Overview

The Sports Field Booking System allows users to find available sports fields, check time-slot availability in real time, and make reservations with integrated payment support. Administrators have a dedicated dashboard for managing the entire lifecycle of fields, bookings, users, and revenue reporting.

Key objectives of the project:

- Provide a streamlined booking experience with real-time availability checks  
- Implement role-based access control to separate user and admin functionality  
- Support multiple payment methods with payment confirmation workflows  
- Deliver a responsive, modern UI using Blade templates and TailwindCSS  

---

## Features

### User Features

- **Account Registration and Login** — Secure authentication with Laravel's built-in auth system  
- **Field Search and Filtering** — Search for sports fields by type, location, and availability  
- **Field Details** — View field information, amenities, pricing, and available time slots  
- **Real-Time Availability Check** — Verify slot availability before booking  
- **Booking Management** — Create, view, and cancel bookings from a personal dashboard  
- **Payment Processing** — Complete bookings with supported payment methods  
- **Favorites** — Save frequently booked fields for quick access  
- **Notifications** — Receive booking confirmations and status updates  

### Admin Features

- **Admin Dashboard** — Overview of key metrics including total bookings, revenue, and user activity  
- **Field Management** — Full CRUD operations for sports fields (create, edit, delete, configure time slots)  
- **Booking Management** — View, filter, and manage all bookings across the platform  
- **Payment Confirmation** — Review and confirm pending payments  
- **User Management** — View and manage registered user accounts  
- **Pricing Settings** — Configure dynamic pricing rules for different time slots and field types  
- **Reports** — Generate revenue and booking reports for business insights  

---

## Tech Stack

| Layer        | Technology                          |
|--------------|-------------------------------------|
| Language     | PHP 8.2                             |
| Framework    | Laravel 12                          |
| Database     | MySQL                               |
| Templating   | Blade                               |
| Styling      | TailwindCSS                         |
| Build Tool   | Vite                                |
| Auth         | Laravel UI (session-based)          |
| Testing      | PHPUnit 11                          |
| Containerization | Docker                          |

---

## System Architecture

The application follows the **MVC (Model-View-Controller)** pattern, enforcing a clear separation of concerns across three layers:

```
┌──────────────────┐     ┌──────────────────────┐     ┌─────────────────┐
│                  │     │                      │     │                 │
│   View (Blade)   │◄───►│  Controller (PHP)    │◄───►│  MySQL Database │
│   TailwindCSS    │     │  Business Logic      │     │  Eloquent ORM   │
│                  │     │  Request Validation   │     │                 │
└──────────────────┘     └──────────────────────┘     └─────────────────┘
       │                          │                           │
  Presentation              Application                  Data Layer
  - Search UI              - AuthController            - users
  - Booking forms          - BookingController         - sports_fields
  - Admin panels           - PaymentController         - bookings
  - Notifications          - Admin Controllers         - favorites
                           - Middleware (auth, admin)   - notifications
                                                       - pricing_settings
```

**Design decisions:**

- **Eloquent ORM** — All database interactions use Laravel's Eloquent models, eliminating raw SQL and reducing the risk of injection vulnerabilities.  
- **Middleware-based access control** — Routes are protected by `auth` and `admin` middleware, ensuring role-based authorization at the routing layer.  
- **Resourceful controllers** — Admin controllers follow Laravel's resource controller conventions for consistent CRUD operations.  

---

## Project Structure

```
sports_field_booking_system/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/              # Admin controllers (Dashboard, Fields, Bookings, Users, Reports, Settings)
│   │   │   ├── AuthController.php
│   │   │   ├── BookingController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── FavoritesController.php
│   │   │   ├── NotificationController.php
│   │   │   └── PaymentController.php
│   │   └── Middleware/
│   │       └── AdminMiddleware.php
│   ├── Models/                     # Eloquent models (User, SportsField, Booking, Favorite, Notification, PricingSetting)
│   └── Providers/
├── database/
│   ├── migrations/                 # 16 migration files defining the schema
│   └── seeders/
├── resources/
│   └── views/
│       ├── admin/                  # Admin panel views (dashboard, fields, bookings, reports, settings, users)
│       ├── auth/                   # Login and registration views
│       ├── booking/                # Search, field details, booking views
│       ├── favorites/
│       ├── notifications/
│       ├── dashboard.blade.php
│       └── welcome.blade.php
├── routes/
│   └── web.php                     # All route definitions
├── public/
├── composer.json
├── package.json
├── vite.config.js
└── Dockerfile
```

---

## Installation Guide

### Prerequisites

- PHP >= 8.2  
- Composer  
- Node.js and npm  
- MySQL  

### Steps

1. **Clone the repository**

```bash
git clone https://github.com/your-username/sports-field-booking-system.git
cd sports-field-booking-system
```

2. **Install PHP dependencies**

```bash
composer install
```

3. **Install frontend dependencies**

```bash
npm install
```

4. **Configure environment variables**

```bash
cp .env.example .env
```

Open `.env` and update the database credentials:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sports_field_booking
DB_USERNAME=root
DB_PASSWORD=your_password
```

5. **Generate application key**

```bash
php artisan key:generate
```

6. **Run database migrations**

```bash
php artisan migrate
```

7. **Build frontend assets**

```bash
npm run build
```

8. **Start the development server**

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`.

**Alternative:** Run the full development stack (server, queue, logs, Vite) concurrently:

```bash
composer run dev
```

---

## Future Improvements

- **Online payment gateway integration** — Support credit card and e-wallet payments via third-party APIs (VNPay, Momo)  
- **Email and SMS notifications** — Send booking confirmations and reminders via email or SMS  
- **Field reviews and ratings** — Allow users to rate and review fields after their booking  
- **Calendar-based booking view** — Display field availability on an interactive calendar  
- **Mobile-responsive optimization** — Further refine the UI for seamless mobile experience  
- **API layer** — Expose a RESTful API with token-based authentication for mobile app integration  
- **Multi-language support** — Add localization for Vietnamese and English  

---

## Conclusion

The Sports Field Booking System is a complete, functional web application that demonstrates proficiency in full-stack development with the Laravel framework. The project covers essential aspects of real-world software engineering:

- **End-to-end feature development** — From user authentication and search to booking, payment, and admin management  
- **Clean architecture** — MVC pattern with middleware-based authorization, Eloquent ORM, and Blade templating  
- **Database design** — Normalized schema with proper migrations, relationships, and constraints  
- **Role-based access control** — Separate user and admin interfaces with route-level protection  
- **Modern frontend** — Responsive UI built with TailwindCSS and compiled via Vite  

This project reflects practical experience in building, structuring, and deploying a Laravel application that addresses a real-world use case.
