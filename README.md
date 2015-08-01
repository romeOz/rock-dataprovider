Data providers (sorting, pagination)
=======================

Independent fork by [Yii2 Data 2.0.4](https://github.com/yiisoft/yii2).

[![Latest Stable Version](https://poser.pugx.org/romeOz/rock-dataprovider/v/stable.svg)](https://packagist.org/packages/romeOz/rock-dataprovider)
[![Total Downloads](https://poser.pugx.org/romeOz/rock-dataprovider/downloads.svg)](https://packagist.org/packages/romeOz/rock-dataprovider)
[![Build Status](https://travis-ci.org/romeOz/rock-dataprovider.svg?branch=master)](https://travis-ci.org/romeOz/rock-dataprovider)
[![HHVM Status](http://hhvm.h4cc.de/badge/romeoz/rock-dataprovider.svg)](http://hhvm.h4cc.de/package/romeoz/rock-dataprovider)
[![Coverage Status](https://coveralls.io/repos/romeOz/rock-dataprovider/badge.svg?branch=master)](https://coveralls.io/r/romeOz/rock-dataprovider?branch=master)
[![License](https://poser.pugx.org/romeOz/rock-dataprovider/license.svg)](https://packagist.org/packages/romeOz/rock-dataprovider)

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

 * PHP 5.4+
 * [Rock DB](https://github.com/romeOz/rock-db) **(optional)**. Should be installed: `composer require romeoz/rock-db:*`

License
-------------------

Data providers is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).