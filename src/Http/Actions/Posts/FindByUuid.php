<?php

namespace GeekBrains\LevelTwo\Http\Actions\Posts;

use GeekBrains\LevelTwo\Blog\{Exceptions\AuthException,
    Exceptions\HttpException,
    Exceptions\InvalidArgumentException,
    Exceptions\PostNotFoundException,
    Repositories\PostsRepository\PostsRepositoryInterface,
    UUID};
use GeekBrains\LevelTwo\Http\{Actions\ActionInterface,
    Auth\TokenAuthenticationInterface,
    ErrorResponse,
    Response,
    SuccessfulResponse,
    Request};

/*GET http://localhost:80/posts/show?uuid=5ea43514-dba4-4498-b0dc-d221075850c3
Authorization: Bearer ceb2ec8366bdba432083be336e829d1bb13b71846a60b76660fab49c37983c6d0defd90c982a4987*/

class FindByUuid implements ActionInterface
{
    public function __construct(
        private PostsRepositoryInterface $postsRepository,
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
            $user = $this->authentication->user($request);
        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $postUuid = $request->query('uuid');
        } catch (HttpException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $post =  $this->postsRepository->get(new UUID($postUuid));
        } catch (PostNotFoundException $e) {
            return new ErrorResponse($e->getMessage());
        }

        return new SuccessfulResponse([
            'uuid' => $postUuid,
            'title' => $post->title()
        ]);
    }
}

