<?php
/**
 * Script pour extraire tous les textes français non traduits du projet
 * et les ajouter aux fichiers de traduction
 */

require_once __DIR__ . '/controler/functions/i18n.php';

class TranslationExtractor {
    private $translations = [];
    private $basePath;
    private $excludedFiles = [
        'extract_translations.php',
        'composer.json',
        'composer.lock'
    ];
    
    public function __construct($basePath = __DIR__) {
        $this->basePath = $basePath;
        $this->loadExistingTranslations();
    }
    
    private function loadExistingTranslations() {
        $translationFile = $this->basePath . '/translations/fr.json';
        if (file_exists($translationFile)) {
            $content = file_get_contents($translationFile);
            $this->translations = json_decode($content, true) ?: [];
        }
    }
    
    public function extractFromFile($filePath) {
        $content = file_get_contents($filePath);
        $newTranslations = [];
        
        // Patterns pour trouver les textes français
        $patterns = [
            // echo "texte"
            '/echo\s+["\']([A-Za-zÀ-ÿ\s\.,!?;:()\-]{10,})["\']/u',
            // print "texte"
            '/print\s+["\']([A-Za-zÀ-ÿ\s\.,!?;:()\-]{10,})["\']/u',
            // return "texte"
            '/return\s+["\']([A-Za-zÀ-ÿ\s\.,!?;:()\-]{10,})["\']/u',
            // throw new Exception("texte")
            '/throw\s+new\s+Exception\s*\(\s*["\']([A-Za-zÀ-ÿ\s\.,!?;:()\-]{10,})["\']/u',
            // 'message' => 'texte'
            '/["\']message["\']\s*=>\s*["\']([A-Za-zÀ-ÿ\s\.,!?;:()\-]{10,})["\']/u',
            // 'error' => 'texte'
            '/["\']error["\']\s*=>\s*["\']([A-Za-zÀ-ÿ\s\.,!?;:()\-]{10,})["\']/u',
            // 'success' => 'texte'
            '/["\']success["\']\s*=>\s*["\']([A-Za-zÀ-ÿ\s\.,!?;:()\-]{10,})["\']/u',
        ];
        
        foreach ($patterns as $pattern) {
            preg_match_all($pattern, $content, $matches);
            foreach ($matches[1] as $match) {
                $text = trim($match);
                if ($this->isFrenchText($text) && !$this->isAlreadyTranslated($text)) {
                    $key = $this->generateKey($text, $filePath);
                    $newTranslations[$key] = $text;
                }
            }
        }
        
        return $newTranslations;
    }
    
