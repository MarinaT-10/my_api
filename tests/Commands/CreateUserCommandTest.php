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
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class CreateUserCommandTest extends TestCase
{
    /**
     * @return void
     * @throws ArgumentsException
     * @throws CommandException
     */
    public function testItRequiresPassword(): void
    {
        $command = new CreateUserCommand(
            $this->makeUsersRepository(),
            new DummyLogger()
        );
        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('No such argument: password');
        $command->handle(new Arguments([
            'username' => 'Ivan',
            'last_name' => 'Ivanov',
            'first_name' => 'Ivan'
        ]));
    }

     public function testItThrowsAnExceptionWhenUserAlreadyExists(): void
    {
        $command = new CreateUserCommand(
            new DummyUsersRepository(),
            new DummyLogger()
        );

        // Описываем тип ожидаемого исключения
        $this->expectException(CommandException::class);

        // и его сообщение
        $this->expectExceptionMessage('User already exists: Ivan');

        // Запускаем команду с аргументами
        $command->handle(new Arguments([
            'username' => 'Ivan',
            'password' => '123'
            ])
        );
    }



    // Тест проверяет, что команда действительно требует имя пользователя
    public function testItRequiresFirstName(): void
    {

        $usersRepository = new class implements UsersRepositoryInterface {
            public function save(User $user): void
            {
                // Ничего не делаем
            }

            public function get(UUID $uuid): User
            {
                // И здесь ничего не делаем
                throw new UserNotFoundException("Not found");
            }

            public function getByUsername(string $username): User
            {
                // И здесь ничего не делаем
                throw new UserNotFoundException("Not found");
            }
        };

        // Передаём объект анонимного класса
        // в качестве реализации UsersRepositoryInterface
        $command = new CreateUserCommand($usersRepository, new DummyLogger());

        // Ожидаем, что будет брошено исключение
        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('No such argument: first_name');

        // Запускаем команду
        $command->handle(new Arguments([
            'username' => 'Ivan',
            'password' => '123']
        ));
    }


    public function testItRequiresLastName(): void
    {
        // Передаём в конструктор команды объект, возвращаемый нашей функцией
        $command = new CreateUserCommand($this->makeUsersRepository(), new DummyLogger());
        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('No such argument: last_name');
        $command->handle(new Arguments([
            'username' => 'Ivan',

            // Нам нужно передать имя пользователя,
            // чтобы дойти до проверки наличия фамилии
            'first_name' => 'Ivan',
            'password' => '123'
        ]));
    }



    // Тест, проверяющий, что команда сохраняет пользователя в репозитории
    public function testItSavesUserToRepository(): void
    {
        // Создаём объект анонимного класса
        $usersRepository = new class implements UsersRepositoryInterface {

            private bool $called = false;
            public function save(User $user): void
            {
            // Запоминаем, что метод save был вызван
                $this->called = true;
            }

            public function get(UUID $uuid): User
            {throw new UserNotFoundException("Not found");
            }

            public function getByUsername(string $username): User
            {
                throw new UserNotFoundException("Not found");
            }
            // Этого метода нет в контракте UsersRepositoryInterface,
            // но ничто не мешает его добавить.
            // С помощью этого метода мы можем узнать,
            // был ли вызван метод save
            public function wasCalled(): bool
            {
                return $this->called;
            }
        };
        // Передаём наш мок в команду
        $command = new CreateUserCommand($usersRepository, new DummyLogger());

        // Запускаем команду
        $command->handle(new Arguments([
            'username' => 'Ivan',
            'password' => '123',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin',
        ]));

        // Проверяем утверждение относительно мока,
        // а не утверждение относительно команды
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