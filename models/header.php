<?php
// Inclure le système de traduction principal
require_once __DIR__ . '/../controler/functions/i18n.php';

function headerAction($page){ 
	// Initialiser le système de traduction
	I18nManager::getInstance();
	
	// Récupérer les console wrappers actifs pour le menu
	$console_wrappers = [];
	try {
		require_once __DIR__ . '/../controler/functions/database.php';
		require_once __DIR__ . '/admin/ConsoleWrapperManager.php';
		$db = pdo_connect();
		
		// Récupérer la configuration
		include(__DIR__ . '/../controler/conf.php');
		
		$consoleWrapperManager = new ConsoleWrapperManager($conf);
		$console_wrappers = $consoleWrapperManager->getActiveWrappers();
	} catch (Exception $e) {
		// Ignorer l'erreur si les tables n'existent pas encore
		$console_wrappers = [];
	}
	
	$page = array(
		'page' => $page,
		'console_wrappers' => $console_wrappers
	);
	return template("../view/header.html.php", $page);
}
?>
