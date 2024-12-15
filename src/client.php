<?php
// Inclure la connexion à la base de données
include("pdo.php");

// Vérifier que la session est démarrée
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['identifiant'])) {
    header('Location: ../index.php');
}

// Récupérer les informations de l'utilisateur connecté
if (isset($_SESSION['identifiant'])) {
    if ($_SESSION['role']=='ProductOwner'){
            $idUtilisateur = $_GET['id'];

    } else{
        $idUtilisateur = $_SESSION['id_utilisateurs'];
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





// Récupérer le solde total
include("client_include/query_solde.php");
$soldeTotal = getSoldeTotal($mysqli, $idUtilisateur);

// Récupérer les activités récentes
include("client_include/query_activite_recente.php");
$remises_recentes = getActivitesRecentes($mysqli, $idUtilisateur);




// Filtre par date pour les remises
include("client_include/query_activite_date.php");
$dateDebut_remise = $_POST['date_debut_remise'] ?? date('Y-m-d', strtotime('-1 month'));
$dateFin_remise = $_POST['date_fin_remise'] ?? date('Y-m-d');

$remises_date = getActivitesParDate($mysqli, $idUtilisateur, $dateDebut_remise, $dateFin_remise);



// Filtre par date pour les impayés
include("client_include/query_impaye.php");
$dateDebut_impaye = $_POST['date_debut_impaye'] ?? date('Y-m-d', strtotime('-1 month'));
$dateFin_impaye = $_POST['date_fin_impaye'] ?? date('Y-m-d');
$impayes = getImpayes($mysqli, $idUtilisateur, $dateDebut_impaye, $dateFin_impaye);






// Graphique des impayés
include("client_include/query_graphe.php");
$dateDebut_graphe = date('Y-m-d', strtotime('-1 month'));
$dateFin_graphe = date('Y-m-d');
$dateFin_graphe_duree = date('Y-m-d');
$duree_graphe = 4;
$formatGraphique = 'histogramme';

if (isset($_POST['format_graphique'])) {
    $formatGraphique = $_POST['format_graphique'];
}

if (isset($_POST['date_debut_graphe']) && isset($_POST['date_fin_graphe'])) {
    $dateDebut_graphe = $_POST['date_debut_graphe'];
    $dateFin_graphe = $_POST['date_fin_graphe'];
} else if (isset($_POST['date_debut_graphe_duree']) && isset($_POST['duree_graphe'])){
    $dateDebut_graphe = date('Y-m-d', strtotime('-' . intval($_POST['duree_graphe']) . ' months', strtotime($dateFin_graphe_duree)));
    $dateFin_graphe = $_POST['date_fin_graphe_duree'];
}

$impayesData = getImpayesGraphData($mysqli, $idUtilisateur, $dateDebut_graphe, $dateFin_graphe, $formatGraphique);

include("client_include/query_comparaison.php");
$impayeComparaison = getComparaisonGraphData($mysqli, $idUtilisateur);

include("client_include/query_evolution.php");
$soldeMois = getSoldeMois($mysqli, $idUtilisateur);





require_once('client_include/export.php');
require_once('client_include/export_impaye.php');

// Vérification de la méthode POST et du paramètre d'export
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export'])) {
    $format = $_POST['format'];
    $export = $_POST['export'];


    if ($export === '1' && !empty($remises_recentes)) {
        exportData($remises_recentes, $format, 'EXPORT DES REMISES RECENTES');
    } elseif ($export === '2' && !empty($remises_date)) {
        exportData($remises_date, $format, 'EXPORT DES REMISES PAR DATES');
    } elseif ($export === '3' && !empty($impayes)) {
        exportDataImpaye($impayes, $format, 'EXPORT DES IMPAYES');
    } elseif ($export === '4' && !empty($impayesData)) {
        exportDataGraphe($impayes, $format, 'EXPORT DES IMPAYES');
    }
    exit;
}

//Nombres de remises
$totalRemise= getTotalActivites($mysqli, $idUtilisateur);

//Nombres de remises par date
$totalRemiseDate = getNumActivites($mysqli, $idUtilisateur, $dateDebut_remise, $dateFin_remise);
//Nombre d'impayés par date
$totalImpaye = getNumImpayes($mysqli, $idUtilisateur, $dateDebut_impaye, $dateFin_impaye);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client</title>
    <link rel="stylesheet" href="../css/stylesheet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Gloock&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Cormorant+Unicase:wght@300;400;500;600;700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Cormorant+Unicase:wght@300;400;500;600;700&family=Gloock&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Paytone+One&display=swap');
    </style>
</head>
<body class="client">
<?php
include ("header.inc.php");
?>
<div class="header-client">
    <p><?php echo $raisonSociale; ?></p>

    <div>
        <span class="nav-item"> <a href="#solde"> Solde</a></span>
        <span class="nav-item"><a href="#activite_recentes">Activités récentes</a></span>
        <span class="nav-item"><a href="#activite_par_date">Activités par date</a></span>
        <span class="nav-item"><a href="#impayes">Impayés</a></span>
        <span class="nav-item"><a href="#graphes">Graphes</a></span>


    </div>
</div>

<div class="solde" id="solde">
    <h2>Solde total</h2>
    <h1>~ <?php echo $soldeTotal;  ?> € ~</h1>
</div>

<section>
    <div class="container-activite">



        <!------------------------ ACTIVITE RECENTES ----------------------->

        <div class="activite" id="activite_recentes">
            <h2>Activités récentes</h2>
            <p>Nombre de résultats : <?php echo $totalRemise?></p>
            <table>
                <thead>
                <tr>
                    <th>Date de traitement</th>
                    <th>Numéro de remise</th>
                    <th>Somme des transactions</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($remises_recentes)): ?>
                    <?php foreach ($remises_recentes as $remise): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($remise['date_traitement'])); ?></td>
                            <td><a href="client_include/client_details_remise.php?num_remise=<?php echo $remise['num_remise']; ?>"><?php echo $remise['num_remise']; ?></a></td>
                            <td><?php echo number_format($remise['total_transactions'], 2, ',', ' ') . ' €'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">Aucunes remises récentes trouvée.</td>
                    </tr>
                <?php endif; ?>

                </tbody>
            </table>

            <form method="POST" action="">
                <div class="export-options" >
                    <span>Exportation : </span>
                    <label class="custom-radio"><input type="radio" id="xls" name="format" value="xls"/>XLS</label>
                    <label class="custom-radio"><input type="radio" id="csv" name="format" value="csv"/>CSV</label>
                    <label class="custom-radio"><input type="radio" id="pdf" name="format" value="pdf"/>PDF</label>
                    <input type="hidden" name="export" value="1">
                    <button type="submit">Exporter</button>
                    <a href="client_include/activite_recente.php?id=<?php echo $idUtilisateur; ?>">
                        <span>Voir plus</span>
                    </a>
                </div>
            </form>
        </div>



        <!------------------------ ACTIVITE PAR DATE ----------------------->


        <div class="activite" id="activite_par_date">
            <h2>Activités par date</h2>

            <!-- Formulaire pour filtrer par date -->
            <form method="POST" action="">
                <div class="trier">
                    <label for="date_debut_remise">Date de début :</label>
                    <input type="date" id="date_debut_remise" name="date_debut_remise" value="<?php echo htmlspecialchars($dateDebut_remise); ?>" required>

                    <label for="date_fin_remise">Date de fin :</label>
                    <input type="date" id="date_fin_remise" name="date_fin_remise" value="<?php echo htmlspecialchars($dateFin_remise); ?>" required>

                    <button type="submit">Filtrer</button>
                </div>
            </form>

            <p>Nombre de résultats : <?php echo $totalRemiseDate?></p>

            <!-- Table des remises filtrées par date -->
            <table>
                <thead>
                <tr>
                    <th>Date de traitement</th>
                    <th>Numéro de remise</th>
                    <th>Somme des transactions</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($remises_date)): ?>
                    <?php foreach ($remises_date as $remise): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($remise['date_traitement'])); ?></td>
                            <td><a href="client_include/client_details_remise.php?num_remise=<?php echo $remise['num_remise']; ?>"><?php echo $remise['num_remise']; ?></a></td>
                            <td><?php echo number_format($remise['total_transactions'], 2, ',', ' ') . ' €'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">Aucune remise trouvée pour cette période.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>

            <form method="POST" action="">
                <div class="export-options">
                    <span>Exportation : </span>
                    <label class="custom-radio"><input type="radio" name="format" value="xls"/>XLS</label>
                    <label class="custom-radio"><input type="radio" name="format" value="csv"/>CSV</label>
                    <label class="custom-radio"><input type="radio" name="format" value="pdf"/>PDF</label>
                    <input type="hidden" name="export" value="2">
                    <input type="hidden" name="date_debut_remise" value="<?php echo htmlspecialchars($dateDebut_remise); ?>">
                    <input type="hidden" name="date_fin_remise" value="<?php echo htmlspecialchars($dateFin_remise); ?>">

                    <button type="submit">Exporter</button>
                    <a href="client_include/activite_date.php?id=<?php echo $idUtilisateur; ?>">
                        <span>Voir plus</span></a>
                </div>
            </form>
        </div>


        <!------------------------ IMPAYE ----------------------->


        <div class="activite" id="impayes">
            <h2>Impayés</h2>
            <form method="POST" action="">
                <div class="trier">
                    <label>Début : </label>
                    <input type="date" name="date_debut_impaye" value="<?php echo htmlspecialchars($dateDebut_impaye); ?>" required>
                    <label>Fin : </label>
                    <input type="date" name="date_fin_impaye" value="<?php echo htmlspecialchars($dateFin_impaye); ?>" required>
                    <button type="submit">Filtrer</button>
                </div>
            </form>
            <p>Nombre de résultats : <?php echo $totalImpaye?></p>
            <table>
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Numéro de Remise</th>
                    <th>Description</th>
                    <th>Montant</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($impayes)): ?>
                    <?php foreach ($impayes as $impaye): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($impaye['date_traitement'])); ?></td>
                            <td><a href="client_include/client_details_remise.php?num_remise=<?php echo $impaye['num_remise']; ?>"><?php echo $impaye['num_remise']; ?></a></td>
