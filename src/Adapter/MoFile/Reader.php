<?php

namespace PoMoh\Adapter\MoFile;

class Reader {

    private string $string;

    private int $length;

    public function __construct(string $path) {
        $this->string = (string) file_get_contents($path);
        $this->length = strlen($this->string);
    }

    public function read(int $pos, int $bytes): string {
        if ($pos + $bytes > $this->length) {
            throw new ReaderException('Not enough bytes!');
        }
        return substr($this->string, $pos, $bytes);
    }

    public function readInt(string $unpack, int $pos): int {
        $data = unpack($unpack, $this->read($pos, 4));
        if ($data === false) {
            return PHP_INT_MAX;
        }
        $result = $data[1];
        return $result < 0 ? PHP_INT_MAX : $result;
    }

    public function readIntArray(string $unpack, int $pos, int $count): array {
        $data = unpack($unpack . $count, $this->read($pos, 4 * $count));
        if ($data === false) {
            return [];
        }
        return $data;
    }
}
