Data providers (sorting, pagination)
=======================

Independent fork by [Yii2 Data 2.0.4](https://github.com/yiisoft/yii2).

Features
-------------------

 * Providers:
    - array
    - active (required by [Rock DB](https://github.com/romeOz/rock-db/))
 * Pagination
 * Sorting
 * **Standalone module/component for [Rock Framework](https://github.com/romeOz/rock)**
 
> Bolded features are different from [Yii2 Data](https://github.com/yiisoft/yii2).

Installation
-------------------

From the Command Line:

`composer require romeoz/rock-dataprovider:*`

In your composer.json:

```json
{
    "require": {
        "romeoz/rock-dataprovider": "*"
    }
}
```

Requirements
-------------------

 * **PHP 5.4+**
 * [Rock DB](https://github.com/romeOz/rock-db) **(optional)**. Should be installed: `composer require romeoz/rock-db:*`

License
-------------------

Data providers is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).