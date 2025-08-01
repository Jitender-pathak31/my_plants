<?php

class Singleton_database
{
    private static $instance = null;
    private $connection;

    private $host = 'localhost'; // localhost
    private $db_name = 'pflanzen_db'; // DB name
    private $username = 'root'; // username
    private $password = ''; // password

    private function __construct()
    {
        try {
            $this->connection = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Setze den Zeichensatz auf UTF-8
            $this->connection->exec("set names utf8");
        } catch (PDOException $e) {
            error_log("Datenbankverbindungsfehler: " . $e->getMessage());
            die("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
        }
    }

    public static function getInstance(): Singleton_database
    {
        if (self::$instance == null) {
            self::$instance = new Singleton_database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}
