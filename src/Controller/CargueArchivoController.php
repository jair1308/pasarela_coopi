<?php

namespace Drupal\pasarela\Controller;

use Drupal\Core\Database\Connection;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Drupal\pasarela\Services\Archivos;

class CargueArchivoController extends ControllerBase
{
  private $archivos;

  private $accountProxy;

  private $db;
  /**
   * Atributo para obtener las variables de sesión en Drupal.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  private $session;
  
  /**
   * Constructor de la clase codeudores controlador.
   */
  public function __construct(Connection $database, Archivos $archivos, AccountProxyInterface $accountProxy = NULL, ConfigFactoryInterface $configFactory, SessionInterface $session)
  {
    $this->db = $database;
    $this->archivos = $archivos;
    $this->accountProxy = $accountProxy;
    $this->configFactory = $configFactory;
    $this->session = $session;
  }

  /**
   * Función create de la clase codeudores.
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('database'),
      $container->get('pasarela.archivos'),
      $container->get('current_user'),
      $container->get('config.factory'),
      $container->get('session')
    );
  }

  /**
   * Función para renderizar la vista de los certificados
   */
  public function index(){
    $build[] = [
      '#theme' => 'archivos',
      '#attached' => [
        'library' => [
          'pasarela/archivos-scripts',
        ],
      ]
    ];
    return $build;
  }

  function updateArchivoDev(Request $request) {
    return $this->archivos->ejecutarArchivo($_FILES["archivosAdjuntos"]["tmp_name"],$_FILES["archivosAdjuntos"]["name"]);
  }
}
