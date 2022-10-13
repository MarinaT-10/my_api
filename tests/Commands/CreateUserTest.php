<?php

namespace GeekBrains\LevelTwo\Commands;

use GeekBrains\LevelTwo\Blog\{Commands\Arguments,
    Commands\CreateUserCommand,
    Commands\Users\CreateUser,
    Exceptions\ArgumentsException,
    Exceptions\CommandException,
    Exceptions\UserNotFoundException,
    Repositories\UsersRepository\DummyUsersRepository,
    Repositories\UsersRepository\UsersRepositoryInterface,
    User,
    UUID};
use GeekBrains\LevelTwo\tests\DummyLogger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class CreateUserTest extends TestCase
{
    /**
     * @return void
     * @throws ExceptionInterface
     */
    public function testItRequiresPasswordNew(): void
    {
        $command = new CreateUser(
            $this->makeUsersRepository(),
        );
        $this->expectException(RuntimeException::class);

        $this->expectExceptionMessage(
            'Not enough arguments (missing: "first_name, last_name, password")'
        );

        $command->run(
            new ArrayInput([
                'username' => 'Ivan',
            ]),
            new NullOutput()
        );
    }


    /**
     * @throws ExceptionInterface
     */
    public function testItRequiresFirstNameNew(): void
    {

        $command = new CreateUser(
            $this->makeUsersRepository()
        );
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Not enough arguments (missing: "first_name, last_name").'
        );
        $command->run(
            new ArrayInput([
                'username' => 'Ivan',
                'password' => 'some_password',
            ]),
            new NullOutput()
        );

    }


    /**
     * @throws ExceptionInterface
     */
    public function testItRequiresLastNameNew(): void
    {
        // Тестируем новую команду
        $command = new CreateUser(
            $this->makeUsersRepository(),
        );

        // Меняем тип ожидаемого исключения ..
        $this->expectException(RuntimeException::class);
        // .. и его сообщение
        $this->expectExceptionMessage(
            'Not enough arguments (missing: "last_name").'
        );

        // Запускаем команду методом run вместо handle
        $command->run(
        // Передаём аргументы как ArrayInput,а не Arguments
        // Сами аргументы не меняются
            new ArrayInput([
                'first_name' => 'Ivan',
                'username' => 'Ivan',
                'password' => 'some_password'
            ]),
            // Передаём также объект,реализующий контракт OutputInterface
            // Нам подойдёт реализация,которая ничего не делает
            new NullOutput()
        );
    }


    /**
     * @throws ExceptionInterface
     */
    public function testItSavesUserToRepositoryNew(): void
    {
        $usersRepository = new class implements UsersRepositoryInterface {
            private bool $called = false;

            public function save(User $user): void
            {
                $this->called = true;
            }

            public function get(UUID $uuid): User
            {throw new UserNotFoundException("Not found");
            }

            public function getByUsername(string $username): User
            {
                throw new UserNotFoundException("Not found");
            }

            public function wasCalled(): bool
            {
                return $this->called;
            }
        };

        $command = new CreateUser(
            $usersRepository
        );

        $command->run(
            new ArrayInput([
                'username' => 'Ivan',
                'password' => 'some_password',
                'first_name' => 'Ivan',
                'last_name' => 'Nikitin',
            ]),
            new NullOutput()
        );
        $this->assertTrue($usersRepository->wasCalled());
    }

    private function makeUsersRepository(): UsersRepositoryInterface
    {
        return new class implements UsersRepositoryInterface {
            public function save(User $user): void
            {
            }
            public function get(UUID $uuid): User
            {
                throw new UserNotFoundException("Not found");
            }
            public function getByUsername(string $username): User
            {
                throw new UserNotFoundException("Not found");
            }
        };
    }

}