<?php
// view_responses.php - View all proposal responses

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

// Get all responses
$sql = "SELECT * FROM proposal_responses ORDER BY created_at DESC";
$result = $conn->query($sql);

// Get summary statistics
$summary_sql = "SELECT response, COUNT(*) as count FROM proposal_responses GROUP BY response";
$summary_result = $conn->query($summary_sql);

$summary = ['yes' => 0, 'no' => 0];
while($row = $summary_result->fetch_assoc()) {
    $summary[$row['response']] = $row['count'];
}

// Get total responses
$total_sql = "SELECT COUNT(*) as total FROM proposal_responses";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_responses = $total_row['total'];

// Get unique visitors
$unique_sql = "SELECT COUNT(DISTINCT ip_address) as unique_visitors FROM proposal_responses";
$unique_result = $conn->query($unique_sql);
$unique_row = $unique_result->fetch_assoc();
$unique_visitors = $unique_row['unique_visitors'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manvi's Proposal Responses</title>
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
            margin-bottom: 15px;
        }
        
        .subtitle {
            font-size: 1.1rem;
            color: #fbb6ce;
            margin-bottom: 25px;
        }
        
        .summary-cards {
            display: flex;
            justify-content: center;
            gap: 25px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }
        
        .summary-card {
            background: rgba(30, 15, 45, 0.9);
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            min-width: 180px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(184, 50, 128, 0.15);
        }
        
        .yes-card {
            border-top: 4px solid #f687b3;
        }
        
        .no-card {
            border-top: 4px solid #8a1b61;
        }
        
        .total-card {
            border-top: 4px solid #d53f8c;
        }
        
        .summary-card h3 {
            font-size: 1.2rem;
            margin-bottom: 12px;
            color: #fbb6ce;
        }
        
        .count {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .yes-count {
            color: #f687b3;
        }
        
        .no-count {
            color: #8a1b61;
        }
        
        .total-count {
            color: #d53f8c;
        }
        
        .responses-table-container {
            background: rgba(30, 15, 45, 0.9);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(184, 50, 128, 0.15);
            margin-bottom: 30px;
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }
        
        th {
            background: rgba(184, 50, 128, 0.2);
            color: #fbb6ce;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid rgba(184, 50, 128, 0.3);
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid rgba(184, 50, 128, 0.1);
        }
        
        tr:hover {
            background: rgba(184, 50, 128, 0.05);
        }
        
        .response-yes {
            color: #f687b3;
            font-weight: bold;
            background: rgba(246, 135, 179, 0.1);
            padding: 5px 12px;
            border-radius: 20px;
            display: inline-block;
        }
        
        .response-no {
            color: #8a1b61;
            font-weight: bold;
            background: rgba(138, 27, 97, 0.1);
            padding: 5px 12px;
            border-radius: 20px;
            display: inline-block;
        }
        
        .timestamp {
            color: #f687b3;
            font-size: 0.9rem;
        }
        
        .ip-address {
            font-family: monospace;
            font-size: 0.9rem;
            color: #fbb6ce;
        }
        
        footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            color: #f687b3;
            font-size: 0.9rem;
            border-top: 1px solid rgba(184, 50, 128, 0.1);
        }
        
        .stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 20px;
            color: #fbb6ce;
        }
        
        @media (max-width: 768px) {
            h1 {
                font-size: 2.2rem;
            }
            
            .summary-cards {
                flex-direction: column;
                align-items: center;
            }
            
            .summary-card {
                width: 100%;
                max-width: 300px;
            }
            
            .responses-table-container {
                padding: 15px;
            }
            
            th, td {
                padding: 10px;
                font-size: 0.9rem;
            }
            
            .count {
                font-size: 2.5rem;
            }
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #f687b3;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <header>
            <h1>Manvi's Proposal Responses</h1>
            <p class="subtitle">Tracking the most important answer of my life ❤️</p>
        </header>
        
        <div class="summary-cards">
            <div class="summary-card yes-card">
                <h3>Yes Responses</h3>
                <div class="count yes-count"><?php echo $summary['yes']; ?></div>
                <p>Beautiful "Yes" from Manvi</p>
            </div>
            
            <div class="summary-card no-card">
                <h3>No Responses</h3>
                <div class="count no-count"><?php echo $summary['no']; ?></div>
                <p>Manvi tried to say no</p>
            </div>
            
            <div class="summary-card total-card">
                <h3>Total Responses</h3>
                <div class="count total-count"><?php echo $total_responses; ?></div>
                <p>All responses recorded</p>
            </div>
        </div>
        
        <div class="responses-table-container">
            <h2 style="margin-bottom: 20px; color: #fbb6ce; text-align: center;">All Responses</h2>
            <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Response</th>
                            <th>Name</th>
                            <th>Date & Time</th>
                            <th>IP Address</th>
                            <th>Device Info</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row["id"]; ?></td>
                                <td>
                                    <span class="response-<?php echo $row["response"]; ?>">
                                        <?php echo strtoupper($row["response"]); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row["girlfriend_name"]); ?></td>
                                <td class="timestamp">
                                    <?php echo date('M d, Y h:i A', strtotime($row["created_at"])); ?>
                                </td>
                                <td class="ip-address"><?php echo $row["ip_address"]; ?></td>
                                <td title="<?php echo htmlspecialchars($row["user_agent"]); ?>">
                                    <i class="fas fa-mobile-alt"></i>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-heart"></i>
                    <h3>No responses yet</h3>
                    <p>Waiting for Manvi's beautiful answer... ❤️</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="stats">
            <div><i class="fas fa-users"></i> Unique Visitors: <?php echo $unique_visitors; ?></div>
            <div><i class="fas fa-history"></i> Last Updated: <?php echo date('h:i A'); ?></div>
        </div>
        
        <footer>
            <p>Made with ❤️ by Hitesh | Last updated: <?php echo date('F j, Y, g:i a'); ?></p>
            <p style="margin-top: 10px; font-size: 0.8rem; color: #d53f8c;">This page is private and only accessible to Hitesh</p>
        </footer>
    </div>
</body>
</html>
<?php
$conn->close();
?>