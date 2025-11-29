<?php
require_once 'db.php';

// Récupérer les KPIs
function getKPIs() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM kpis ORDER BY id DESC LIMIT 1");
    return $stmt->fetch();
}

// Récupérer toutes les commandes
function getAllOrders($status = 'all') {
    global $pdo;
    if ($status === 'all') {
        $stmt = $pdo->query("SELECT * FROM orders ORDER BY order_date DESC");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE status = ? ORDER BY order_date DESC");
        $stmt->execute([$status]);
    }
    return $stmt->fetchAll();
}

// Récupérer les statistiques des commandes
function getOrderStats() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT 
            status,
            COUNT(*) as count
        FROM orders
        GROUP BY status
    ");
    return $stmt->fetchAll();
}

// Mettre à jour les KPIs
function updateKPIs($users, $revenue, $orders, $response) {
    global $pdo;
    $stmt = $pdo->prepare("
        UPDATE kpis 
        SET total_users = ?, revenue = ?, total_orders = ?, avg_response = ?
        WHERE id = 1
    ");
    return $stmt->execute([$users, $revenue, $orders, $response]);
}

// Récupérer les données de revenus pour les graphiques
function getRevenueData($period = '7D') {
    global $pdo;
    
    switch($period) {
        case '7D':
            $days = 7;
            break;
        case '30D':
            $days = 30;
            break;
        case '1Y':
            $days = 365;
            break;
        default:
            $days = 7;
    }
    
    // Simulation de données (à adapter selon vos besoins)
    $data = [];
    for ($i = $days; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $data[] = [
            'date' => $date,
            'revenue' => rand(15000, 25000),
            'profit' => rand(4000, 8000)
        ];
    }
    
    return $data;
}

// Nettoyer et sécuriser les entrées
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Vérifier les credentials de connexion
function verifyLogin($username, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    
    return false;
}
?>