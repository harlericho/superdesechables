<?php
class Db
{
    private static $connection = null;

    private static function getConfig()
    {
        $configPath = dirname(__DIR__) . '/config.ini';
        if (!file_exists($configPath)) {
            throw new Exception("El archivo de configuración no se encontró en: $configPath");
        }

        $config = parse_ini_file($configPath, true);
        if (!$config) {
            throw new Exception("Error al leer el archivo de configuración.");
        }

        return $config['database'];
    }
    public static function dbConnection()
    {
        try {
            // Reutilizar la conexión existente (Singleton)
            if (self::$connection !== null) {
                return self::$connection;
            }

            $config = self::getConfig();
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
            $db = new PDO($dsn, $config['user'], $config['password']);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$connection = $db;
            return $db;
        } catch (PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
        }
    }
}
