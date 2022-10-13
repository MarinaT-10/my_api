<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\PostsRepository;

use PDOException;
use GeekBrains\LevelTwo\Blog\{
    Post,
    UUID};
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\LevelTwo\Blog\Exceptions\{PostNotFoundException,
    InvalidArgumentException,
    PostsRepositoryException,
    UserNotFoundException};
use \PDO;
use \PDOStatement;
use Psr\Log\LoggerInterface;

class SqlitePostsRepository implements PostsRepositoryInterface
{
    private PDO $connection;
    private LoggerInterface $logger;

    public function __construct(PDO $connection, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->logger = $logger;
    }

    public function save(Post $post): void
    {

        $statement = $this->connection->prepare(
            'INSERT INTO posts (uuid, author_uuid, title, text) VALUES (:uuid, :author_uuid, :title, :text)'
        );

        $statement->execute([
            ':uuid' => $post->uuid(),
            ':author_uuid' => $post->user()->uuid(),
            ':title' => $post->title(),
            ':text' => $post->text(),
        ]);
        $this->logger->info("Post created: {$post->uuid()}");
    }


    /**
     * @throws PostNotFoundException
     * @throws InvalidArgumentException
     * @throws UserNotFoundException
     */
    public function get(UUID $uuid): Post
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM posts WHERE uuid = :uuid'
        );

        $statement->execute([
            ':uuid' => (string)$uuid
        ]);


        return $this->getPost ($statement, $uuid);
    }



    /**
     * @throws PostNotFoundException
     * @throws InvalidArgumentException|UserNotFoundException
     */
    public function getPost(PDOStatement $statement, string $postUuid): Post
    {
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result === false) {
            $message = "Cannot get post: $postUuid";
            $this->logger->warning($message);
            throw new PostNotFoundException($message);
        }
        $userRepository = new SqliteUsersRepository($this->connection, $this->logger);
        $user = $userRepository->get(new UUID($result['author_uuid']));

        return  new Post (
            new UUID($result['uuid']),
            $user,
            $result["title"],
            $result['text']);
    }


    /**
     * @param UUID $uuid
     * @return void
     * @throws PostsRepositoryException
     */
    public function delete(UUID $uuid): void
    {
        try {
            $statement = $this->connection->prepare(
                'DELETE FROM posts WHERE uuid = ?'
            );
            $statement->execute([(string)$uuid]);
        } catch (PDOException $e) {
            throw new PostsRepositoryException(
                $e->getMessage(), (int)$e->getCode(), $e
            );
        }
    }
}
