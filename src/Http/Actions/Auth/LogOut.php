<?php

namespace GeekBrains\LevelTwo\Http\Actions\Auth;

use DateTimeImmutable;
use GeekBrains\LevelTwo\Blog\Exceptions\{
    AuthException,
    AuthTokenNotFoundException,
    HttpException};
use GeekBrains\LevelTwo\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
use GeekBrains\LevelTwo\Http\{Actions\ActionInterface,
    Request,
    Response,
    SuccessfulResponse};

class LogOut implements ActionInterface
{
    private const HEADER_PREFIX = 'Bearer ';

    public function __construct(
        private AuthTokensRepositoryInterface $authTokensRepository,
    )
    {
    }

    /**
     * @throws AuthException
     */
    public function handle(Request $request): Response
    {
        try {
        $header = $request->header('Authorization');
        } catch (HttpException $e) {
            throw new AuthException($e->getMessage());
        }

        if (!str_starts_with($header, self::HEADER_PREFIX)) {
            throw new AuthException("Malformed token: [$header]");
        }

        $token = mb_substr($header, strlen(self::HEADER_PREFIX));

        try {
            $authToken = $this->authTokensRepository->get($token);
        } catch (AuthTokenNotFoundException) {
            throw new AuthException("Bad token: [$token]");
        }

        $authToken->setExpiresOn(new DateTimeImmutable("now"));

        $this->authTokensRepository->save($authToken);

        return new SuccessfulResponse([
            'token' => $authToken->token(),
        ]);

    }
}