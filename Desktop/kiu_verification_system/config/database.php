<?php
/**
 * Database Configuration
 * KIU Automated Tuition Verification & Green Card System
 */

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        $this->host = function_exists('kiu_env') ? kiu_env('DB_HOST', 'localhost') : (getenv('DB_HOST') ?: 'localhost');
        $this->db_name = function_exists('kiu_env') ? kiu_env('DB_NAME', 'Greencard_system') : (getenv('DB_NAME') ?: 'Greencard_system');
        $this->username = function_exists('kiu_env') ? kiu_env('DB_USER', 'root') : (getenv('DB_USER') ?: 'root');
        $this->password = function_exists('kiu_env') ? kiu_env('DB_PASS', '') : (getenv('DB_PASS') ?: '');
    }

    /**
     * Get database connection
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                )
            );
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            throw new Exception("Database connection failed");
        }

        return $this->conn;
    }

    /**
     * Close database connection
     */
    public function closeConnection() {
        $this->conn = null;
    }
}
