<?php


use GeekBrains\LevelTwo\Blog\Commands\{
    CreateUserCommand,
    Arguments,
    FakeData\PopulateDB,
    Posts\DeletePost,
    Users\CreateUser,
    Users\UpdateUser};
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;


$container = require __DIR__ . '/bootstrap.php';

// Получаем объект логгера из контейнера
$logger = $container->get(LoggerInterface::class);

// Создаём объект приложения
$application = new Application();

// Перечисляем классы команд
$commandsClasses = [
    CreateUser::class,
    DeletePost::class,
    UpdateUser::class,
    PopulateDB::class
];

foreach ($commandsClasses as $commandClass) {
    // Посредством контейнера создаём объект команды
    $command = $container->get($commandClass);

    // Добавляем команду к приложению
    $application->add($command);
}



try {
    // Запускаем приложение
    $application->run();
} catch (Exception $e) {
    echo "{$e->getMessage()}\n";
    $logger->error($e->getMessage(), ['exception' => $e]);
}


