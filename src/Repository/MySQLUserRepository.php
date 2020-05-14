<?php

declare(strict_types=1);

namespace Pw\Pay\Repository;

use Composer\Repository\InstalledFilesystemRepository;
use PDO;
use Pw\Pay\Model\Transaction;
use Pw\Pay\Model\User;
use Pw\Pay\Model\UserRepository;

final class MySQLUserRepository implements UserRepository
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    private PDOSingleton $database;

    public function __construct(PDOSingleton $database)
    {
        $this->database = $database;
    }

    public function save(User $user): void
    {
        $query = <<<'QUERY'
        INSERT INTO user(email, password, phone, birthdate,created_at, updated_at)
        VALUES(:email, :password, :phone, :birthdate,:created_at, :updated_at)
QUERY;
        $statement = $this->database->connection()->prepare($query);

        $email = $user->getEmail();
        $password = hash('sha256', $user->getPassword());
        $phone = $user->getPhone();
        $birthday = $user->getBirthday();
        $createdAt = $user->getCreatedAt()->format(self::DATE_FORMAT);
        $updatedAt = $user->getUpdatedAt()->format(self::DATE_FORMAT);

        $statement->bindParam('email', $email, PDO::PARAM_STR);
        $statement->bindParam('password', $password, PDO::PARAM_STR);
        $statement->bindParam('phone', $phone, PDO::PARAM_STR);
        $statement->bindParam('birthdate', $birthday, PDO::PARAM_STR);
        $statement->bindParam('created_at', $createdAt, PDO::PARAM_STR);
        $statement->bindParam('updated_at', $updatedAt, PDO::PARAM_STR);

        $statement->execute();
    }

    public function getUser(string $email)
    {

        $query = <<<'QUERY'
        SELECT * FROM user WHERE email = :email;
QUERY;
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('email', $email, PDO::PARAM_STR);
        $statement->execute();
        $userInfo = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (empty($userInfo)) {
            return null;
        }

        $user = new User();
        foreach ($userInfo as $row) {
            $user->setId((int)$row['id']);
            $user->setEmail($row['email']);
            $user->setPhone($row['phone']);
            $user->setBirthday($row['birthdate']);
            $user->setPassword($row['password']);
            $user->setActive((bool)$row['active']);
            $user->setIBAN($row['iban']);
            $user->setBalance((float)$row['balance']);

        }
        return $user;

    }

    public function getUserWithCookie(int $id)
    {

        $query = <<<'QUERY'
        SELECT * FROM user WHERE id = :id;
QUERY;
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('id', $id, PDO::PARAM_STR);
        $statement->execute();
        $userInfo = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (empty($userInfo)) {
            return null;
        }

        $user = new User();
        foreach ($userInfo as $row) {
            $user->setId((int)$row['id']);
            $user->setEmail($row['email']);
            $user->setPhone($row['phone']);
            $user->setBirthday($row['birthdate']);
            $user->setPassword($row['password']);
            $user->setActive((bool)$row['active']);
            $user->setIBAN($row['iban']);
            $user->setBalance((float)$row['balance']);
        }
        return $user;

    }
    public function getUserNameEmail($id)
    {

        $query = <<<'QUERY'
        SELECT owner, email FROM user WHERE id = :id;
QUERY;
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('id', $id, PDO::PARAM_STR);
        $statement->execute();
        $userInfo = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (empty($userInfo)) {
            return null;
        }
        $data = array(
            "owner" => $userInfo[0]['owner'],
            "email" => $userInfo[0]['email']

        );
        return $data;

    }


    public function getUserId(string $email)
    {

        $query = <<<'QUERY'
        SELECT id FROM user WHERE email = :email;
QUERY;
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('email', $email, PDO::PARAM_STR);
        $statement->execute();
        $userInfo = $statement->fetch();

        if ($userInfo['id'] != null) {

            return $userInfo['id'];
        }

        return null;
    }

    public function savePhone($email, $phone): void
    {
        $query = <<<'QUERY'
        UPDATE user SET phone = :phone WHERE email = :email
QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('phone', $phone, PDO::PARAM_STR);
        $statement->bindParam('email', $email, PDO::PARAM_STR);
        $statement->execute();
    }

    public function savePassword($email, $password): void
    {
        $query = <<<'QUERY'
        UPDATE user SET password = :password WHERE email = :email
QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('password', $password, PDO::PARAM_STR);
        $statement->bindParam('email', $email, PDO::PARAM_STR);
        $statement->execute();
    }


    public function saveToken($id, $token): void
    {
        $query = <<<'QUERY'
        INSERT INTO tokens(id, token)
        VALUES(:id, :token)
QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('id', $id, PDO::PARAM_STR);
        $statement->bindParam('token', $token, PDO::PARAM_STR);

        $statement->execute();
    }

    public function getUserIdFromToken(string $token)
    {
        try {


            $query = <<<'QUERY'
        SELECT id FROM tokens WHERE token = :token AND active = 0;
QUERY;
            $statement = $this->database->connection()->prepare($query);
            $statement->bindParam('token', $token, PDO::PARAM_STR);
            $statement->execute();
            $userInfo = $statement->fetch();
            if ($userInfo && $userInfo['id'] != null) {
                $query = <<<'QUERY'
        UPDATE tokens SET active = 1 WHERE id = :id ;
QUERY;
                $statement = $this->database->connection()->prepare($query);
                $statement->bindParam('id', $userInfo['id'], PDO::PARAM_INT);
                $statement->execute();

                return $userInfo['id'];

            }

            return null;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function activate($id): void
    {
        $query = <<<'QUERY'
        UPDATE user SET active = 1 WHERE id = :id;
QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('id', $id, PDO::PARAM_STR);

        $statement->execute();
    }

    public function newTransaction(Transaction $transaction): void
    {
        $query = <<<'QUERY'
        INSERT INTO transactions (senderid, receiverid, amount, datetrans, payed )
        VALUES(:senderid, :receiverid, :amount, :datetrans, :payed )
QUERY;
        $statement = $this->database->connection()->prepare($query);

        $senderid = $transaction->getSenderid();
        $receiverid = $transaction->getReceiverId();
        $amount = $transaction->getAmount();
        $datetrans = $transaction->getDate()->format(self::DATE_FORMAT);
        $payed = $transaction->isPayed();

        $statement->bindParam('senderid', $senderid, PDO::PARAM_INT);
        $statement->bindParam('receiverid', $receiverid, PDO::PARAM_INT);
        $statement->bindParam('amount', $amount, PDO::PARAM_INT);
        $statement->bindParam('datetrans', $datetrans, PDO::PARAM_STR);
        $statement->bindParam('payed', $payed, PDO::PARAM_BOOL);

        $statement->execute();
    }

    public function getTransaction(int $id)
    {

        $query = <<<'QUERY'
        SELECT * FROM transactions WHERE id = :id;
QUERY;
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('id', $id, PDO::PARAM_STR);
        $statement->execute();
        $transaction = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (empty($transaction)) {
            return null;
        }

        $t = new Transaction();
        foreach ($transaction as $row) {
            $t->setId((int)$row['id']);
            $t->setSenderId((int)$row['senderid']);
            $t->setReceiverId((int)$row['receiverid']);
            $t->setAmount((float)$row['amount']);
        }
        return $t;

    }

    public function getUserTransactions(int $id)
    {

        $query = <<<'QUERY'
        SELECT * FROM transactions WHERE (senderid = :id OR receiverid = :id) AND payed = 1 ORDER BY datetrans DESC;
QUERY;
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('id', $id, PDO::PARAM_STR);
        $statement->execute();


        while ($fila = $statement->fetch(PDO::FETCH_ASSOC)) {
            $namemail = $this->getUserNameEmail($fila['senderid']);
            $namemailreceiver = $this->getUserNameEmail($fila['receiverid']);
            $datos [] = Transaction::withParams($fila['id'],$fila['senderid'], $fila['receiverid'], $fila['amount'], $fila['datetrans'], $namemail['owner'] . " " . $namemail['email'], $namemailreceiver['owner'] . " " . $namemailreceiver['email']);
        }
        if (isset($datos)) {
            return $datos;
        }
        return null;

    }
    public function getLastUserTransactions(int $id)
    {

        $query = <<<'QUERY'
        SELECT * FROM transactions WHERE (senderid = :id OR receiverid = :id) AND payed = 1 ORDER BY datetrans DESC;
QUERY;
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('id', $id, PDO::PARAM_STR);
        $statement->execute();
        $lines = 0;
        while ($fila = $statement->fetch(PDO::FETCH_ASSOC)) {
            $namemail = $this->getUserNameEmail($fila['senderid']);
            $namemailreceiver = $this->getUserNameEmail($fila['receiverid']);
            $datos [] = Transaction::withParams($fila['id'],$fila['senderid'],$fila['receiverid'],$fila['amount'],$fila['datetrans'],$namemail['owner'] . " ". $namemail['email'], $namemailreceiver['owner']. " ". $namemailreceiver['email']);
            $lines++;
            if ($lines > 5) {
                return $datos;
            }
        }

        if (isset($datos)) {
            return $datos;
        }
        return null;

    }

    public function getPendingRequests(int $id)
    {

        $query = <<<'QUERY'
        SELECT * FROM transactions WHERE (senderid = :id) AND payed = 0 ORDER BY datetrans DESC;
QUERY;
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('id', $id, PDO::PARAM_STR);
        $statement->execute();
        $lines = 0;

        while ($fila = $statement->fetch(PDO::FETCH_ASSOC)) {
            $namemail = $this->getUserNameEmail($fila['senderid']);
            $namemailreceiver = $this->getUserNameEmail($fila['receiverid']);
            $datos [] = Transaction::withParams($fila['id'],$fila['senderid'], $fila['receiverid'], $fila['amount'], $fila['datetrans'], $namemail['owner'] . " " . $namemail['email'], $namemailreceiver['owner'] . " " . $namemailreceiver['email']);
        }
        if (isset($datos)) {
            return $datos;
        }
        return null;

    }

    public function acceptRequestTransaction(int $id): void
    {
        $query = <<<'QUERY'
        UPDATE transaction SET payed = 1, WHERE id = :id;
QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('id', $id, PDO::PARAM_STR);

        $statement->execute();
    }

    public function setPayedTransaction($id): void
    {
        $query = <<<'QUERY'
        UPDATE transactions SET payed = 1 WHERE id = :id;
QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('id', $id, PDO::PARAM_STR);

        $statement->execute();
    }

    public function saveIBAN(string $email, string $iban, string $owner): void
    {
        $query = <<<'QUERY'
        UPDATE user SET iban = :iban, owner = :owner WHERE email = :email;
QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('email', $email, PDO::PARAM_STR);
        $statement->bindParam('iban', $iban, PDO::PARAM_STR);
        $statement->bindParam('owner', $owner, PDO::PARAM_STR);

        $statement->execute();
    }

    public function getBalance(string $email)
    {

        $query = <<<'QUERY'
        SELECT balance FROM user WHERE email = :email;
QUERY;
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('email', $email, PDO::PARAM_STR);
        $statement->execute();
        $userInfo = $statement->fetch();

        if (isset($userInfo['balance'])) {
            return $userInfo['balance'];
        }

        return null;
    }

    public function getBalanceWithId(int $id)
    {

        $query = <<<'QUERY'
        SELECT balance FROM user WHERE email = :id;
QUERY;
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('id', $id, PDO::PARAM_STR);
        $statement->execute();
        $userInfo = $statement->fetch();

        if (isset($userInfo['balance'])) {
            return $userInfo['balance'];
        }

        return null;
    }

    public function addAmount( int $id, float $balance): void
    {
        $query = <<<'QUERY'
        UPDATE user SET balance = :balance WHERE id = :id;
QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('id', $id, PDO::PARAM_STR);
        $statement->bindParam('balance', $balance, PDO::PARAM_STR);

        $statement->execute();
    }

    public function addAmountEmail( $email, float $balance): void
    {
        $query = <<<'QUERY'
        UPDATE user SET balance = :balance WHERE email = :email;
QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('email', $email, PDO::PARAM_STR);
        $statement->bindParam('balance', $balance, PDO::PARAM_STR);

        $statement->execute();
    }

    public function addAmountWithId(int $id, float $balance): void
    {
        $query = <<<'QUERY'
        UPDATE user SET balance = :balance WHERE id = :id;
QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('id', $id, PDO::PARAM_STR);
        $statement->bindParam('balance', $balance, PDO::PARAM_STR);

        $statement->execute();
    }
}