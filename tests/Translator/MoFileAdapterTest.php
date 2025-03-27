<?php

namespace PoMoh\Tests\Translator;

use PHPUnit\Framework\TestCase;

use PoMoh\Adapter\MoFileAdapter;
use PoMoh\Translator;

class MoFileAdapterTest extends TestCase {

    public function testAdapter() {
        $adapter = new MoFileAdapter('es_MX', dirname(__FILE__) . '/lang');
        $translator = new Translator($adapter);
        $string = $translator->translate('This is a string');
        $this->assertEquals('Este es un texto', $string);
        $string = $translator->translate('The quick brown fox jumps over the lazy dog');
        $this->assertEquals('El zorro rápido marrón salta por encima del perro perezoso', $string);
        $string = $translator->pluralize('A fox', '%d foxes', 1);
        $this->assertEquals('Un zorro', $string);
        $string = $translator->pluralize('A fox', '%d foxes', 5);
        $this->assertEquals('%d zorros', $string);
    }
}
