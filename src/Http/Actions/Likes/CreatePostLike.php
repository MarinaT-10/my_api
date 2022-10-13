<?php

namespace GeekBrains\LevelTwo\Http\Actions\Likes;

use GeekBrains\LevelTwo\Http\{Actions\ActionInterface,
    Auth\IdentificationInterface,
    Auth\TokenAuthenticationInterface,
    ErrorResponse,
    Request,
    Response,
    SuccessfulResponse};
use GeekBrains\LevelTwo\Blog\Exceptions\AuthException;
use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\LikeAlreadyExists;
use GeekBrains\LevelTwo\Blog\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Like;
use GeekBrains\LevelTwo\Blog\Repositories\LikesRepository\LikesRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\UUID;
use Psr\Log\LoggerInterface;

/*POST http://localhost:80/posts/like
Authorization: Bearer ceb2ec8366bdba432083be336e829d1bb13b71846a60b76660fab49c37983c6d0defd90c982a4987

{
"post_uuid": "5ea43514-dba4-4498-b0dc-d221075850c3"
}*/


class CreatePostLike implements ActionInterface
{
    public function __construct(
        private LikesRepositoryInterface $likesRepository,
        private PostsRepositoryInterface $postsRepository,
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
        } catch(HttpException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $this->postsRepository->get(new UUID($post_uuid));
        } catch (PostNotFoundException $e){
            return new ErrorResponse($e->getMessage());
        }

        try {
            $this->likesRepository->getLikeFromUserToPost($post_uuid, $author->uuid());
        } catch (LikeAlreadyExists $e) {
            return new ErrorResponse($e->getMessage());
        }

        $newLikeUuid = UUID::random();

        $like = new Like(
            $newLikeUuid,
            new UUID($post_uuid),
            new UUID($author->uuid())
        );

        $this->likesRepository->save($like);
        $this->logger->info("Like created: {$like->uuid()}");

        return new SuccessfulResponse([
            'uuid' => (string)$newLikeUuid,
        ]);
    }
}