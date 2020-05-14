<?php

namespace Pw\Pay\Controller;

use Psr\Container\ContainerInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Pw\Pay\Model\User;
use Iban\Validation\Validator;
use Iban\Validation\Iban;
use Pw\Pay\Model\Transaction;
use DateTime;

final class LoadMoneyController
{

    private bool $IBANOK;
    private String $IBAN;
    private String $email;
    private String $errorIBAN;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->IBAN = "";
        $this->IBANOK = false;
        $this->errorIBAN = "";
    }

    public function showLoadMoney(Request $request, Response $response): Response
    {
        $this->getUserInfo();

        if (strcmp($this->IBAN, "") == 0) {
            return $this->container->get('view')->render($response, 'loadMoney.twig', []);
        } else {
            $aux = substr_replace($this->IBAN, '******************', 6, -4);
            $info = array(
                "IBAN" => $aux,
                "IBANOK" => true);
            return $this->container->get('view')->render($response, 'loadMoney.twig', $info);
        }
    }

    public function loadBankAccount(Request $request, Response $response): Response
    {
        try {

            $this->getUserInfo();

            $data = $request->getParsedBody();

            $this->IBANOK = $this->validateIBAN($data['IBAN']);

            if ($this->IBANOK) {
                $this->container->get('user_repository')->saveIBAN($this->email, $data['IBAN'], $data['owner']);
                $info = array(
                    "IBAN" => substr_replace($data['IBAN'], '******************', 6, -4),
                    "IBANOK" => $this->IBANOK);
                return $this->container->get('view')->render($response, 'loadMoney.twig', $info);
            } else {
                $info = array(
                    "owner" => $data['owner'],
                    "IBAN" => $data['IBAN'],
                    "IBANOK" => $this->IBANOK,
                    "error" => $this->errorIBAN);

                return $this->container->get('view')->render($response, 'loadMoney.twig', $info);
            }


        } catch (\mysql_xdevapi\Exception $exception) {
            $response->getBody()
                ->write('Unexpected error: ' . $exception->getMessage());
            return $response->withStatus(500);
        }

        return $response->withStatus(201);
    }

    public function loadMoney(Request $request, Response $response): Response
    {
        $this->getUserInfo();
        $data = $request->getParsedBody();
        $amountOK = preg_match('/^[0-9]+(?:\.[0-9]{0,2})?$/', $data['amount']);

        if ($amountOK) {

            $balance = $this->container->get('user_repository')->getBalance($this->email);
            $this->container->get('user_repository')->addAmount((int)$_COOKIE['logged'], floatval($data['amount']) + floatval($balance));
            $transaction = Transaction::simpleTrans(
                (int)$_COOKIE['logged'],                  //SENDER _ Entenc que es a qui li demano
                (int)$_COOKIE['logged'],    //RECEIVER _ Entenc que com jo demano, jo rebo
                floatval($data['amount']),
                new DateTime(),
                true
            );
            $this->container->get('user_repository')->newTransaction($transaction);
            header("Location: /dashboard");
            die();
            return $this->container->get('view')->render($response, 'dashboard.twig', []);
        } else {
            $info = array(
                "IBAN" => $this->IBAN,
                "IBANOK" => true,
                "amountOK" => $amountOK,
                "amount" => $data['amount']);
            return $this->container->get('view')->render($response, 'loadMoney.twig', $info);
        }
    }

    private function getUserInfo()
    {
        $userInfo = $this->container->get('user_repository')->getUserWithCookie((int)$_COOKIE['logged']);
        $this->IBAN = $userInfo->getIBAN();
        $this->email = $userInfo->getEmail();
    }

    private function validateIBAN($iban)
    {
        $validator = new Validator();

        if (!$validator->validate($iban)) {
            foreach ($validator->getViolations() as $violation) {
                $this->errorIBAN = $violation;
                return false;
            }
        }
        return true;
    }
}