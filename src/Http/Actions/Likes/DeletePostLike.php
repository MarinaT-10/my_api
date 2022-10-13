<?php

namespace GeekBrains\LevelTwo\Http\Actions\Likes;

use GeekBrains\LevelTwo\Blog\Exceptions\AuthException;
use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\LikeNotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\LikesRepository\LikesRepositoryInterface;
use GeekBrains\LevelTwo\Http\{Actions\ActionInterface,
    Auth\TokenAuthenticationInterface,
    ErrorResponse,
    Request,
    Response,
    SuccessfulResponse};
use GeekBrains\LevelTwo\Blog\UUID;
use Psr\Log\LoggerInterface;

/*DELETE http://localhost:80/likes?uuid=1b23785c-be44-4d1e-801f-cc0b27155a54
Authorization: Bearer ceb2ec8366bdba432083be336e829d1bb13b71846a60b76660fab49c37983c6d0defd90c982a4987*/


class DeletePostLike implements ActionInterface
{
    public function __construct(
        private LikesRepositoryInterface $likesRepository,
        private LoggerInterface $logger,
        private TokenAuthenticationInterface $authentication
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
            $likeUuid = $request->query('uuid');
            $this->likesRepository->get(new UUID($likeUuid));

        } catch (LikeNotFoundException $error) {
            return new ErrorResponse($error->getMessage());
        }

        $this->likesRepository->delete(new UUID($likeUuid));

        $this->logger->info("Like to the post deleted!");

        return new SuccessfulResponse([
            'uuid' => $likeUuid,
        ]);
    }
}