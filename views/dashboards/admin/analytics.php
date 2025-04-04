<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Analytics Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Event Analytics Dashboard</h2>

        <div class="row">
            <!-- Quick Stats Cards -->
            <div class="col-md-3">
                <div class="card text-center mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Events</h5>
                        <p class="card-text display-6" id="totalEventsCount">45</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Average Attendance</h5>
                        <p class="card-text display-6" id="averageAttendance">68%</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Most Active Category</h5>
                        <p class="card-text display-6" id="mostActiveCategory">Academic</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Registrations</h5>
                        <p class="card-text display-6" id="totalRegistrations">1,245</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Event Attendance by Category Chart -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Event Attendance by Category</h5>
                        <canvas id="attendanceByCategoryChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Monthly Event Trends Chart -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Monthly Event Trends</h5>
                        <canvas id="monthlyEventTrendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Attendance by Category Chart
        const attendanceCategoryCtx = document.getElementById('attendanceByCategoryChart').getContext('2d');
        new Chart(attendanceCategoryCtx, {
            type: 'pie',
            data: {
                labels: ['Academic', 'Social', 'Career', 'Cultural'],
                datasets: [{
                    data: [45, 30, 15, 10],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(255, 206, 86, 0.6)'
                    ]
                }]
            }
        });

        // Monthly Event Trends Chart
        const monthlyTrendsCtx = document.getElementById('monthlyEventTrendsChart').getContext('2d');
        new Chart(monthlyTrendsCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Number of Events',
                    data: [5, 7, 6, 9, 8, 10],
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.3
                }]
            }
        });
    </script>
</body>
</html>