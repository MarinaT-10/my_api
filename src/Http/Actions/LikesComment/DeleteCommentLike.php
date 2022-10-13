<?php

namespace GeekBrains\LevelTwo\Http\Actions\LikesComment;

use GeekBrains\LevelTwo\Blog\Exceptions\AuthException;
use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\LikeNotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\LikesCommentsRepository\LikesCommentsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Http\Actions\ActionInterface;
use GeekBrains\LevelTwo\Http\Auth\TokenAuthenticationInterface;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\Response;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;
use Psr\Log\LoggerInterface;

/*DELETE http://localhost:80/comments/likes?uuid=5fb71255-b83b-4ed9-a451-db7ef2045636
Authorization: Bearer ceb2ec8366bdba432083be336e829d1bb13b71846a60b76660fab49c37983c6d0defd90c982a4987*/

class DeleteCommentLike implements ActionInterface
{
    public function __construct(
        private LikesCommentsRepositoryInterface $likesCommentsRepository,
        private LoggerInterface $logger,
        private TokenAuthenticationInterface $authentication,
    )
    {
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
            $likeCommentUuid = $request->query('uuid');
            $this->likesCommentsRepository->get(new UUID($likeCommentUuid));

        } catch (LikeNotFoundException $error) {
            return new ErrorResponse($error->getMessage());
        }

        $this->likesCommentsRepository->delete(new UUID($likeCommentUuid));
        $this->logger->info("This like to comment deleted!");

        return new SuccessfulResponse([
            'uuid' => $likeCommentUuid,
        ]);
    }
}