<?php

namespace GeekBrains\LevelTwo;


use GeekBrains\LevelTwo\Blog\{
    Comment,
    Post,
    User,
    UUID,
    Exceptions\CommentNotFoundException};
use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use GeekBrains\LevelTwo\Person\Name;
use GeekBrains\LevelTwo\tests\DummyLogger;
use PHPUnit\Framework\TestCase;

class SqliteCommentsRepositoryTest extends TestCase
{

    //Проверяем, что репозиторий бросает исключение, если комментарий не найден
    public function testItThrowsAnExceptionWhenCommentNotFound(): void
    {
        $connectionMock = $this->createStub(\PDO::class);
        $statementStub = $this->createStub(\PDOStatement::class);

        $statementStub->method('fetch')->willReturn(false);
        $connectionMock->method('prepare')->willReturn($statementStub);

        $repository = new SqliteCommentsRepository($connectionMock, new DummyLogger());

        $this->expectException(CommentNotFoundException::class);
        $this->expectExceptionMessage('Cannot get comment: 221e1111-e11b-11d1-a111-111111111221');

        $repository->get(new UUID('221e1111-e11b-11d1-a111-111111111221'));

    }

    //Проверяем, что комментарий сохраняется в репозиторий;
    public function testItSavesCommentToDatabase(): void
    {
        $connectionStub = $this->createStub(\PDO::class);
        $statementMock = $this->createMock(\PDOStatement::class);

        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                ':uuid' => '968e4567-e89b-12d3-a456-426614174000',
                ':author_uuid' => '5a91ed7a-0ae4-495f-b666-c52bc8f13fe4',
                ':post_uuid' => '7b094211-1881-40f4-ac73-365ad0b2b2d4',
                ':text' => 'Text comment',

            ]);

        $connectionStub->method('prepare')->willReturn($statementMock);

        $repository = new SqliteCommentsRepository($connectionStub, new DummyLogger());

        $user = new User(
            new UUID('5a91ed7a-0ae4-495f-b666-c52bc8f13fe4'),
            new Name('first_name', 'last_name'),
            'name',
            '123'
        );

        $post =new Post(
            new UUID('7b094211-1881-40f4-ac73-365ad0b2b2d4'),
            $user,
            'Title Post',
            'Text, text, text'
        );

        $connectionStub->method('prepare')->willReturn($statementMock);

        $repository->save(
            new Comment(
                new UUID('968e4567-e89b-12d3-a456-426614174000'),
                $user,
                $post,
                'Text comment'
            )
        );
    }

    //Проверяем, что репозиторий находит комментарий по UUID
    public function testItGetCommentByUuid(): void
    {

        $connectionStub = $this->createStub(\PDO::class);
        $statementMock = $this->createMock(\PDOStatement::class);

        $statementMock->method('fetch')->willReturn([
            'uuid' => '968e4567-e89b-12d3-a456-426614174000',
            'author_uuid' => '5a91ed7a-0ae4-495f-b666-c52bc8f13fe4',
            'post_uuid' => '7b094211-1881-40f4-ac73-365ad0b2b2d4',
            'text' => 'Text comment',
            'title' => 'Title',
            'text_post' => 'Text test Mock',
            'username' => 'ivan123',
            'password' => '123',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin'
        ]);


        $connectionStub->method('prepare')->willReturn($statementMock);

        $commentsRepository = new SqliteCommentsRepository($connectionStub, new DummyLogger());
        $comment = $commentsRepository->get(new UUID('968e4567-e89b-12d3-a456-426614174000'));

        $this->assertSame('968e4567-e89b-12d3-a456-426614174000', (string)$comment->uuid());
    }

}