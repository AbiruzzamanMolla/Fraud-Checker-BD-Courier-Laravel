<div align="center">
  <h1>🛡️ Fraud-Checker-BD-Courier-Laravel</h1>
  <p><strong>A powerful Laravel package to analyze customer delivery behavior across top Bangladeshi courier services.</strong></p>
  
  [![Latest Version on Packagist](https://img.shields.io/packagist/v/azmolla/fraud-checker-bd-courier-laravel.svg?style=flat-square)](https://packagist.org/packages/azmolla/fraud-checker-bd-courier-laravel)
  [![Total Downloads](https://img.shields.io/packagist/dt/azmolla/fraud-checker-bd-courier-laravel.svg?style=flat-square)](https://packagist.org/packages/azmolla/fraud-checker-bd-courier-laravel)
  [![License](https://img.shields.io/packagist/l/azmolla/fraud-checker-bd-courier-laravel.svg?style=flat-square)](https://packagist.org/packages/azmolla/fraud-checker-bd-courier-laravel)
  [![PHP Version Require](https://img.shields.io/packagist/php-v/azmolla/fraud-checker-bd-courier-laravel?style=flat-square)](https://packagist.org/packages/azmolla/fraud-checker-bd-courier-laravel)
</div>

---

## 📌 Introduction

E-commerce businesses in Bangladesh often face significant losses due to fraudulent orders and high return rates. **Fraud-Checker-BD-Courier-Laravel** protects your bottom line by instantly analyzing a customer's track record across major logistics providers (**Steadfast**, **Pathao**, and **RedX**).

By checking a simple phone number, you get immediate insights into a customer's success and cancellation ratios, helping you decide whether to approve, verify, or reject cash-on-delivery (COD) shipments.

## 🎯 Key Capabilities

- 🔍 **Multi-Courier Analytics:** Fetch delivery histories simultaneously from Steadfast, Pathao, and RedX.
- 📊 **Aggregated Statistics:** Get a unified view of total deliveries, successes, cancellations, and percentages.
- 📱 **Smart Number Validation:** Built-in strictly enforced validation for standard Bangladeshi mobile numbers (e.g., `017XXXXXXXX`).
- 🏗️ **SOLID Architecture:** Highly decoupled under the hood. You can easily interact with individual couriers thanks to strict contract implementations.
- ⚡ **Developer Friendly:** Simple Facade access and effortless Laravel integration.

---

## 💻 Requirements

- **PHP:** `^8.2.0`
- **Laravel:** `8.x`, `9.x`, `10.x`, `11.x`, `12.x`, or `13.x`
- **Guzzle:** `^7.8`

---

## 📦 Installation

**1. Install via Composer:**

```bash
composer require azmolla/fraud-checker-bd-courier-laravel
```

**2. Publish Configuration File:**

```bash
php artisan vendor:publish --tag="config"
```

_(This will create a `config/fraud-checker-bd-courier.php` file in your application directory.)_

_(Note: Laravel Auto-Discovery is fully supported. If you are using Laravel 5.4 or older, you will need to manually register the Service Provider and Facade in `config/app.php`.)_

---

## 🧩 Environment Setup

To authenticate with the respective courier APIs, strictly add your credentials to your application's `.env` file:

```dotenv
# Pathao Credentials
PATHAO_USER="your_pathao_email@example.com"
PATHAO_PASSWORD="your_pathao_password"

# Steadfast Credentials
STEADFAST_USER="your_steadfast_email@example.com"
STEADFAST_PASSWORD="your_steadfast_password"

# RedX Credentials
# Use your registered phone number (without +880, e.g., 01*********)
REDX_PHONE="your_redx_login_phone_number"
REDX_PASSWORD="your_redx_password"
```

---

## 🚀 Quick Start

Checking a customer's fraud probability is a one-liner using the provided Facade.

```php
use FraudCheckerBdCourier;

// Input a standard 11-digit Bangladeshi mobile number
$report = FraudCheckerBdCourier::check('01*********');

dump($report);
```

### 🖥️ API Response GUI

![API Response GUI](Screenshot%202026-03-07%20122633.png)

### 📈 Structural Response Example

The package returns a highly structured array indicating individual and aggregated network metrics:

```php
[
    'steadfast' => ['success' => 3, 'cancel' => 1, 'total' => 4, 'success_ratio' => 75.0],
    'pathao'    => ['success' => 5, 'cancel' => 2, 'total' => 7, 'success_ratio' => 71.43],
    'redx'      => ['success' => 20, 'cancel' => 5, 'total' => 25, 'success_ratio' => 80.0],

    // The summary across all supported couriers
    'aggregate' => [
        'total_success'    => 28,
        'total_cancel'     => 8,
        'total_deliveries' => 36,
        'success_ratio'    => 77.78,
        'cancel_ratio'     => 22.22
    ]
]
```

---

## 🛠️ Advanced Usage (SOLID Design)

For granular control, the package is architected using SOLID principles. Every courier class adheres to `Azmolla\FraudCheckerBdCourier\Contracts\CourierServiceInterface`, guaranteeing a uniform `getDeliveryStats(string $phoneNumber): array` signature.

### Instantiating Individual Services

If you only need to run analytics against a single courier, avoid the Facade overhead and use the specific service classes directly:

```php
use Azmolla\FraudCheckerBdCourier\Services\PathaoService;
use Azmolla\FraudCheckerBdCourier\Services\SteadfastService;
use Azmolla\FraudCheckerBdCourier\Services\RedxService;

$steadfastData = (new SteadfastService())->getDeliveryStats('01*********');
$redxData      = (new RedxService())->getDeliveryStats('01*********');
$pathaoData    = (new PathaoService())->getDeliveryStats('01*********');
```

---

## 📱 Phone Validation Helper

Numbers are strictly validated against `^01[3-9][0-9]{8}$` to prevent unnecessary API failures.

- ✅ **Valid:** `01*********`, `01876543219`
- ❌ **Invalid:** `+8801*********`, `1234567890`, `02171234567`

You can manually trigger this validation check:

```php
use Azmolla\FraudCheckerBdCourier\Helpers\CourierDataValidator;

CourierDataValidator::checkBdMobile('01*********');
// Throws InvalidArgumentException if formatting fails
```

---

## 🧪 Testing

The package includes an extensive test suite built with `orchestra/testbench` and `phpunit`. API calls are safely mocked, meaning you do not need live `.env` credentials to confidently run tests locally.

```bash
composer test
# Or if you don't have scripts defined:
./vendor/bin/phpunit
```

---

## 🙏 Acknowledgments

Special thanks to **[S. Ahmad](https://github.com/ShahariarAhmad)** for the initial inspiration and discovering the API endpoints.

---

## ‍💻 Created By

**Abiruzzaman Molla**

- 📧 Email: [abiruzzaman.molla@gmail.com](mailto:abiruzzaman.molla@gmail.com)
- 🐙 GitHub Profile: [@AbiruzzamanMolla](https://github.com/AbiruzzamanMolla)
- 📦 Repository: [Fraud-Checker-BD-Courier-Laravel](https://github.com/AbiruzzamanMolla/Fraud-Checker-BD-Courier-Laravel)

<br/>
<div align="center">
  <i>If you find this package helpful in fighting fraudulent orders, please consider starring the repository! ⭐</i>
</div>
