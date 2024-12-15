<?php
ob_start(); // Commencer le tampon de sortie
error_reporting(E_ALL);
ini_set('display_errors', 1);

use fpdf\FPDF;

if (file_exists('../include/fpdf/fpdf.php')) {
    require_once('../include/fpdf/fpdf.php');
} elseif (file_exists('../../include/fpdf/fpdf.php')) {
    require_once('../../include/fpdf/fpdf.php');
}

function exportData($data, $format, $title) {
    // Démarrer le tampon de sortie
    ob_start();

    if ($format === 'xls') {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="export.xls"');
        echo "<table>
                <tr>
                    <td colspan='3'>EXTRAIT DU : " . date('d/m/Y') . "</td>
                </tr>
                <tr>
                    <th>DATE DE TRAITEMENT</th>
                    <th>NUMERO DE REMISE</th>
                    <th>SOMME DES TRANSACTIONS</th>
                </tr>";
        foreach ($data as $item) {
            echo "<tr>
                    <td>" . date('d/m/Y', strtotime($item['date_traitement'])) . "</td>
                    <td>" . htmlspecialchars($item['num_remise']) . "</td>
                    <td>" . htmlspecialchars(number_format($item['total_transactions'], 2, ',', ' ')) . " €</td>
                  </tr>";
        }
        echo "</table>";
        exit;
    } elseif ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="export.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, []);
        fputcsv($output, ['EXTRAIT DU  : ' . date('d/m/Y')]);
        fputcsv($output, []);
        fputcsv($output, ['Date de traitement', 'Numéro de remise', 'Somme des transactions']);
        foreach ($data as $item) {
            fputcsv($output, [
                date('d/m/Y', strtotime($item['date_traitement'])),
                htmlspecialchars($item['num_remise']),
                htmlspecialchars(number_format($item['total_transactions'], 2, ',', ' '))
            ]);
        }
        fclose($output);
        exit;
    } elseif ($format === 'pdf') {
        // Assurez-vous qu'il n'y a pas de sortie avant cela
        ob_end_clean(); // Nettoyer le tampon de sortie avant d'envoyer le PDF

        // Création du document PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);

        // Titre
        $pdf->Cell(0, 10, $title, 0, 1, 'C');
        $pdf->Ln(10);

        $pdf->Cell(0, 10, 'EXTRAIT DU : ' . date('d/m/Y'), 0, 1, 'C');
        $pdf->Ln(10);

        // En-têtes du tableau
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(60, 10, 'DATE DE TRAITEMENT', 1);
        $pdf->Cell(60, 10, 'NUMERO DE REMISE', 1);
        $pdf->Cell(70, 10, 'SOMME DES TRANSACTIONS', 1);
        $pdf->Ln();

        // Données du tableau
        $pdf->SetFont('Arial', '', 12);
        foreach ($data as $item) {
            $pdf->Cell(60, 10, date('d/m/Y', strtotime($item['date_traitement'])), 1);
            $pdf->Cell(60, 10, htmlspecialchars($item['num_remise']), 1);
            $pdf->Cell(70, 10, htmlspecialchars(number_format($item['total_transactions'], 2, ',', ' ')) . ' euros', 1);
            $pdf->Ln();
        }

        // Sortie du PDF
        $pdf->Output('D', 'export.pdf');
        exit;
    }

    // Fin du tampon de sortie (dans le cas où il y a une erreur)
    ob_end_flush();
}
