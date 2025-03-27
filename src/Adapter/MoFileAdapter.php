<?php

namespace PoMoh\Adapter;

use FilesystemIterator;
use PoMoh\Adapter\MoFile\Translation;

class MoFileAdapter implements AdapterInterface {

    protected string $locale = '';

    protected string $path = '';

    /**
     * @var array<Translation>
     */
    protected array $locales = [];

    protected array $strings = [];

    public function __construct(string $locale, string $path) {
        $this->locale = $locale;
        $this->path = $path;
        $iterator = new FilesystemIterator($path);
        foreach ($iterator as $file) {
            if ( $file->getExtension() == 'mo' ) {
                $translation = new Translation( $file->getPathname() );
                $locale = $translation->getHeader('Language', $file->getBasename('.' . $file->getExtension()));
                $this->locales[$locale] = $translation;
            }
        }
    }

    public function translate(string $string): string {
        $locale = $this->locales[$this->locale] ?? null;
        return $locale ? $locale->translate($string) : $string;
    }

    public function pluralize(string $singular, string $plural, int $count): string {
        $locale = $this->locales[$this->locale] ?? null;
        return $locale ? $locale->pluralize($singular, $plural, $count) : ($count == 1 ? $singular : $plural);
    }
}
