<?php
require('../components/connect.php');
require('../lib/fpdf.php'); // Make sure to download and include the FPDF library
checkRole(['Super Admin', 'Reports Admin']);

class PDF extends FPDF
{
    // Header for the report
    function Header()
    {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'Sales and Inventory Report', 0, 1, 'C');
        $this->Ln(10);
    }

    // Footer for the report
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

// Initialize the PDF
$pdf = new PDF();
$pdf->AddPage();

// Fetch and display the most viewed items
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Most Viewed Products', 0, 1);
$pdf->SetFont('Arial', '', 10);

// Replace 'views' with the correct column name in your database
$select_most_viewed = $conn->prepare("SELECT product_name, views FROM products ORDER BY views DESC LIMIT 10");
$select_most_viewed->execute();
$most_viewed = $select_most_viewed->fetchAll(PDO::FETCH_ASSOC);

foreach ($most_viewed as $product) {
    $pdf->Cell(0, 8, $product['product_name'] . " - Views: " . $product['views'], 0, 1);
}

// Fetch and display the least purchased items
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Least Purchased Products', 0, 1);
$pdf->SetFont('Arial', '', 10);

$select_least_purchased = $conn->prepare("SELECT product_name, quantity_sold FROM products ORDER BY quantity_sold ASC LIMIT 10");
$select_least_purchased->execute();
$least_purchased = $select_least_purchased->fetchAll(PDO::FETCH_ASSOC);

foreach ($least_purchased as $product) {
    $pdf->Cell(0, 8, $product['product_name'] . " - Quantity Sold: " . $product['quantity_sold'], 0, 1);
}

// Fetch and display stock status masterlist
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Product Masterlist with Stock Status', 0, 1);
$pdf->SetFont('Arial', '', 10);

$select_stock_status = $conn->prepare("SELECT product_name, stock_quantity FROM products");
$select_stock_status->execute();
$stock_status = $select_stock_status->fetchAll(PDO::FETCH_ASSOC);

foreach ($stock_status as $product) {
    $pdf->Cell(0, 8, $product['product_name'] . " - Stock Quantity: " . $product['stock_quantity'], 0, 1);
}

// Output the PDF as a file
$pdf->Output('D', 'sales_inventory_report.pdf'); // 'D' forces download; change to 'I' to open in browser
?>
