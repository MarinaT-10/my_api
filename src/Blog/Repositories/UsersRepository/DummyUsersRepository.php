<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\UsersRepository;

use GeekBrains\LevelTwo\Person\Name;
use GeekBrains\LevelTwo\Blog\{
    Exceptions\UserNotFoundException,
    User,
    UUID};

class DummyUsersRepository implements UsersRepositoryInterface
{

    /**
     * @throws UserNotFoundException
     */
    public function save(User $user): void
    {

    }

    /**
     * @throws UserNotFoundException
     */
    public function get(UUID $uuid): User
    {
        throw new UserNotFoundException("Not found");
    }

    public function getByUsername(string $username): User
    {
        return new User(UUID::random(), new Name("first", "last"), "user123", "123");
    }
}