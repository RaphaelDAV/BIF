<?php

function exportDataGraphe($data, $format, $title) {
    if ($format == 'xls') {
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$title.xls\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        // Écrire les données dans le format XLS
        echo "<table border='1'>";
        echo "<tr><th>Mois</th><th>Nombre d'impayés</th></tr>";
        foreach ($data as $row) {
            echo "<tr><td>{$row['mois']}</td><td>{$row['nombre_impayes']}</td></tr>";
        }
        echo "</table>";
        exit;
    } elseif ($format == 'csv') {
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=\"$title.csv\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        // Écrire les données dans le format CSV
        $output = fopen("php://output", "w");
        fputcsv($output, ['Mois', 'Nombre d\'impayés']); // En-têtes
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }
}

$testData = [
    ['mois' => 'Janvier', 'nombre_impayes' => 5],
    ['mois' => 'Février', 'nombre_impayes' => 3],
];
exportDataGraphe($testData, 'csv', 'Export des impayes');

?>
