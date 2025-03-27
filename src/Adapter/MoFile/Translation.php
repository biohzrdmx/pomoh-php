<?php

namespace PoMoh\Adapter\MoFile;

class Translation {

    protected string $path;

    protected array $headers = [];

    protected array $strings = [];

    protected string $plural_equation = '';

    protected int $plural_count = 0;

    protected bool $loaded = false;

    public function __construct(string $path) {
        $this->path = $path;
    }

    public function getHeader(string $header, string $default = ''): string {
        if (! $this->loaded ) $this->load();
        return $this->strings[$header] ?? $default;
    }

    public function translate(string $string): string {
        if (! $this->loaded ) $this->load();
        return $this->strings[$string] ?? $string;
    }

    public function pluralize(string $singular, string $plural, int $count): string {
        if (! $this->loaded ) $this->load();
        $key = $singular . "\u{0}" . $plural;
        if (! isset( $this->strings[$key] ) ) {
            return $count !== 1 ? $plural : $singular;
        }
        $string = $this->strings[$key];
        $selected = $this->selectString($count);
        $list = explode("\u{0}", $string);
        if (array_key_exists($selected, $list)) {
            return $list[$selected];
        }
        return $list[0];
    }

    protected function load(): bool {
        $parser = new Parser($this->path);
        $this->strings = $parser->parse();
        $this->loaded = true;
        $header = $this->strings[''] ?? '';
        if ($header) {
            $this->parseHeader($header);
            unset( $this->strings[''] );
        }
        return true;
    }

    protected function parseHeader(string $header): void {
        $headers = explode("\n", $header);
        foreach ($headers as $header) {
            if (!$header) continue;
            $parts = explode(":", $header, 2);
            $this->headers[ trim( $parts[0] )] = trim( $parts[1] );
        }
    }

    protected function selectString(int $n): int {
        $expr = $this->getPluralForms();
        $function = new Expression($expr);
        $plural = $function->get($n);
        if ($plural >= $this->plural_count) {
            $plural = $this->plural_count - 1;
        }
        return $plural;
    }

    protected function getPluralForms(): string {
        if (! $this->plural_equation ) {
            $expr = $this->getHeader('Plural-Forms', 'nplurals=2; plural=n == 1 ? 0 : 1;');
            $this->plural_equation = $this->sanitizePluralExpression($expr);
            $this->plural_count = $this->extractPluralCount($expr);
        }
        return $this->plural_equation;
    }

    protected function extractPluralCount(string $expr): int {
        $parts = explode(';', $expr, 2);
        $nplurals = explode('=', trim($parts[0]), 2);
        if (strtolower(rtrim($nplurals[0])) !== 'nplurals') {
            return 1;
        }
        if (count($nplurals) === 1) {
            return 1;
        }
        return (int) $nplurals[1];
    }

    protected function sanitizePluralExpression(string $expr): string {
        # Parse equation
        $expr = explode(';', $expr);
        $expr = count($expr) >= 2 ? $expr[1] : $expr[0];
        $expr = trim(strtolower($expr));
        # Strip plural prefix
        if (str_starts_with($expr, 'plural')) {
            $expr = ltrim(substr($expr, 6));
        }
        # Strip equals
        if (str_starts_with($expr, '=')) {
            $expr = ltrim(substr($expr, 1));
        }
        # Cleanup from unwanted chars
        $expr = preg_replace('@[^n0-9:\(\)\?=!<>/%&| ]@', '', $expr);
        return (string) $expr;
    }
}
