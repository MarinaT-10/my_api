<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\LikesRepository;

use GeekBrains\LevelTwo\Blog\{Like, UUID};

interface LikesRepositoryInterface
{
    public function save(Like $like): void;
    public function get (UUID $uuid): Like;
    public function getByPostUuid(UUID $uuid): array;
    public function delete(UUID $uuid): void;
}