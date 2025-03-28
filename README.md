# pomoh-php

Translation abstraction library for PO/MO files

Make your projects translatable with the use of PO/MO files with or without `ext-gettext`.

### Basic usage

First require `biohzrdmx/pomoh-php` with Composer.

#### Creating a `Translator` instance

Then create a `Translator` instance:

```php
use PoMoh\Translator;

$translator = new Translator($adapter);
```

#### Adapters

You will need to pass an `AdapterInterface` implementation instance; PoMoh includes two:

- `GetTextAdapter` - Use the built-in gettext functions, requires `ext-gettext`
- `MoFileAdapter` - Load and parse MO files in runtime, doesn't require `ext-gettext` but may be a little slower

For example, to use `GetTextAdapter`:

```php
use PoMoh\Translator;
use PoMoh\Adapter\GetTextAdapter;

$adapter = new GetTextAdapter('es_ES', 'pomoh', dirname(__FILE__) . '/lang');
$translator = new Translator($adapter);
```

As you can see you will need to pass the language identifier, the text-domain and the directory where the translations reside, according to the `<directory>/<language>/LC_MESSAGES/<textdomain>.mo` structure, for example:

```
/lang/es_ES/LC_MESSAGES/pomoh.po
/lang/es_ES/LC_MESSAGES/pomoh.mo
/lang/de_DE/LC_MESSAGES/pomoh.po
/lang/de_DE/LC_MESSAGES/pomoh.mo
/lang/fr_FR/LC_MESSAGES/pomoh.po
/lang/fr_FR/LC_MESSAGES/pomoh.mo
```

If you can not (or don't want to) use `ext-gettext` you can leverage the `MoFileAdapter`:

```php
use PoMoh\Translator;
use PoMoh\Adapter\MoFileAdapter;

$adapter = new MoFileAdapter('es_MX', dirname(__FILE__) . '/lang');
$translator = new Translator($adapter);
```

This adapter takes only two parameters, the language code and the directory where the translations reside, according to the `<directory>/<language>.mo` structure, for example:

```
/lang/es_ES.po
/lang/es_ES.mo
/lang/de_DE.po
/lang/de_DE.mo
/lang/fr_FR.po
/lang/fr_FR.mo
```

#### Translating strings

Once you have a `Translator` instance just call the `translate` method:

```php
$string = $translator->translate('This is a string');
```

#### Pluralization

The library includes basic support for pluralization through the `pluralize` method:

```php
$string = $translator->pluralize('A fox', '%d foxes', 5);
```

Pluralization rules are either handled by `ngettext` or directly loaded from the MO file; either way they are language dependent.

If no pluralization rules are found the default one will be used (`nplurals=2; plural=n == 1 ? 0 : 1;`).

### Acknowledgements

This library includes code from other projects, namely:

- [phpmyadmin/motranslator](https://github.com/phpmyadmin/motranslator) - GPL Licensed
- [WordPress/WordPress](https://github.com/WordPress/WordPress) - GPL Licensed

### Licensing

This software is released under the MIT license.

Copyright © 2025 biohzrdmx

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

### Credits

**Lead coder:** biohzrdmx &lt;[github.com/biohzrdmx](http://github.com/biohzrdmx)&gt;
