<?php
require_once 'db.php';
require_once 'fpdf.php'; // Ensure fpdf.php is in the same directory

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access");
}

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, 'Income & Service Management Report', 0, 1, 'C');
        $this->SetFont('Arial', 'I', 9);
        $this->Cell(0, 5, 'Generated on: ' . date('Y-m-d H:i:s'), 0, 1, 'C');
        $this->Ln(5);

        // Table Header
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(59, 130, 246);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(30, 8, 'Date', 1, 0, 'C', true);
        $this->Cell(60, 8, 'Title', 1, 0, 'L', true);
        $this->Cell(45, 8, 'Category', 1, 0, 'L', true);
        $this->Cell(35, 8, 'Amount (INR)', 1, 0, 'R', true);
        $this->Cell(20, 8, 'User', 1, 1, 'C', true);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// Fetch income entries
$stmt = $pdo->query("SELECT e.entry_date, e.title, e.category, e.amount, u.username 
                     FROM entries e 
                     LEFT JOIN users u ON e.user_id = u.id 
                     ORDER BY e.entry_date DESC");
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(0, 0, 0);

$totalAmount = 0;

foreach ($entries as $row) {
    $pdf->Cell(30, 7, $row['entry_date'], 1, 0, 'C');
    $pdf->Cell(60, 7, substr($row['title'], 0, 30), 1, 0, 'L');
    $pdf->Cell(45, 7, $row['category'], 1, 0, 'L');
    $pdf->Cell(35, 7, 'Rs. ' . number_format($row['amount'], 2), 1, 0, 'R');
    $pdf->Cell(20, 7, $row['username'], 1, 1, 'C');
    $totalAmount += $row['amount'];
}

// Summary Row
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(135, 8, 'Total Income', 1, 0, 'R');
$pdf->Cell(35, 8, 'Rs. ' . number_format($totalAmount, 2), 1, 0, 'R');
$pdf->Cell(20, 8, '', 1, 1, 'C');

$pdf->Output('I', 'Income_Report.pdf');
