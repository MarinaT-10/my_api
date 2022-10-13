<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\LikesCommentsRepository;

use GeekBrains\LevelTwo\Blog\{LikeComment, UUID};

interface LikesCommentsRepositoryInterface
{
    public function save(LikeComment $likeComment): void;
    public function get (UUID $uuid): LikeComment;
    public function getByCommentUuid(UUID $uuid): array;
    public function delete(UUID $uuid): void;
}