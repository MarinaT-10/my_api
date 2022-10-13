<?php

namespace GeekBrains\LevelTwo\Blog;

use GeekBrains\LevelTwo\Person\Name;


class User extends \GeekBrains\LevelTwo\Blog\UUID
{
    private UUID $uuid;
    private Name $name;
    private string $username;
    private string $hashedPassword;


    /**
     * @param UUID $uuid
     * @param Name $name
     * @param string $login
     * @param string $hashedPassword
     */
    public function __construct(UUID $uuid, Name $name, string $username, string $hashedPassword)
    {
        $this->uuid = $uuid;
        $this->name = $name;
        $this->username = $username;
        $this->hashedPassword = $hashedPassword;
    }

    public function __toString(): string
    {
        return "Юзер $this->uuid с именем $this->name и логином $this->username." . PHP_EOL;
    }

    /**
     * @return string
     */
    public function hashedPassword(): string
    {
        return $this->hashedPassword;
    }

    // Функция для создания нового пользователя
    public static function createFrom(
        Name $name,
        string $username,
        string $password
    ): self {
        // Генерируем UUID
        $uuid = UUID::random();
        return new self(
            $uuid,
            $name,
            $username,
            // Передаём сгенерированный UUID в функцию хеширования пароля
            self::hash($password, $uuid),
        );
    }

    // Функция для вычисления хеша
    private static function hash(string $password, UUID $uuid): string
    {
        return hash('sha256', $uuid . $password);
    }

    // Функция для проверки предъявленного пароля
    public function checkPassword(string $password): bool
    {
        // Передаём UUID пользователя в функцию хеширования пароля
        return $this->hashedPassword === self::hash($password, $this->uuid);
    }



    /**
     * @return UUID
     */
    public function uuid(): UUID
    {
        return $this->uuid;
    }


    /**
     * @return Name
     */
    public function name(): Name
    {
        return $this->name;
    }

    /**
     * @param Name $name
     */
    public function setName(Name $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function username(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

}