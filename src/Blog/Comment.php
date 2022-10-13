<?php

namespace GeekBrains\LevelTwo\Blog;

use GeekBrains\LevelTwo\Person\Person;

class Comment
{
    private UUID $uuid;
    private User $user;
    private Post $post;
    private string $text;


    /**
     * @param UUID $uuid
     * @param User $user
     * @param Post $post
     * @param string $text
     */
    public function __construct(UUID $uuid, User $user, Post $post, string $text)
    {
        $this->uuid = $uuid;
        $this->user = $user;
       $this->post = $post;
        $this->text = $text;
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
     * @return User
     */

    public function user(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return Post
     */
    public function post(): Post
    {
        return $this->post;
    }

    /**
     * @param Post $post
     */
    public function setPost(Post $post): void
    {
        $this->post = $post;
    }

    /**
     * @return string
     */
    public function text(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

}