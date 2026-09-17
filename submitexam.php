<?php
session_start();
header('Content-Type: application/json');
include 'config.php';

if (!isset($_SESSION['registration_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

$registration_id = $_SESSION['registration_id'];
$exam_id = isset($_POST['exam_id']) ? intval($_POST['exam_id']) : 0;

if ($exam_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid exam ID.']);
    exit();
}

$score = 0;
$total = 0;
$debug_data = [];

// Optional: Clear existing answers
$clear_existing = $conn->prepare("DELETE FROM user_answers WHERE registration_id = ? AND exam_id = ?");
$clear_existing->bind_param("ii", $registration_id, $exam_id);
$clear_existing->execute();

// Process each answer
foreach ($_POST as $key => $value) {
    if (strpos($key, 'answer_') === 0) {
        $index = substr($key, 7);
        $question_id = isset($_POST["question_id$index"]) ? intval($_POST["question_id$index"]) : 0;
        if ($question_id <= 0) continue;

        // Get the correct answer from DB
        $stmt = $conn->prepare("SELECT correctanswer FROM questions WHERE question_id = ? AND exam_id = ?");
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
            exit();
        }

        $stmt->bind_param("ii", $question_id, $exam_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $correct_raw = $row['correctanswer'];
            $selected_raw = $value;

            // Compare as string to avoid type mismatch
           $correct = strval($correct_raw);
           $selected = preg_replace('/[^0-9]+/', '', $selected_raw);
            // $selected = strval($selected_raw);
            $is_correct = ($correct === $selected) ? 1 : 0;
            // echo json_encode(['status' => 'error', 'message' => 'Insert prepare failed: ' . $correct]);
            // exit();
            // Debug log
            error_log("QID: $question_id | Selected: $selected_raw | Correct: $correct_raw | Result: $is_correct");

            $debug_data[] = [
                'question_id' => $question_id,
                'correct_raw' => $correct_raw,
                'selected_raw' => $selected_raw,
                'is_correct' => $is_correct
            ];

            // Insert answer
            $insert = $conn->prepare("INSERT INTO user_answers (registration_id, exam_id, question_id, selected_answer, correct_answer, is_correct)
                                      VALUES (?, ?, ?, ?, ?, ?)");
            if (!$insert) {
                echo json_encode(['status' => 'error', 'message' => 'Insert prepare failed: ' . $conn->error]);
                exit();
            }

            $insert->bind_param("iiissi", $registration_id, $exam_id, $question_id, $selected_raw, $correct_raw, $is_correct);
            if (!$insert->execute()) {
                echo json_encode(['status' => 'error', 'message' => 'Insert execute failed: ' . $insert->error]);
                exit();
            }

            if ($is_correct) $score++;
            $total++;
        }
    }
}

// Check if score already exists
$check = $conn->prepare("SELECT 1 FROM scores WHERE registration_id = ? AND exam_id = ?");
$check->bind_param("ii", $registration_id, $exam_id);
$check->execute();
$check->store_result();

if ($check->num_rows == 0) {
    $submitted_at = date("Y-m-d H:i:s");
    $store = $conn->prepare("INSERT INTO scores (registration_id, exam_id, score, total_questions, submitted_at)
                             VALUES (?, ?, ?, ?, ?)");
    $store->bind_param("iiiis", $registration_id, $exam_id, $score, $total, $submitted_at);
    if (!$store->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'Score insert failed: ' . $store->error]);
        exit();
    }
}

// Return result
echo json_encode([
    'status' => 'success',
    'score' => $score,
    'total_questions' => $total,
    'debug' => $debug_data
]);
?>
