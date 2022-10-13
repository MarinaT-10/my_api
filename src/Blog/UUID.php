<?php

namespace GeekBrains\LevelTwo\Blog;

use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
class UUID extends \GeekBrains\LevelTwo\Blog\Post
{
    public function __construct(
        private string $uuidString
    ) {

        if (!uuid_is_valid($uuidString)) {
            throw new InvalidArgumentException(
                "Malformed UUID: $this->uuidString"
            );
        }
    }
    // А так мы можем сгенерировать новый случайный UUID
    // и получить его в качестве объекта нашего класса
    public static function random(): self
    {
        return new self(uuid_create(UUID_TYPE_RANDOM));
    }
    public function __toString(): string
    {
        return $this->uuidString;
    }
}