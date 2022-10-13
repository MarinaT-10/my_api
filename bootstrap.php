<?php

use Dotenv\Dotenv;
use Faker\Provider\Lorem;
use Faker\Provider\ru_RU\{
    Internet,
    Person,
    Text};
use GeekBrains\LevelTwo\Blog\Container\DIContainer;
use GeekBrains\LevelTwo\Blog\Repositories\{AuthTokensRepository\AuthTokensRepositoryInterface,
    AuthTokensRepository\SqliteAuthTokensRepository,
    CommentsRepository\CommentsRepositoryInterface,
    CommentsRepository\SqliteCommentsRepository,
    LikesCommentsRepository\LikesCommentsRepositoryInterface,
    LikesCommentsRepository\SqliteLikesCommentsRepository,
    LikesRepository\LikesRepositoryInterface,
    LikesRepository\SqliteLikesRepository,
    PostsRepository\PostsRepositoryInterface,
    PostsRepository\SqlitePostsRepository,
    UsersRepository\SqliteUsersRepository,
    UsersRepository\UsersRepositoryInterface};
use GeekBrains\LevelTwo\Http\Auth\{
    AuthenticationInterface,
    BearerTokenAuthentication,
    JsonBodyUsernameIdentification,
    JsonBodyUuidIdentification,
    PasswordAuthentication,
    PasswordAuthenticationInterface,
    TokenAuthenticationInterface
};
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Faker\Generator;

require_once __DIR__ . '/vendor/autoload.php';

Dotenv::createImmutable(__DIR__)->safeLoad();


// Создаём объект контейнера
$container = new DIContainer();

// .. и настраиваем его:

// 1. подключение к БД
$container->bind(
    PDO::class,
    new PDO('sqlite:' . __DIR__ . '/' . $_ENV['SQLITE_DB_PATH'])
);


$logger = (new Logger('blog'));

// Включаем логирование в файлы,
// если переменная окружения LOG_TO_FILES содержит значение 'yes'
if ('yes' === $_ENV['LOG_TO_FILES']) {
    $logger
        ->pushHandler(new StreamHandler(
            __DIR__ . '/logs/blog.log'
        ))
        ->pushHandler(new StreamHandler(
            __DIR__ . '/logs/blog.error.log',
            level: Logger::ERROR,
            bubble: false,
        ));
    }
// Включаем логирование в консоль,
// если переменная окружения LOG_TO_CONSOLE содержит значение 'yes'
if ('yes' === $_ENV['LOG_TO_CONSOLE']) {
    $logger
        ->pushHandler(
            new StreamHandler("php://stdout")
        );
}

// Создаём объект генератора тестовых данных
$faker = new \Faker\Generator();

// Инициализируем необходимые нам виды данных
$faker->addProvider(new Person($faker));
$faker->addProvider(new Text($faker));
$faker->addProvider(new Internet($faker));
$faker->addProvider(new Lorem($faker));

// Добавляем генератор тестовых данных
// в контейнер внедрения зависимостей
$container->bind(
    Generator::class,
    $faker
);

$container->bind(
    TokenAuthenticationInterface::class,
    BearerTokenAuthentication::class
);

$container->bind(
    PasswordAuthenticationInterface::class,
    PasswordAuthentication::class
);
$container->bind(
    AuthTokensRepositoryInterface::class,
    SqliteAuthTokensRepository::class
);

$container->bind(
    AuthenticationInterface::class,
    PasswordAuthentication::class
);

$container->bind(
    IdentificationInterface::class,
    JsonBodyUsernameIdentification::class
);

$container->bind(
    LoggerInterface::class,
    $logger
);


// репозиторий статей
$container->bind(
    PostsRepositoryInterface::class,
    SqlitePostsRepository::class
);

// репозиторий пользователей
$container->bind(
    UsersRepositoryInterface::class,
    SqliteUsersRepository::class
);

// репозиторий комментариев
$container->bind(
    CommentsRepositoryInterface::class,
    SqliteCommentsRepository::class
);

// репозиторий лайков
$container->bind(
    LikesRepositoryInterface::class,
    SqliteLikesRepository::class
);

// репозиторий лайков комментариев
$container->bind(
    LikesCommentsRepositoryInterface::class,
    SqliteLikesCommentsRepository::class
);

// Возвращаем объект контейнера
return $container;

