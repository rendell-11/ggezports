<?php
require('fpdf.php'); // Include the FPDF library

// Create a new PDF instance
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Add title to the PDF
$pdf->Cell(40, 10, 'Products Report');
$pdf->Ln(10); // Line break

// Sample data (replace with your data fetching logic)
$data = [
    ['Product 1', 'Category 1', 'Price 1'],
    ['Product 2', 'Category 2', 'Price 2'],
];

// Adding table headers
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, 'Product Name', 1);
$pdf->Cell(50, 10, 'Category', 1);
$pdf->Cell(50, 10, 'Price', 1);
$pdf->Ln();

// Adding table rows
$pdf->SetFont('Arial', '', 12);
foreach ($data as $row) {
    foreach ($row as $col) {
        $pdf->Cell(50, 10, $col, 1);
    }
    $pdf->Ln();
}

// Get the chart images from the form submission
$chartImages = [];
for ($i = 0; $i < 3; $i++) {
    if (isset($_POST['chart_' . $i])) {
        $chartImages[] = $_POST['chart_' . $i]; // Base64 image string
    }
}

// Add the charts as images in the PDF
foreach ($chartImages as $imageData) {
    // Decode the base64 string and save it as an image file temporarily
    $imgData = base64_decode(preg_replace('/^data:image\/(png|jpeg|jpg|gif);base64,/', '', $imageData));

    // Create a unique file path for the image
    $imgPath = 'chart_' . uniqid() . '.png';
    file_put_contents($imgPath, $imgData);

    // Add the image to the PDF (resize if necessary)
    $pdf->Image($imgPath, 10, $pdf->GetY(), 180); // Adjust positioning and size as needed

    // Add space between charts
    $pdf->Ln(100);

    // Clean up the temporary image file
    unlink($imgPath);
}

// Output the PDF
$pdf->Output('D', 'products_report.pdf'); // Forces download with the name 'products_report.pdf'
?>


<script>
    // Capture the images of the graphs before submitting the form
    document.querySelector('.styled-button').addEventListener('click', function (event) {
        event.preventDefault(); // Prevent form submission for now

        // Ensure that charts are fully rendered before capturing
        var chartImages = [];
        var chartIds = ['myChart', 'orderChart', 'profitChart'];

        var allChartsCaptured = true; // Flag to ensure all charts are captured
        chartIds.forEach(function (chartId, index) {
            var chartElement = document.getElementById(chartId);

            if (chartElement) {
                // Wait for the chart to fully load and capture its image
                chartImages.push(chartElement.toDataURL());
            } else {
                allChartsCaptured = false; // If any chart is missing, set flag to false
                chartImages.push(null); // Add null for missing charts
            }
        });

        // If all charts are captured successfully, submit the form
        if (allChartsCaptured) {
            // Store the chart images in hidden form fields
            var form = document.querySelector('form');
            chartImages.forEach(function (image, index) {
                if (image) {  // Only add input if image is captured
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'chart_' + index;
                    input.value = image;
                    form.appendChild(input);
                }
            });

            // Now submit the form
            form.submit();
        } else {
            alert("Some charts are missing or not rendered properly.");
        }
    });
</script>

