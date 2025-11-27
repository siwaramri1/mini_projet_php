<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Vérifier que l'utilisateur est connecté
requireLogin();

// Récupérer les données initiales
$user = getCurrentUser();
$kpis = getKPIs();
$orders = getAllOrders();
$orderStats = getOrderStats();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard</title>
  
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>
  <script src="app.js" defer></script>
</head>
<body>
  <div class="app">

    <aside class="sidebar" aria-label="Navigation principale">
      <div class="logo">
        <div class="logo-mark">M</div>
        <div class="logo-text"></div>
      </div>

      <nav class="nav" role="navigation" aria-label="Menu principal">
        <ul>
          <li class="nav-item active">Dashboard</li>
          <li class="nav-item">Analytics</li>
          <li class="nav-item">Users</li>
          <li class="nav-item">Products</li>
          <li class="nav-item">Orders</li>
          <li class="nav-item">Reports</li>
        </ul>
      </nav>

      <div class="sidebar-footer">
        <a href="logout.php" style="color: #999; text-decoration: none; padding: 10px;">🚪 Déconnexion</a>
      </div>
    </aside>

    
    <main class="main">
      <header class="topbar">
        <div class="search">
          <input type="search" placeholder="Search... (Ctrl+K)" aria-label="Recherche">
        </div>
        <div class="top-actions">
          <button class="icon-btn" aria-label="Notifications">🔔<span class="badge">3</span></button>
          <div class="profile">
            <img src="./images/avatar.jpg" alt="<?php echo htmlspecialchars($user['username']); ?>">
            <span class="profile-name"><?php echo htmlspecialchars($user['username']); ?></span>
          </div>
        </div>
      </header>

      <section class="header-row">
        <div>
          <h2 class="page-title">Dashboard</h2>
          <p class="page-sub">Welcome back! Here's what's happening.</p>
        </div>
        <div class="header-actions">
          <button class="btn primary">+ New Item</button>
          <button class="btn" onclick="window.location.href='export.php?status=' + document.getElementById('statusFilter').value">Export CSV</button>
        </div>
      </section>

   
      <section class="grid">

      
        <div class="kpi-grid">
          <div class="kpi card">
            <div class="kpi-title">Total Users</div>
            <div class="kpi-value" id="kpiUsers"><?php echo number_format($kpis['total_users']); ?></div>
            <div class="kpi-diff positive" id="kpiUsersDiff">+12.5%</div>
          </div>
          <div class="kpi card">
            <div class="kpi-title">Revenue</div>
            <div class="kpi-value" id="kpiRevenue">€<?php echo number_format($kpis['revenue'], 0); ?></div>
            <div class="kpi-diff positive" id="kpiRevenueDiff">+8.2%</div>
          </div>
          <div class="kpi card">
            <div class="kpi-title">Orders</div>
            <div class="kpi-value" id="kpiOrders"><?php echo number_format($kpis['total_orders']); ?></div>
            <div class="kpi-diff negative" id="kpiOrdersDiff">-1.3%</div>
          </div>
          <div class="kpi card">
            <div class="kpi-title">Avg. Response</div>
            <div class="kpi-value" id="kpiResponse"><?php echo number_format($kpis['avg_response'], 1); ?>s</div>
            <div class="kpi-diff positive" id="kpiResponseDiff">+4.6%</div>
          </div>
        </div>

       
        <div class="card revenue-card">
          <div class="card-header">
            <h3>Revenue Overview</h3>
            <div class="chart-controls">
              <button class="chip active" data-period="7D">7D</button>
              <button class="chip" data-period="30D">30D</button>
              <button class="chip" data-period="1Y">1Y</button>
            </div>
          </div>
          <div class="card-body chart-wrap">
            <canvas id="revenueChart" role="img" aria-label="Graphique des revenus"></canvas>
          </div>
        </div>

 
        <div class="right-column">
          <div class="card activity-card">
            <h4>Recent Activity</h4>
            <ul id="activityList">
              <li><strong>New user registered</strong><span class="muted">2 minutes ago</span></li>
              <li><strong>Order #1234 completed</strong><span class="muted">5 minutes ago</span></li>
              <li><strong>Server maintenance scheduled</strong><span class="muted">1 hour ago</span></li>
            </ul>
          </div>

          <div class="card donut-card">
            <h4>Order Status Distribution</h4>
            <div class="chart-wrap small">
              <canvas id="ordersDonut" role="img" aria-label="Diagramme des statuts de commande"></canvas>
            </div>
          </div>
        </div>

        
        <div class="card users-graph">
          <h4>User Growth (Last 7 Days)</h4>
          <div class="chart-wrap">
            <canvas id="usersChart" role="img" aria-label="Croissance des utilisateurs"></canvas>
          </div>
        </div>

        <div class="card orders-table">
          <h4>Recent Orders</h4>

      
          <div class="filter">
            <label for="statusFilter">Filter by Status:</label>
            <select id="statusFilter">
              <option value="all">All</option>
              <option value="completed">Completed</option>
              <option value="pending">Pending</option>
              <option value="cancelled">Cancelled</option>
              <option value="processing">Processing</option>
            </select>
          </div>

          <table>
            <thead>
              <tr><th>Order ID</th><th>Customer</th><th>Amount</th><th>Status</th><th>Date</th></tr>
            </thead>
            <tbody id="ordersTableBody">
              <?php foreach ($orders as $order): ?>
              <tr data-status="<?php echo $order['status']; ?>">
                <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                <td>€<?php echo number_format($order['amount'], 2); ?></td>
                <td class="status <?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($order['order_date'])); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="card storage-card">
          <h4>Storage Status</h4>
          <div class="storage-inner">
            <div class="storage-usage">Used: <strong>68%</strong></div>
            <div class="progress" aria-hidden="true">
              <div class="progress-fill" style="width:68%"></div>
            </div>
            <p class="muted">102 GB of 150 GB used</p>
          </div>
        </div>

      </section>

      <footer class="footer"></footer>
    </main>
  </div>
</body>
</html>