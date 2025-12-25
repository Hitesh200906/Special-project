<?php
// view_statistics.php - View detailed statistics

// Database configuration
$servername = "localhost";
$username = "root"; // Change this to your database username
$password = ""; // Change this to your database password
$dbname = "manvi_proposal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get response summary by date
$date_sql = "SELECT 
                DATE(created_at) as response_date,
                response,
                COUNT(*) as count
             FROM proposal_responses 
             GROUP BY DATE(created_at), response
             ORDER BY response_date DESC";
$date_result = $conn->query($date_sql);

// Get hourly distribution
$hour_sql = "SELECT 
                HOUR(created_at) as hour,
                response,
                COUNT(*) as count
             FROM proposal_responses 
             GROUP BY HOUR(created_at), response
             ORDER BY hour";
$hour_result = $conn->query($hour_sql);

// Get browser/device info
$device_sql = "SELECT 
                CASE 
                    WHEN user_agent LIKE '%Mobile%' THEN 'Mobile'
                    WHEN user_agent LIKE '%Tablet%' THEN 'Tablet'
                    ELSE 'Desktop'
                END as device_type,
                COUNT(*) as count
             FROM proposal_responses 
             GROUP BY device_type";
$device_result = $conn->query($device_sql);

// Get total statistics
$stats_sql = "SELECT 
                (SELECT COUNT(*) FROM proposal_responses) as total_responses,
                (SELECT COUNT(DISTINCT ip_address) FROM proposal_responses) as unique_visitors,
                (SELECT COUNT(*) FROM proposal_responses WHERE response = 'yes') as total_yes,
                (SELECT COUNT(*) FROM proposal_responses WHERE response = 'no') as total_no";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposal Statistics - Manvi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f0815 0%, #1a0b23 100%);
            color: #f8e8f8;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        header {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px;
            background: rgba(138, 27, 97, 0.15);
            border-radius: 15px;
            border: 1px solid rgba(184, 50, 128, 0.2);
        }
        
        h1 {
            font-family: 'Dancing Script', cursive;
            font-size: 3rem;
            color: #f687b3;
            margin-bottom: 10px;
        }
        
        .subtitle {
            font-size: 1.1rem;
            color: #fbb6ce;
            margin-bottom: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: rgba(30, 15, 45, 0.9);
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(184, 50, 128, 0.15);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            color: #f687b3;
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 2.8rem;
            font-weight: bold;
            color: #f687b3;
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 1.1rem;
            color: #fbb6ce;
        }
        
        .chart-container {
            background: rgba(30, 15, 45, 0.9);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(184, 50, 128, 0.15);
        }
        
        .chart-title {
            font-size: 1.5rem;
            color: #fbb6ce;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .data-table th {
            background: rgba(184, 50, 128, 0.2);
            color: #fbb6ce;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid rgba(184, 50, 128, 0.1);
        }
        
        .data-table tr:hover {
            background: rgba(184, 50, 128, 0.05);
        }
        
        .yes-badge {
            background: rgba(246, 135, 179, 0.1);
            color: #f687b3;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.9rem;
        }
        
        .no-badge {
            background: rgba(138, 27, 97, 0.1);
            color: #8a1b61;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.9rem;
        }
        
        .percentage {
            font-size: 0.9rem;
            color: #fbb6ce;
            margin-left: 10px;
        }
        
        .progress-bar {
            height: 10px;
            background: rgba(184, 50, 128, 0.1);
            border-radius: 5px;
            margin-top: 5px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            border-radius: 5px;
        }
        
        .yes-progress {
            background: linear-gradient(90deg, #f687b3, #d53f8c);
        }
        
        .no-progress {
            background: linear-gradient(90deg, #8a1b61, #5a1040);
        }
        
        footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            color: #f687b3;
            font-size: 0.9rem;
            border-top: 1px solid rgba(184, 50, 128, 0.1);
        }
        
        .navigation {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }
        
        .nav-btn {
            background: rgba(184, 50, 128, 0.2);
            color: #fbb6ce;
            border: 1px solid rgba(184, 50, 128, 0.3);
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .nav-btn:hover {
            background: rgba(184, 50, 128, 0.3);
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            h1 {
                font-size: 2.2rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .stat-card {
                padding: 20px;
            }
            
            .chart-container {
                padding: 15px;
            }
            
            .data-table th,
            .data-table td {
                padding: 10px;
                font-size: 0.9rem;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <header>
            <h1>Proposal Statistics</h1>
            <p class="subtitle">Detailed analytics for Manvi's proposal responses</p>
        </header>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-number"><?php echo $stats['total_responses']; ?></div>
                <div class="stat-label">Total Responses</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number"><?php echo $stats['total_yes']; ?></div>
                <div class="stat-label">Yes Responses</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-number"><?php echo $stats['total_no']; ?></div>
                <div class="stat-label">No Responses</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?php echo $stats['unique_visitors']; ?></div>
                <div class="stat-label">Unique Visitors</div>
            </div>
        </div>
        
        <!-- Response Rate Chart -->
        <div class="chart-container">
            <h2 class="chart-title">Response Rate</h2>
            <?php
            $total = $stats['total_responses'];
            $yes_percentage = $total > 0 ? ($stats['total_yes'] / $total) * 100 : 0;
            $no_percentage = $total > 0 ? ($stats['total_no'] / $total) * 100 : 0;
            ?>
            <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                    <span>Yes: <?php echo $stats['total_yes']; ?> 
                        <span class="percentage">(<?php echo round($yes_percentage, 1); ?>%)</span>
                    </span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill yes-progress" style="width: <?php echo $yes_percentage; ?>%"></div>
                </div>
            </div>
            
            <div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                    <span>No: <?php echo $stats['total_no']; ?> 
                        <span class="percentage">(<?php echo round($no_percentage, 1); ?>%)</span>
                    </span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill no-progress" style="width: <?php echo $no_percentage; ?>%"></div>
                </div>
            </div>
        </div>
        
        <!-- Responses by Date -->
        <div class="chart-container">
            <h2 class="chart-title">Responses by Date</h2>
            <?php if ($date_result->num_rows > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Response</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $date_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($row['response_date'])); ?></td>
                                <td>
                                    <span class="<?php echo $row['response'] == 'yes' ? 'yes-badge' : 'no-badge'; ?>">
                                        <?php echo strtoupper($row['response']); ?>
                                    </span>
                                </td>
                                <td><?php echo $row['count']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: #fbb6ce; padding: 20px;">No date data available</p>
            <?php endif; ?>
        </div>
        
        <!-- Device Distribution -->
        <div class="chart-container">
            <h2 class="chart-title">Device Usage</h2>
            <?php if ($device_result->num_rows > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Device Type</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $device_data = [];
                        $device_total = 0;
                        while($row = $device_result->fetch_assoc()) {
                            $device_data[] = $row;
                            $device_total += $row['count'];
                        }
                        
                        foreach($device_data as $device):
                            $percentage = $device_total > 0 ? ($device['count'] / $device_total) * 100 : 0;
                        ?>
                            <tr>
                                <td><?php echo $device['device_type']; ?></td>
                                <td><?php echo $device['count']; ?></td>
                                <td><?php echo round($percentage, 1); ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: #fbb6ce; padding: 20px;">No device data available</p>
            <?php endif; ?>
        </div>
        
        <div class="navigation">
            <a href="view_responses.php" class="nav-btn">
                <i class="fas fa-list"></i> View All Responses
            </a>
            <a href="index.html" class="nav-btn">
                <i class="fas fa-heart"></i> Back to Proposal
            </a>
        </div>
        
        <footer>
            <p>Statistics generated on <?php echo date('F j, Y, g:i a'); ?></p>
            <p style="margin-top: 10px; font-size: 0.8rem; color: #d53f8c;">
                Private statistics dashboard for Hitesh
            </p>
        </footer>
    </div>
</body>
</html>
<?php
$conn->close();
?>