<?php

declare(strict_types=1);

namespace Pw\Pay\Model;

interface UserRepository
{
    public function save(User $user): void;

    public function getUser(string $email);

    public function getUserWithCookie(int $id);

    public function getUserId(string $email);

    public function savePhone($email, $phone): void;

    public function savePassword($email, $password): void;

    public function saveToken($id, $token): void;

    public function getUserIdFromToken(string $token);

    public function activate(int $id): void;

    public function newTransaction(Transaction $transaction): void;

    public function getTransaction(int $id);

    public function getUserTransactions(int $id);

    public function getPendingRequests(int $id);

    public function acceptRequestTransaction(int $id): void;

    public function setPayedTransaction($id): void;

    public function saveIBAN(string $email, string $iban, string $owner): void;

    public function getBalance(string $email);

    public function getBalanceWithId(int $id);

    public function addAmount(int $id, float $balance): void;

    public function addAmountWithId(int $id, float $balance): void;
}
