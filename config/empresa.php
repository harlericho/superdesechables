<?php
class Empresa
{
  private static function getConfig(): array
  {
    $configPath = dirname(__DIR__) . '/empresa.ini'; // Ajusta la ruta si es necesario

    if (!file_exists($configPath)) {
      throw new Exception("Error: El archivo de configuración no existe en: $configPath");
    }

    $config = parse_ini_file($configPath, true);
    if (!$config || !isset($config['empresa'])) {
      throw new Exception("Error: No se pudo leer la configuración de la empresa.");
    }

    return $config['empresa'];
  }

  public static function getBanner1(): string
  {
    $config = self::getConfig();
    return $config['banner1'] ?? '';
  }

  public static function getBanner2(): string
  {
    $config = self::getConfig();
    return $config['banner2'] ?? '';
  }
  public static function getNavbar(): string
  {
    $config = self::getConfig();
    return $config['navbar'] ?? '';
  }
  public static function getNombre(): string
  {
    $config = self::getConfig();
    return $config['nombre'] ?? '';
  }
  public static function getTelefono(): string
  {
    $config = self::getConfig();
    return $config['telefono'] ?? '';
  }
  public static function getDireccion(): string
  {
    $config = self::getConfig();
    return $config['direccion'] ?? '';
  }
  public static function getEmail(): string
  {
    $config = self::getConfig();
    return $config['email'] ?? '';
  }
  public static function getEmailClientes(): string
  {
    $config = self::getConfig();
    return $config['emailclientes'] ?? '';
  }
  public static function getTitulo1(): string
  {
    $config = self::getConfig();
    return $config['titulo1'] ?? '';
  }
  public static function getTitulo2(): string
  {
    $config = self::getConfig();
    return $config['titulo2'] ?? '';
  }
  public static function getComercio(): string
  {
    $config = self::getConfig();
    return $config['comercio'] ?? '';
  }
  public static function getDominio(): string
  {
    $config = self::getConfig();
    return $config['dominio'] ?? '';
  }
  public static function getVersion(): string
  {
    $config = self::getConfig();
    return $config['version'] ?? '';
  }
}
