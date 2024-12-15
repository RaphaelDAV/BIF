<?php

include("pdo.php");


function getImpayesGraphData( $mysqli, $idUtilisateur, $dateDebut, $dateFin, $formatGraphique) {

    if ($formatGraphique == 'histogramme') {
        $stmtImpayesGraph = $mysqli->prepare('
            SELECT 
            DATE_FORMAT(r.date_traitement, "%Y-%m") AS mois,
            COUNT(i.numero_dossier) AS nombre_impayes
            FROM BIF_remise r
            JOIN BIF_transaction t ON r.num_remise = t.num_remise
            JOIN BIF_impaye i ON t.id_transaction = i.id_transaction
            WHERE r.id_utilisateurs = ? AND r.date_traitement BETWEEN ? AND ?
            GROUP BY mois
            ORDER BY mois ASC');
    } else {
        $stmtImpayesGraph = $mysqli->prepare('
            SELECT ti.libelle_impaye, COUNT(i.numero_dossier) AS nombre_impayes
            FROM BIF_remise r
            JOIN BIF_transaction t ON r.num_remise = t.num_remise
            JOIN BIF_impaye i ON t.id_transaction = i.id_transaction
            JOIN BIF_type_impaye ti ON i.code_impaye = ti.code_impaye
            WHERE r.id_utilisateurs = ? AND r.date_traitement BETWEEN ? AND ?
            GROUP BY ti.libelle_impaye');
    }

    $stmtImpayesGraph->bind_param('iss', $idUtilisateur, $dateDebut, $dateFin);
    $stmtImpayesGraph->execute();
    return $stmtImpayesGraph->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
