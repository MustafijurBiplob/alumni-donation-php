<?php
    require_once 'includes/user_auth.php'; // Ensures user is logged in
    require_once 'config.php';
    require_once 'includes/functions.php';
    // require('vendor/fpdf/fpdf.php'); // Assuming FPDF is installed via Composer or manually placed

    $user_id = $_SESSION['user_id'];
    $donation_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($donation_id <= 0) {
        set_message("Invalid donation ID.", "danger");
        redirect('donation_history.php');
    }

    // Fetch donation details, ensuring it belongs to the logged-in user and is approved
    $stmt = $conn->prepare("SELECT d.id, d.amount, d.transaction_id, d.transfer_date, d.created_at, u.name as user_name, u.email as user_email
                           FROM donations d
                           JOIN users u ON d.user_id = u.id
                           WHERE d.id = ? AND d.user_id = ? AND d.status = 'Approved'");
    $stmt->bind_param("ii", $donation_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $donation = $result->fetch_assoc();
    $stmt->close();

    if (!$donation) {
        set_message("Donation not found, not approved, or you do not have permission to view this receipt.", "danger");
        redirect('donation_history.php');
    }

    // --- Placeholder for PDF Generation ---
    // In a real application, you would use a library like FPDF or TCPDF here.
    // For now, we'll output a simple HTML representation or a message.

    header('Content-Type: text/html'); // Change to application/pdf when using a PDF library
    // header('Content-Disposition: attachment; filename="receipt_'.$donation_id.'.pdf"'); // Uncomment for PDF download

    echo "<html><head><title>Donation Receipt</title>";
    echo "<style> body { font-family: sans-serif; padding: 20px; } h1 { color: #333; } table { width: 100%; border-collapse: collapse; margin-top: 20px; } th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } th { background-color: #f2f2f2; } </style>";
    echo "</head><body>";
    echo "<h1>Donation Receipt</h1>";
    echo "<p><strong>Receipt ID:</strong> DON-" . str_pad($donation['id'], 6, '0', STR_PAD_LEFT) . "</p>";
    echo "<p><strong>Date Issued:</strong> " . date("Y-m-d H:i:s") . "</p>";
    echo "<hr>";
    echo "<h2>Donor Information</h2>";
    echo "<p><strong>Name:</strong> " . htmlspecialchars($donation['user_name']) . "</p>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($donation['user_email']) . "</p>";
    echo "<h2>Donation Details</h2>";
    echo "<table>";
    echo "<tr><th>Donation ID</th><td>" . $donation['id'] . "</td></tr>";
    echo "<tr><th>Amount</th><td>$" . number_format($donation['amount'], 2) . "</td></tr>";
    echo "<tr><th>Transaction ID</th><td>" . htmlspecialchars($donation['transaction_id']) . "</td></tr>";
    echo "<tr><th>Transfer Date</th><td>" . htmlspecialchars($donation['transfer_date']) . "</td></tr>";
    echo "<tr><th>Approval Date</th><td>" . date("Y-m-d", strtotime($donation['created_at'])) . " (Approx. system record date)</td></tr>"; // Note: Using created_at as proxy for approval date here. Add an 'approved_at' field for accuracy.
    echo "</table>";
    echo "<hr>";
    echo "<p>Thank you for your generous contribution to the Alumni Association!</p>";
    echo "<p style='font-size: 0.8em; color: grey;'>This is a system-generated receipt. For official tax purposes, please consult relevant documentation.</p>";
    echo "</body></html>";

    // --- FPDF Example Snippet (Requires FPDF library) ---
    /*
    require('vendor/fpdf/fpdf.php'); // Adjust path if needed

    class PDF extends FPDF {
        function Header() {
            $this->SetFont('Arial','B',15);
            $this->Cell(80);
            $this->Cell(30,10,'Donation Receipt',0,0,'C');
            $this->Ln(20);
        }
        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial','I',8);
            $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
            $this->Ln();
             $this->Cell(0,10,'Thank you for your donation!',0,0,'C');
        }
    }

    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('Arial','',12);

    $pdf->Cell(0,10,'Receipt ID: DON-' . str_pad($donation['id'], 6, '0', STR_PAD_LEFT),0,1);
    $pdf->Cell(0,10,'Date Issued: ' . date("Y-m-d H:i:s"),0,1);
    $pdf->Ln(10);

    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,10,'Donor Information',0,1);
    $pdf->SetFont('Arial','',12);
    $pdf->Cell(0,10,'Name: ' . $donation['user_name'],0,1);
    $pdf->Cell(0,10,'Email: ' . $donation['user_email'],0,1);
    $pdf->Ln(10);

    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,10,'Donation Details',0,1);
    $pdf->SetFont('Arial','',12);
    $pdf->Cell(50,10,'Donation ID:',1); $pdf->Cell(0,10,$donation['id'],1,1);
    $pdf->Cell(50,10,'Amount:',1); $pdf->Cell(0,10,'$' . number_format($donation['amount'], 2),1,1);
    $pdf->Cell(50,10,'Transaction ID:',1); $pdf->Cell(0,10,$donation['transaction_id'],1,1);
    $pdf->Cell(50,10,'Transfer Date:',1); $pdf->Cell(0,10,$donation['transfer_date'],1,1);
    // Add Approval Date if available
    $pdf->Ln(10);

    $pdf->Output('D', 'receipt_'.$donation_id.'.pdf'); // D forces download
    exit;
    */

    exit; // Important to prevent any further output
    ?>
