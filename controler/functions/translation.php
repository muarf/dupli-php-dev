<?php
/**
 * Module de traduction pour l'application Duplicator
 * 
 * Ce module gère la traduction des textes de l'interface utilisateur
 * en français et en anglais.
 */

// Configuration des langues supportées
define('SUPPORTED_LANGUAGES', ['fr', 'en']);
define('DEFAULT_LANGUAGE', 'fr');

// Variable globale pour la langue actuelle
$current_language = DEFAULT_LANGUAGE;

// Charger la langue depuis la session ou les paramètres
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['lang']) && in_array($_GET['lang'], SUPPORTED_LANGUAGES)) {
    $current_language = $_GET['lang'];
    $_SESSION['language'] = $current_language;
} elseif (isset($_SESSION['language']) && in_array($_SESSION['language'], SUPPORTED_LANGUAGES)) {
    $current_language = $_SESSION['language'];
} else {
    // Détecter la langue du navigateur
    $browser_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'fr', 0, 2);
    if (in_array($browser_lang, SUPPORTED_LANGUAGES)) {
        $current_language = $browser_lang;
    }
    $_SESSION['language'] = $current_language;
}

// Tableau des traductions
$translations = [];

/**
 * Charger les traductions depuis les fichiers de langue
 * 
 * @param string $lang Code de la langue (fr, en)
 * @return array Tableau des traductions
 */
function load_translations(string $lang): array
{
    global $translations;
    
    $lang_file = __DIR__ . "/../../lang/{$lang}.php";
    
    if (file_exists($lang_file)) {
        $translations = include $lang_file;
    } else {
        // Fallback vers le français si la langue n'existe pas
        $fallback_file = __DIR__ . "/../../lang/fr.php";
        if (file_exists($fallback_file)) {
            $translations = include $fallback_file;
        } else {
            $translations = [];
        }
    }
    
    return $translations;
}

/**
 * Fonction de traduction principale
 * 
 * @param string $key Clé de traduction
 * @param array $params Paramètres pour le remplacement (optionnel)
 * @return string Texte traduit
 */
function _($key, array $params = []): string
{
    global $current_language, $translations;
    
    // Charger les traductions si pas encore fait
    if (empty($translations)) {
        $translations = load_translations($current_language);
    }
    
    // Récupérer la traduction
    $translation = $translations[$key] ?? $key;
    
    // Remplacer les paramètres si fournis
    if (!empty($params)) {
        foreach ($params as $param_key => $param_value) {
            $translation = str_replace("{{$param_key}}", $param_value, $translation);
        }
    }
    
    return $translation;
}

/**
 * Obtenir la langue actuelle
 * 
 * @return string Code de la langue actuelle
 */
function get_current_language(): string
{
    global $current_language;
    return $current_language;
}

/**
 * Obtenir la liste des langues supportées
 * 
 * @return array Liste des codes de langue
 */
function get_supported_languages(): array
{
    return SUPPORTED_LANGUAGES;
}

/**
 * Obtenir le nom de la langue en français
 * 
 * @param string $lang_code Code de la langue
 * @return string Nom de la langue
 */
function get_language_name(string $lang_code): string
{
    $names = [
        'fr' => 'Français',
        'en' => 'English'
    ];
    
    return $names[$lang_code] ?? $lang_code;
}

/**
 * Générer l'URL pour changer de langue
 * 
 * @param string $lang_code Code de la langue
 * @return string URL complète
 */
function get_language_url(string $lang_code): string
{
    $current_url = $_SERVER['REQUEST_URI'];
    $parsed_url = parse_url($current_url);
    
    // Supprimer le paramètre lang existant
    if (isset($parsed_url['query'])) {
        parse_str($parsed_url['query'], $query_params);
        unset($query_params['lang']);
    } else {
        $query_params = [];
    }
    
    // Ajouter le nouveau paramètre lang
    $query_params['lang'] = $lang_code;
    
    // Reconstruire l'URL
    $new_query = http_build_query($query_params);
    $new_url = $parsed_url['path'];
    if (!empty($new_query)) {
        $new_url .= '?' . $new_query;
    }
    
    return $new_url;
}

// Charger les traductions au chargement du module
load_translations($current_language);

// Log de l'initialisation
if (function_exists('log_info')) {
    log_info("Module de traduction initialisé - Langue: {$current_language}", 'translation.php');
}
?>