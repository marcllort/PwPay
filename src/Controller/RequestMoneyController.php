<?php

declare(strict_types=1);

namespace Pw\Pay\Controller;


use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Pw\Pay\Model\Transaction;
use Pw\Pay\Model\User;
use DateTime;


final class RequestMoneyController
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showRequestMoney(Request $request, Response $response): Response
    {
        return $this->container->get('view')->render($response,'requestMoney.twig',[]);
    }

    public function requestMoney(Request $request, Response $response): Response
    {
        $this->getUserInfo();
        $data = $request->getParsedBody();
        $emailOK = $this->validateEmail($data['email']);
        $emailExist = $this->validateEmailExist($data['email']);
        $amountOK = preg_match('/^[0-9]+(?:\.[0-9]{0,2})?$/', $data['amount']);

        if ($this->email == $data['email']) {
            $emailOK = false;
        }

        if ($emailOK && $emailExist && $amountOK)
        {
           $transaction = Transaction::simpleTrans(
                $this->sender->getId(),                  //SENDER _ Entenc que es a qui li demano
                (int)$_COOKIE['logged'],    //RECEIVER _ Entenc que com jo demano, jo rebo
                floatval($data['amount']),
                new DateTime(),
                false
            );
            $this->container->get('user_repository')->newTransaction($transaction);
            echo "<script>
                    alert('Petition sended succesfully.');
                    window.location.href='/dashboard'
                    </script>";
            return $this->container->get('view')->render($response, 'dashboard.twig', []);
        } else {
            $info = array(
                "emailOK" => $emailOK,
                "emailExist" => $emailExist,
                "email" => $data['email'],
                "amountOK" => $amountOK,
                "amount" => $data['amount']);
            return $this->container->get('view')->render($response,'requestMoney.twig',$info);
        }
        
    }

    public function showRequestPendingMoney(Request $request, Response $response): Response
    {

        $usertrans = $this->container->get('user_repository')->getPendingRequests((int)$_COOKIE['logged']);
        return $this->container->get('view')->render(
            $response,
            'pendingRequest.twig',
            array("transactions" => $usertrans)
        );
    }

    public function acceptRequestMoney(Request $request, Response $response): Response
    {

        $data = $request->getParsedBody();
        $transaction = $this->container->get('user_repository')->getTransaction((int)$data['id']);
        if ($transaction != null) {
            $sender = $this->container->get('user_repository')->getUserWithCookie((int)$transaction->getSenderId());
            $receiver = $this->container->get('user_repository')->getUserWithCookie((int)$transaction->getReceiverId());

            if ($sender != null && $receiver != null) {
                if ($sender->getBalance() - $transaction->getAmount() >= 0){
                    $this->container->get('user_repository')->addAmount($sender->getId(), 
                        $sender->getBalance() - $transaction->getAmount());
                    $this->container->get('user_repository')->addAmountEmail($receiver->getEmail(),
                        $receiver->getBalance() + $transaction->getAmount());
                    $this->container->get('user_repository')->setPayedTransaction($transaction->getId());


                    echo "<script>
                    alert('Money sended correctly.');
                    window.location.href='/dashboard'
                    </script>";
                } else {
                    echo "<script>
                        alert('Sorry, you dont have enought money!');
                        window.location.href='/dashboard'
                        </script>";
                }
            } else {
                echo "<script>
                    alert('Something goes wrong. If the problem persist, please contact with support team.');
                    window.location.href='/'
                    </script>";
            }
        }

        die();
        return $response->withHeader('RequestsPending', '/account/money/requests/pending');
    }

    private function getUserInfo()
    {
        $userInfo = $this->container->get('user_repository')->getUserWithCookie((int)$_COOKIE['logged']);
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

    private function validateEmailExist ($email)
    {
        $this->sender = $this->container->get('user_repository')->getUser($email);
        if ($this->sender == null) {
            return false;
        } else {
            return true;
        }
    }
}