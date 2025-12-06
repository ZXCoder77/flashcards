<?php
// CORS headers
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
$allowed_origins = [
    'https://nihongo-flashcards.netlify.app',
    'http://localhost',
    'http://127.0.0.1'
];

if (in_array($origin, $allowed_origins) || strpos($origin, 'http://localhost') === 0) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: *");
}

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"));

// Auth check
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : (isset($data->user_id) ? $data->user_id : null);

if (!$user_id) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized."]);
    exit();
}

switch ($method) {
    case 'GET':
        $query = "SELECT * FROM groups WHERE user_id = ? ORDER BY name ASC";
        $stmt = $db->prepare($query);
        $stmt->execute([$user_id]);
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($groups);
        break;

    case 'POST':
        if (!empty($data->name)) {
            $query = "INSERT INTO groups SET name = :name, user_id = :user_id";
            $stmt = $db->prepare($query);

            $data->name = htmlspecialchars(strip_tags($data->name));

            $stmt->bindParam(":name", $data->name);
            $stmt->bindParam(":user_id", $user_id);

            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(["message" => "Group created.", "id" => $db->lastInsertId()]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to create group."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data."]);
        }
        break;

    case 'PUT':
        if (!empty($data->id) && !empty($data->name)) {
            // Verify ownership
            $checkQuery = "SELECT id FROM groups WHERE id = ? AND user_id = ?";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->execute([$data->id, $user_id]);

            if ($checkStmt->rowCount() > 0) {
                $query = "UPDATE groups SET name = :name WHERE id = :id";
                $stmt = $db->prepare($query);

                $data->name = htmlspecialchars(strip_tags($data->name));

                $stmt->bindParam(":name", $data->name);
                $stmt->bindParam(":id", $data->id);

                if ($stmt->execute()) {
                    echo json_encode(["message" => "Group updated."]);
                } else {
                    http_response_code(503);
                    echo json_encode(["message" => "Unable to update group."]);
                }
            } else {
                http_response_code(403);
                echo json_encode(["message" => "Access denied."]);
            }
        }
        break;

    case 'DELETE':
        if (!empty($data->id)) {
            // Verify ownership
            $checkQuery = "SELECT id FROM groups WHERE id = ? AND user_id = ?";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->execute([$data->id, $user_id]);

            if ($checkStmt->rowCount() > 0) {
                $query = "DELETE FROM groups WHERE id = ?";
                $stmt = $db->prepare($query);

                if ($stmt->execute([$data->id])) {
                    echo json_encode(["message" => "Group deleted."]);
                } else {
                    http_response_code(503);
                    echo json_encode(["message" => "Unable to delete group."]);
                }
            } else {
                http_response_code(403);
                echo json_encode(["message" => "Access denied."]);
            }
        }
        break;
}
?>