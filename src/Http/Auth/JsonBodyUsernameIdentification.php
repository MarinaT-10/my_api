<?php

namespace GeekBrains\LevelTwo\Http\Auth;

use GeekBrains\LevelTwo\Blog\{
    Exceptions\AuthException,
    Exceptions\HttpException,
    Exceptions\UserNotFoundException,
    Repositories\UsersRepository\UsersRepositoryInterface,
    User
};
use GeekBrains\LevelTwo\Http\Request;

class JsonBodyUsernameIdentification implements IdentificationInterface
{
    public function __construct(
        private UsersRepositoryInterface $usersRepository
    )
    {
    }

    /**
     * @throws AuthException
     */
    public function user(Request $request): User
    {
        try {
            // Получаем имя пользователя из JSON-тела запроса;
            // ожидаем, что имя пользователя находится в поле username
            $username = $request->jsonBodyField('username');
        } catch (HttpException $e) {
            // Если невозможно получить имя пользователя из запроса -
            // бросаем исключение
            throw new AuthException($e->getMessage());
        }

        try {
            // Ищем пользователя в репозитории и возвращаем его
            return $this->usersRepository->getByUsername($username);
        } catch (UserNotFoundException $e) {
            //Если пользователь не найден - бросаем исключение
            throw new AuthException($e->getMessage());
        }
    }
}