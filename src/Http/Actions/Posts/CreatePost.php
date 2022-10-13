<?php

namespace GeekBrains\LevelTwo\Http\Actions\Posts;

use GeekBrains\LevelTwo\Person\Name;
use GeekBrains\LevelTwo\Blog\{
    Post,
    UUID};
use GeekBrains\LevelTwo\Blog\Exceptions\{AuthException, HttpException, InvalidArgumentException, UserNotFoundException};
use GeekBrains\LevelTwo\Blog\Repositories\{
    PostsRepository\PostsRepositoryInterface,
    UsersRepository\UsersRepositoryInterface};
use GeekBrains\LevelTwo\Http\{Actions\ActionInterface,
    Auth\AuthenticationInterface,
    Auth\IdentificationInterface,
    Auth\TokenAuthenticationInterface,
    ErrorResponse,
    Request,
    Response,
    SuccessfulResponse};
use Psr\Log\LoggerInterface;

/*POST http://localhost:80/posts/create
Authorization: Bearer ceb2ec8366bdba432083be336e829d1bb13b71846a60b76660fab49c37983c6d0defd90c982a4987

{
"title": "title",
"text": "text"
}*/

class CreatePost implements ActionInterface
{
    public function __construct(
        private PostsRepositoryInterface $postsRepository,
        private TokenAuthenticationInterface $authentication,
        private LoggerInterface $logger
    )
    {
    }

    public function handle(Request $request): Response
    {
        try {
            $user = $this->authentication->user($request);
        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }


        $newPostUuid = UUID::random();

        try {
            $post = new Post(
                $newPostUuid,
                $user,
                $request->jsonBodyField('title'),
                $request->jsonBodyField('text'),
            );
        } catch (HttpException $e) {
            return new ErrorResponse($e->getMessage());
        }
        $this->postsRepository->save($post);

        // Логируем UUID новой статьи
        $this->logger->info("Post created: $newPostUuid");

        return new SuccessfulResponse([
            'uuid' => (string)$newPostUuid,
        ]);
    }
}