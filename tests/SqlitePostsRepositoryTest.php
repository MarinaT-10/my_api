<?php

namespace GeekBrains\LevelTwo;

use GeekBrains\LevelTwo\Blog\{
    Exceptions\PostNotFoundException,
    Post,
    User,
    UUID};
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use GeekBrains\LevelTwo\Person\Name;
use GeekBrains\LevelTwo\tests\DummyLogger;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class SqlitePostsRepositoryTest extends TestCase
{

    //Проверяем, что репозиторий бросает исключение, если статья не найдена
    public function testItThrowsAnExceptionWhenPostNotFound(): void
    {
        $connectionMock = $this->createStub(PDO::class);
        $statementStub = $this->createStub(PDOStatement::class);

        $statementStub->method('fetch')->willReturn(false);
        $connectionMock->method('prepare')->willReturn($statementStub);

        $repository = new SqlitePostsRepository($connectionMock, new DummyLogger());

        $this->expectException(PostNotFoundException::class);
        $this->expectExceptionMessage('Cannot get post: 111e1111-e11b-11d1-a111-111111111111');

        $repository->get(new UUID('111e1111-e11b-11d1-a111-111111111111'));

    }

    //Проверяем, что статья сохраняется в репозиторий;
    public function testItSavesPostToDatabase(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                ':uuid' => '123e4567-e89b-12d3-a456-426614174000',
                ':author_uuid' => '123e4567-e89b-12d3-a456-426614174000',
                ':title' => 'Title',
                ':text' => 'Post text',

            ]);

        $connectionStub->method('prepare')->willReturn($statementMock);

        $repository = new SqlitePostsRepository($connectionStub, new DummyLogger());

        $user = new User(
            new UUID('123e4567-e89b-12d3-a456-426614174000'),
            new Name('first_name', 'last_name'),
            'name',
            '123'
        );

        $connectionStub->method('prepare')->willReturn($statementMock);

        $repository->save(
            new Post(
                new UUID('123e4567-e89b-12d3-a456-426614174000'),
                $user,
                'Title',
                'Post text'
            )
        );
    }

    //Проверяем, что репозиторий находит статью по UUID
    public function testItGetPostByUuid(): void
    {

        $connectionStub = $this->createStub(\PDO::class);
        $statementMock = $this->createMock(\PDOStatement::class);

        $statementMock->method('fetch')->willReturn([
            'uuid' => '7b094211-1881-40f4-ac73-365ad0b2b2d4',
            'author_uuid' => '5a91ed7a-0ae4-495f-b666-c52bc8f13fe4',
            'title' => 'Title',
            'text' => 'Post text',
            'username' => 'ivan123',
            'password' => '123',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin'
        ]);


        $connectionStub->method('prepare')->willReturn($statementMock);

        $postRepository = new SqlitePostsRepository($connectionStub, new DummyLogger());
        $post = $postRepository->get(new UUID('7b094211-1881-40f4-ac73-365ad0b2b2d4'));

        $this->assertSame('7b094211-1881-40f4-ac73-365ad0b2b2d4', (string)$post->uuid());
    }

}