<?php

namespace GeekBrains\LevelTwo\Blog\Commands;

use GeekBrains\LevelTwo\Blog\Exceptions\CommandException;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\{Exceptions\ArgumentsException, Exceptions\InvalidArgumentException, User, UUID};
use GeekBrains\LevelTwo\Person\Name;
use Psr\Log\LoggerInterface;

//php cli.php username=ivan first_name=Ivan last_name=Nikitin

class CreateUserCommand
{
    // Команда зависит от контракта репозитория пользователей,
    // а не от конкретной реализации
    public function __construct(
        private UsersRepositoryInterface $usersRepository,
        private LoggerInterface $logger,
    )
    {
    }

    /**
     * @param Arguments $arguments
     * @throws ArgumentsException
     * @throws CommandException
     */
    public function handle(Arguments $arguments): void
    {
        $this->logger->info("Create user command started");

        $username = $arguments->get('username');

        // Проверяем, существует ли пользователь в репозитории
        if ($this->userExists($username)) {

            // Логируем сообщение с уровнем WARNING
            $this->logger->warning("User already exists: $username");
            throw new CommandException("User already exists: $username");
        }

        // Создаём объект пользователя
        // Функция createFrom сама создаст UUID и захеширует пароль
        $user = User::createFrom(
            new Name(
                $arguments->get('first_name'),
                $arguments->get('last_name'),
            ),
            $username,
            $arguments->get('password'),
        );

        $this->usersRepository->save($user);

        // Логируем информацию о новом пользователе
        $this->logger->info("User created: " .$user->uuid());
    }

    private function userExists(string $username): bool
    {
        try {
            $this->usersRepository->getByUsername($username);
        } catch (UserNotFoundException) {
            return false;
        }
        return true;
    }
}