<!--                            <td>--><?php //echo htmlspecialchars($impaye['num_remise']); ?><!--</td>-->
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
            <form method="POST" action="">
                <div class="export-options">
                    <span>Exportation : </span>
                    <label class="custom-radio"><input type="radio" name="format" value="xls"/>XLS</label>
                    <label class="custom-radio"><input type="radio" name="format" value="csv"/>CSV</label>
                    <label class="custom-radio"><input type="radio" name="format" value="pdf"/>PDF</label>
                    <input type="hidden" name="export" value="3">
                    <button type="submit">Exporter</button>
                    <a href="client_include/impaye.php?id=<?php echo $idUtilisateur; ?>">
                        <span>Voir plus</span></a>
                </div>
            </form>
        </div>
</section>





<!------------------------ EVOLUTION ----------------------->
<section class="container-evolution" id="graphes">
    <aside class="gauche-evolution">
        <h1>Évolution des impayés</h1>

        <!-- Type de sélection -->

        <form method="POST" action="">
            <h2>Type de sélection</h2>
            <!-- Boutons radio pour choisir entre 'Date à Date' ou 'Date et Durée' -->
            <div class="radio-group">
                <input type="radio" id="choix-date" name="choix_periode" value="date_a_date" checked>
                <label for="choix-date">Début:</label>
                <input type="date" name="date_debut_graphe" value="<?php echo $dateDebut_graphe; ?>">
                <span></span>
                <label for="date_fin_graphe">Fin:</label>
                <input type="date" name="date_fin_graphe" value="<?php echo $dateFin_graphe; ?>">
            </div>
            <br>
            <div class="radio-group">
                <input type="radio" id="choix-duree" name="choix_periode" value="date_et_duree">
                <label for="duree_graphe">Durée (mois):</label>
                <input type="number" name="duree_graphe" value="<?php echo $duree_graphe; ?>">
                <span></span>
                <label for="choix-duree">Fin:</label>
                <input type="date" name="date_fin_graphe_duree" value="<?php echo $dateFin_graphe_duree; ?>">


            </div>

            <!-- Section du format du graphique -->
            <h2>Format:</h2>
            <div class="form-format">
                <div class="radio-group">
                    <input type="radio" id="histogramme" name="format_graphique" value="histogramme" checked>
                    <label for="histogramme">Histogramme</label>
                    <span></span>
                    <input type="radio" id="secteurs" name="format_graphique" value="secteurs">
                    <label for="secteurs">Secteurs</label>
                </div>
            </div>
            <br>
            <button type="submit">Générer</button>
        </form>
        <form method="POST" action="client_include/export_graphe.php">
            <div class="export-options">
                <span>Exportation : </span>
                <label class="custom-radio"><input type="radio" name="format" value="xls"/>XLS</label>
                <label class="custom-radio"><input type="radio" name="format" value="csv"/>CSV</label>
                <label class="custom-radio"><input type="radio" name="format" value="pdf"/>PDF</label>
                <input type="hidden" name="export" value="4">
                <button type="submit">Exporter</button>
            </div>
        </form>
    </aside>
    <aside class="droite-evolution"><canvas id="impayesChart" width="100" height="100"></canvas></aside>



