<?php

namespace Pw\Pay\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

final class LogInMiddleware
{
    public function __invoke(Request $request, RequestHandler $next): Response
    {

        $isLogged = false;
        if (isset($_COOKIE['logged'])) {
            $isLogged = (bool)$_COOKIE['logged'];
        }

        if (!$isLogged) {
            $response = new Response();

            return $response->withHeader('Location', '/sign-in')->withStatus(302);
        }

        return $next->handle($request);
    }
}