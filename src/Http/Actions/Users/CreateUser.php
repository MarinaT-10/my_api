<?php

namespace GeekBrains\LevelTwo\Http\Actions\Users;

use GeekBrains\LevelTwo\Http\{
    Actions\ActionInterface,
    ErrorResponse,
    Request,
    Response,
    SuccessfulResponse};
use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use Psr\Log\LoggerInterface;
use GeekBrains\LevelTwo\Blog\{User, UUID};
use GeekBrains\LevelTwo\Person\Name;

class CreateUser implements ActionInterface
{
    public function __construct(
        private UsersRepositoryInterface $usersRepository,
        private LoggerInterface $logger,
    )
    {
    }

    public function handle(Request $request): Response
    {
        try {

            $newUserUuid = UUID::random();

            $user = new User(
            $newUserUuid,
            new Name(
                $request->jsonBodyField('first_name'),
                $request->jsonBodyField('last_name')
            ),
            $request->jsonBodyField('username'),
            $request->jsonBodyField('password'),
            );

        } catch(HttpException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $this->usersRepository->save($user);
        $this->logger->info("User \"{$user->username()}\"created: {$user->uuid()}");

        return new SuccessfulResponse([
            'uuid' => (string)$newUserUuid,
        ]);
    }
}