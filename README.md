# Laravel eloquent models cache helper

## Install

To install the package in your project, you need to require the package via composer:

```sh
composer require mixislv/laravel-model-self-cache
```

## Basic usage

Add the trait to your model.

```php
namespace App;

use mixisLv\SelfCache\Traits\SelfCache;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use SelfCache;

    /**
     * @see \App\Traits\SelfCache;
     * @var string
     */
    protected static $selfCacheKeyId = 'id';

    /**
     * @see \App\Traits\SelfCache;
     * @var int
     */
    protected static $selfCacheKeyExpiration = 134;

    // ...
}
```

Retrieve a model by its primary key.

```php
    $user = User::getBySelfCacheId($userId);
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information about recent changes.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email info@mixis.lv instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
