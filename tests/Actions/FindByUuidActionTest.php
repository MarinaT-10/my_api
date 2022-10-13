<?php

namespace Actions;

use GeekBrains\LevelTwo\Blog\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Person\Name;
use GeekBrains\LevelTwo\Blog\{
    Post,
    User,
    UUID};
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Http\{Actions\Posts\FindByUuid,
    Auth\TokenAuthenticationInterface,
    ErrorResponse,
    Request,
    SuccessfulResponse};
use PHPUnit\Framework\TestCase;

class FindByUuidActionTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disable
     */
    public function testItReturnsErrorResponseIfNoUuidProvided(): void
    {

        $request = new Request([], [], '');

        $postsRepository = $this->postsRepository([]);
        $authenticationStub = $this->createStub(TokenAuthenticationInterface::class);
        //Создаём объект действия
        $action = new FindByUuid($postsRepository, $authenticationStub);

        // Запускаем действие
        $response = $action->handle($request);

        // Проверяем, что ответ - неудачный
        $this->assertInstanceOf(ErrorResponse::class, $response);

        // Описываем ожидание того, что будет отправлено в поток вывода
        $this->expectOutputString('{"success":false,"reason":"No such query param in the request: uuid"}');

        // Отправляем ответ в поток вывода
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    // Тест, проверяющий, что будет возвращён неудачный ответ,
    // если пост не найден
    public function testItReturnsErrorResponseIfPostNotFound(): void
    {
        $request = new Request(['uuid' => '6b146f5c-34bb-4f21-ae22-09298703ad19'], [], '');

        $postsRepository = $this->postsRepository([]);
        $authenticationStub = $this->createStub(TokenAuthenticationInterface::class);

        $action = new FindByUuid($postsRepository, $authenticationStub);

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Not found"}');

        $response->send();
    }


    private  function postsRepository(array $posts): PostsRepositoryInterface
    {
        return new class($posts) implements PostsRepositoryInterface {
            public function __construct(
                private array $posts,
            )
            {
            }

            public function save(Post $post): void
            {

            }

            public function get(UUID $uuid): Post
            {
                foreach ($this->posts as $post) {
                    if ($post instanceof Post && $post === $post->uuid()) {
                        return $post;
                    }
                }
                throw new PostNotFoundException("Not found");
            }

            public function delete(UUID $uuid): void
            {

            }
        };
    }

}