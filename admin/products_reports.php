<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
}

// Fetch sales data from the orders table
$salesData = [];
$select_sales = $conn->prepare("SELECT DATE(placed_on) AS order_date, SUM(total_price) AS total_sales FROM `orders` GROUP BY DATE(placed_on) ORDER BY DATE(placed_on) DESC LIMIT 30");
$select_sales->execute();
$sales = $select_sales->fetchAll(PDO::FETCH_ASSOC);

foreach ($sales as $sale) {
    $salesData[$sale['order_date']] = (float)$sale['total_sales'];
}

// Prepare data for the line chart
$xValues = array_keys($salesData);
$yValues = array_values($salesData);

// Fetch order status counts
$orderCounts = [];
$select_order_counts = $conn->prepare("SELECT payment_status, COUNT(*) as count FROM `orders` GROUP BY payment_status");
$select_order_counts->execute();
$order_statuses = $select_order_counts->fetchAll(PDO::FETCH_ASSOC);

foreach ($order_statuses as $status) {
    $orderCounts[$status['payment_status']] = (int)$status['count'];
}

// Prepare data for the pie chart
$pendingOrders = isset($orderCounts['pending']) ? $orderCounts['pending'] : 0;
$completedOrders = isset($orderCounts['completed']) ? $orderCounts['completed'] : 0;
$totalOrders = $pendingOrders + $completedOrders;

// Fetch total profits from completed orders grouped by month
$profitsData = [];
$select_profits = $conn->prepare("
    SELECT DATE_FORMAT(placed_on, '%Y-%m') AS order_month, SUM(total_price) AS total_profits 
    FROM `orders` 
    WHERE payment_status = 'completed' 
    GROUP BY order_month 
    ORDER BY order_month DESC
");
$select_profits->execute();
$profits = $select_profits->fetchAll(PDO::FETCH_ASSOC);

foreach ($profits as $profit) {
    $profitsData[$profit['order_month']] = (float)$profit['total_profits'];
}

// Prepare data for the bar chart
$profitMonths = array_keys($profitsData);
$profitValues = array_values($profitsData);


$salesEvents = [];
$select_calendar_sales = $conn->prepare("SELECT DATE(placed_on) AS order_date, SUM(total_price) AS total_sales FROM `orders` GROUP BY DATE(placed_on)");
$select_calendar_sales->execute();
$calendar_sales = $select_calendar_sales->fetchAll(PDO::FETCH_ASSOC);

foreach ($calendar_sales as $sale) {
    $salesEvents[] = [
        'title' => '₱' . number_format($sale['total_sales'], 2), 
        'start' => $sale['order_date'], 
    ];
}
?>

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Products Sold</title>

   <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.4/main.min.css" rel="stylesheet">
   <link rel="stylesheet" href="../css/adminstyle.css">

   <style>
      #myChart {
          background-color: #ffffff; /* White background for the chart */
          border: 1px solid #ddd; /* Light gray border */
          padding: 10px; /* Padding around the graph */
          border-radius: 10px; /* Rounded corner effect */
          display: block; /* Make the canvas a block element */
          margin: 0 auto; /* Center the canvas horizontally */
      }
      #orderChart {
          background-color: #ffffff; /* White background for the pie chart */
          border: 1px solid #ddd; /* Light gray border */
          padding: 10px; /* Padding around the graph */
          border-radius: 10px; /* Rounded corner effect */
          display: block; /* Make the canvas a block element */
          margin: 0 auto; /* Center the canvas horizontally */
          max-width: 600px; /* Set a maximum width for the pie chart */
      }
      #profitChart {
          background-color: #ffffff; /* White background for the bar chart */
          border: 1px solid #ddd; /* Light gray border */
          padding: 10px; /* Padding around the graph */
          border-radius: 10px; /* Rounded corner effect */
          display: block; /* Make the canvas a block element */
          margin: 0 auto; /* Center the canvas horizontally */
          max-width: 800px; /* Set a maximum width for the bar chart */
      }
    #calendar {
        max-width: 900px;
        margin: 40px auto;
        background-color: #ffffff; /* White background */
        padding: 15px; /* Add padding inside the calendar */
        border: 1px solid #ddd; /* Light gray border */
        border-radius: 10px; /* Rounded corners */
    }

    .fc .fc-toolbar { 
        background-color: #ffffff; /* Toolbar background */
    }
    
    .fc-daygrid-day { 
        background-color: #ffffff; /* Ensure individual day cells are white */
    }
    .center-form {
        display: flex;
        justify-content: center; /* Horizontal centering */
        align-items: center; /* Vertical centering (if needed) */
        height: 100vh; /* Optional: centers vertically relative to the viewport */
    }

    /* Style the button */
    .styled-button {
        display: flex;
        align-items: center;
        background-color: #ff0000; /* Bright red color */
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        text-decoration: none;
        transition: background-color 0.3s ease;
    }

    .styled-button:hover {
        background-color: #cc0000; /* Darker red on hover */
    }

    .styled-button img.button-icon {
        width: 20px; /* Adjust size as needed */
        height: 20px;
        margin-right: 10px; /* Space between icon and text */
    }


      .chart-container {
          display: flex;
          justify-content: center;
          align-items: center;
          height: auto; /* Adjust height based on content */
      }
      .heading {
          font-size: 24px; /* Increase heading size */
      }
   </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="accounts">
    <form method="post" action="download_pdf.php">
        <button type="submit" class="styled-button">
            <img src="download-icon.png" alt="Download Icon" class="button-icon"> Download as PDF
        </button>
    </form>
