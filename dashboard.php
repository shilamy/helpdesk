<?php
// dashboard.php
$page_title = "Dashboard";
require_once 'includes/header.php';

// Get dashboard statistics
$stats = getDashboardStats();

// Get additional data for charts
$weekly_trends = getWeeklyTrends();
$status_distribution = getStatusDistribution();
$monthly_overview = getMonthlyOverview();
$department_visitors = getVisitorsByDepartment();
?>

<div class="container">
    <div class="main-content">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h3 style="margin-bottom: 15px; color: var(--primary-brown);">Quick Actions</h3>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="visitor-registration.php"><i class="fas fa-user-plus"></i> Register Visitor</a></li>
                <li><a href="visitor-management.php"><i class="fas fa-list"></i> Active Visitors</a></li>
                <li><a href="visitor-management.php?filter=history"><i class="fas fa-history"></i> Visitor History</a></li>
                <?php if (canViewReports()): ?>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <?php endif; ?>
                <?php if (canManageUsers()): ?>
                <li><a href="user-management.php"><i class="fas fa-users"></i> User Management</a></li>
                <?php endif; ?>
                <?php if (canManageSystem()): ?>
                <li><a href="system-settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <?php endif; ?>
                <li><a href="#"><i class="fas fa-question-circle"></i> Help</a></li>
            </ul>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                <h4 style="margin-bottom: 10px; color: var(--primary-brown);">System Status</h4>
                <div class="system-status">
                    <div class="status-indicator"></div>
                    <span>All Systems Operational</span>
                </div>
                <div style="font-size: 0.9rem; color: var(--text-muted); margin-top: 10px;">
                    Last updated: <span id="currentTime"><?php echo date('M j, Y H:i'); ?></span>
                </div>
            </div>
        </aside>
        
        <!-- Content Area -->
        <main class="content">
            <h1 style="margin-bottom: 20px; color: var(--primary-brown);">Dashboard Overview</h1>
            
            <!-- Statistics Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['today_visitors']; ?></div>
                    <div class="stat-label">Visitors Today</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['active_visitors']; ?></div>
                    <div class="stat-label">Currently Active</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['pending_badges']; ?></div>
                    <div class="stat-label">Pending Badge Returns</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['week_visitors']; ?></div>
                    <div class="stat-label">This Week</div>
                </div>
            </div>
            
            <!-- Charts Section -->
            <div class="charts-grid">
                <!-- Weekly Trend Line Chart -->
                <div class="chart-card">
                    <h3 style="margin-bottom: 15px; color: var(--primary-brown);">Weekly Visitor Trend</h3>
                    <canvas id="weeklyTrendChart" height="250"></canvas>
                </div>
                
                <!-- Current Status Pie Chart -->
                <div class="chart-card">
                    <h3 style="margin-bottom: 15px; color: var(--primary-brown);">Visitor Status Distribution</h3>
                    <canvas id="statusPieChart" height="250"></canvas>
                </div>
                
                <!-- Monthly Overview Bar Graph -->
                <div class="chart-card">
                    <h3 style="margin-bottom: 15px; color: var(--primary-brown);">Monthly Overview</h3>
                    <canvas id="monthlyBarChart" height="250"></canvas>
                </div>
                
                <!-- Visitors by Host Department -->
                <div class="chart-card">
                    <h3 style="margin-bottom: 15px; color: var(--primary-brown);">Visitors by Host Department</h3>
                    <canvas id="departmentChart" height="250"></canvas>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="visitor-registration.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i>
                    Register Visitor
                </a>
                <a href="visitor-management.php" class="btn btn-success">
                    <i class="fas fa-list"></i>
                    Manage Visitors
                </a>
                <?php if (canViewReports()): ?>
                <a href="reports.php" class="btn btn-info">
                    <i class="fas fa-chart-bar"></i>
                    View Reports
                </a>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<!-- Add Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Chart data from PHP
