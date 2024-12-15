<?php
include("pdo.php");

function getImpayes($mysqli, $userId, $dateDebut, $dateFin) {
    $stmt = $mysqli->prepare('
        SELECT r.date_traitement, r.num_remise, ti.libelle_impaye, t.montant
        FROM BIF_remise r
        JOIN BIF_transaction t ON r.num_remise = t.num_remise
        JOIN BIF_impaye i ON t.id_transaction = i.id_transaction
        JOIN BIF_type_impaye ti ON i.code_impaye = ti.code_impaye
        WHERE r.id_utilisateurs = ? AND t.sens = "-" AND r.date_traitement BETWEEN ? AND ?
        ORDER BY r.date_traitement DESC
        LIMIT 3
    ');
    $stmt->bind_param('iss', $userId, $dateDebut, $dateFin);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}

function getNumImpayes($mysqli, $userId, $dateDebut, $dateFin) {
    $stmt = $mysqli->prepare('
        SELECT COUNT(*) AS total_impayes
        FROM BIF_remise r
        JOIN BIF_transaction t ON r.num_remise = t.num_remise
        JOIN BIF_impaye i ON t.id_transaction = i.id_transaction
        WHERE r.id_utilisateurs = ? AND t.sens = "-" AND r.date_traitement BETWEEN ? AND ?
    ');
    $stmt->bind_param('iss', $userId, $dateDebut, $dateFin);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc()['total_impayes'];
}


?>
