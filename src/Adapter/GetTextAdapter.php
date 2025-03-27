<?php

namespace PoMoh\Adapter;

class GetTextAdapter implements AdapterInterface {

    public function __construct(string $locale, string $text_domain, string $directory) {
        putenv("LC_ALL={$locale}");
        setlocale(LC_ALL, $locale);
        bindtextdomain($text_domain, $directory);
        textdomain($text_domain);
    }

    public function translate(string $string): string {
        return gettext($string);
    }

    public function pluralize(string $singular, string $plural, int $count): string {
        return ngettext($singular, $plural, $count);
    }
}
