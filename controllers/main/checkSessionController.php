<?php

/**
 * Controlador para verificar el estado de la sesión
 * Retorna información sobre el tiempo restante de sesión
 * IMPORTANTE: Este endpoint NO renueva la sesión, solo lee el estado
 */

include_once '../../config/session.php';

// Inicializar sesión
SessionManager::initSession();

// Verificar si la sesión expiró
$expired = false;
if (!isset($_SESSION['email'])) {
  $expired = true;
}

// Verificar expiración por tiempo (SIN actualizar timestamp)
$sessionExpired = SessionManager::isSessionExpired();

// Preparar respuesta
$response = array(
  'expired' => $expired || $sessionExpired,
  'remaining' => SessionManager::getRemainingTime(),
  'remaining_seconds' => SessionManager::getRemainingSeconds(),
  'status' => 'active'
);

// Si la sesión expiró, actualizar el estado
if ($expired || $sessionExpired) {
  $response['status'] = 'expired';
}

// Enviar respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);
