<?php

include("pdo.php");

function getComparaisonGraphData($mysqli, $idUtilisateur) {
    $stmtComparaison = $mysqli->prepare("
       SELECT 
           (SELECT SUM(t.montant) 
            FROM BIF_transaction t
            JOIN BIF_remise r ON t.num_remise = r.num_remise
            LEFT JOIN BIF_impaye i ON t.id_transaction = i.id_transaction
            WHERE r.id_utilisateurs = ? AND t.sens = '+' AND i.id_transaction IS NULL) AS chiffre_affaire_total,

           (SELECT SUM(t.montant)
            FROM BIF_transaction t
            JOIN BIF_impaye i ON t.id_transaction = i.id_transaction
            JOIN BIF_remise r ON t.num_remise = r.num_remise
            WHERE r.id_utilisateurs = ?) AS montant_impayes;
    ");

    // Bind the parameter
    $stmtComparaison->bind_param('ii', $idUtilisateur, $idUtilisateur);
    $stmtComparaison->execute();

    // Fetch the result

    return $stmtComparaison->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
