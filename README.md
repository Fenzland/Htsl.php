Htsl.php
================================

[![Platform](https://img.shields.io/badge/PHP-v7.0-blue.svg)](http://php.net/)
[![Build Status](https://travis-ci.org/Fenzland/Htsl.php.svg?branch=test)](https://travis-ci.org/Fenzland/Htsl.php)
[![Build Status](https://scrutinizer-ci.com/g/Fenzland/Htsl.php/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Fenzland/Htsl.php/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Fenzland/Htsl.php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Fenzland/Htsl.php/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/fenzland/htsl/v/stable)](https://packagist.org/packages/fenzland/htsl)
[![Latest Unstable Version](https://poser.pugx.org/fenzland/htsl/v/unstable)](https://packagist.org/packages/fenzland/htsl)
[![License](https://poser.pugx.org/fenzland/htsl/license)](https://packagist.org/packages/fenzland/htsl)
[![Total Downloads](https://poser.pugx.org/fenzland/htsl/downloads)](https://packagist.org/packages/fenzland/htsl)
[![Monthly Downloads](https://poser.pugx.org/fenzland/htsl/d/monthly)](https://packagist.org/packages/fenzland/htsl)
[![Daily Downloads](https://poser.pugx.org/fenzland/htsl/d/daily)](https://packagist.org/packages/fenzland/htsl)


A PHP library to translate HTSL(HyperText Structured Language) to HTML.

HTSL is a language designed to take place of HTML, SVG, XML or other markup language. Which is simpler, cleaner, more readable, content evident and easy to write than HTML.

An amazing sight is all browsers work with HTSL instead of HTML. But now, we must compile HTSL into HTML for browsers. So that is why Htsl.php here.

# Platform
php ~7.0

# Documentation
See [htsl.fenzland.com](http://htsl.fenzland.com).

# Base Usage

Step 1. Get Htsl.php by composer
``` bash
composer require fenzland/htsl:@dev
```

Step 2. Make a instance of Htsl
``` php
$htsl= new Htsl\Htsl(/*[custom configuration here]*/);
```

Step 3. Compiling a HTSL file into a PHP file.
``` php
$htsl->compile($fromFile,$toFile);
```

Step 4. Include the compiled file.

# Framework support

Laravel: [Htsl 4 Laravel](https://github.com/Fenzland/Htsl-laravel)


# License

[MIT license](http://opensource.org/licenses/MIT).
