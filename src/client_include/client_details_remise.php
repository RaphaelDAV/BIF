<?php
// Inclure la connexion à la base de données
//require("/src/pdo.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);
require __DIR__ . '/../pdo.php';

// Vérifier que la session est démarrée
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['identifiant'])) {
    header('Location: ../../index.php');
}

// Récupérer les informations de l'utilisateur connecté
if (isset($_SESSION['identifiant'])) {
    if ($_SESSION['role']=='ProductOwner'){
        $user_id = $_GET['id'];

    } else{
        $user_id = $_SESSION['id_utilisateurs'];
    }


    if (isset($_SESSION['raison_sociale'])) {
        $raisonSociale = $_SESSION['raison_sociale'];
    } else if ($_SESSION['role']=='Admin'){
        $raisonSociale = 'Admin';
    } else{
        $raisonSociale = 'Product Owner';
        echo $raisonSociale;
    }
}

// Vérifier si le numéro de remise est passé en paramètre

if (!isset($_GET['num_remise'])) {
    die('Numéro de remise manquant.');
}

$num_remise = $_GET['num_remise'];

// Préparer la requête pour vérifier que la remise appartient au client
$query = $mysqli->prepare("
    SELECT * 
    FROM BIF_remise 
    WHERE num_remise = ? 
    AND id_utilisateurs = ?
");
$query->bind_param('si', $num_remise, $user_id);
$query->execute();
$result = $query->get_result();
$transactions_impayés = $result->fetch_all(MYSQLI_ASSOC);

// Paramètres de pagination
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10; // Par défaut, 10 éléments par page
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Paramètres de tri
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date_transaction'; // Colonne par défaut
$order = isset($_GET['order']) && in_array(strtoupper($_GET['order']), ['ASC', 'DESC']) ? strtoupper($_GET['order']) : 'ASC';

// Calculer le nombre total d'éléments
$countQuery = $mysqli->prepare("
    SELECT COUNT(*) AS total
    FROM BIF_transaction t
    JOIN BIF_remise r ON t.num_remise = r.num_remise
    WHERE r.num_remise = ? AND r.id_utilisateurs = ?
");

$countQuery->bind_param('si', $num_remise, $user_id);
$countQuery->execute();
$countResult = $countQuery->get_result();
$totalTransactions = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalTransactions / $limit);

// Requête principale avec pagination et tri
$query = $mysqli->prepare("
    SELECT 
        t.id_transaction, 
        t.date_transaction, 
        t.num_carte, 
        t.montant, 
        t.sens, 
        t.devise, 
        t.reseau, 
        t.num_autorisation,
        COALESCE(type.libelle_impaye, 'Aucun') AS type_impaye
    FROM 
        BIF_transaction t
    LEFT JOIN 
        BIF_impaye i ON t.id_transaction = i.id_transaction
    LEFT JOIN 
        BIF_type_impaye type ON i.code_impaye = type.code_impaye
    JOIN 
        BIF_remise r ON t.num_remise = r.num_remise
    WHERE 
        r.num_remise = ? 
        AND r.id_utilisateurs = ?
    ORDER BY $sort $order
    LIMIT ? OFFSET ?
");
$query->bind_param('siii', $num_remise, $user_id, $limit, $offset);
$query->execute();
$result = $query->get_result();

require_once('export.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export'])) {
    $format = $_POST['format'];
    exportData($transactions_impayés, $format, 'Export des remises impayées');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la remise <?php echo htmlspecialchars($num_remise); ?></title>
    <link rel="stylesheet" href="../../css/stylesheet.css">
</head>
<body class="client">

<?php include("../header.inc.php"); ?>

<div class="header-client">
    <p><?php echo htmlspecialchars($raisonSociale); ?></p>
</div>


<h1 id="titre-activite">Détails de la remise</h1>

<section>
    <div class="container-activite">
        <div class="activite" >
            <h2>Détails de la remise <?php echo htmlspecialchars($num_remise); ?></h2>


            <table>
                <thead>
                <tr>
                    <th>
                        <a href="?num_remise=<?php echo $num_remise; ?>&page=1&limit=<?php echo $limit; ?>&sort=id_transaction&order=<?php echo $sort === 'id_transaction' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>">
                            ID Transaction
                            <?php if ($sort === 'id_transaction'): ?>
                                <?php echo $order === 'ASC' ? ' &#9650;' : ' &#9660;'; ?>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th>
                        <a href="?num_remise=<?php echo $num_remise; ?>&page=1&limit=<?php echo $limit; ?>&sort=date_transaction&order=<?php echo $sort === 'date_transaction' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>">
                            Date
                            <?php if ($sort === 'date_transaction'): ?>
                                <?php echo $order === 'ASC' ? ' &#9650;' : ' &#9660;'; ?>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th>Numéro de Carte</th>
                    <th>
                        <a href="?num_remise=<?php echo $num_remise; ?>&page=1&limit=<?php echo $limit; ?>&sort=montant&order=<?php echo $sort === 'montant' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>">
                            Montant
                            <?php if ($sort === 'montant'): ?>
                                <?php echo $order === 'ASC' ? ' &#9650;' : ' &#9660;'; ?>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th>Sens</th>
                    <th>Devise</th>
                    <th>Réseau</th>
                    <th>Numéro d'autorisation</th>
                    <th>
                        <a href="?num_remise=<?php echo $num_remise; ?>&page=1&limit=<?php echo $limit; ?>&sort=type_impaye&order=<?php echo $sort === 'type_impaye' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>">
                            Type Impayé
                            <?php if ($sort === 'type_impaye'): ?>
                                <?php echo $order === 'ASC' ? ' &#9650;' : ' &#9660;'; ?>
                            <?php endif; ?>
                        </a>
                    </th>
                </tr>

                </thead>

                <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($transaction = $result->fetch_assoc()): ?>
                        <?php
                        // Détermine si la transaction est impayée
                        $isImpayee = $transaction['type_impaye'] !== 'Aucun';
                        ?>
                        <tr class="<?php echo $isImpayee ? 'impayee' : ''; ?>">
                            <td><?php echo htmlspecialchars($transaction['id_transaction']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($transaction['date_transaction'])); ?></td>
                            <td><?php echo htmlspecialchars($transaction['num_carte']); ?></td>
                            <td><?php echo number_format($transaction['montant'], 2, ',', ' ') . ' €'; ?></td>
                            <td><?php echo htmlspecialchars($transaction['sens']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['devise']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['reseau']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['num_autorisation']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['type_impaye']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">Aucune transaction trouvée pour cette remise.</td>
                    </tr>
                <?php endif; ?>
                </tbody>

            </table>

            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?num_remise=<?php echo $num_remise; ?>&page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>">Précédent</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?num_remise=<?php echo $num_remise; ?>&page=<?php echo $i; ?>&limit=<?php echo $limit; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>" class="<?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?num_remise=<?php echo $num_remise; ?>&page=<?php echo $page + 1; ?>&limit=<?php echo $limit; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>">Suivant</a>
                <?php endif; ?>
            </div>


        </div>
    </div>
    <div id="back_to_board">
        <a href="../client.php">Retour au tableau de bord</a>
    </div>
</section>

<?php include("../footer.inc.php"); ?>

</body>
</html>
