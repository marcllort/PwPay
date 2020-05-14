<?php

declare(strict_types=1);

namespace Pw\Pay\Controller;


use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Pw\Pay\Model\Transaction;
use Pw\Pay\Model\User;
use DateTime;


final class SendMoneyController
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showSendMoney(Request $request, Response $response): Response
    {
        return $this->container->get('view')->render(
            $response,
            'sendMoney.twig',
            []
        );
    }

    public function sendMoney(Request $request, Response $response): Response
    {

        $this->getUserInfo();
        $data = $request->getParsedBody();
        $amountOK = preg_match('/^[0-9]+(?:\.[0-9]{0,2})?$/', $data['amount']);
        $emailOK = $this->validateEmail($data['email']);
        $emailExist = $this->validateEmailExist($data['email']);

        if ($this->email == $data['email']) {
            $emailOK = false;
        }

        if ($emailOK && $emailExist && $amountOK) {
            $balance = $this->container->get('user_repository')->getBalance($this->email);
            $balanceReceiver = $this->container->get('user_repository')->getBalance($data['email']);
            $newBalance = floatval($balance) - floatval($data['amount']);

            if ($newBalance >= 0 && isset($balanceReceiver)) {
                $this->container->get('user_repository')->addAmountEmail($this->email, $newBalance);
                $newBalanceReceiver = floatval($balanceReceiver) + floatval($data['amount']);
                $this->container->get('user_repository')->addAmountEmail($data['email'], $newBalanceReceiver);

                $transaction = Transaction::simpleTrans(
                    (int)$_COOKIE['logged'],
                    $this->sender->getId(),
                    (int)$data['amount'],
                    new DateTime(),
                    true
                );

                header("Location: /dashboard");
                $this->container->get('user_repository')->newTransaction($transaction);
                die();
                return $this->container->get('view')->render($response, 'dashboard.twig', []);
            } else {
                echo "Not enough money on your account!";
                $info = array(
                    "emailOK" => $emailOK,
                    "emailExist" => $emailExist,
                    "email" => $data['email'],
                    "amountOK" => false,
                    "amount" => $data['amount']);
                return $this->container->get('view')->render($response, 'sendMoney.twig', $info);
            }

        } else {
            $info = array(
                "emailOK" => $emailOK,
                "emailExist" => $emailExist,
                "email" => $data['email'],
                "amountOK" => $amountOK,
                "amount" => $data['amount']);
            return $this->container->get('view')->render($response, 'sendMoney.twig', $info);
        }
    }

    private function getUserInfo()
    {
        $userInfo = $this->container->get('user_repository')->getUserWithCookie((int)$_COOKIE['logged']);
        $this->IBAN = $userInfo->getIBAN();
        $this->email = $userInfo->getEmail();
    }

    private function validateEmail($email)
    {
        if (preg_match("/salle.url.edu$/", $email)) {
            return true;
        } else {
            return false;
        }
    }

    private function validateEmailExist($email)
    {
        $this->sender = $this->container->get('user_repository')->getUser($email);
        if ($this->sender == null) {
            return false;
        } else {
            return true;
        }
    }

}