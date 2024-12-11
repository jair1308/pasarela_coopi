<?php

namespace Drupal\pasarela\Services;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\JsonResponse;

class Archivos {

  public function ejecutarArchivo($rutaArchivo, $nameArchivo)
  {
    ini_set('memory_limit', '512M');

    $fileContent = file_get_contents($rutaArchivo);
    if ($fileContent === false) {
        die("Error al leer el archivo");
    }
    $lines = explode(PHP_EOL, $fileContent);

    $result = [];
    
    foreach ($lines as $line) {
      if (count(explode('|', $line)) == 1) {
        continue;
      }
      $result[] = explode('|', $line);
    }

    $lineasT = count($result);
    // Activamos conección a la nueva base de datos 
    Database::setActiveConnection('database_pasarela');
    $connection = Database::getConnection();

    $values["proceso_file"] = $nameArchivo;
    $id_proceso = $connection->insert('fcd_proceso')
        ->fields($values)
        ->execute();

    $create_temp = "CREATE TEMPORARY TABLE `temp_fcd_fac` (
        `id` int NOT NULL AUTO_INCREMENT,
        `fact_id_pago` varchar(13) CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL,
        `fact_id_tipo` varchar(5) CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
        `fact_id_cliente` varchar(30) CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
        `fact_valor` decimal(15,2) DEFAULT NULL,
        `fact_valor_iva` decimal(15,2) DEFAULT NULL,
        `fact_concepto` varchar(80) CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
        `fact_email` varchar(100) CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
        `fact_nombre` varchar(50) CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
        `fact_fecha` date DEFAULT NULL,
        `fact_nit` varchar(30) CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
        `fact_total` decimal(15,2) DEFAULT NULL,
        `fact_chk` char(1) CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT '0',
        `fact_servicio` varchar(100) CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
        `fact_contrato` varchar(100) CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
        `fact_estado` varchar(100) CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT 'NN',
        `id_proceso` int NOT NULL,
        `transa_id` int DEFAULT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM AUTO_INCREMENT=451 DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci";
    $connection->query($create_temp);

    $qry = 'INSERT INTO fcd_fac(fact_id_pago,fact_id_tipo,fact_id_cliente,fact_valor,fact_valor_iva,fact_concepto,fact_email,fact_nombre,fact_total,fact_fecha,fact_nit,id_proceso)VALUES';
    foreach ($result as $key => $value) {
        $dateObject = \DateTime::createFromFormat('d/m/Y', $value[12]);
        $fecha = $dateObject->format('Y-m-d');
        $nit = $this->reemplazarTexto($value[2]);
  
        $qry .= "('".$value[0]."','".$value[1]."','".$value[2]."','".$value[3]."','".$value[4]."','".$value[5]."','".$value[7]."','".$value[9]."', '0','".$fecha."','".$nit."','".$id_proceso."')";
        if ($key !== array_key_last($result)) {
          $qry .= ", ";
        }
    }

    $fConection = $connection->query($qry);

    $migrate_data_query = "INSERT INTO fcd_fac (fact_id_pago,fact_id_tipo,fact_id_cliente,fact_valor,fact_valor_iva,fact_concepto,fact_email,fact_nombre,fact_total,fact_fecha,fact_nit,id_proceso)
                          SELECT fact_id_pago,fact_id_tipo,fact_id_cliente,fact_valor,fact_valor_iva,fact_concepto,fact_email,fact_nombre,fact_total,fact_fecha,fact_nit,id_proceso FROM temp_fcd_fac";

    $connection->query($migrate_data_query);
        
    $drop_temp_table_query = "DROP TEMPORARY TABLE IF EXISTS temp_fcd_fac";
    $connection->query($drop_temp_table_query);              
    Database::setActiveConnection();

    return new JsonResponse(["msg" => "Lineas insertadas", "lineasT" => $lineasT]);
  }

  public function reemplazarTexto($texto) {
    $patrones = [
        '/A0000/', '/AA00/', '/AA0/', '/A000/', '/A00/', '/A0/', 
        '/AC/', '/AB/', '/AA/', '/P0/', '/T0/', '/TA/', '/T/', '/AK/', '/A/'
    ];
    
    $reemplazos = [
        '', '1000', '100', '', '', '', 
        '12', '11', '10', '', '', '', '', '20', ''
    ];

    $texto = preg_replace($patrones, $reemplazos, $texto);
    
    return $texto;
}
}
