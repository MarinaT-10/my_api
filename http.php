<?php

use GeekBrains\LevelTwo\Http\{Actions\Auth\LogIn,
    Actions\Auth\LogOut,
    Actions\Comments\CreateComment,
    Actions\Comments\DeleteComment,
    Actions\Likes\CreatePostLike,
    Actions\Likes\DeletePostLike,
    Actions\Likes\FindLikesByUuidPost,
    Actions\LikesComment\CreateCommentLike,
    Actions\LikesComment\DeleteCommentLike,
    Actions\Posts\CreatePost,
    Actions\Posts\DeletePost,
    Actions\Posts\FindByUuid,
    Actions\Users\CreateUser,
    Actions\Users\FindByUsername,
    ErrorResponse,
    Request};
use GeekBrains\LevelTwo\Blog\{Exceptions\AppException,};
use Psr\Log\LoggerInterface;

$container = require __DIR__ . '/bootstrap.php';

$logger = $container->get(LoggerInterface::class);

$request = new Request(
    $_GET,
    $_SERVER,
    file_get_contents('php://input'),
);


try {
    $path = $request->path();
} catch (HttpException $e) {
    // Логируем сообщение с уровнем WARNING
    $logger->warning($e->getMessage());

    (new ErrorResponse)->send();
    return;
}

try {
    $method = $request->method();
} catch (HttpException $e) {
    // Логируем сообщение с уровнем WARNING
    $logger->warning($e->getMessage())
    (new ErrorResponse)->send();
    return;
}

$routes = [
    'GET' => [
        '/users/show' => FindByUsername::class,
        '/posts/show' => FindByUuid::class,
    ],
    'POST' => [
        '/lo0gin' => LogIn::class,
        '/logout' => LogOut::class,
        '/users/create' => CreateUser::class,
        '/posts/create' => CreatePost::class,
        '/posts/comment' => CreateComment::class,
        '/posts/like' => CreatePostLike::class,
        '/posts/comments/like'=> CreateCommentLike::class
    ],

    'DELETE' => [
        '/posts' => DeletePost::class,
        '/comments' => DeleteComment::class,
        '/likes' => DeletePostLike::class,
        '/comments/likes' => DeleteCommentLike::class
    ],
];

if (!array_key_exists($method, $routes) || !array_key_exists($path, $routes[$method])) {
    // Логируем сообщение с уровнем NOTICE
    $message = "Route not found: $method $path";
    $logger->notice($message);
    (new ErrorResponse($message))->send();
    return;
}

// Получаем имя класса действия для маршрута
$actionClassName = $routes[$method][$path];

// С помощью контейнера создаём объект нужного действия
$action = $container->get($actionClassName);

try {
    $response = $action->handle($request);
} catch (AppException $e) {
    $logger->error($e->getMessage(), ['exception' => $e]);
    (new ErrorResponse($e->getMessage()))->send();
    return;
}

$response->send();


