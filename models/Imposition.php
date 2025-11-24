<?php

use setasign\Fpdi\TcpdfFpdi as TCPDI;

class Imposition
{
    private $pdf;
    private $previewPdf;
    private $sourceFile;
    private $settings;

    public function __construct($sourceFile, $settings = [])
    {
        $this->sourceFile = $sourceFile;
        // Paramètres par défaut
        $this->settings = array_merge([
            'scale' => 100, // Pourcentage
            'gutter_x' => 0, // mm
            'gutter_y' => 0, // mm
            'crop_marks' => false,
            'crop_mark_len' => 2, // mm (Court pour style discret)
            'crop_mark_width' => 0.1, // mm
            'orientation' => 'L', // L = Landscape (A3 paysage)
            'n_up' => 2, // Nombre de poses (2, 4, 8)
            'duplex' => true, // Mode Recto-Verso par défaut
            'preview_mode' => false,
            'add_page_numbers_in_gutters' => false,
            'addPageNumberCallback' => null // Callback pour ajouter les numéros de pages
        ], $settings);

        $this->pdf = new TCPDI();
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
        if ($this->settings['preview_mode']) {
            $this->previewPdf = new TCPDI();
            $this->previewPdf->setPrintHeader(false);
            $this->previewPdf->setPrintFooter(false);
        }
    }

    public function process($outputFile, $previewOutputFile = null)
    {
        // 1. Initialisation et import
        $pageCount = $this->pdf->setSourceFile($this->sourceFile);
        if ($this->previewPdf) {
            $this->previewPdf->setSourceFile($this->sourceFile);
        }
        
        $nUp = intval($this->settings['n_up']);
        if ($nUp < 1) $nUp = 2;
        
        $duplex = !empty($this->settings['duplex']);

        // Orientation automatique
        if ($nUp == 4) {
            $this->settings['orientation'] = 'P'; // Portrait pour 4 poses
        } else {
            $this->settings['orientation'] = 'L'; // Paysage pour 2 et 8 poses
        }

        // Définition de la grille (cols x rows)
        if ($nUp == 2) {
            $cols = 2; $rows = 1;
        } elseif ($nUp == 4) {
            $cols = 2; $rows = 2;
        } elseif ($nUp == 8) {
            $cols = 4; $rows = 2;
        } else {
            $cols = ceil(sqrt($nUp)); 
            $rows = ceil($nUp / $cols);
        }

        // Cut & Stack : Calcul de la profondeur de la pile
        // Si Duplex, on consomme 2 pages source pour 1 feuille physique (Recto+Verso)
        // Donc la pile monte moins vite en nombre de feuilles.
        $pagesPerSheet = $nUp * ($duplex ? 2 : 1);
        // On utilise ceil, mais attention si pageCount n'est pas un multiple parfait.
        // Le nombre de feuilles PHYSIQUES dans la pile :
        $stackDepth = ceil($pageCount / $pagesPerSheet);

        // Configuration format sortie A3
        $a3Width = 420;
        $a3Height = 297;

        if ($this->settings['orientation'] == 'P') {
            $a3Width = 297;
            $a3Height = 420;
        }

        // Boucle sur les feuilles à générer
        for ($i = 1; $i <= $stackDepth; $i++) {
            
            // --- RECTO ---
            $this->pdf->AddPage($this->settings['orientation'], 'A3');
            if ($this->previewPdf) {
                $this->previewPdf->AddPage($this->settings['orientation'], 'A3');
            }
            
            for ($pos = 0; $pos < $nUp; $pos++) {
                // Calcul de la page source pour cette position
                if ($duplex) {
                    // Formule Recto-Verso :
                    // Pile n° 'pos'. Chaque pile a 'stackDepth' feuilles.
                    // Chaque feuille consomme 2 pages (Recto, Verso).
                    // Page de début de la pile 'pos' = pos * (stackDepth * 2)
                    // Dans la pile, on est à la feuille 'i'.
                    // Page Recto = DebutPile + (i * 2) - 1 (car page 1 est sur feuille 1)
                    // Page Verso = DebutPile + (i * 2)
                    
                    // Exemple : pos=0, i=1 -> (0) + 2 - 1 = 1. Correct.
                    $pageNo = ($pos * $stackDepth * 2) + ($i * 2) - 1;
                } else {
                    // Formule Simplex
                    $pageNo = $i + ($pos * $stackDepth);
                }

                if ($pageNo <= $pageCount) {
                    $currentRow = floor($pos / $cols);
                    $currentCol = $pos % $cols;

                    $this->placePage($pageNo, $currentCol, $currentRow, $cols, $rows, $a3Width, $a3Height);
                }
            }

            // --- VERSO (Seulement si Duplex) ---
            if ($duplex) {
                $this->pdf->AddPage($this->settings['orientation'], 'A3');
                if ($this->previewPdf) {
                    $this->previewPdf->AddPage($this->settings['orientation'], 'A3');
                }
                
                for ($pos = 0; $pos < $nUp; $pos++) {
                    // Page Verso correspondant à la même position "logique" dans la pile
                    $pageNo = ($pos * $stackDepth * 2) + ($i * 2);

                    if ($pageNo <= $pageCount) {
                        // Logique Miroir pour le Verso
                        // On prend la position 'pos' (qui correspond à une case de la grille)
                        // et on cherche où l'imprimer physiquement pour qu'elle soit au dos.
                        
                        $origRow = floor($pos / $cols);
                        $origCol = $pos % $cols;
                        
                        // Inversion des colonnes (Miroir axe vertical)
                        // Col 0 devient Col Max, Col 1 devient Col Max-1...
                        $mirrorCol = ($cols - 1) - $origCol;
                        $mirrorRow = $origRow; // La ligne ne change pas si on tourne la page comme un livre

                        $this->placePage($pageNo, $mirrorCol, $mirrorRow, $cols, $rows, $a3Width, $a3Height);
                    }
                }
            }
        }

        // S'assurer que le répertoire existe et normaliser le chemin
        $outputDir = dirname($outputFile);
        if (!is_dir($outputDir)) {
            @mkdir($outputDir, 0755, true);
        }
        // Utiliser realpath() pour obtenir le chemin réel (résout la casse)
        $realOutputDir = realpath($outputDir);
        if ($realOutputDir !== false) {
            $outputFile = $realOutputDir . DIRECTORY_SEPARATOR . basename($outputFile);
        }
        $this->pdf->Output($outputFile, 'F');
        
        if ($this->previewPdf && $previewOutputFile) {
            $previewDir = dirname($previewOutputFile);
            if (!is_dir($previewDir)) {
                @mkdir($previewDir, 0755, true);
            }
            $realPreviewDir = realpath($previewDir);
            if ($realPreviewDir !== false) {
                $previewOutputFile = $realPreviewDir . DIRECTORY_SEPARATOR . basename($previewOutputFile);
            }
            $this->previewPdf->Output($previewOutputFile, 'F');
        }
    }

