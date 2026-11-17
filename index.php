<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use iutnc\deefy\dispatch\Dispatcher;
use iutnc\deefy\repository\DeefyRepository;

// Initialisation de la configuration de la base de données
$configFile = __DIR__ . '/config/db.config.ini';
if (file_exists($configFile)) {
    try {
        DeefyRepository::setConfig($configFile);
    } catch (\Exception $e) {
        // En cas d'erreur de lecture du fichier de conf, on continue pour laisser l'interface s'afficher
    }
}

// Lancement de l'application via le Dispatcher
$dispatcher = new Dispatcher();
$dispatcher->run();
