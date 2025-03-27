<?php

namespace PoMoh;

use PoMoh\Adapter\AdapterInterface;

class Translator {

    protected AdapterInterface $adapter;

    public function __construct(AdapterInterface $adapter) {
        $this->adapter = $adapter;
    }

    public function translate(string $string): string {
        return $this->adapter->translate($string);
    }

    public function pluralize(string $singular, string $plural, int $count): string {
        return $this->adapter->pluralize($singular, $plural, $count);
    }
}
