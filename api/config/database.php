<?php
class Database
{
    private $host = "localhost";
    private $db_name = "u956761219_flashcards";
    private $username = "u956761219_root";
    private $password = "u956761219U";
    public $conn;

    public function getConnection()
    {
        // XAMPP
        if (file_exists("/xampp")) {
            $this->host = "localhost";
            $this->db_name = "flashcards";
            $this->username = "root";
            $this->password = "";
        }

        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
            file_put_contents("debug.html", "Connection error: " . $exception->getMessage() . "<hr>", FILE_APPEND);
        }

        return $this->conn;
    }
}
?>