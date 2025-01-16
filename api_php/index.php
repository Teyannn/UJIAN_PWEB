<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, PUT, DELETE, GET');
header('Access-Control-Allow-Headers: Content-Type');

include_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        createCharacter();
        break;

    case 'PUT':
        updateCharacter();
        break;

    case 'DELETE':
        deleteCharacter();
        break;

    case 'GET':
        getCharacter();
        break;

    default:
        echo json_encode(['message' => 'Invalid Request']);
        break;
}

// Create a new Character
function createCharacter() {
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data->nama) && isset($data->level) && isset($data->health) && isset($data->mana)) {
        $conn = getConnection();
        $stmt = $conn->prepare("INSERT INTO karakter (nama, level, health, mana) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('siii', $data->nama, $data->level, $data->health, $data->mana);

        if ($stmt->execute()) {
            echo json_encode(['message' => 'Character Created']);
        } else {
            echo json_encode(['message' => 'Character Not Created']);
        }

        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(['message' => 'Incomplete Data']);
    }
}

// Update a Character
function updateCharacter() {
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data->id) && (!empty($data->nama) || isset($data->level) || isset($data->health) || isset($data->mana))) {
        $conn = getConnection();
        $updateFields = [];
        $params = [];
        $types = '';

        if (!empty($data->nama)) {
            $updateFields[] = "nama = ?";
            $params[] = $data->nama;
            $types .= 's';
        }
        if (isset($data->level)) {
            $updateFields[] = "level = ?";
            $params[] = $data->level;
            $types .= 'i';
        }
        if (isset($data->health)) {
            $updateFields[] = "health = ?";
            $params[] = $data->health;
            $types .= 'i';
        }
        if (isset($data->mana)) {
            $updateFields[] = "mana = ?";
            $params[] = $data->mana;
            $types .= 'i';
        }

        $params[] = $data->id;
        $types .= 'i';

        $updateQuery = implode(", ", $updateFields);
        $stmt = $conn->prepare("UPDATE karakter SET $updateQuery WHERE id = ?");
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            echo json_encode(['message' => 'Character Updated']);
        } else {
            echo json_encode(['message' => 'Character Not Updated']);
        }

        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(['message' => 'Invalid or Incomplete Data']);
    }
}

// Delete a Character
function deleteCharacter() {
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data->id)) {
        $conn = getConnection();
        $stmt = $conn->prepare("DELETE FROM karakter WHERE id = ?");
        $stmt->bind_param('i', $data->id);

        if ($stmt->execute()) {
            echo json_encode(['message' => 'Character Deleted']);
        } else {
            echo json_encode(['message' => 'Character Not Deleted']);
        }

        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(['message' => 'Invalid ID']);
    }
}

// Get Characters (Retrieve all or one by ID)
function getCharacter() {
    $conn = getConnection();

    // Check if character ID is provided in the query string
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM karakter WHERE id = ?");
        $stmt->bind_param('i', $id);
    } else {
        $stmt = $conn->prepare("SELECT * FROM karakter");
    }

    if ($stmt) {  
        $stmt->execute();
        $result = $stmt->get_result();

        // If there are results, fetch them and return as JSON
        if ($result->num_rows > 0) {
            $characters = [];
            while ($row = $result->fetch_assoc()) {
                $characters[] = $row;
            }
            echo json_encode($characters);
        } else {
            echo json_encode(['message' => 'No Characters Found']);
        }

        $stmt->close();
    } else {
        echo json_encode(['message' => 'Invalid Query']);
    }

    $conn->close();
}