</section>

<section class="accounts">


   <h1 class="heading">Products Sold</h1>

   <canvas id="myChart" style="width:80%;max-width:800px;"></canvas>   

   <h2 class="heading" style="margin-top: 140px;">Sales Calendar</h2>
<div id="calendar"></div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth', // Month view
            events: <?= json_encode($salesEvents); ?>, // Load events from PHP
            headerToolbar: {
                left: 'prev,next today', // Controls for navigation
                center: 'title', // Title of the current view
                right: 'dayGridMonth,timeGridWeek,timeGridDay' // View switcher
            },
            eventColor: '#28a745', // Color of the events
            eventTextColor: '#ffffff' // Text color within events
        });

        calendar.render();
    });
</script>


   <script>
       // Line chart data
       var xValues = <?= json_encode($xValues); ?>; // X-axis labels (dates)
       var yValues = <?= json_encode($yValues); ?>; // Y-axis values (sales)

       new Chart("myChart", {
           type: "line",
           data: {
               labels: xValues,
               datasets: [{
                   backgroundColor: "rgba(255, 255, 255, 0.2)",
                   borderColor: "rgba(75, 192, 192, 1)", // Set a single color for the line
                   data: yValues,
                   borderWidth: 2,
                   fill: false
               }]
           },
           options: {
               legend: {display: false},
               title: {
                   display: true,
                   text: "Total Sales Over Time"
               },
               scales: {
                   yAxes: [{
                       ticks: {
                           fontColor: 'black'
                       }
                   }]
               }
           }
       });
   </script>

   <h2 class="heading" style="margin-top: 140px;">Order Status</h2>
   <canvas id="orderChart" style="width:80%;max-width:600px; "></canvas>

   <script>
       // Pie chart data
       var orderCounts = {
           labels: ["Pending Orders", "Completed Orders"],
           datasets: [{
               data: [<?= $pendingOrders; ?>, <?= $completedOrders; ?>],
               backgroundColor: ["#FF6384", "#36A2EB"], // Solid colors for pie chart
           }]
       };

       new Chart("orderChart", {
           type: "pie",
           data: orderCounts,
           options: {
               title: {
                   display: true,
                   text: "Order Status Overview",
                   fontSize: 20 // Increase font size for the title
               },
               legend: {
                   display: true,
                   position: 'top',
                   labels: {
                       fontSize: 16 // Increase font size for legend
                   }
               }
           }
       });
   </script>

   <h2 class="heading" style="margin-top: 140px;">Total Profits from Completed Orders</h2>
   <canvas id="profitChart" style="width:80%;max-width:700px; height: 800px;"></canvas>

   <script>
       // Bar chart data
       var profitMonths = <?= json_encode($profitMonths); ?>; // X-axis labels (months)
       var profitValues = <?= json_encode($profitValues); ?>; // Y-axis values (profits)

       new Chart("profitChart", {
           type: "bar",
           data: {
               labels: profitMonths,
               datasets: [{
                   label: 'Total Profits',
                   backgroundColor: "rgba(75, 192, 192, 0.5)", // Set a color for the bars
                   borderColor: "rgba(75, 192, 192, 1)", // Set a border color for the bars
                   data: profitValues,
                   borderWidth: 2
               }]
           },
           options: {
               title: {
                   display: true,
                   text: "Total Profits Over Time",
                   fontSize: 20 // Increase font size for the title
               },
               scales: {
                   yAxes: [{
                       ticks: {
                           beginAtZero: true,
                           fontColor: 'black' // Set font color for Y-axis
                       }
                   }],
                   xAxes: [{
                       ticks: {
                           fontColor: 'black' // Set font color for X-axis
                       }
                   }]
               },
               legend: {
                   display: true,
                   position: 'top',
                   labels: {
                       fontSize: 16 // Increase font size for legend
                   }
               }
           }
       });
   </script>

</section>

<script src="../js/admin_script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.4/main.min.js"></script>


</body>
</html>
