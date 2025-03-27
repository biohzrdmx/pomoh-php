<?php

namespace PoMoh\Adapter;

interface AdapterInterface {

    public function translate(string $string): string;

    public function pluralize(string $singular, string $plural, int $count): string;
}
