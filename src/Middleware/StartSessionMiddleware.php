<?php

namespace Pw\Pay\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

final class StartSessionMiddleware
{
    public function __invoke(Request $request, RequestHandler $next): Response
    {
        session_start();
        return $next->handle($request);
    }
}