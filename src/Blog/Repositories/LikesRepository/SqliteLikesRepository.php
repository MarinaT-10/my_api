<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\LikesRepository;

use GeekBrains\LevelTwo\Blog\{Exceptions\InvalidArgumentException,
    Exceptions\LikeAlreadyExists,
    Exceptions\LikeNotFoundException,
    Like,
    UUID};
use PDO;
use PDOStatement;
use Psr\Log\LoggerInterface;


class SqliteLikesRepository implements LikesRepositoryInterface
{
    private PDO $connection;
    private LoggerInterface $logger;

    public function __construct(PDO $connection, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->logger = $logger;
    }

    public function save(Like $like): void
    {

        $statement = $this->connection->prepare(
            'INSERT INTO likes (uuid, post_uuid, author_uuid) VALUES (:uuid, :post_uuid, :author_uuid) '
        );

        $statement->execute([
            ':uuid' => (string)$like->uuid(),
            ':post_uuid' => (string)$like->postUuid(),
            ':author_uuid' => (string)$like->authorUuid()
        ]);
        $this->logger->info("Like created: {$like->uuid()}");
    }

    /**
     * @throws LikeNotFoundException
     * @throws InvalidArgumentException
     */
    public function get(UUID $uuid): Like
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM likes WHERE uuid = :uuid'
        );

        $statement->execute([
            ':uuid' => (string)$uuid
        ]);

        return $this->getLike($statement, $uuid);
    }

    /**
     * @throws LikeNotFoundException
     * @throws InvalidArgumentException
     */
    private function getLike (PDOStatement $statement, $likeUuid): Like
    {
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result === false) {
            $message = "Cannot get like: $likeUuid";
            $this->logger->warning($message);

            throw new LikeNotFoundException($message);
        }

        return new Like(
            new UUID($result['uuid']),
            new UUID($result['post_uuid']),
            new UUID($result['author_uuid'])
        );
    }

    /**
     * @param UUID $uuid
     * @return array
     * @throws InvalidArgumentException
     * @throws LikeNotFoundException
     */
    public function getByPostUuid(UUID $uuid): array
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM likes WHERE post_uuid = :uuid'
        );

        $statement->execute([
            ':uuid' => (string)$uuid
        ]);


    }

    /**
     * @throws LikeNotFoundException
     * @throws InvalidArgumentException
     */
    public function getLikes(PDOStatement $statement, string $likeUuid): array
    {
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        if ($result == false) {
            $message = "Cannot get like: $likeUuid";
            $this->logger->warning($message);

            throw new LikeNotFoundException($message);
        }

        $likes = [];
        foreach ($result as $like){
            $likes[]= new Like(
                uuid: new UUID($like['uuid']),
                post_uuid: new UUID($like['post_uuid']),
                author_uuid: new UUID($like['author_uuid'])
            );
        }
        return $likes;
    }

    /**
     * @throws LikeAlreadyExists
     */
    public function getLikeFromUserToPost ($post_uuid, $author_uuid): void
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM likes WHERE post_uuid =:post_uuid AND author_uuid = :author_uuid'
        );

        $statement->execute([
            ':post_uuid' => $post_uuid,
            ':author_uuid' => $author_uuid
        ]
        );

        $isExisted = $statement->fetch();

        if($isExisted) {
            $message = "The like for this post already exists!";
            $this->logger->warning($message);

            throw new LikeAlreadyExists($message);
        }
    }

    /**
     * @param UUID $uuid
     * @return void
     */
    public function delete(UUID $uuid): void
    {
        $statement = $this->connection->prepare(
            'DELETE FROM likes WHERE likes.uuid = :uuid'
        );

        $statement->execute([
            ':uuid' => (string)$uuid
        ]);

        $this->logger->info("Like to the post deleted!");

    }
}