# NgpContact

[![Software License][ico-license]](LICENSE)

The NgpContact class allows you to easily create and save contacts to an NGP account via Php.

## Install

Via Composer

``` bash
$ composer require nmcteam/ngp-contact
```

## Usage

``` php
$person = array(
   'firstName' => 'Han',
   'lastName' => 'Solo',
   'email' => 'scruffy.nerfherder@rebelalliance.org', //REQUIRED
);
$contact = new NgpContact('your-ngp-api-key', $person);
$contact->save();
```

## Security

If you discover any security related issues, please email sam@newmediacampaigns.com instead of using the issue tracker.

## Credits

- [Sam LeBlanc][link-author]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square

[link-author]: https://github.com/scleblanc