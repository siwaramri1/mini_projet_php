<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Vérifier que l'utilisateur est connecté
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

// Définir le header JSON
header('Content-Type: application/json');

// Récupérer l'action demandée
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'kpis':
            // Récupérer les KPIs
            $kpis = getKPIs();
            echo json_encode([
                'success' => true,
                'data' => [
                    'users' => $kpis['total_users'],
                    'revenue' => number_format($kpis['revenue'], 2),
                    'orders' => $kpis['total_orders'],
                    'response' => number_format($kpis['avg_response'], 1)
                ]
            ]);
            break;
            
        case 'orders':
            // Récupérer les commandes avec filtre optionnel
            $status = $_GET['status'] ?? 'all';
            $orders = getAllOrders($status);
            echo json_encode([
                'success' => true,
                'data' => $orders
            ]);
            break;
            
        case 'order_stats':
            // Récupérer les statistiques des commandes pour le donut chart
            $stats = getOrderStats();
            $formattedStats = [
                'completed' => 0,
                'processing' => 0,
                'pending' => 0,
                'cancelled' => 0
            ];
            
            foreach ($stats as $stat) {
                $formattedStats[$stat['status']] = (int)$stat['count'];
            }
            
            echo json_encode([
                'success' => true,
                'data' => $formattedStats
            ]);
            break;
            
        case 'revenue':
            // Récupérer les données de revenus
            $period = $_GET['period'] ?? '7D';
            $revenueData = getRevenueData($period);
            echo json_encode([
                'success' => true,
                'data' => $revenueData
            ]);
            break;
            
        case 'activities':
            // Récupérer les activités récentes (simulées pour l'instant)
            $activities = [
                ['text' => 'Nouvelle commande reçue', 'time' => 'Il y a 2 minutes'],
                ['text' => 'Utilisateur enregistré', 'time' => 'Il y a 5 minutes'],
                ['text' => 'Commande #' . rand(1000, 9999) . ' complétée', 'time' => 'Il y a 10 minutes'],
                ['text' => 'Maintenance programmée', 'time' => 'Il y a 1 heure']
            ];
            
            echo json_encode([
                'success' => true,
                'data' => $activities
            ]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Action non reconnue'
            ]);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur serveur : ' . $e->getMessage()
    ]);
}
?>