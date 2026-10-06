<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courier Dashboard - Reports</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="courier1.css">
    <script src="darkmode.js"></script>

</head>
<body>

    <!-- Sidebar Section -->
    <div class="sidebar">
        <h2>Courier Dashboard</h2>
        <ul>
            <li><a href="courier.php">Profile</a></li>
            <li><a href="#">My Deliveries</a></li>
            <li><a href="#">Notifications</a></li>
            <li class="active"><a href="reports.php">Reports</a></li>
            <li><a href="#">Settings</a></li>
        </ul>
        
        <!-- Toggle Switch for Dark Mode -->
        <div class="toggle-container">
            <label class="toggle">
                <input type="checkbox" id="mode-toggle" onchange="toggleMode()">
                <span class="slider"></span>
            </label>
            <span class="ms-2"> Dark Mode</span>
        </div>
    </div>

    <!-- Main Content Section -->
    <div class="main-content">
        <div class="reports-container">
            <div class="reports-card">
                <div class="reports-header">
                    <h1>Delivery Reports</h1>
                </div>

                <!-- Reports Filters -->
                <div class="filters">
                    <form>
                        <div class="row">
                            <div class="col-md-4">
                                <label for="start-date">Start Date:</label>
                                <input type="date" id="start-date" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label for="end-date">End Date:</label>
                                <input type="date" id="end-date" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label for="status">Status:</label>
                                <select id="status" class="form-control">
                                    <option value="">All</option>
                                    <option value="Delivered">Delivered</option>
                                    <option value="Pending">Pending</option>
                                    <option value="In Progress">In Progress</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12 text-end">
                                <button type="submit" class="btn btn-primary">Generate Report</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Reports Table -->
                <div class="reports-table mt-4">
                    <h2 class="section-title">Report Summary</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tracking Number</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Recipient</th>
                                <th>Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>123456789</td>
                                <td>2024-09-29</td>
                                <td>Delivered</td>
                                <td>Jane Smith</td>
                                <td>New York</td>
                            </tr>
                            <tr>
                                <td>987654321</td>
                                <td>2024-09-28</td>
                                <td>Delivered</td>
                                <td>Mark Wilson</td>
                                <td>Los Angeles</td>
                            </tr>
                            <tr>
                                <td>112233445</td>
                                <td>2024-09-27</td>
                                <td>Pending</td>
                                <td>Emily Johnson</td>
                                <td>Chicago</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Export Options -->
                <div class="export-options mt-4">
                    <button class="btn btn-success">Download as CSV</button>
                    <button class="btn btn-warning">Download as PDF</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>




</body>
</html>
