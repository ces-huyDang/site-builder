<?php

namespace Drupal\main\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\main\Services\MainService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Blog routes.
 *
 * @category Controller
 *
 * @package Custom
 */
class MainController extends ControllerBase {
  /**
   * Main Service.
   *
   * @var \Drupal\main\Services\MainService
   */
  protected $mainService;

  /**
   * Constructs a new instance of the MainService class.
   *
   * @param \Drupal\main\Services\MainService $main_service
   *   To use method from MainService.
   */
  public function __construct(MainService $main_service) {
    $this->mainService = $main_service;
  }

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Implemented by service container classes.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('main.service'),
      );
  }

  /**
   * Retrieves a list of manga.
   *
   * @return array An array of manga objects.
   */
  public function mangaList() {
    $mangaList = $this->mainService->getMangaList();
    return [
      '#theme' => 'manga_list',
      '#data' => [
        'mangaList' => $mangaList,
      ],
    ];
  }

}
