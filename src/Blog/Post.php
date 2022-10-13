<?php

namespace GeekBrains\LevelTwo\Blog;
;

class Post
{
    private UUID $uuid;
    private User $user;
    private string $title;
    private string $text;

    public function __construct(UUID $uuid, User $user, string $title, string $text)
    {
        $this->uuid = $uuid;
        $this->title = $title;
        $this->text = $text;
        $this->user = $user;
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
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
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



    public function __toString(): string
    {
        return $this->user . ' пишет: ' . $this->text . '. Это статья под названием: '. $this->title . PHP_EOL;
    }

}