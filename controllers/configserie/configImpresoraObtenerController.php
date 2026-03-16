<?php
require_once '../../config/session.php';

$configPath = dirname(__DIR__, 2) . '/empresa.ini';
$config = parse_ini_file($configPath, true);
$impresora = $config['empresa']['impresora_ticket'] ?? 'XP-80C';

echo json_encode(['impresora' => $impresora]);
