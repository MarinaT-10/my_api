<?php

namespace GeekBrains\LevelTwo\Blog;

use DateTimeImmutable;

class AuthToken
{
    public function __construct(
        // Строка токена
        private string $token,
        // UUID пользователя
        private UUID $userUuid,
        // Срок годности
        private DateTimeImmutable $expiresOn
    )
    {
    }

    /**
     * @return string
     */
    public function token(): string
    {
        return $this->token;
    }

    /**
     * @return UUID
     */
    public function userUuid(): UUID
    {
        return $this->userUuid;
    }

    /**
     * @return DateTimeImmutable
     */
    public function expiresOn(): DateTimeImmutable
    {
        return $this->expiresOn;
    }

    /**
     * @param DateTimeImmutable $expiresOn
     */
    public function setExpiresOn(DateTimeImmutable $expiresOn): void
    {
        $this->expiresOn = $expiresOn;
    }

}