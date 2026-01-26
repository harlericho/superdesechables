<?php
class CSVProcessor
{
  public function procesarCSV($filePath)
  {
    $data = [];

    if (($handle = fopen($filePath, "r")) !== FALSE) {
      $header = fgetcsv($handle, 1000, ",");

      if (!$header) {
        return false; // Error al leer la cabecera
      }

      while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
        // Verificar si la cantidad de columnas coincide con el encabezado
        if (count($row) == count($header)) {
          $data[] = array_combine($header, $row);
        } else {
          // Manejar filas que no coinciden en número de columnas
          // Aquí podrías decidir qué hacer, como omitir la fila o agregar un mensaje de advertencia
        }
      }

      fclose($handle);
    }

    return $data;
  }
}