    public function getPreviewPdf()
    {
        return $this->previewPdf;
    }

    private function placePage($pageNo, $colIndex, $rowIndex, $totalCols, $totalRows, $sheetWidth, $sheetHeight)
    {
        // Import de la page
        try {
            $tplIdx = $this->pdf->importPage($pageNo);
            $previewTplIdx = null;
            if ($this->previewPdf) {
                $previewTplIdx = $this->previewPdf->importPage($pageNo);
            }
        } catch (\Exception $e) {
            return; // Page invalide ou erreur
        }
        
        $size = $this->pdf->getTemplateSize($tplIdx);

        // Calcul des dimensions redimensionnées
        $scaleFactor = 1;
        
        // Mode 1 : Dimensions cibles définies (prioritaire)
        if (!empty($this->settings['target_width']) && $this->settings['target_width'] > 0) {
             $scaleFactor = $this->settings['target_width'] / $size['width'];
        } 
        elseif (!empty($this->settings['target_height']) && $this->settings['target_height'] > 0) {
             $scaleFactor = $this->settings['target_height'] / $size['height'];
        }
        // Mode 2 : Pourcentage
        else {
            $scaleFactor = $this->settings['scale'] / 100;
        }

        $finalW = $size['width'] * $scaleFactor;
        $finalH = $size['height'] * $scaleFactor;

        $gutterX = floatval($this->settings['gutter_x']);
        $gutterY = floatval($this->settings['gutter_y']);

        // Calcul du bloc total de contenu (toutes les poses + gouttières INTERNES)
        $totalContentWidth = ($totalCols * $finalW) + (($totalCols - 1) * $gutterX);
        $totalContentHeight = ($totalRows * $finalH) + (($totalRows - 1) * $gutterY);

        // Point de départ global pour centrer le bloc sur la feuille
        $globalStartX = ($sheetWidth - $totalContentWidth) / 2;
        $globalStartY = ($sheetHeight - $totalContentHeight) / 2;

        // Position X, Y de cette pose spécifique
        $x = $globalStartX + ($colIndex * ($finalW + $gutterX));
        $y = $globalStartY + ($rowIndex * ($finalH + $gutterY));

        // Ajout de la page dans le PDF final
        $this->pdf->useTemplate($tplIdx, $x, $y, $finalW, $finalH);

        // Ajout de la page dans le PDF preview
        if ($this->previewPdf && $previewTplIdx) {
            $this->previewPdf->useTemplate($previewTplIdx, $x, $y, $finalW, $finalH);

            // Add page number to preview if callback is provided
            if ($this->settings['addPageNumberCallback'] && is_callable($this->settings['addPageNumberCallback'])) {
                call_user_func($this->settings['addPageNumberCallback'], $this->previewPdf, $pageNo, $x, $y, $finalW, $finalH, 0);
            }
        }

        // Numéros dans les gouttières (pour preview et final si activé)
        if ($this->settings['add_page_numbers_in_gutters']) {
            $this->addPageNumberInGutter($pageNo, $x, $y, $finalW, $finalH, $colIndex, $rowIndex, $totalCols, $totalRows, $gutterX, $gutterY, $globalStartX, $globalStartY);
        }

        // Traits de coupe
        if ($this->settings['crop_marks']) {
            $this->drawCropMarks($x, $y, $finalW, $finalH);
        }
    }

