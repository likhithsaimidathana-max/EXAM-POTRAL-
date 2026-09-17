<?php
session_start();
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

function send_json($success, $message, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => (bool) $success,
        'message' => $message,
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(false, 'Method not allowed.', 405);
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    send_json(false, 'Admin login required.', 403);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    send_json(false, 'Invalid JSON payload.', 400);
}

$exam_id = isset($data['exam_id']) ? intval($data['exam_id']) : 0;
$questions = isset($data['questions']) && is_array($data['questions']) ? $data['questions'] : [];
$userPasskeys = isset($data['userPasskeys']) && is_array($data['userPasskeys']) ? $data['userPasskeys'] : [];

if ($exam_id <= 0) {
    send_json(false, 'Missing or invalid exam_id.', 400);
}

if (empty($questions)) {
    send_json(false, 'No questions provided.', 400);
}

if (empty($userPasskeys)) {
    send_json(false, 'No users selected for passkeys.', 400);
}

$conn->begin_transaction();

try {
    $questionStmt = $conn->prepare(
        'INSERT INTO questions (exam_id, section_id, question, option1, option2, option3, option4, correctanswer) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );

    if (!$questionStmt) {
        throw new Exception('Failed to prepare question insert. ' . $conn->error);
    }

    foreach ($questions as $item) {
        $section_id = isset($item['section_id']) ? intval($item['section_id']) : 0;
        $question = isset($item['question']) ? trim($item['question']) : '';
        $option1 = isset($item['option1']) ? trim($item['option1']) : '';
        $option2 = isset($item['option2']) ? trim($item['option2']) : '';
        $option3 = isset($item['option3']) ? trim($item['option3']) : '';
        $option4 = isset($item['option4']) ? trim($item['option4']) : '';
        $correctanswer = isset($item['correctanswer']) ? intval($item['correctanswer']) : 0;

        if ($section_id <= 0 || $question === '' || $option1 === '' || $option2 === '' || $option3 === '' || $option4 === '' || $correctanswer < 1 || $correctanswer > 4) {
            throw new Exception('One or more questions are missing required values.');
        }

        $questionStmt->bind_param('iisssssi', $exam_id, $section_id, $question, $option1, $option2, $option3, $option4, $correctanswer);
        if (!$questionStmt->execute()) {
            throw new Exception('Question insert failed: ' . $questionStmt->error);
        }
    }

    $questionStmt->close();

    $passkeyStmt = $conn->prepare(
        'SELECT passkey_id FROM passkey WHERE registration_id = ? AND exam_id = ? LIMIT 1'
    );
    if (!$passkeyStmt) {
        throw new Exception('Failed to prepare passkey lookup. ' . $conn->error);
    }

    $insertStmt = $conn->prepare(
        'INSERT INTO passkey (registration_id, exam_id, passkey_n, is_used) VALUES (?, ?, ?, 0)'
    );
    if (!$insertStmt) {
        throw new Exception('Failed to prepare passkey insert. ' . $conn->error);
    }

    $updateStmt = $conn->prepare(
        'UPDATE passkey SET passkey_n = ?, is_used = 0 WHERE passkey_id = ?'
    );
    if (!$updateStmt) {
        throw new Exception('Failed to prepare passkey update. ' . $conn->error);
    }

    foreach ($userPasskeys as $entry) {
        $registration_id = isset($entry['registration_id']) ? intval($entry['registration_id']) : 0;
        $passkey_n = isset($entry['passkey_n']) ? trim($entry['passkey_n']) : '';

        if ($registration_id <= 0 || $passkey_n === '') {
            throw new Exception('One or more selected users are missing passkeys.');
        }

        $passkeyStmt->bind_param('ii', $registration_id, $exam_id);
        $passkeyStmt->execute();
        $passkeyStmt->store_result();

        if ($passkeyStmt->num_rows > 0) {
            $passkeyStmt->bind_result($passkey_id);
            $passkeyStmt->fetch();
            $updateStmt->bind_param('si', $passkey_n, $passkey_id);
            if (!$updateStmt->execute()) {
                throw new Exception('Passkey update failed: ' . $updateStmt->error);
            }
        } else {
            $insertStmt->bind_param('iis', $registration_id, $exam_id, $passkey_n);
            if (!$insertStmt->execute()) {
                throw new Exception('Passkey insert failed: ' . $insertStmt->error);
            }
        }

        $passkeyStmt->free_result();
        $passkeyStmt->reset();
    }

    $passkeyStmt->close();
    $insertStmt->close();
    $updateStmt->close();

    $conn->commit();
    send_json(true, 'Questions and passkeys saved successfully.');
} catch (Exception $e) {
    $conn->rollback();
    send_json(false, $e->getMessage(), 400);
}
