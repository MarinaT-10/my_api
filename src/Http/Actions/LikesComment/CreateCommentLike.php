<?php

namespace GeekBrains\LevelTwo\Http\Actions\LikesComment;

use GeekBrains\LevelTwo\Blog\{Exceptions\AuthException,
    Exceptions\HttpException,
    Exceptions\InvalidArgumentException,
    Exceptions\LikeAlreadyExists,
    LikeComment,
    Repositories\LikesCommentsRepository\LikesCommentsRepositoryInterface,
    UUID};
use GeekBrains\LevelTwo\Http\{Actions\ActionInterface,
    Auth\TokenAuthenticationInterface,
    ErrorResponse,
    Request,
    Response,
    SuccessfulResponse};
use Psr\Log\LoggerInterface;

/*POST http://localhost:80/posts/comments/like
Authorization: Bearer ceb2ec8366bdba432083be336e829d1bb13b71846a60b76660fab49c37983c6d0defd90c982a4987

{
"post_uuid": "58afab5a-1d24-4dfe-a552-ec1f8d366b34",
"comment_uuid": "bff3782a-ed2a-410b-ad25-779f70f8f848"
}*/

class CreateCommentLike implements ActionInterface
{
    public function __construct(
        private LikesCommentsRepositoryInterface $likesCommentsRepository,
        private LoggerInterface $logger,
        private TokenAuthenticationInterface $authentication,
    )
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function handle(Request $request): Response
    {
        try {
            $author = $this->authentication->user($request);
        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $post_uuid = $request->jsonBodyField('post_uuid');
            $comment_uuid = $request->jsonBodyField('comment_uuid');
        } catch(HttpException $e) {
            return new ErrorResponse($e->getMessage());
        }

       try {
            $this->likesCommentsRepository->getLikeFromUserToComment($comment_uuid, $author->uuid());
        } catch (LikeAlreadyExists $e) {
            return new ErrorResponse($e->getMessage());
        }

        $newLikeCommentUuid = UUID::random();

        $likeComment = new LikeComment(
            $newLikeCommentUuid,
            new UUID($post_uuid),
            new UUID($comment_uuid),
            new UUID($author->uuid())
        );

        $this->likesCommentsRepository->save($likeComment);

        $this->logger->info("Like to the post {$likeComment->postUuid()} created: {$likeComment->uuid()}");

        return new SuccessfulResponse([
            'uuid' => (string)$newLikeCommentUuid,
        ]);
    }
}