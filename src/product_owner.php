<?php
include("pdo.php"); // Connexion à la base de données

// Vérifier que la session est démarrée
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fonction pour récupérer tous les comptes commerçants
function getAllAccounts($mysqli) {
    $query = 'SELECT id_utilisateurs, identifiant, siren, raison_sociale, (SELECT SUM(montant) FROM BIF_transaction WHERE num_remise IN (SELECT num_remise FROM BIF_remise WHERE id_utilisateurs = BIF_utilisateurs.id_utilisateurs)) AS total_solde FROM BIF_utilisateurs WHERE role = "Client"';
    $stmt = $mysqli->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}

function getAccountsWithImpayes($mysqli) {
    $query = '
        SELECT u.id_utilisateurs, u.identifiant, u.siren, u.raison_sociale, 
               SUM(t.montant) AS total_impayes
        FROM BIF_utilisateurs u
        JOIN BIF_remise r ON u.id_utilisateurs = r.id_utilisateurs
        JOIN BIF_transaction t ON r.num_remise = t.num_remise
        JOIN BIF_impaye i ON t.id_transaction = i.id_transaction
        JOIN BIF_type_impaye ti ON i.code_impaye = ti.code_impaye
        WHERE t.sens = "-" 
        GROUP BY u.id_utilisateurs
        ORDER BY u.siren ASC
    ';
    $stmt = $mysqli->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}

// Récupérer tous les comptes
$accounts = getAllAccounts($mysqli);
$impayes = getAccountsWithImpayes($mysqli);





//Graphe impayé
function getImpayesByMotif($mysqli) {
    $query = '
        SELECT ti.libelle_impaye, COUNT(*) AS nombre_impayes
        FROM BIF_remise r
        JOIN BIF_transaction t ON r.num_remise = t.num_remise
        JOIN BIF_impaye i ON t.id_transaction = i.id_transaction
        JOIN BIF_type_impaye ti ON i.code_impaye = ti.code_impaye
        WHERE t.sens = "-"
        GROUP BY ti.libelle_impaye
    ';
    $stmt = $mysqli->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}

// Récupérer les impayés par motifs
$impayesByMotif = getImpayesByMotif($mysqli);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Owner</title>
    <link rel="stylesheet" href="../css/po.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Gloock&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Cormorant+Unicase:wght@300;400;500;600;700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Paytone+One&display=swap');
        .negative-balance {
            color: red; /* Couleur rouge pour les soldes négatifs */
        }
    </style>
</head>
<body>
<?php include("header.inc.php"); ?>
<div class="header-client">
    <p><?php echo $_SESSION['identifiant']; ?></p>
    <div>
        <span class="nav-item">Solde</span>
        <span class="nav-item">Transactions</span>
        <span class="nav-item">Extractions</span>
    </div>
</div>
<div class="po"><h1>Page Product Owner</h1></div>

<div class="compte">
    <h2>Comptes clients</h2>
    <table border="1">
        <thead>
        <tr>
            <th>Identifiant</th>
            <th>SIREN</th>
            <th>Raison Sociale</th>
            <th>Solde Total</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($accounts as $account) {
            // Détermine si le solde est négatif pour l'affichage
            $soldeClass = ($account['total_solde'] < 0) ? 'negative-balance' : '';
            echo '<tr>
                <td><a href="client.php?id=' . $account['id_utilisateurs'] . '">Voir le client</a></td>
                <td>' . $account['siren'] . '</td>
                <td>' . $account['raison_sociale'] . '</td>
                <td class="' . $soldeClass . '">' . $account['total_solde'] . '</td>
              </tr>';

        }
        ?>
        </tbody>
    </table>
</div>


<div class="impaye">
    <h2>Impayés clients</h2>
    <table border="1">
        <thead>
        <tr>
            <th>Identifiant</th>
            <th>SIREN</th>
            <th>Total impayés</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($impayes as $impaye) {
            // Détermine si le solde des impayés est négatif pour l'affichage
            $soldeClass = ($impaye['total_impayes'] < 0) ? 'negative-balance' : '';
            echo '<tr>
                <td><a href="client.php?id=' . $impaye['id_utilisateurs'] . '">Voir le client</a></td>
                <td>' . $impaye['siren'] . '</td>
                <td class="' . $soldeClass . '">' . $impaye['total_impayes'] . '</td>
              </tr>';
        }
        ?>
        </tbody>
    </table>

</div>




<div class="graphique"> <canvas id="impayesChart" width="100" height="400"> </div>
<div id="impayes-data" style="display:none;"><?php echo json_encode($impayesByMotif); ?></div>
<div id="format-graphique" style="display:none;"><?php echo 'secteurs'; ?></div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="client_include/chart.js"></script>

<?php include("footer.inc.php"); ?>
</body>
</html>
