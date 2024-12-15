<?php
$chiffreAffaire = [];
$montantImpayes = [];

// Parcourir les résultats et les répartir dans des tableaux séparés
foreach ($impayeComparaison as $row) {
    $chiffreAffaire[] = $row['chiffre_affaire_total'];
    $montantImpayes[] = $row['montant_impayes'];
}

// Générer les données sous format JSON pour les intégrer au script JavaScript
$labels = ['Montant total', 'Impayés'];
?>
<script>
    var ctx = document.getElementById('evolutionSolde').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'bar', // ou 'line', 'pie', etc.
        data: {
            labels: <?php echo json_encode($labels); ?>, // Les étiquettes (labels)
            datasets: [{
                label: 'Chiffre d'affaires',
                data: <?php echo json_encode($chiffreAffaire); ?>, // Les données pour le chiffre d'affaires
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }, {
                label: 'Impayés',
                data: <?php echo json_encode($montantImpayes); ?>, // Les données pour les impayés
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
