<?php
include("../pdo.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['identifiant'])) {
    $idUtilisateur = $_SESSION['identifiant'];
    $raisonSociale = isset($_SESSION['raison_sociale']) ? $_SESSION['raison_sociale'] : ($_SESSION['role'] == 'Admin' ? 'Admin' : 'Product Owner');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['date_debut'], $_POST['date_fin'])) {
    $dateDebut = $_POST['date_debut'];
    $dateFin = $_POST['date_fin'];
    $_SESSION['date_debut'] = $dateDebut;
    $_SESSION['date_fin'] = $dateFin;
} elseif (isset($_GET['date_debut'], $_GET['date_fin'])) {
    $dateDebut = $_GET['date_debut'];
    $dateFin = $_GET['date_fin'];
} else {
    $dateDebut = isset($_SESSION['date_debut']) ? $_SESSION['date_debut'] : date('Y-m-d', strtotime('-1 month'));
    $dateFin = isset($_SESSION['date_fin']) ? $_SESSION['date_fin'] : date('Y-m-d');
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sortColumn = isset($_GET['sort']) && in_array($_GET['sort'], ['date_traitement', 'montant', 'num_remise', 'libelle_impaye']) ? $_GET['sort'] : 'num_remise';
$order = isset($_GET['order']) && in_array($_GET['order'], ['asc', 'desc']) ? $_GET['order'] : 'asc';
$orderByClause = "$sortColumn $order";

$perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

$searchClause = !empty($search) ? "AND (ti.libelle_impaye LIKE ? OR r.num_remise LIKE ?)" : '';
$params = [$_SESSION['id_utilisateurs'], $dateDebut, $dateFin];

if (!empty($search)) {
    $params = array_merge($params, ["%$search%", "%$search%"]);
}
$params = array_merge($params, [$perPage, $offset]);

$stmtImpayes = $mysqli->prepare("
    SELECT r.date_traitement, r.num_remise, ti.libelle_impaye, t.montant
    FROM BIF_remise r
    JOIN BIF_transaction t ON r.num_remise = t.num_remise
    JOIN BIF_impaye i ON t.id_transaction = i.id_transaction
    JOIN BIF_type_impaye ti ON i.code_impaye = ti.code_impaye
    WHERE r.id_utilisateurs = ? 
    AND r.date_traitement BETWEEN ? AND ?
    AND t.sens = '-' 
    $searchClause
    ORDER BY $orderByClause
    LIMIT ? OFFSET ?
");

$typeString = 'iss' . str_repeat('s', !empty($search) ? 2 : 0) . 'ii';
$stmtImpayes->bind_param($typeString, ...$params);
$stmtImpayes->execute();
$resultImpayes = $stmtImpayes->get_result();
$impayes = $resultImpayes->fetch_all(MYSQLI_ASSOC);

$totalStmt = $mysqli->prepare("
    SELECT COUNT(*) as total 
    FROM BIF_remise r
    JOIN BIF_transaction t ON r.num_remise = t.num_remise
    JOIN BIF_impaye i ON t.id_transaction = i.id_transaction
    JOIN BIF_type_impaye ti ON i.code_impaye = ti.code_impaye
    WHERE r.id_utilisateurs = ? 
    AND r.date_traitement BETWEEN ? AND ?
    AND t.sens = '-' 
    $searchClause
");

$totalParams = [$_SESSION['id_utilisateurs'], $dateDebut, $dateFin];
if (!empty($search)) {
    $totalParams = array_merge($totalParams, ["%$search%", "%$search%"]);
}

$typeStringTotal = 'iss' . str_repeat('s', !empty($search) ? 2 : 0);
$totalStmt->bind_param($typeStringTotal, ...$totalParams);
$totalStmt->execute();
$totalResult = $totalStmt->get_result();
$totalImpayes = $totalResult->fetch_assoc()['total'];

$totalPages = ceil($totalImpayes / $perPage);

require_once('export_impaye.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export'])) {
    $format = $_POST['format'];
    exportDataImpaye($impayes, $format, 'Export des impayes');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client</title>
    <link rel="stylesheet" href="../../css/stylesheet.css">
    <style></style>
</head>
<body class="client">
<?php include("../header.inc.php"); ?>

<div class="header-client">
    <p><?php echo htmlspecialchars($raisonSociale); ?></p>
    <div>
        <span class="nav-item">Solde</span>
        <span class="nav-item">Transactions</span>
        <span class="nav-item">Extractions</span>
    </div>
</div>

<h1 id="titre-activite">IMPAYÉS</h1>

<section>
    <div class="container-activite">
        <div class="activite">
            <h2>Impayés</h2>
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
                <input type="hidden" name="date_debut" value="<?php echo $dateDebut; ?>">
                <input type="hidden" name="date_fin" value="<?php echo $dateFin; ?>">
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
            </form>


            <form method="POST" action="">
                <div class="trier">
                    <label>Début : </label>
                    <input type="date" name="date_debut" value="<?php echo $dateDebut; ?>" required />
                    <label>Fin : </label>
                    <input type="date" name="date_fin" value="<?php echo $dateFin; ?>" required />
                    <button type="submit">Filtrer</button>
                </div>
            </form>

            <p>Nombre de résultats : <?php echo $totalImpayes?></p>

            <table>
                <thead>
                <tr>
                    <<th>
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
                        <a href="?sort=description&order=<?php echo ($sortColumn == 'description' && $order == 'asc') ? 'desc' : 'asc'; ?>&per_page=<?php echo $perPage; ?>&page=<?php echo $page; ?>&search=<?php echo htmlspecialchars($search); ?>">
                            Description
                            <?php if ($sortColumn == 'description&'): ?>
                                <?php echo $order == 'asc' ? ' &#9650;' : ' &#9660;'; ?>
                            <?php endif; ?>
                        </a>
                    </th>

                    <th>
                        <a href="?sort=t.montant&order=<?php echo ($sortColumn == 't.montant' && $order == 'asc') ? 'desc' : 'asc'; ?>&per_page=<?php echo $perPage; ?>&page=<?php echo $page; ?>&search=<?php echo htmlspecialchars($search); ?>">
                            Montant
                            <?php if ($sortColumn == 't.montant'): ?>
                                <?php echo $order == 'asc' ? ' &#9650;' : ' &#9660;'; ?>
                            <?php endif; ?>
                        </a>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($impayes)): ?>
                    <?php foreach ($impayes as $impaye): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($impaye['date_traitement'])); ?></td>
                            <td><a href="client_details_remise.php?num_remise=<?php echo $impaye['num_remise']; ?>"><?php echo $impaye['num_remise']; ?></a></td>
                            <td><?php echo htmlspecialchars($impaye['libelle_impaye']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($impaye['montant'], 2, ',', ' ') . ' €'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">Aucun impayé trouvé pour cette période.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>

            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&per_page=<?php echo $perPage; ?>&sort=<?php echo $sortColumn; ?>&order=<?php echo $order; ?>&date_debut=<?php echo $dateDebut; ?>&date_fin=<?php echo $dateFin; ?>&search=<?php echo urlencode($search); ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>


            <form method="POST" action="">
                <div class="export-options">
                    <span>Exportation : </span>
                    <label class="custom-radio"><input type="radio" name="format" value="xls"/>XLS</label>
                    <label class="custom-radio"><input type="radio" name="format" value="csv"/>CSV</label>
                    <label class="custom-radio"><input type="radio" name="format" value="pdf"/>PDF</label>
                    <input type="hidden" name="export" value="3">
                    <button type="submit">Exporter</button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php include("../footer.inc.php"); ?>
</body>
</html>
