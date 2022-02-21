<h1 align=center>NuSOAP</h1>

<p align=center>
NuSOAP is a rewrite of SOAPx4, provided by NuSphere and Dietrich Ayala. It is a set of PHP classes - no PHP extensions required - that allow developers to create and consume web services based on SOAP 1.1, WSDL 1.1 and HTTP 1.0/1.1.
</p>

<p align=center>
🕹 <a href="https://f3l1x.io">f3l1x.io</a> | 💻 <a href="https://github.com/f3l1x">f3l1x</a> | 🐦 <a href="https://twitter.com/xf3l1x">@xf3l1x</a>
</p>

<p align=center>
  All credits belongs to official authors, take a look at <a href="https://sourceforge.net/projects/nusoap/">sourceforge.net/projects/nusoap/</a>
</p>

<p align=center>
    <a href="https://travis-ci.org/pwnlabs/nusoap"><img src="https://img.shields.io/travis/pwnlabs/nusoap.svg?style=flat-square"></a>
    <a href="https://packagist.org/packages/econea/nusoap"><img src="https://img.shields.io/packagist/l/econea/nusoap.svg?style=flat-square"></a>
    <a href="https://packagist.org/packages/econea/nusoap"><img src="https://img.shields.io/packagist/dt/econea/nusoap.svg?style=flat-square"></a>
    <a href="https://packagist.org/packages/econea/nusoap"><img src="https://img.shields.io/packagist/v/econea/nusoap.svg?style=flat-square"></a>
    <a href="http://bit.ly/ctteg"><img src="https://img.shields.io/gitter/room/contributte/contributte.svg?style=flat-square"></a>
</p>

-----

## Info

- Supported PHP: [5.4 - 8.1](https://travis-ci.org/pwnlabs/nusoap)
- Latest version: [0.9.11](https://github.com/pwnlabs/nusoap/releases/tag/v0.9.11)
- Dev version: [develop](https://github.com/pwnlabs/nusoap/tree/develop)
- Official project: https://sourceforge.net/projects/nusoap/

## Installation

To install this library use [Composer](https://getcomposer.org/).

```
composer require econea/nusoap
```

**Bleeding edge**

If you want to test bleeding edge, follow this.

```json
{
  "require": {
    "econea/nusoap": "dev-develop"
  },
  "minimum-stability": "dev",
  "prefer-stable": true
}
```

## Usage

```php
// Config
$client = new nusoap_client('example.com/api/v1', 'wsdl');
$client->soap_defencoding = 'UTF-8';
$client->decode_utf8 = FALSE;

// Calls
$result = $client->call($action, $data);
```

## Development

See [how to contribute](https://contributte.org/contributing.html) to this package.

This package is currently maintaining by these authors.

<a href="https://github.com/f3l1x">
    <img width="80" height="80" src="https://avatars2.githubusercontent.com/u/538058?v=3&s=80">
</a>

-----

Consider to [support](https://github.com/sponsors/f3l1x) **f3l1x**. Also thank you for using this package.
