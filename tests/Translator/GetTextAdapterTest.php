<?php

namespace PoMoh\Tests\Translator;

use PHPUnit\Framework\TestCase;

use PoMoh\Adapter\GetTextAdapter;
use PoMoh\Translator;

class GetTextAdapterTest extends TestCase {

    public function testAdapter() {
        $adapter = new GetTextAdapter('es_MX', 'pomoh', dirname(__FILE__) . '/lang');
        $translator = new Translator($adapter);
        $string = $translator->translate('This is a string');
        $this->assertEquals('Este es un texto', $string);
        $string = $translator->translate('The quick brown fox jumps over the lazy dog');
        $this->assertEquals('El zorro rápido marrón salta por encima del perro perezoso', $string);
        $string = $translator->pluralize('fox', 'foxes', 1);
        $this->assertEquals('fox', $string);
        $string = $translator->pluralize('fox', 'foxes', 5);
        $this->assertEquals('foxes', $string);
    }
}
