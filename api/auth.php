<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->action)) {
    echo json_encode(["message" => "Action required."]);
    exit();
}

if ($data->action == 'register') {
    if (!empty($data->username) && !empty($data->password)) {
        $query = "INSERT INTO users SET username = :username, password = :password";
        $stmt = $db->prepare($query);

        $data->username = htmlspecialchars(strip_tags($data->username));
        $password_hash = password_hash($data->password, PASSWORD_BCRYPT);

        $stmt->bindParam(':username', $data->username);
        $stmt->bindParam(':password', $password_hash);

        try {
            if ($stmt->execute()) {
                echo json_encode(["message" => "User was registered."]);
            } else {
                echo json_encode(["message" => "Unable to register user."]);
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                http_response_code(400);
                echo json_encode(["message" => "Username already exists."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to register user."]);
            }
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Incomplete data."]);
    }
} elseif ($data->action == 'login') {
    if (!empty($data->username) && !empty($data->password)) {
        $query = "SELECT id, username, password FROM users WHERE username = :username LIMIT 0,1";
        $stmt = $db->prepare($query);

        $data->username = htmlspecialchars(strip_tags($data->username));
        $stmt->bindParam(':username', $data->username);
        $stmt->execute();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($data->password, $row['password'])) {
                // In a real app, generate a JWT here. For simplicity, we return the user ID.
                echo json_encode([
                    "message" => "Login successful.",
                    "user_id" => $row['id'],
                    "username" => $row['username']
                ]);
            } else {
                http_response_code(401);
                echo json_encode(["message" => "Invalid password."]);
            }
        } else {
            http_response_code(401);
            echo json_encode(["message" => "User not found."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Incomplete data."]);
    }
}
?>