<?php

namespace Actions;

use GeekBrains\LevelTwo\Blog\Exceptions\AuthException;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\JsonException;
use GeekBrains\LevelTwo\Blog\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Http\Actions\Posts\CreatePost;
use GeekBrains\LevelTwo\Http\Auth\JsonBodyUuidIdentification;
use GeekBrains\LevelTwo\Http\Auth\TokenAuthenticationInterface;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;
use GeekBrains\LevelTwo\Person\Name;
use GeekBrains\LevelTwo\tests\DummyLogger;
use PHPUnit\Framework\TestCase;

class CreatePostActionTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsErrorResponseIfNotFoundUser(): void
    {
        $request = new Request([], [],
            '{
            "author_uuid":"77bd08a7-373e-4a42-b77c-e17dfb8bb010",
            "title":"title",
            "text":"text"
            }');

        $postsRepositoryStub = $this->createStub(PostsRepositoryInterface::class);
        $authenticationStub = $this->createStub(TokenAuthenticationInterface::class);

        $authenticationStub
            ->method('user')
            ->willThrowException(
                new AuthException('Cannot find user: 77bd08a7-373e-4a42-b77c-e17dfb8bb010')
            );

        $action = new CreatePost($postsRepositoryStub, $authenticationStub, new DummyLogger());

        // Запускаем действие
        $response = $action->handle($request);

        $response->send();

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Cannot find user: 77bd08a7-373e-4a42-b77c-e17dfb8bb010"}');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws InvalidArgumentException
     */
    public function testItReturnsErrorResponseIfUuidInInvalidFormat(): void
    {
        $request = new Request([], [],
            '{
            "author_uuid":"10355573537-0805-4d7a-830e-22b48ddd1b4859c",
            "title":"title",
            "text":"text"
            }');

        $postsRepositoryStub = $this->createStub(PostsRepositoryInterface::class);
        $authenticationsStub = $this->createStub(TokenAuthenticationInterface::class);

        $authenticationsStub
            ->method('user')
            ->willThrowException(
                new AuthException('Malformed UUID: 10355573537-0805-4d7a-830e-22b48ddd1b4859c')
            );

        $action = new CreatePost($postsRepositoryStub, $authenticationsStub, new DummyLogger());


        $response = $action->handle($request);

        $response->send();

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Malformed UUID: 10355573537-0805-4d7a-830e-22b48ddd1b4859c"}');

    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     */
    public function testItReturnsErrorResponseIfNoTextProvided(): void
    {
        $request = new Request([], [],
            '{
            "author_uuid":"77bd08a7-373e-4a42-b77c-e17dfb8bb010",
            "title":"title"
            }');

        $postsRepository = $this->postsRepository([]);
        $authenticationStub = $this->createStub(TokenAuthenticationInterface::class);

        $authenticationStub
            ->method('user')
            ->willReturn(
                new User(
                    new UUID("77bd08a7-373e-4a42-b77c-e17dfb8bb010"),
                    new Name('first', 'last'),
                    'username',
                    '123'
                )
            );

        $action = new CreatePost($postsRepository, $authenticationStub, new DummyLogger());

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"No such field: text"}');

        $response->send();
    }

    private  function postsRepository(): PostsRepositoryInterface
    {
        return new class() implements PostsRepositoryInterface {
            private bool $called = false;

            public function __construct()
            {
            }

            public function save(Post $post): void
            {
                $this->called = true;
            }

            public function get(UUID $uuid): Post
            {
                throw new PostNotFoundException('Not found');
            }

            public function getByTitle(string $title): Post
            {
                throw new PostNotFoundException('Not found');
            }

            public function getCalled(): bool
            {
                return $this->called;
            }

            public function delete(UUID $uuid): void
            {
            }
        };
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsSuccessfulResponse(): void
    {
        $request = new Request([], [], '{
        "author_uuid":"77bd08a7-373e-4a42-b77c-e17dfb8bb010",
        "title":"title",
        "text":"text"}');

        $postsRepositoryStub = $this->createStub(PostsRepositoryInterface::class);
        $authenticationStub = $this->createStub(TokenAuthenticationInterface::class);

        $authenticationStub
            ->method('user')
            ->willReturn(
                new User(
                    new UUID("77bd08a7-373e-4a42-b77c-e17dfb8bb010"),
                    new Name('first', 'last'),
                    'username',
                    '123'
                )
            );

        $action = new CreatePost($postsRepositoryStub, $authenticationStub, new DummyLogger());

        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);

        $this->setOutputCallback(function ($data) {
            $dataDecode = json_decode(
                $data,
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );

            $dataDecode['data']['uuid'] = "77bd08a7-373e-4a42-b77c-e17dfb8bb010";
            return json_encode(
                $dataDecode,
                JSON_THROW_ON_ERROR
            );
        });

        $this->expectOutputString('{"success":true,"data":{"uuid":"77bd08a7-373e-4a42-b77c-e17dfb8bb010"}}');


        $response->send();
    }

    private  function usersRepository(array $users): UsersRepositoryInterface
    {
        return new class($users) implements UsersRepositoryInterface
        {
            public function __construct(
                private array $users
            )
            {
            }

            public function save(User $user): void
            {
            }

            public function get(UUID $uuid): User
            {
                foreach ($this->users as $user) {
                    if ($user instanceof User && (string)$uuid == $user->uuid()) {
                        return $user;
                    }
                }
                throw new UserNotFoundException('Cannot find user: ' . $uuid);
            }

            public function getByUsername(string $username): User
            {
                throw new UserNotFoundException('Not found');
            }
        };
    }
}