<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository;

use GeekBrains\LevelTwo\Blog\{Comment,
    Exceptions\CommentNotFoundException,
    Exceptions\InvalidArgumentException,
    Exceptions\PostNotFoundException,
    Exceptions\UserNotFoundException,
    Repositories\PostsRepository\SqlitePostsRepository,
    Repositories\UsersRepository\SqliteUsersRepository,
    UUID};
use \PDO;
use PDOStatement;
use Psr\Log\LoggerInterface;


class SqliteCommentsRepository implements CommentsRepositoryInterface
{
    private PDO $connection;
    private LoggerInterface $logger;

    public function __construct(PDO $connection, LoggerInterface $logger)

    {
        $this->connection = $connection;
        $this->logger = $logger;
    }

    public function save(Comment $comment): void
    {

        $statement = $this->connection->prepare(
            'INSERT INTO comments (
                      uuid, author_uuid, post_uuid, text) 
                    VALUES (:uuid, :author_uuid, :post_uuid,:text)'
        );

        $statement->execute([
            ':uuid' => $comment->uuid(),
            ':author_uuid' => $comment->user()->uuid(),
            ':post_uuid' => $comment->post()->uuid(),
            ':text' => $comment->text(),
        ]);
        $this->logger->info("Comment created: {$comment->uuid()}");
    }


    /**
     * @throws CommentNotFoundException
     * @throws InvalidArgumentException
     * @throws UserNotFoundException
     * @throws PostNotFoundException
     */
    public function get(UUID $uuid): Comment
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM comments WHERE uuid = :uuid'
        );

        $statement->execute([
            ':uuid' => (string)$uuid
        ]);

        return $this->getComment($statement, $uuid);
    }


    /**
     * @throws CommentNotFoundException
     * @throws InvalidArgumentException
     * @throws UserNotFoundException
     * @throws PostNotFoundException
     */
    public function getComment(PDOStatement $statement, string $commentUuid): Comment
    {
        $result = $statement->fetch(PDO::FETCH_ASSOC);


        if ($result === false) {
            $message = "Cannot get comment: $commentUuid";
            $this->logger->warning($message);
            throw new CommentNotFoundException($message);
        }

        $userRepository = new SqliteUsersRepository($this->connection, $this->logger);
        $user = $userRepository->get(new UUID($result['author_uuid']));

        $postRepository = new SqlitePostsRepository($this->connection, $this->logger);
        $post = $postRepository->get(new UUID($result['post_uuid']));

        return  new Comment (
            new UUID($result['uuid']),
            $user,
            $post,
            $result['text']);
    }

    public function delete(UUID $uuid): void
    {
        $statement = $this->connection->prepare(
            'DELETE FROM comments WHERE comments.uuid = :uuid'
        );

        $statement->execute([
            ':uuid' => (string)$uuid
        ]);

       $this->logger->info("This comment deleted");
    }


}

