<?php
header('Content-Type: application/json');
echo json_encode([
    'php_version' => phpversion(),
    'loaded_extensions' => get_loaded_extensions(),
    'pdo_drivers' => PDO::getAvailableDrivers(),
]);
