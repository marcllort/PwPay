<?php

declare(strict_types=1);

namespace Pw\Pay\Repository;

use PDO;

final class PDOSingleton
{
    private const CONNECTION_STRING = 'mysql:host=%s;port=%s;dbname=%s';

    private static ?PDOSingleton $instance = null;

    private PDO $connection;

    private function __construct(
        string $username,
        string $password,
        string $host,
        string $port,
        string $database
    )
    {
        $db = new PDO(
            sprintf(self::CONNECTION_STRING, $host, $port, $database),
            $username,
            $password
        );

        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->connection = $db;
    }

    public static function getInstance(
        string $username,
        string $password,
        string $host,
        string $port,
        string $database
    ): PDOSingleton
    {
        if (self::$instance === null) {
            self::$instance = new self(
                $username,
                $password,
                $host,
                $port,
                $database
            );
        }

        return self::$instance;
    }

    public function connection(): PDO
    {
        return $this->connection;
    }
}