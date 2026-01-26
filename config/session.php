<?php

/**
 * Configuración de sesiones
 * Gestiona el tiempo de vida y seguridad de las sesiones
 */

class SessionManager
{
  /**
   * Obtiene el tiempo de vida de la sesión desde el archivo empresa.ini
   * @return int Tiempo en segundos
   */
  private static function getSessionLifetime(): int
  {
    $configPath = dirname(__DIR__) . '/empresa.ini';

    if (file_exists($configPath)) {
      $config = parse_ini_file($configPath, true);
      if (isset($config['empresa']['session_lifetime'])) {
        return (int)$config['empresa']['session_lifetime'];
      }
    }

    // Valor por defecto si no se encuentra en el archivo: 30 minutos
    return 1800;
  }

  /**
   * Inicializa la sesión con configuración de seguridad
   */
  public static function initSession()
  {
    if (session_status() === PHP_SESSION_NONE) {
      // Configuración de seguridad de la sesión
      ini_set('session.cookie_httponly', 1);
      ini_set('session.use_only_cookies', 1);
      ini_set('session.cookie_secure', 0); // Cambiar a 1 si usas HTTPS

      // Establecer tiempo de vida de la cookie de sesión
      ini_set('session.gc_maxlifetime', self::getSessionLifetime());

      session_start();
    }
  }

  /**
   * Verifica si la sesión ha expirado
   * @return bool true si la sesión expiró, false si aún es válida
   */
  public static function checkSessionExpiration()
  {
    // Verificar si existe el timestamp de última actividad
    if (isset($_SESSION['LAST_ACTIVITY'])) {
      // Calcular tiempo transcurrido desde la última actividad
      $inactive_time = time() - $_SESSION['LAST_ACTIVITY'];

      // Si el tiempo de inactividad supera el límite, destruir la sesión
      if ($inactive_time > self::getSessionLifetime()) {
        self::destroySession();
        return true;
      }
    }

    // Actualizar el timestamp de última actividad
    $_SESSION['LAST_ACTIVITY'] = time();

    // Si no existe el timestamp de creación, crearlo
    if (!isset($_SESSION['CREATED'])) {
      $_SESSION['CREATED'] = time();
    }

    return false;
  }

  /**
   * Destruye la sesión actual
   */
  public static function destroySession()
  {
    session_unset();
    session_destroy();
    session_write_close();
    setcookie(session_name(), '', 0, '/');
  }

  /**
   * Establece el timestamp inicial al iniciar sesión
   */
  public static function setLoginTimestamp()
  {
    $_SESSION['LAST_ACTIVITY'] = time();
    $_SESSION['CREATED'] = time();
  }

  /**
   * Obtiene el tiempo restante de la sesión en minutos
   * @param bool $includeDecimals Si es true, devuelve decimales (ej: 0.5), si es false redondea
   * @return float Minutos restantes
   */
  public static function getRemainingTime($includeDecimals = false)
  {
    if (isset($_SESSION['LAST_ACTIVITY'])) {
      $remaining = self::getSessionLifetime() - (time() - $_SESSION['LAST_ACTIVITY']);
      $minutes = $remaining / 60;
      return max(0, $includeDecimals ? round($minutes, 1) : round($minutes));
    }
    return 0;
  }

  /**
   * Obtiene el tiempo restante en segundos
   * @return int Segundos restantes
   */
  public static function getRemainingSeconds()
  {
    if (isset($_SESSION['LAST_ACTIVITY'])) {
      $remaining = self::getSessionLifetime() - (time() - $_SESSION['LAST_ACTIVITY']);
      return max(0, $remaining);
    }
    return 0;
  }

  /**
   * Verifica si la sesión ha expirado SIN actualizar el timestamp
   * Solo para lectura, no renueva la sesión
   * @return bool true si la sesión expiró, false si aún es válida
   */
  public static function isSessionExpired()
  {
    if (isset($_SESSION['LAST_ACTIVITY'])) {
      $inactive_time = time() - $_SESSION['LAST_ACTIVITY'];
      return ($inactive_time > self::getSessionLifetime());
    }
    return false;
  }
}
