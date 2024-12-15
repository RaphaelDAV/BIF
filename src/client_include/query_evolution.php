<?php
// Récupérer le solde mois par mois
function getSoldeMois($mysqli, $idUtilisateur) {
    $query = "SELECT 
        DATE_FORMAT(r.date_traitement, '%Y-%m') AS mois,
        SUM(CASE WHEN t.sens = '+' THEN t.montant ELSE 0 END) AS solde
    FROM 
        BIF_remise r
    LEFT JOIN 
        BIF_transaction t ON r.num_remise = t.num_remise
    WHERE 
        r.id_utilisateurs = ?
    GROUP BY 
        mois
    ORDER BY 
        mois;";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $idUtilisateur);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}
