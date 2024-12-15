<?php

use fpdf\FPDF;
if (file_exists('../include/fpdf/fpdf.php')) {
    require_once('../include/fpdf/fpdf.php');
} elseif (file_exists('../../include/fpdf/fpdf.php')) {
    require_once('../../include/fpdf/fpdf.php');
}

function exportDataImpaye($data, $format, $title) {
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
                    <th>TYPE D'IMPAYE</th>
                    <th>MONTANT</th>
                </tr>";
        foreach ($data as $item) {
            echo "<tr>
                    <td>" . date('d/m/Y', strtotime($item['date_traitement'])) . "</td>
                    <td>" . htmlspecialchars($item['num_remise']) . "</td>
                    <td>" . htmlspecialchars($item['libelle_impaye']) . "</td>
                    <td>" . htmlspecialchars(number_format($item['montant'], 2, ',', ' ')) . " €</td>
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
        fputcsv($output, ['Date de traitement', 'Numéro de remise', 'Type d\'impayé', 'Montant']);
        foreach ($data as $item) {
            fputcsv($output, [
                date('d/m/Y', strtotime($item['date_traitement'])),
                htmlspecialchars($item['num_remise']),
                htmlspecialchars($item['libelle_impaye']),
                htmlspecialchars(number_format($item['montant'], 2, ',', ' '))
            ]);
        }
        fclose($output);
        exit;
    } elseif ($format === 'pdf') {
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
        $pdf->Cell(50, 10, 'DATE DE TRAITEMENT', 1);
        $pdf->Cell(50, 10, 'NUMERO DE REMISE', 1);
        $pdf->Cell(50, 10, 'LIBELLE DE L\'IMPAYE', 1);
        $pdf->Cell(50, 10, 'MONTANT', 1);
        $pdf->Ln();

        // Données du tableau
        $pdf->SetFont('Arial', '', 12);
        foreach ($data as $item) {
            $pdf->Cell(50, 10, date('d/m/Y', strtotime($item['date_traitement'])), 1);
            $pdf->Cell(50, 10, htmlspecialchars($item['num_remise']), 1);
            $pdf->Cell(50, 10, htmlspecialchars($item['libelle_impaye']), 1);
            $pdf->Cell(50, 10, htmlspecialchars(number_format($item['montant'], 2, ',', ' ')) . ' euros', 1);
            $pdf->Ln();
        }

        // Sortie du PDF
        $pdf->Output('D', 'export.pdf');
        exit;
    }
}


?>