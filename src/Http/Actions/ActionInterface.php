<?php

namespace GeekBrains\LevelTwo\Http\Actions;

use GeekBrains\LevelTwo\Http\{Request, Response};

interface ActionInterface
{
    public function handle(Request $request): Response;
}