# PHPStan PSR3 logger rules

PHPStan rules for [PSR3](https://www.php-fig.org/psr/psr-3/) compatible logger packages to report misuse and possible security risks.

Supports
- psr/log interface
- any PSR3 compatible logger package, e.g. monolog/monolog
- illuminate/log facade (laravel)
- REDAXO rex_logger

## Related articles

- [Using PSR-3 placeholders properly](https://peakd.com/hive-168588/@crell/using-psr-3-placeholders-properly) by [@crell](https://github.com/Crell)

## Installation

To use this extension, require it in [Composer](https://getcomposer.org/):

```
composer require --dev staabm/phpstan-psr3
```

If you also install [phpstan/extension-installer](https://github.com/phpstan/extension-installer) then you're all set!

<details>
  <summary>Manual installation</summary>

If you don't want to use `phpstan/extension-installer`, include extension.neon in your project's PHPStan config:

```
includes:
    - vendor/staabm/phpstan-psr3/extension.neon
```

</details>