    private function addPageNumberInGutter($pageNo, $x, $y, $w, $h, $colIndex, $rowIndex, $totalCols, $totalRows, $gutterX, $gutterY, $globalStartX, $globalStartY)
    {
        // Utiliser le PDF preview si disponible, sinon le PDF final
        $targetPdf = $this->previewPdf ? $this->previewPdf : $this->pdf;
        
        $targetPdf->setAutoPageBreak(false);
        $targetPdf->SetFont('helvetica', '', 6); // Police petite (taille 6)
        $targetPdf->SetTextColor(0, 0, 0); // Noir
        
        // Logique : Impaire (Recto) -> Bas Droite, Paire (Verso) -> Bas Gauche
        // Positionnement : Dans la gouttière, juste à côté du trait de coupe vertical
        
        $isOdd = ($pageNo % 2 != 0);
        
        if ($isOdd) {
            // Page Impaire : Bas Droite
            // On place le numéro dans la gouttière à droite de la page, aligné en bas
            // Si on est sur la dernière colonne, on le met à droite quand même (marge ext)
            
            $posX = $x + $w - 1; // Correction: 1mm à droite (était -2)
            $posY = $y + $h;     // Correction: 1mm plus haut (était +1)
            
            // Si c'est la dernière colonne et pas de gouttière à droite, on le met quand même
            // L'utilisateur a parlé de "gouttière", donc on suppose qu'il y a de l'espace
            
            $targetPdf->SetXY($posX, $posY);
            $targetPdf->Cell(10, 4, (string)$pageNo, 0, 0, 'L', false);
        } else {
            // Page Paire : Bas Gauche
            // On place le numéro dans la gouttière à gauche de la page, aligné en bas
            
            $posX = $x - 9; // Correction: 1mm à gauche (était -8)
            $posY = $y + $h;     // Correction: 1mm plus haut (était +1)
            
            $targetPdf->SetXY($posX, $posY);
            $targetPdf->Cell(10, 4, (string)$pageNo, 0, 0, 'R', false);
        }
    }

    private function drawCropMarks($x, $y, $w, $h)
    {
        $len = $this->settings['crop_mark_len'];
        $this->pdf->SetLineWidth($this->settings['crop_mark_width']);
        $this->pdf->SetDrawColor(0, 0, 0); 

        $offset = 1; // mm d'espace vide avant le trait (Réduit pour style discret)

        // Haut-Gauche
        $this->pdf->Line($x - $offset - $len, $y, $x - $offset, $y); 
        $this->pdf->Line($x, $y - $offset - $len, $x, $y - $offset); 

        // Haut-Droite
        $this->pdf->Line($x + $w + $offset, $y, $x + $w + $offset + $len, $y); 
        $this->pdf->Line($x + $w, $y - $offset - $len, $x + $w, $y - $offset); 

        // Bas-Gauche
        $this->pdf->Line($x - $offset - $len, $y + $h, $x - $offset, $y + $h); 
        $this->pdf->Line($x, $y + $h + $offset, $x, $y + $h + $offset + $len); 

        // Bas-Droite
        $this->pdf->Line($x + $w + $offset, $y + $h, $x + $w + $offset + $len, $y + $h); 
        $this->pdf->Line($x + $w, $y + $h + $offset, $x + $w, $y + $h + $offset + $len); 
    }
}

