<?php

/**
 * Obtiene la configuración de sesión desde empresa.ini
 * Retorna el tiempo de vida de la sesión en minutos
 */

// Leer configuración desde empresa.ini
$configPath = dirname(dirname(__DIR__)) . '/empresa.ini';
$lifetime_seconds = 1800; // Valor por defecto: 30 minutos

if (file_exists($configPath)) {
  $config = parse_ini_file($configPath, true);
  if (isset($config['empresa']['session_lifetime'])) {
    $lifetime_seconds = (int)$config['empresa']['session_lifetime'];
  }
}

// Convertir a minutos
$lifetime_minutes = round($lifetime_seconds / 60, 1);

// Preparar respuesta
$response = array(
  'lifetime_seconds' => $lifetime_seconds,
  'lifetime_minutes' => $lifetime_minutes
);

// Enviar respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);
