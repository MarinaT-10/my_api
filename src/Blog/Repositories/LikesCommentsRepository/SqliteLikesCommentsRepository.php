<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\LikesCommentsRepository;

use GeekBrains\LevelTwo\Blog\Exceptions\{
    InvalidArgumentException,
    LikeAlreadyExists,
    LikeNotFoundException
};
use GeekBrains\LevelTwo\Blog\{Like, LikeComment, UUID};
use PDO;
use PDOStatement;
use Psr\Log\LoggerInterface;

class SqliteLikesCommentsRepository implements LikesCommentsRepositoryInterface
{
    private PDO $connection;
    private LoggerInterface $logger;

    public function __construct(PDO $connection, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->logger = $logger;
    }

    public function save(LikeComment $likeComment): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO likesToComments (uuid, post_uuid, comment_uuid, author_uuid) VALUES (:uuid, :post_uuid, :comment_uuid, :author_uuid) '
        );

        $statement->execute([
            ':uuid' => (string)$likeComment->uuid(),
            ':post_uuid' => (string)$likeComment->postUuid(),
            ':comment_uuid' => (string)$likeComment->commentUuid(),
            ':author_uuid' => (string)$likeComment->authorUuid()
        ]);
        $this->logger->info("Like to the post {$likeComment->postUuid()} created: {$likeComment->uuid()}");
    }

    public function get (UUID $uuid): LikeComment
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM likesToComments WHERE uuid = :uuid'
        );

        $statement->execute([
            ':uuid' => (string)$uuid
        ]);

        return $this->getLikeComment($statement, $uuid);
    }

    /**
     * @throws LikeNotFoundException
     * @throws InvalidArgumentException
     */
    private function getLikeComment(PDOStatement $statement, $likeCommentUuid):LikeComment
    {
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result === false) {
            $message = "Cannot get like: $likeCommentUuid";
            $this->logger->warning($message);

            throw new LikeNotFoundException($message);
        }

        return new LikeComment(
            new UUID($result['uuid']),
            new UUID($result['post_uuid']),
            new UUID($result['comment_uuid']),
            new UUID($result['author_uuid'])
        );
    }


    public function getByCommentUuid(UUID $uuid): array
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM likesToComments WHERE comment_uuid = :uuid'
        );

        $statement->execute([
            ':uuid' => (string)$uuid
        ]);

        return $this->getCommentLikes ($statement, $uuid);
    }

    /**
     * @throws LikeNotFoundException
     * @throws InvalidArgumentException
     */
    public function getCommentLikes(PDOStatement $statement, string $likeCommentUuid): array
    {
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        if ($result === false) {
            $message = "Cannot get like: $likeCommentUuid";
            $this->logger->warning($message);

            throw new LikeNotFoundException($message);
        }

        $likes = [];
        foreach ($result as $likeComment){
            $likes[]= new LikeComment(
                uuid: new UUID($likeComment['uuid']),
                post_uuid: new UUID($likeComment['post_uuid']),
                comment_uuid: new UUID($likeComment['comment_uuid']),
                author_uuid: new UUID($likeComment['author_uuid'])
            );
        }
        return $likes;
    }

    /**
     * @throws LikeAlreadyExists
     */
    public function getLikeFromUserToComment($comment_uuid, $author_uuid): void
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM likesToComments WHERE comment_uuid =:comment_uuid AND author_uuid = :author_uuid'
        );

        $statement->execute([
                ':comment_uuid' => $comment_uuid,
                ':author_uuid' => $author_uuid
            ]
        );

        $isExisted = $statement->fetch();

        if ($isExisted) {
            $message = "The like for this comment already exists!";
            $this->logger->warning($message);
            throw new LikeAlreadyExists($message);
        }
    }

    public function delete(UUID $uuid): void
    {
        $statement = $this->connection->prepare('DELETE FROM likesToComments WHERE likesToComments.uuid = :uuid'
        );

        $statement->execute([
            ':uuid' => (string)$uuid
        ]);
        $this->logger->info("This like to comment deleted!");
    }
}

