<?php

declare(strict_types=1);

namespace Pw\Pay\Controller;


use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Pw\Pay\Model\User;

final class TransactionController
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showTransactions(Request $request, Response $response): Response
    {
        $userInfo = $this->container->get('user_repository')->getUserWithCookie((int)$_COOKIE['logged']);

        $usertrans = $this->container->get('user_repository')->getUserTransactions($userInfo->getId());
        $info = array(
            "id" => $userInfo->getId(),
            "email" => $userInfo->getEmail(),
            "balance" => $userInfo->getBalance(),
            "transactions" => $usertrans
        );

        return $this->container->get('view')->render(
            $response,
            'transactions.twig',
            $info
        );

    }

}