const chartData = {
    weeklyTrend: <?php echo json_encode($weekly_trends); ?>,
    statusDistribution: <?php echo json_encode($status_distribution); ?>,
    monthlyOverview: <?php echo json_encode($monthly_overview); ?>,
    departmentVisitors: <?php echo json_encode($department_visitors); ?>
};

// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Weekly Trend Line Chart
    const weeklyCtx = document.getElementById('weeklyTrendChart').getContext('2d');
    new Chart(weeklyCtx, {
        type: 'line',
        data: {
            labels: chartData.weeklyTrend.labels,
            datasets: [{
                label: 'Visitors',
                data: chartData.weeklyTrend.data,
                borderColor: '#8B4513',
                backgroundColor: 'rgba(139, 69, 19, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Visitors'
                    }
                }
            }
        }
    });

    // Status Distribution Pie Chart
    const statusCtx = document.getElementById('statusPieChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'pie',
        data: {
            labels: chartData.statusDistribution.labels,
            datasets: [{
                data: chartData.statusDistribution.data,
                backgroundColor: [
                    '#28a745', // Checked In - Green
                    '#dc3545', // Checked Out - Red
                    '#ffc107'  // Pending - Yellow
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Monthly Overview Bar Chart
    const monthlyCtx = document.getElementById('monthlyBarChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: chartData.monthlyOverview.labels,
            datasets: [{
                label: 'Visitors',
                data: chartData.monthlyOverview.data,
                backgroundColor: 'rgba(139, 69, 19, 0.7)',
                borderColor: '#8B4513',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Visitors'
                    }
                }
            }
        }
    });

    // Department Visitors Chart
    const deptCtx = document.getElementById('departmentChart').getContext('2d');
    new Chart(deptCtx, {
        type: 'doughnut',
        data: {
            labels: chartData.departmentVisitors.labels,
            datasets: [{
                data: chartData.departmentVisitors.data,
                backgroundColor: [
                    '#8B4513', '#A0522D', '#CD853F', '#D2691E',
                    '#DEB887', '#F4A460', '#8B7355', '#BC8F8F'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12
                    }
                }
            }
        }
    });

    // Update current time every minute
    function updateCurrentTime() {
        const now = new Date();
        const options = { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric', 
            hour: '2-digit', 
            minute: '2-digit' 
        };
        document.getElementById('currentTime').textContent = now.toLocaleDateString('en-US', options);
    }

    // Update time immediately and then every minute
    updateCurrentTime();
    setInterval(updateCurrentTime, 60000);
});

// Add auto-refresh for dashboard data (every 2 minutes)
setTimeout(function() {
    window.location.reload();
}, 120000);
</script>

<style>
:root {
    --primary-brown: #8B4513;
    --light-brown: #A0522D;
    --dark-brown: #654321;
    --bg-card: #ffffff;
    --border-color: #e0e0e0;
    --text-color: #333333;
    --text-muted: #666666;
    --success-green: #28a745;
    --error-red: #dc3545;
    --warning-yellow: #ffc107;
    --info-blue: #17a2b8;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f8f9fa;
    color: var(--text-color);
    line-height: 1.6;
}

.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.main-content {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 30px;
    align-items: start;
}

/* Sidebar Styles */
.sidebar {
    background: var(--bg-card);
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    border: 1px solid var(--border-color);
    position: sticky;
    top: 20px;
}

.sidebar h3 {
    font-size: 1.2rem;
    margin-bottom: 20px;
    font-weight: 700;
}

.sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-menu li {
    margin-bottom: 8px;
}

.sidebar-menu a {
    display: flex;
    align-items: center;
    padding: 14px 18px;
    color: var(--text-color);
    text-decoration: none;
    border-radius: 12px;
    transition: all 0.3s ease;
    font-weight: 500;
}

.sidebar-menu a:hover {
    background: rgba(139, 69, 19, 0.1);
    color: var(--primary-brown);
    transform: translateX(5px);
}

.sidebar-menu a.active {
    background: var(--primary-brown);
    color: white;
    box-shadow: 0 4px 15px rgba(139, 69, 19, 0.3);
}