    private function isFrenchText($text) {
        // Vérifier si le texte contient des caractères français typiques
        $frenchChars = ['à', 'â', 'ä', 'é', 'è', 'ê', 'ë', 'ï', 'î', 'ô', 'ö', 'ù', 'û', 'ü', 'ÿ', 'ç', 'À', 'Â', 'Ä', 'É', 'È', 'Ê', 'Ë', 'Ï', 'Î', 'Ô', 'Ö', 'Ù', 'Û', 'Ü', 'Ÿ', 'Ç'];
        
        foreach ($frenchChars as $char) {
            if (strpos($text, $char) !== false) {
                return true;
            }
        }
        
        // Vérifier des mots français courants
        $frenchWords = ['erreur', 'succès', 'changement', 'machine', 'tirage', 'impossible', 'fichier', 'données', 'paramètres', 'manquant', 'trouvé', 'ajouté', 'supprimé', 'modifié', 'mise', 'jour', 'lors', 'de', 'la', 'du', 'des', 'avec', 'pour', 'dans', 'sur', 'par', 'est', 'sont', 'ont', 'peut', 'doit', 'être', 'avoir', 'faire', 'aller', 'venir', 'voir', 'savoir', 'pouvoir', 'vouloir', 'falloir', 'devoir', 'prendre', 'donner', 'mettre', 'dire', 'parler', 'entendre', 'comprendre', 'connaître', 'croire', 'penser', 'trouver', 'chercher', 'demander', 'répondre', 'écrire', 'lire', 'apprendre', 'enseigner', 'travailler', 'jouer', 'manger', 'boire', 'dormir', 'vivre', 'mourir', 'naître', 'grandir', 'vieillir', 'changer', 'devenir', 'rester', 'partir', 'arriver', 'revenir', 'rentrer', 'sortir', 'entrer', 'monter', 'descendre', 'passer', 'traverser', 'tourner', 'marcher', 'courir', 'sauter', 'tomber', 'lever', 'baisser', 'ouvrir', 'fermer', 'commencer', 'finir', 'continuer', 'arrêter', 'reprendre', 'recommencer', 'essayer', 'réussir', 'échouer', 'gagner', 'perdre', 'acheter', 'vendre', 'payer', 'coûter', 'valoir', 'prix', 'argent', 'euro', 'euros', 'franc', 'francs', 'centime', 'centimes'];
        
        $textLower = strtolower($text);
        foreach ($frenchWords as $word) {
            if (strpos($textLower, $word) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    private function isAlreadyTranslated($text) {
        // Vérifier si ce texte est déjà dans les traductions existantes
        foreach ($this->translations as $category => $translations) {
            if (is_array($translations)) {
                foreach ($translations as $key => $value) {
                    if ($value === $text) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
    
    private function generateKey($text, $filePath) {
        // Générer une clé basée sur le contexte du fichier et le texte
        $relativePath = str_replace($this->basePath . '/', '', $filePath);
        $pathParts = explode('/', $relativePath);
        $fileName = pathinfo($relativePath, PATHINFO_FILENAME);
        
        // Nettoyer le texte pour créer une clé
        $cleanText = strtolower($text);
        $cleanText = preg_replace('/[^a-z0-9\s]/', '', $cleanText);
        $cleanText = preg_replace('/\s+/', '_', trim($cleanText));
        $cleanText = substr($cleanText, 0, 30); // Limiter la longueur
        
        // Déterminer la catégorie basée sur le fichier
        $category = 'common';
        if (strpos($relativePath, 'admin') !== false) {
            $category = 'admin';
        } elseif (strpos($relativePath, 'error') !== false) {
            $category = 'error';
        } elseif (strpos($relativePath, 'imposition') !== false) {
            $category = 'imposition';
        } elseif (strpos($relativePath, 'tirage') !== false) {
            $category = 'tirage';
        }
        
        return $category . '.' . $cleanText;
    }
    
    public function scanProject() {
        $newTranslations = [];
        
        // Scanner les fichiers PHP
        $phpFiles = $this->getPhpFiles();
        foreach ($phpFiles as $file) {
            $translations = $this->extractFromFile($file);
            $newTranslations = array_merge($newTranslations, $translations);
        }
        
        return $newTranslations;
    }
    
    private function getPhpFiles() {
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->basePath));
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $relativePath = str_replace($this->basePath . '/', '', $file->getPathname());
                if (!in_array(basename($relativePath), $this->excludedFiles)) {
                    $files[] = $file->getPathname();
                }
            }
        }
        
        return $files;
    }
    
    public function saveTranslations($newTranslations) {
        // Organiser les nouvelles traductions par catégorie
        $organized = [];
        foreach ($newTranslations as $key => $value) {
            $parts = explode('.', $key, 2);
            $category = $parts[0];
            $subKey = $parts[1] ?? $key;
            
            if (!isset($organized[$category])) {
                $organized[$category] = [];
            }
            $organized[$category][$subKey] = $value;
        }
        
        // Fusionner avec les traductions existantes
        foreach ($organized as $category => $translations) {
            if (!isset($this->translations[$category])) {
                $this->translations[$category] = [];
            }
            $this->translations[$category] = array_merge($this->translations[$category], $translations);
        }
        
        // Sauvegarder
        $translationFile = $this->basePath . '/translations/fr.json';
        $json = json_encode($this->translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($translationFile, $json);
        
        return count($newTranslations);
    }
}

// Exécution du script
if (php_sapi_name() === 'cli' || isset($_GET['run'])) {
    $extractor = new TranslationExtractor();
    $newTranslations = $extractor->scanProject();
    
    if (!empty($newTranslations)) {
        $count = $extractor->saveTranslations($newTranslations);
        echo "Ajout de $count nouvelles traductions au fichier fr.json\n";
        
        // Afficher les nouvelles traductions
        foreach ($newTranslations as $key => $value) {
            echo "- $key: $value\n";
        }
    } else {
        echo "Aucune nouvelle traduction trouvée.\n";
    }
}
?>