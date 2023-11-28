<p align="center"><img src="/socialcard.png" alt="Social Card of Laravel Sudani Package"></p>

# Sudani SPay API integration with Laravel

[![Latest Version](https://img.shields.io/github/release/alkhatibdev/laravel-sudani.svg?style=flat-square)](https://github.com/alkhatibdev/laravel-sudani/releases)
![Packagist Downloads (custom server)](https://img.shields.io/packagist/dt/alkhatibdev/laravel-sudani)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

# Introduction
Laravel Sudani is Sudani SPay API integration with Laravel, made to simplify the process and API calls and let developers focus on other integration parts and logic. See also [Laravel Zain](https://github.com/alkhatibdev/laravel-zain).

# Installation

## Requirements

- PHP >= `7.4.x`
- Laravel >= `7.x`

## install via composer

```shell
composer require alkhatibdev/laravel-sudani
```

## Publish Configs 

```shell
php artisan vendor:publish --tag=laravel-sudani-config
```

A `laravel-sudani.php` config file will be published on your `configs` directory, with the following content:

```php
<?php

return [

    'base_url' => env('SUDANI_SERVER_BASE_API_URL'),

    'provider_key' => env('SUDANI_PROVIDER_KEY'),

    'service_code' => env('SUDANI_SERVICE_CODE'),

    'username' => env('SUDANI_USERNAME'),

    'password' => env('SUDANI_PASSWORD'),

    'enable_logging' => false,

];
```

Don't forget to set all these variable on your `.env` file

```env
SUDANI_SERVER_BASE_API_URL=http://196.1.241.110/SPayAPI/Service/
SUDANI_PROVIDER_KEY=xxxxxxxx
SUDANI_SERVICE_CODE=xxxxx-xxxx-xxxx-xxxx-xxxxxxxxx
SUDANI_USERNAME=xxxxxx
SUDANI_PASSWORD=xxxxxx
```

# Usage

## Initial Payment/Subscription

```php
use AlkhatibDev\LaravelSudani\Facades\Sudani;

// Initiate payment request
$response = Sudani::initiate($phone)
```

When `initiate` payment request successfully sent, a SMS with `OTP` code will be send to the `$phone` number, and `$response` will contain a `requestId` and you should save it to the next step `verify`.

## Verify Payment/Subscription

```php

$response = Sudani::verify($otp, $requestId)

```

## Check Subscription

```php

$response = Sudani::checkSubscription($phone)

```

## Unsubscribe

```php

$response = Sudani::unsubscribe($phone)

// cacheToken($response['token'])

```

## Login and Cache SPay token

Out of the box the package will encrypt the password and login automatically and get the `token` and use it for each action `initiate`, `verify` ..etc per request.
If you want to cache the token and use it for furthor requests, you can request `token` like this:

```php
$token = Sudani::token()
```

And you can cache it and use it for each request for the next 24 hours, for example:

```php
// $token = getCachedToken()

$response = Sudani::withToken($token)->initiate($phone)
$response = Sudani::withToken($token)->verify($phone)
...
```

## Logging
You can enable logging from package config file 

```
'enable_logging' => true,
```

# Other Packages
- ### [Laravel Zain](https://github.com/alkhatibdev/laravel-zain) DSP API Integration

# License

Laravel Sudani is open-sourced software licensed under the [MIT license](LICENSE).
