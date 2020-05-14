<?php

declare(strict_types=1);

namespace Pw\Pay\Model;

use Date;
use DateTime;

final class User
{
    private int $id;
    private string $email;
    private string $password;
    private string $phone;
    private string $birthday;
    private float $balance;
    private string $iban;
    private string $owner;
    private DateTime $createdAt;
    private DateTime $updatedAt;
    private bool $active;

    /**
     * User constructor.
     * @param int $id
     * @param string $email
     * @param string $password
     * @param string $phone
     * @param string $birthday
     * @param DateTime $createdAt
     * @param DateTime $updatedAt
     * @param bool $active
     * @param float $balance
     */

    /**
     * User constructor.
     */
    public function __construct()
    {
    }

    public static function withParams(string $email, string $password, string $phone, string $birthday, DateTime $createdAt, DateTime $updatedAt, bool $active, float $balance)
    {
        $instance = new self();
        $instance->email = $email;
        $instance->password = $password;
        $instance->phone = $phone;
        $instance->birthday = $birthday;
        $instance->createdAt = $createdAt;
        $instance->updatedAt = $updatedAt;
        $instance->active = $active;
        $instance->balance = $balance;
        $instance->iban = "";
        $instance->owner = "";


        return $instance;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return Date
     */
    public function getBirthday(): string
    {
        return $this->birthday;
    }

    /**
     * @param Date $birthday
     */
    public function setBirthday(string $birthday): void
    {
        $this->birthday = $birthday;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     */
    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return DateTime
     */
    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTime $updatedAt
     */
    public function setUpdatedAt(DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return float
     */
    public function getBalance(): float
    {
        return $this->balance;
    }

    /**
     * @param float $balance
     */
    public function setBalance(float $balance): void
    {
        $this->balance = $balance;
    }

    /**
     * @return string
     */
    public function getIBAN(): string
    {
        return $this->iban;
    }

    /**
     * @param string $email
     */
    public function setIBAN(string $iban): void
    {
        $this->iban = $iban;
    }

    /**
     * @return string
     */
    public function getOwner(): string
    {
        return $this->owner;
    }

    /**
     * @param string $email
     */
    public function setOwner(string $owner): void
    {
        $this->owner = $owner;
    }

}
