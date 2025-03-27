<?php

namespace PoMoh\Adapter\MoFile;

class Parser {

    public const MAGIC_BE = "\x95\x04\x12\xde";

    public const MAGIC_LE = "\xde\x12\x04\x95";

    protected string $path;

    public function __construct(string $path) {
        $this->path = $path;
    }

    public function parse(): array {
        $strings = [];

        if (! is_readable($this->path)) {
            throw new ParserException("File '{$this->path}' does not exist");
        }

        $stream = new Reader($this->path);

        try {
            $magic = $stream->read(0, 4);
            if ($magic === self::MAGIC_LE) {
                $unpack = 'V';
            } elseif ($magic === self::MAGIC_BE) {
                $unpack = 'N';
            } else {
                throw new ParserException("File '{$this->path}' has bad magic number");
            }

            # Parse header
            $total = $stream->readInt($unpack, 8);
            $originals = $stream->readInt($unpack, 12);
            $translations = $stream->readInt($unpack, 16);

            # Get original and translations tables
            $total_times_two = (int) ($total * 2);// Fix for issue #36 on ARM
            $table_originals = $stream->readIntArray($unpack, $originals, $total_times_two);
            $table_translations = $stream->readIntArray($unpack, $translations, $total_times_two);

            # Read all strings to the cache
            for ($i = 0; $i < $total; ++$i) {
                $i_times_two = $i * 2;
                $i_plus_one = $i_times_two + 1;
                $i_plus_two = $i_times_two + 2;
                $original = $stream->read($table_originals[$i_plus_two], $table_originals[$i_plus_one]);
                $translation = $stream->read($table_translations[$i_plus_two], $table_translations[$i_plus_one]);
                $strings[$original] = $translation;
            }

            return $strings;
        } catch (ReaderException) {
            throw new ParserException("Error while reading file '{$this->path}' , probably too short");
        }
    }
}