.sidebar-menu i {
    width: 20px;
    margin-right: 12px;
    font-size: 1.1rem;
}

/* System Status */
.system-status {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: rgba(40, 167, 69, 0.1);
    border-radius: 10px;
    border-left: 4px solid var(--success-green);
}

.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: var(--success-green);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

/* Statistics Cards */
.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 24px;
    margin-bottom: 40px;
}

.stat-card {
    background: var(--bg-card);
    padding: 30px 25px;
    border-radius: 16px;
    text-align: center;
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    border: 1px solid var(--border-color);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

.stat-number {
    font-size: 3rem;
    font-weight: 700;
    color: var(--primary-brown);
    margin-bottom: 12px;
    line-height: 1;
}

.stat-label {
    font-size: 0.9rem;
    color: var(--text-muted);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.8px;
}

/* Charts Grid */
.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.chart-card {
    background: var(--bg-card);
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    border: 1px solid var(--border-color);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.chart-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

.chart-card h3 {
    font-size: 1.1rem;
    margin-bottom: 20px;
    text-align: center;
    color: var(--primary-brown);
    font-weight: 600;
    border-bottom: 2px solid var(--border-color);
    padding-bottom: 10px;
}

/* Quick Actions */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 40px;
}

.btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 25px 20px;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    cursor: pointer;
    font-size: 0.95rem;
    text-align: center;
    gap: 12px;
    min-height: 120px;
}

.btn i {
    font-size: 2rem;
    margin-bottom: 8px;
}

.btn-primary {
    background: var(--primary-brown);
    color: white;
}

.btn-primary:hover {
    background: #A0522D;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(139, 69, 19, 0.3);
}

.btn-success {
    background: var(--success-green);
    color: white;
}

.btn-success:hover {
    background: #218838;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.btn-info {
    background: var(--info-blue);
    color: white;
}

.btn-info:hover {
    background: #138496;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(23, 162, 184, 0.3);
}

/* Content Area */
.content h1 {
    font-size: 2.2rem;
    margin-bottom: 30px;
    font-weight: 700;
    color: var(--primary-brown);
}

/* Responsive Design */
@media (max-width: 1200px) {
    .charts-grid {
        grid-template-columns: 1fr 1fr;
    }
    
    .main-content {
        grid-template-columns: 260px 1fr;
        gap: 25px;
    }
}

@media (max-width: 992px) {
    .main-content {
        grid-template-columns: 1fr;
        gap: 25px;
    }
    
    .sidebar {
        position: static;
        margin-bottom: 0;
    }
    
    .charts-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-container {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .container {
        padding: 15px;
    }
    
    .stats-container {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .stat-card {
        padding: 25px 20px;
    }
    
    .stat-number {
        font-size: 2.5rem;
    }
    
    .charts-grid {
        gap: 20px;
    }
    
    .chart-card {
        padding: 20px;
    }
    
    .quick-actions {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .btn {
        padding: 20px 15px;
        min-height: 100px;
    }
    
    .sidebar {
        padding: 20px;
    }
}

@media (max-width: 480px) {
    .content h1 {
        font-size: 1.8rem;
    }
    
    .stat-number {
        font-size: 2.2rem;
    }
    
    .chart-card {
        padding: 15px;
    }
    
    .charts-grid {
        grid-template-columns: 1fr;
    }
    
    .sidebar-menu a {
        padding: 12px 15px;
    }
}

/* Print Styles */
@media print {
    .sidebar,
    .btn,
    .system-status {
        display: none !important;
    }
    
    .main-content {
        grid-template-columns: 1fr !important;
    }
    
    .stats-container,
    .charts-grid {
        break-inside: avoid;
    }
    
    .stat-card,
    .chart-card {
        break-inside: avoid;
        box-shadow: none !important;
        border: 2px solid #000 !important;
    }
    
    .stat-card:hover,
    .chart-card:hover {
        transform: none !important;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>