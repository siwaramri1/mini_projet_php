<?php
session_start();
require_once 'auth.php';
require_once 'functions.php';

// Vérifier que l'utilisateur est connecté
requireLogin();

// Récupérer le filtre de statut si présent
$status = $_GET['status'] ?? 'all';

// Récupérer les commandes
$orders = getAllOrders($status);

// Définir les headers pour le téléchargement CSV
$filename = 'orders_export_' . date('Y-m-d_H-i-s') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Créer le flux de sortie
$output = fopen('php://output', 'w');

// Ajouter le BOM UTF-8 pour Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// En-têtes du CSV
$headers = ['Order ID', 'Customer', 'Amount', 'Status', 'Date'];
fputcsv($output, $headers);

// Ajouter les données
foreach ($orders as $order) {
    $row = [
        $order['order_number'],
        $order['customer_name'],
        '€' . number_format($order['amount'], 2),
        ucfirst($order['status']),
        date('d/m/Y', strtotime($order['order_date']))
    ];
    fputcsv($output, $row);
}

// Ajouter une ligne de résumé
fputcsv($output, []); // Ligne vide
fputcsv($output, ['Total des commandes', count($orders)]);

// Calculer le total des montants
$total = array_sum(array_column($orders, 'amount'));
fputcsv($output, ['Montant total', '€' . number_format($total, 2)]);

fclose($output);
exit;
?>