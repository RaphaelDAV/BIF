<?php
include("pdo.php");

function getActivitesParDate($mysqli, $userId, $dateDebut, $dateFin) {
    $stmt = $mysqli->prepare('
        SELECT BIF_remise.num_remise, BIF_remise.date_traitement, SUM(BIF_transaction.montant) AS total_transactions 
        FROM BIF_remise 
        JOIN BIF_transaction ON BIF_remise.num_remise = BIF_transaction.num_remise
        WHERE BIF_remise.id_utilisateurs = ? 
        AND BIF_remise.date_traitement BETWEEN ? AND ?
        GROUP BY BIF_remise.num_remise, BIF_remise.date_traitement 
        ORDER BY BIF_remise.date_traitement DESC
        LIMIT 3
    ');
    $stmt->bind_param('iss', $userId, $dateDebut, $dateFin);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}

function getNumActivites($mysqli, $userId, $dateDebut, $dateFin) {
    $stmt = $mysqli->prepare('
        SELECT COUNT(DISTINCT BIF_remise.num_remise) AS total_activites
        FROM BIF_remise 
        WHERE BIF_remise.id_utilisateurs = ? 
        AND BIF_remise.date_traitement BETWEEN ? AND ?
    ');
    $stmt->bind_param('iss', $userId, $dateDebut, $dateFin);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc()['total_activites'];
}
?>
