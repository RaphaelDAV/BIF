<?php
include("pdo.php");

function getActivitesRecentes($mysqli, $userId) {
    $stmt = $mysqli->prepare('
        SELECT BIF_remise.num_remise, BIF_remise.date_traitement, SUM(BIF_transaction.montant) AS total_transactions 
        FROM BIF_remise 
        JOIN BIF_transaction ON BIF_remise.num_remise = BIF_transaction.num_remise
        WHERE BIF_remise.id_utilisateurs = ? 
        GROUP BY BIF_remise.num_remise, BIF_remise.date_traitement 
        ORDER BY BIF_remise.date_traitement DESC 
        LIMIT 3
    ');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}

function getPaginatedActivites($mysqli, $userId, $perPage, $offset, $sortColumn, $order) {
    $stmt = $mysqli->prepare("
        SELECT BIF_remise.date_traitement, BIF_remise.num_remise, SUM(BIF_transaction.montant) AS montant
        FROM BIF_remise
        JOIN BIF_transaction ON BIF_remise.num_remise = BIF_transaction.num_remise
        WHERE BIF_remise.id_utilisateurs = ?
        GROUP BY BIF_remise.num_remise, BIF_remise.date_traitement
        ORDER BY $sortColumn $order
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param('iii', $userId, $perPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}

function getTotalActivites($mysqli, $userId) {
    $stmt = $mysqli->prepare("
        SELECT COUNT(DISTINCT num_remise) as total 
        FROM BIF_remise 
        WHERE id_utilisateurs = ?
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc()['total'];
}
?>
