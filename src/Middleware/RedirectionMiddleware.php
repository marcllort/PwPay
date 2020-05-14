<?php

namespace Pw\Pay\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

final class RedirectionMiddleware
{
    public function __invoke(Request $request, RequestHandler $next): Response
    {

        $isLogged = false;
        if (isset($_COOKIE['logged']) && $_COOKIE['logged'] > 0) {
            $isLogged = (bool)$_COOKIE['logged'];
            $response = new Response();
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }


        return $next->handle($request);
    }
}

