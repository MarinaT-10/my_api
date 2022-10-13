<?php

namespace GeekBrains\LevelTwo\Http\Actions\Comments;

use GeekBrains\LevelTwo\Blog\Repositories\{
    CommentsRepository\CommentsRepositoryInterface,
    PostsRepository\PostsRepositoryInterface,
    UsersRepository\UsersRepositoryInterface};
use GeekBrains\LevelTwo\Blog\Comment;
use GeekBrains\LevelTwo\Blog\Exceptions\{AuthException,
    HttpException,
    InvalidArgumentException,
    PostNotFoundException,
    UserNotFoundException};
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Http\{Actions\ActionInterface,
    Auth\TokenAuthenticationInterface,
    ErrorResponse,
    Request,
    Response,
    SuccessfulResponse};
use Psr\Log\LoggerInterface;

/*POST http://localhost:80/posts/comment
Authorization: Bearer ceb2ec8366bdba432083be336e829d1bb13b71846a60b76660fab49c37983c6d0defd90c982a4987

{
"post_uuid": "bcfc3263-716a-47e7-8aae-a406e6c41d13",
"text": "text"
}*/

class CreateComment implements ActionInterface
{

    public function __construct(
        private CommentsRepositoryInterface $commentsRepository,
        private PostsRepositoryInterface    $postsRepository,
        private TokenAuthenticationInterface $authentication,
        private LoggerInterface $logger,
    ) {
    }

    public function handle(Request $request): Response
    {

        try {
            $user = $this->authentication->user($request);
        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $postUuid = new UUID($request->jsonBodyField('post_uuid'));
        } catch (HttpException | InvalidArgumentException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $post = $this->postsRepository->get(new UUID($postUuid));
        } catch (PostNotFoundException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $newCommentUuid = UUID::random();

        try {
            $comment = new Comment(
                $newCommentUuid,
                $user,
                $post,
                $request->jsonBodyField('text'),
            );
        } catch (HttpException $e) {
            return new ErrorResponse($e->getMessage());
        }
        $this->commentsRepository->save($comment);

        $this->logger->info("Comment created: {$comment->uuid()}");

        return new SuccessfulResponse([
            'uuid' => (string)$newCommentUuid,
        ]);
    }
}