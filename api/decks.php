<?php
// CORS headers
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
$allowed_origins = [
    'https://nihongo-flashcards.netlify.app',
    'http://localhost',
    'http://127.0.0.1'
];

// Check if origin is allowed
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

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"));

// Simple check for user_id in headers or query params (In real app, use JWT)
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : (isset($data->user_id) ? $data->user_id : null);

if (!$user_id) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized."]);
    exit();
}

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Get specific deck
            $query = "SELECT * FROM decks WHERE id = ? AND user_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$_GET['id'], $user_id]);
            $deck = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($deck) {
                // Get cards for the deck
                $cardQuery = "SELECT * FROM cards WHERE deck_id = ?";
                $cardStmt = $db->prepare($cardQuery);
                $cardStmt->execute([$_GET['id']]);
                $deck['cards'] = $cardStmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($deck);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Deck not found."]);
            }
        } else {
            // Get all decks for user, optionally filtered by group
            $group_id = isset($_GET['group_id']) ? $_GET['group_id'] : null;

            if ($group_id !== null && $group_id !== '') {
                if ($group_id === 'null') {
                    $query = "SELECT * FROM decks WHERE user_id = ? AND group_id IS NULL ORDER BY title ASC";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$user_id]);
                } else {
                    $query = "SELECT * FROM decks WHERE user_id = ? AND group_id = ? ORDER BY title ASC";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$user_id, $group_id]);
                }
            } else {
                $query = "SELECT * FROM decks WHERE user_id = ? ORDER BY title ASC";
                $stmt = $db->prepare($query);
                $stmt->execute([$user_id]);
            }

            $decks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($decks);
        }
        break;

    case 'POST':
        $action = isset($_GET['action']) ? $_GET['action'] : '';

        if (!empty($data->title)) {
            $deck_id = null;
            $is_update = false;

            if ($action === 'import') {
                // Check if deck exists
                $checkQuery = "SELECT id FROM decks WHERE title = ? AND user_id = ?";
                $checkStmt = $db->prepare($checkQuery);
                $checkStmt->execute([$data->title, $user_id]);

                if ($checkStmt->rowCount() > 0) {
                    $existingDeck = $checkStmt->fetch(PDO::FETCH_ASSOC);
                    $deck_id = $existingDeck['id'];
                    $is_update = true;

                    // Delete existing cards
                    $delStmt = $db->prepare("DELETE FROM cards WHERE deck_id = ?");
                    $delStmt->execute([$deck_id]);
                }
            }

            if (!$is_update) {
                $query = "INSERT INTO decks SET title = :title, user_id = :user_id, group_id = :group_id";
                $stmt = $db->prepare($query);

                $data->title = htmlspecialchars(strip_tags($data->title));
                $group_id = isset($data->group_id) ? ($data->group_id === 'null' || $data->group_id === '' ? null : $data->group_id) : null;

                $stmt->bindParam(":title", $data->title);
                $stmt->bindParam(":user_id", $user_id);
                $stmt->bindParam(":group_id", $group_id);

                if ($stmt->execute()) {
                    $deck_id = $db->lastInsertId();
                } else {
                    http_response_code(503);
                    echo json_encode(["message" => "Unable to create deck."]);
                    break;
                }
            }

            // If cards are provided, insert them
            if (isset($data->cards) && is_array($data->cards)) {
                $cardQuery = "INSERT INTO cards (deck_id, side1, side2, side3, side4) VALUES (:deck_id, :side1, :side2, :side3, :side4)";
                $cardStmt = $db->prepare($cardQuery);

                foreach ($data->cards as $card) {
                    $cardStmt->execute([
                        ':deck_id' => $deck_id,
                        ':side1' => $card->side1 ?? '',
                        ':side2' => $card->side2 ?? '',
                        ':side3' => $card->side3 ?? '',
                        ':side4' => $card->side4 ?? ''
                    ]);
                }
            }

            echo json_encode(["message" => $is_update ? "Deck imported (updated)." : "Deck created.", "id" => $deck_id]);

        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data."]);
        }
        break;

    case 'PUT':
        if (!empty($data->id) && !empty($data->title)) {
            // Verify ownership
            $checkQuery = "SELECT id FROM decks WHERE id = ? AND user_id = ?";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->execute([$data->id, $user_id]);

            if ($checkStmt->rowCount() > 0) {
                $query = "UPDATE decks SET title = :title, group_id = :group_id WHERE id = :id";
                $stmt = $db->prepare($query);

                $data->title = htmlspecialchars(strip_tags($data->title));
                $group_id = isset($data->group_id) ? ($data->group_id === 'null' || $data->group_id === '' ? null : $data->group_id) : null;

                $stmt->bindParam(":title", $data->title);
                $stmt->bindParam(":id", $data->id);
                $stmt->bindParam(":group_id", $group_id);

                if ($stmt->execute()) {
                    // Update cards logic could be complex (delete all and re-insert, or update individually)
                    // For simplicity, we'll delete all and re-insert if cards are provided
                    if (isset($data->cards) && is_array($data->cards)) {
                        $deleteCards = "DELETE FROM cards WHERE deck_id = ?";
                        $delStmt = $db->prepare($deleteCards);
                        $delStmt->execute([$data->id]);

                        $cardQuery = "INSERT INTO cards (deck_id, side1, side2, side3, side4) VALUES (:deck_id, :side1, :side2, :side3, :side4)";
                        $cardStmt = $db->prepare($cardQuery);

                        foreach ($data->cards as $card) {
                            $cardStmt->execute([
                                ':deck_id' => $data->id,
                                ':side1' => $card->side1 ?? '',
                                ':side2' => $card->side2 ?? '',
                                ':side3' => $card->side3 ?? '',
                                ':side4' => $card->side4 ?? ''
                            ]);
                        }
                    }
                    echo json_encode(["message" => "Deck updated."]);
                } else {
                    http_response_code(503);
                    echo json_encode(["message" => "Unable to update deck."]);
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
            $checkQuery = "SELECT id FROM decks WHERE id = ? AND user_id = ?";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->execute([$data->id, $user_id]);

            if ($checkStmt->rowCount() > 0) {
                $query = "DELETE FROM decks WHERE id = ?";
                $stmt = $db->prepare($query);

                if ($stmt->execute([$data->id])) {
                    echo json_encode(["message" => "Deck deleted."]);
                } else {
                    http_response_code(503);
                    echo json_encode(["message" => "Unable to delete deck."]);
                }
            } else {
                http_response_code(403);
                echo json_encode(["message" => "Access denied."]);
            }
        }
        break;
}
?>