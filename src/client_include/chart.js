// Convertir les données PHP en JavaScript
var impayesData = JSON.parse(document.getElementById('impayes-data').textContent);

// Créer des tableaux pour les labels et les valeurs
var labels = [];
var valeurs = [];

if ('histogramme' === document.getElementById('format-graphique').textContent) {
    impayesData.forEach(function(row) {
        labels.push(row.mois); // Récupérer le mois (au format "%Y-%m")
        valeurs.push(row.nombre_impayes); // Nombre d'impayés par mois
    });
} else {
    // Format secteurs (par type d'impayé)
    impayesData.forEach(function(row) {
        labels.push(row.libelle_impaye); // Nom du type d'impayé
        valeurs.push(row.nombre_impayes); // Nombre d'impayés par type
    });
}

// Configuration du graphique
var ctx = document.getElementById('impayesChart').getContext('2d');
var impayesChart = new Chart(ctx, {
    type: document.getElementById('format-graphique').textContent === "secteurs" ? "pie" : "bar", // Type de graphique
    data: {
        labels: labels,
        datasets: [{
            label: 'Nombre d\'impayés',
            data: valeurs,
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
