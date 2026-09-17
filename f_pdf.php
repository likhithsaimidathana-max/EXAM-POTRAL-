<?php
session_start();
if (!isset($_SESSION['registration_id'])) {
    header("Location: login.php");
    exit();
}

require('fpdf.php'); 
include 'config.php';

$registration_id = $_SESSION['registration_id'];


$sql = "SELECT s.*, e.examtitle 
        FROM scores s 
        JOIN exam e ON s.exam_id = e.exam_id 
        WHERE s.registration_id = ? 
        ORDER BY s.submitted_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $registration_id);
$stmt->execute();
$result = $stmt->get_result();

$scores = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $scores[] = $row;
    }
}

// Create PDF
$pdf = new FPDF();
$fullname = isset($_SESSION["fullname"]) ? $_SESSION["fullname"] : 'Student';
$registration_id = isset($_SESSION["registration_id"]) ? $_SESSION["registration_id"] : 'registration';

if (empty($scores)) {
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 14);
    $pdf->Cell(0, 10, 'Student Name: ' . $fullname, 0, 1, 'L');
    $pdf->Cell(0, 10, 'Registration Id: ' . $registration_id, 0, 1, 'L');
    $pdf->Ln(10);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'You haven\'t taken any exams yet.', 1, 1, 'C');
} else {
    foreach ($scores as $index => $score) {
        $pdf->AddPage();

        // Student Info
        $pdf->SetFont('Arial', '', 14);
        $pdf->Cell(0, 10, 'Student Name: ' . $fullname, 0, 1, 'L');
        $pdf->Cell(0, 10, 'Registration Id: ' . $registration_id, 0, 1, 'L');

        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'Past Exam Score', 0, 1, 'C');
        $pdf->Ln(5);
// Table Header (Reordered)
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(10, 10, 'S', 1, 0, 'C', true);
$pdf->Cell(55, 10, 'Exam Date', 1, 0, 'C', true);
$pdf->Cell(60, 10, 'Exam Title', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Total Mraks', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Result', 1, 1, 'C', true);

// Exam Data Row (Reordered)
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(10, 10, '1', 1);
$submitted_at = date("d M Y", strtotime($score['submitted_at']));
$pdf->Cell(55, 10, $submitted_at, 1);
$pdf->Cell(60, 10, $score['examtitle'], 1);
$pdf->Cell(40, 10, $score['total_questions'], 1);
$pdf->Cell(25, 10, $score['score'], 1, 1);

    }
}

// Output PDF
$pdf->Output('I', 'Past_Exam_Scores.pdf');
?>