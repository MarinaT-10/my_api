<?php

namespace GeekBrains\LevelTwo\Http\Actions\Posts;

use GeekBrains\LevelTwo\Blog\Exceptions\AuthException;
use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Http\{Actions\ActionInterface,
    Auth\TokenAuthenticationInterface,
    ErrorResponse,
    Request,
    Response,
    SuccessfulResponse};
use GeekBrains\LevelTwo\Blog\UUID;
use Psr\Log\LoggerInterface;

/*DELETE http://localhost:80/posts?uuid=58afab5a-1d24-4dfe-a552-ec1f8d366b34
Authorization: Bearer ceb2ec8366bdba432083be336e829d1bb13b71846a60b76660fab49c37983c6d0defd90c982a4987*/

class DeletePost implements ActionInterface
{
    public function __construct(
        private PostsRepositoryInterface $postsRepository,
        private LoggerInterface $logger,
        private TokenAuthenticationInterface $authentication,
    ) {
    }

    /**
     * @throws HttpException
     * @throws InvalidArgumentException
     */
    public function handle(Request $request): Response
    {
        try {
            $user = $this->authentication->user($request);
        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $postUuid = $request->query('uuid');
            $this->postsRepository->get(new UUID($postUuid));
        } catch (PostNotFoundException $error) {
            return new ErrorResponse($error->getMessage());
        }


        $this->postsRepository->delete(new UUID($postUuid));
        $this->logger->info("This post deleted!");

        return new SuccessfulResponse([
            'uuid' => $postUuid,
        ]);
    }
}