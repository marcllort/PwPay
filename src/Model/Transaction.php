<?php

declare(strict_types=1);

namespace Pw\Pay\Model;

use Date;
use DateTime;
use PhpParser\Node\Scalar\String_;

final class Transaction
{
    public  $id;
    public  $senderId;
    public  $sendername;
    public  $receiverId;
    public  $receivername;
    public  $amount;
    public  $payed;
    public  $date;


    public function __construct()
    {
    }

    public static function withParams( $id,$senderId,  $receiverId,  $amount,  $date,  $sendername, $receivername)
    {
        $instance = new self();
        $instance->id = $id;
        $instance->senderId = $senderId;
        $instance->receiverId = $receiverId;
        $instance->amount = $amount;
        $instance->date = $date;
        $instance->sendername = $sendername;
        $instance->receivername = $receivername;

        return $instance;
    }

    public static function simpleTrans(int $senderId, int $receiverId, float $amount, DateTime $date, bool $payed)
    {
        $instance = new self();
        $instance->senderId = $senderId;
        $instance->receiverId = $receiverId;
        $instance->amount = $amount;
        $instance->date = $date;
        $instance->payed = $payed;


        return $instance;
    }
    public static function onlyids( $id, $senderId,  $receiverId,  $amount)
    {
        $instance = new self();
        $instance->id = $id;
        $instance->senderId = $senderId;
        $instance->receiverId = $receiverId;
        $instance->amount = $amount;
        return $instance;
    }

    /**
     * @return String
     */
    public function getSendername()
    {
        return $this->sendername;
    }

    /**
     * @param String $sendername
     */
    public function setSendername($sendername)
    {
        $this->sendername = $sendername;
    }

    /**
     * @return String
     */
    public function getReceivername()
    {
        return $this->receivername;
    }

    /**
     * @param String $receivername
     */
    public function setReceivername($receivername)
    {
        $this->receivername = $receivername;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getSenderId()
    {
        return $this->senderId;
    }

    /**
     * @param int $senderId
     */
    public function setSenderId($senderId)
    {
        $this->senderId = $senderId;
    }

    /**
     * @return int
     */
    public function getReceiverId()
    {
        return $this->receiverId;
    }

    /**
     * @param int $receiverId
     */
    public function setReceiverId($receiverId)
    {
        $this->receiverId = $receiverId;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return bool
     */
    public function isPayed()
    {
        return $this->payed;
    }

    /**
     * @param bool $payed
     */
    public function setPayed($payed)
    {
        $this->payed = $payed;
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }


}