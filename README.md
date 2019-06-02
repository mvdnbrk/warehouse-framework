# Laravel Warehouse Framework

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![StyleCI][ico-style-ci]][link-style-ci]
[![Total Downloads][ico-downloads]][link-downloads]

## Installation

You can install the package via composer:

```bash
composer require mvdnbrk/warehouse-framework
```

Run the install command:

```bash
php artisan warehouse:install
```

This package uses it's own database.  
By default we assume that you will prepare a connection called "warehouse" in your `config/database.php` file.  
If you would like to use a different connection you can do so by setting `WAREHOUSE_DB_CONNECTION` in your `.env` file.

Now you can run the migrations for this package with:

```bash
php artisan warehouse:migrate
```

## Usage

### Locations

You can retrieve all locations using the `Just\Warehouse\Models\Location` model:

``` php
Location::all();
```

Create a location with this artisan command:

```bash
php artisan warehouse:make:location
```

### Inventory

Add inventory to a location with a GTIN:

``` php
$location = Location::find(1);
$location->addInventory('1234567890005');
```

Move inventory to another location:

```php
$inventory = Inventory::first();
$inventory->moveTo($location);
```

Remove inventory from a location:

``` php
$location = Location::find(1);
$location->removeInventory('1234567890005');
```

Remove **all** inventory from a location:

``` php
$location = Location::find(1);
$location->removeAllInventory();
```

### Orders

Create a new order:

```php
$order = Order::create([
    'order_number' => 'my-first-order-0001',
]);
```

Add order lines:

```php
$order->addLine('1234567890005');
$order->addLine(...);
```

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email mvdnbrk@gmail.com instead of using the issue tracker.

## Credits

- [Mark van den Broek][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/mvdnbrk/warehouse-framework.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/mvdnbrk/warehouse-framework/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/mvdnbrk/warehouse-framework.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/mvdnbrk/warehouse-framework.svg?style=flat-square
[ico-style-ci]: https://styleci.io/repos/149487979/shield?branch=master
[ico-downloads]: https://img.shields.io/packagist/dt/mvdnbrk/warehouse-framework.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/mvdnbrk/warehouse-framework
[link-travis]: https://travis-ci.org/mvdnbrk/warehouse-framework
[link-scrutinizer]: https://scrutinizer-ci.com/g/mvdnbrk/warehouse-framework/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/mvdnbrk/warehouse-framework
[link-style-ci]: https://styleci.io/repos/183472123
[link-downloads]: https://packagist.org/packages/mvdnbrk/warehouse-framework
[link-author]: https://github.com/mvdnbrk
[link-contributors]: ../../contributors

