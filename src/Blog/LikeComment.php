<?php

namespace GeekBrains\LevelTwo\Blog;

class LikeComment
{
    private UUID $uuid;
    private UUID $post_uuid;
    private UUID $comment_uuid;
    private UUID $author_uuid;

    /**
     * @param UUID $uuid
     * @param UUID $post_uuid
     * @param UUID $comment_uuid
     * @param UUID $author_uuid
     */
    public function __construct(UUID $uuid, UUID $post_uuid, UUID $comment_uuid, UUID $author_uuid)
    {
        $this->uuid = $uuid;
        $this->post_uuid = $post_uuid;
        $this->comment_uuid = $comment_uuid;
        $this->author_uuid = $author_uuid;
    }

    /**
     * @return UUID
     */
    public function uuid(): UUID
    {
        return $this->uuid;
    }

    /**
     * @param UUID $uuid
     */
    public function setUuid(UUID $uuid): void
    {
        $this->uuid = $uuid;
    }

    /**
     * @return UUID
     */
    public function postUuid(): UUID
    {
        return $this->post_uuid;
    }

    /**
     * @param UUID $post_uuid
     */
    public function setPostUuid(UUID $post_uuid): void
    {
        $this->post_uuid = $post_uuid;
    }


    /**
     * @return UUID
     */
    public function commentUuid(): UUID
    {
        return $this->comment_uuid;
    }

    /**
     * @param UUID $comment_uuid
     */
    public function setCommentUuid(UUID $comment_uuid): void
    {
        $this->comment_uuid = $comment_uuid;
    }

    /**
     * @return UUID
     */
    public function authorUuid(): UUID
    {
        return $this->author_uuid;
    }

    /**
     * @param UUID $author_uuid
     */
    public function setAuthorUuid(UUID $author_uuid): void
    {
        $this->author_uuid = $author_uuid;
    }
}