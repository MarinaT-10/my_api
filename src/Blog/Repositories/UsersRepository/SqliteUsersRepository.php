<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\UsersRepository;
use GeekBrains\LevelTwo\Blog\{Exceptions\InvalidArgumentException, Exceptions\UserNotFoundException, User, UUID};
use GeekBrains\LevelTwo\Person\Name;
use \PDO;
use \PDOStatement;
use Psr\Log\LoggerInterface;

class SqliteUsersRepository implements UsersRepositoryInterface
{
    private PDO $connection;
    private LoggerInterface $logger;

    public function __construct(PDO $connection, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->logger = $logger;
    }



    public function save(User $user): void
    {

        $statement = $this->connection->prepare(
            'INSERT INTO users (first_name, last_name, uuid, username, password) 
VALUES (:first_name, :last_name, :uuid, :username, :password)
        ON CONFLICT (uuid) DO UPDATE SET
        first_name = :first_name, 
        last_name = :last_name'
        );

        // Выполняем запрос с конкретными значениями
        $statement->execute([
            ':first_name' => $user->name()->first(),
            ':last_name' => $user->name()->last(),
            ':uuid' => (string)$user->uuid(),
            ':username' => $user->username(),
            ':password' => $user->hashedPassword(),
        ]);
        $this->logger->info("User created: {$user->uuid()}");
    }


    /**
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    public function get(UUID $uuid): User
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM users WHERE uuid = ?'
        );

        $statement->execute([(string)$uuid]);

        return $this->getUser ($statement, $uuid);
    }

    /**
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    public function getByUsername(string $username): User
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM users WHERE username = :username'
        );

        $statement->execute([
            ':username' => $username,
        ]);

        return $this->getUser($statement, $username);
    }

    /**
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    private function getUser(PDOStatement $statement, string $errorString): User
    {
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        if ($result === false) {
            $message = "Cannot find user: $errorString";
            $this->logger->warning($message);

            throw new UserNotFoundException($message);
        }

        return new User(
            new UUID($result['uuid']),
            new Name(
                $result['first_name'],
                $result['last_name']),
            $result['username'],
            $result['password']
        );
    }
}