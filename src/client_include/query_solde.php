<?php
include("pdo.php");

function getSoldeTotal($mysqli, $userId) {
    $stmt = $mysqli->prepare("SELECT SUM(montant) as solde_total FROM BIF_transaction JOIN BIF_remise ON BIF_transaction.num_remise = BIF_remise.num_remise WHERE BIF_remise.id_utilisateurs = ? AND BIF_transaction.sens = '+' ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $soldeData = $result->fetch_assoc();

    return $soldeData['solde_total'] ?? '0.00 €';
}
?>
