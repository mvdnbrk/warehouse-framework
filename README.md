# Laravel Warehouse Framework

![PHP version][ico-php-version]
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Tests][ico-tests]][link-tests]
[![Code style][ico-code-style]][link-code-style]
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

Add inventory to a location with a `GTIN` value, you may pass an amount as the second parameter:

``` php
$location = Location::find(1);
$location->addInventory('1300000000000');
$location->addInventory('1234567890005', 2);
```

Move inventory to another location:

```php
$inventory = Inventory::first();
$inventory->move($location);
```

You may also move inventory with it's `GTIN` from one location to another:

``` php
$location1 = Location::find(1);
$location2 = Location::find(2);
$location1->addInventory('1234567890005');

$location1->move('1234567890005', $location2);
```

Moving many items at once from one location to another:

```php
$location->moveMany([
    '1234567890005',
    '1234567890005',
], $location2);
```

> **note**: If you are trying to move many items at once and a failure occurs, an exception will be thrown and none of the items will be moved from one location to another.

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

Add order lines with the `addLine` method by passing a `GTIN` value, you may pass an amount as the second parameter:

```php
$order->addLine('1300000000000');
$order->addLine('1234567890005', 2);
$order->addLine(...);
```

> **note**: You can only add order lines when the status of an order is either `created` or `hold`.  
> The same is true if you try to delete an order line.

Process the order:

```php
$order->process();
```

This will update the order status to `open` and will be ready to be picked.

### Put an order on hold

You can put an order on hold by calling the `hold` method.
Unhold it with the `unhold` method:

```php
$order->hold();
$order->unhold();
```

### Order status

You can determine the status of an order with the following methods on the `status` property:

```php
$order->status->isCreated();
$order->status->isOpen();
$order->status->isBackorder();
$order->status->isHold();
$order->status->isFulfilled();
$order->status->isDeleted();
```


### Pick Lists

Once you have created an order you may retrieve a pick list.  
To determine if a pick list is available and retrieve it:

```php
$order->hasPickList();

$order->pickList();
```

The `pickList` method returns a collection:

```php
$order->pickList()->each(function ($item) {
    $item->get('gtin');
    $item->get('location');
    $item->get('quantity');
});
```

When the order is picked you can mark it as fulfilled with the `markAsFulfilled` method:

```php
$order->markAsFulfilled();
````

### Replacing an order line

If for some reason a product is missing or for example the items is damaged you may replace an order line with the `replace` method:

```php
$order->lines->first()->replace();
```

This will delete the reserved product from the inventory and replaces it with another item (if available).

### Stock

To query stock quantities you may use the `\Just\Warehouse\Facades\Stock` facade:

```php
Stock::available();
Stock::backorder();
Stock::reserved();
```

For a specific GTIN:

```php
Stock::gtin('1300000000000')->available();
Stock::gtin('1300000000000')->backorder();
Stock::gtin('1300000000000')->reserved();
```

### Events

This packages fires several events:

- `InventoryCreated`
- `OrderLineCreated`
- `OrderLineReplaced`
- `OrderStatusUpdated`
- `OrderFulfilled`

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Mark van den Broek][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-php-version]: https://img.shields.io/packagist/php-v/mvdnbrk/warehouse-framework?style=flat-square
[ico-version]: https://img.shields.io/packagist/v/mvdnbrk/warehouse-framework.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-tests]: https://img.shields.io/travis/mvdnbrk/warehouse-framework/master.svg?style=flat-square
[ico-code-style]: https://styleci.io/repos/183472123/shield?branch=master
[ico-downloads]: https://img.shields.io/packagist/dt/mvdnbrk/warehouse-framework.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/mvdnbrk/warehouse-framework
[link-tests]: https://travis-ci.org/mvdnbrk/warehouse-framework
[link-code-style]: https://styleci.io/repos/183472123
[link-downloads]: https://packagist.org/packages/mvdnbrk/warehouse-framework
[link-author]: https://github.com/mvdnbrk
[link-contributors]: ../../contributors

