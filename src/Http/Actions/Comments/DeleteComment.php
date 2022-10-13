<?php

namespace GeekBrains\LevelTwo\Http\Actions\Comments;

use GeekBrains\LevelTwo\Blog\Exceptions\AuthException;
use GeekBrains\LevelTwo\Blog\Exceptions\CommentNotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use GeekBrains\LevelTwo\Http\{Actions\ActionInterface,
    Auth\TokenAuthenticationInterface,
    ErrorResponse,
    Request,
    Response,
    SuccessfulResponse};
use GeekBrains\LevelTwo\Blog\UUID;
use Psr\Log\LoggerInterface;

/*DELETE http://localhost:80/comments?uuid=1936360c-08ee-414a-bf43-b219871fd735
Authorization: Bearer ceb2ec8366bdba432083be336e829d1bb13b71846a60b76660fab49c37983c6d0defd90c982a4987users*/



class DeleteComment implements ActionInterface
{
    public function __construct(
        private CommentsRepositoryInterface $commentsRepository,
        private LoggerInterface $logger,
        private TokenAuthenticationInterface $authentication,
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

        try {
            $commentUuid = $request->query('uuid');
            $this->commentsRepository->get(new UUID($commentUuid));

        } catch (CommentNotFoundException $error) {
            return new ErrorResponse($error->getMessage());
        }

        $this->commentsRepository->delete(new UUID($commentUuid));

        $this->logger->info("This comment deleted");

        return new SuccessfulResponse([
            'uuid' => $commentUuid,
        ]);
    }
}