<?php

namespace GeekBrains\LevelTwo\Http\Auth;

use GeekBrains\LevelTwo\{
    Blog\User,
    Http\Request
};

interface AuthenticationInterface
{
    public function user(Request $request): User;
}