</section>

<section class="stats">
    <aside class="gauche-stats">
        <h2>Comparaison Solde & Impayés</h2>
        <canvas id="impayeSolde" width="100" height="50">
    </aside>
    <aside class="droite-stats">
        <h2>Évolution du solde</h2>
        <canvas id="evolutionSolde" width="100" height="100">
    </aside>

</section>

<?php include ("footer.inc.php"); ?>

<div id="impayes-data" style="display:none;"><?php echo json_encode($impayesData); ?></div>
<div id="format-graphique" style="display:none;"><?php echo $formatGraphique; ?></div>

<div id="impayes-data" style="display:none;"><?php echo json_encode($impayeComparaison); ?></div>
<div id="format-graphique" style="display:none;"><?php echo 'secteurs'; ?></div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="client_include/chart.js"></script>

<?php
$chiffreAffaire = [];
$montantImpayes = [];

foreach ($impayeComparaison as $row) {
    $chiffreAffaire[] = $row['chiffre_affaire_total'];
    $montantImpayes[] = $row['montant_impayes'];
}

// Calcule le total des chiffres d'affaires et des impayés
$totalChiffreAffaire = array_sum($chiffreAffaire);
$totalMontantImpayes = array_sum($montantImpayes);

// Prépare les données pour le diagramme
$data = [$totalChiffreAffaire, $totalMontantImpayes];
$labels = ['Chiffre d\'affaire', 'Impayés'];
?>
<script>
    var ctx = document.getElementById('impayeSolde').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'pie', // Type de graphique
        data: {
            labels: <?php echo json_encode($labels); ?>, // Les étiquettes (labels)
            datasets: [{
                label: 'Montant total',
                data: <?php echo json_encode($data); ?>, // Données pour le graphique
                backgroundColor: [
                    'rgba(124, 98, 74, 0.2)',
                    'rgba(255, 99, 132, 0.2)'
                ],
                borderColor: [
                    '#4D1E10',
                    'rgba(255, 99, 132, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,

        }
    });
</script>




<?php
$mois = [];
$solde = [];
foreach ($soldeMois as $item) {
    $mois[] = $item['mois'];
    $solde[] = (float)$item['solde'];
}
?>
<script>
    var ctx = document.getElementById('evolutionSolde').getContext('2d');
    var soldeChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($mois); ?>,
            datasets: [{
                label: 'Montant',
                data: <?php echo json_encode($solde); ?>,
                backgroundColor: 'rgba(137, 34, 11, 0.2)',
                borderColor: '#89220B',
                borderWidth: 1
            }]
        },
        options: {
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        color: '#e0c7b8',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Mois',
                        color: '#e0c7b8',
                    },
                    ticks: {
                        color: '89220B'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Solde (€)',
                        color: '#e0c7b8'
                    },
                    ticks: {
                        color: '89220B'
                    }
                }
            }
        }
    });
</script>


</body>
</html>



