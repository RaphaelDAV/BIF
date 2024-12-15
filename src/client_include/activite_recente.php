<?php
include("../pdo.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['identifiant'])) {
    $idUtilisateur = $_SESSION['identifiant'];
    $raisonSociale = isset($_SESSION['raison_sociale']) ? $_SESSION['raison_sociale'] : ($_SESSION['role'] == 'Admin' ? 'Admin' : 'Product Owner');
} else {
    header('location: ../../index.php');
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sortColumn = isset($_GET['sort']) && in_array($_GET['sort'], ['date_traitement', 'total_transactions','num_remise']) ? $_GET['sort'] : 'BIF_remise.date_traitement';
$order = isset($_GET['order']) && $_GET['order'] == 'asc' ? 'desc' : 'asc';

$order = (isset($_GET['sort']) && $_GET['sort'] == $sortColumn && $order == 'asc') ? 'desc' : 'asc';

$orderByClause = "$sortColumn $order";

$perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

$searchClause = '';
$params = [];
if (!empty($search)) {
    $searchClause = "AND (BIF_remise.num_remise LIKE ? OR BIF_remise.date_traitement LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$stmtRemises_recentes = $mysqli->prepare("
    SELECT BIF_remise.num_remise, BIF_remise.date_traitement, SUM(BIF_transaction.montant) AS total_transactions 
    FROM BIF_remise 
    JOIN BIF_transaction ON BIF_remise.num_remise = BIF_transaction.num_remise
    WHERE BIF_remise.id_utilisateurs = ? $searchClause
    GROUP BY BIF_remise.num_remise, BIF_remise.date_traitement 
    ORDER BY $orderByClause
    LIMIT ? OFFSET ?
");

if (!empty($search)) {
    $params = array_merge([$_SESSION['id_utilisateurs']], $params, [$perPage, $offset]);
} else {
    $params = [$_SESSION['id_utilisateurs'], $perPage, $offset];
}

$stmtRemises_recentes->bind_param(str_repeat('s', count($params)), ...$params);
$stmtRemises_recentes->execute();
$resultRemises_recentes = $stmtRemises_recentes->get_result();
$remises_recentes = $resultRemises_recentes->fetch_all(MYSQLI_ASSOC);

$totalStmt = $mysqli->prepare('
    SELECT COUNT(DISTINCT BIF_remise.num_remise) as total 
    FROM BIF_remise 
    JOIN BIF_transaction ON BIF_remise.num_remise = BIF_transaction.num_remise
    WHERE BIF_remise.id_utilisateurs = ? 
');
$totalStmt->bind_param('i', $_SESSION['id_utilisateurs']);
$totalStmt->execute();
$totalResult = $totalStmt->get_result();
$totalRemises = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRemises / $perPage);

require_once('export.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export'])) {
    $format = $_POST['format'];
    exportData($remises_recentes, $format, 'Export des remises par dates');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activités Récentes</title>
    <link rel="stylesheet" href="../../css/stylesheet.css">
    <style></style>
</head>
<body class="client">
<?php include("../header.inc.php"); ?>

<div class="header-client">
    <p><?php echo htmlspecialchars($raisonSociale); ?></p>
</div>

<h1 id="titre-activite">ACTIVITÉS RÉCENTES</h1>

<section>
    <div class="container-activite">
        <div class="activite" >
            <h2>Activités récentes</h2>

            <form method="GET" action="" class="search-bar">
                <input type="text" name="search" placeholder="Rechercher par numéro ou date" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Rechercher</button>
                <input type="hidden" name="page" value="<?php echo $page; ?>">
                <input type="hidden" name="per_page" value="<?php echo $perPage; ?>">
                <input type="hidden" name="sort" value="<?php echo $sortColumn; ?>">
                <input type="hidden" name="order" value="<?php echo $order; ?>">
            </form>

            <form method="GET" action="">
                <label for="per_page">Afficher par page : </label>
                <select name="per_page" id="per_page" onchange="this.form.submit()">
                    <option value="5" <?php if ($perPage == 5) echo 'selected'; ?>>5</option>
                    <option value="10" <?php if ($perPage == 10) echo 'selected'; ?>>10</option>
                    <option value="20" <?php if ($perPage == 20) echo 'selected'; ?>>20</option>
                </select>
                <input type="hidden" name="page" value="<?php echo $page; ?>">
                <input type="hidden" name="sort" value="<?php echo $sortColumn; ?>">
                <input type="hidden" name="order" value="<?php echo $order; ?>">
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
            </form>


            <p>Nombre de résultats : <?php echo $totalRemises?></p>

        <table>
            <thead>
            <tr>
                <th>
                    <a href="?sort=date_traitement&order=<?php echo ($sortColumn == 'date_traitement' && $order == 'asc') ? 'desc' : 'asc'; ?>&per_page=<?php echo $perPage; ?>&page=<?php echo $page; ?>&search=<?php echo htmlspecialchars($search); ?>">
                        Date
                        <?php if ($sortColumn == 'date_traitement'): ?>
                            <?php echo $order == 'asc' ? ' &#9650;' : ' &#9660;'; ?>
                        <?php endif; ?>
                    </a>

                </th>
                <th>
                    <a href="?sort=num_remise&order=<?php echo $order == 'asc' ? 'desc' : 'asc'; ?>&per_page=<?php echo $perPage; ?>&page=<?php echo $page; ?>&search=<?php echo htmlspecialchars($search); ?>">
                        Numéro de remise
                        <?php if ($sortColumn == 'num_remise'): ?>
                            <?php echo $order == 'asc' ? ' &#9650;' : ' &#9660;'; ?>
                        <?php endif; ?>
                    </a>


                </th>
                <th>
                    <a href="?sort=total_transactions&order=<?php echo ($sortColumn == 'total_transactions' && $order == 'asc') ? 'desc' : 'asc'; ?>&per_page=<?php echo $perPage; ?>&page=<?php echo $page; ?>&search=<?php echo htmlspecialchars($search); ?>">
                        Montant
                        <?php if ($sortColumn == 'total_transactions'): ?>
                            <?php echo $order == 'asc' ? ' &#9650;' : ' &#9660;'; ?>
                        <?php endif; ?>
                    </a>
                </th>
            </tr>
            </thead>

            <tbody>
            <?php if (!empty($remises_recentes)): ?>
                <?php foreach ($remises_recentes as $activite): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($activite['date_traitement'])); ?></td>
                        <td><a href="client_details_remise.php?num_remise=<?php echo $activite['num_remise']; ?>"><?php echo $activite['num_remise']; ?></a></td>
                        <td><?php echo number_format($activite['total_transactions'], 2, ',', ' ') . ' €'; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">Aucune activité trouvée pour cette période.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&per_page=<?php echo $perPage; ?>&sort=<?php echo $sortColumn; ?>&order=<?php echo $order; ?>"><?php echo $i; ?></a>

            <?php endfor; ?>
        </div>

            <form method="POST" action="">
                <div class="export-options">
                    <span>Exportation : </span>
                    <label class="custom-radio"><input type="radio" name="format" value="xls"/>XLS</label>
                    <label class="custom-radio"><input type="radio" name="format" value="csv"/>CSV</label>
                    <label class="custom-radio"><input type="radio" name="format" value="pdf"/>PDF</label>
                    <input type="hidden" name="export" value="1">

                    <button type="submit">Exporter</button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php include("../footer.inc.php"); ?>
</body>
</html>
