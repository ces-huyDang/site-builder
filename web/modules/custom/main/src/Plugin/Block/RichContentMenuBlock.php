<?php

namespace Drupal\main\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\main\Services\MainService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create a block of carousel to display on index.
 *
 * @Block(
 *  id = "rich_content_menu_block",
 *  admin_label = @Translation("Rich Content Menu Block"),
 *  category = @Translation("Custom"),
 * )
 */
class RichContentMenuBlock extends BlockBase implements ContainerFactoryPluginInterface {
  /**
   * Blog Service.
   *
   * @var \Drupal\main\Services\MainService
   */
  protected $mainService;

  /**
   * {@inheritDoc}
   *
   * @param \Drupal\main\Services\MainService $main_service
   *   To use method from BlogService.
   * @param array $configuration
   *   Configuration.
   * @param string $plugin_id
   *   Plugin_id.
   * @param mixed $plugin_definition
   *   Plugin_definition.
   */
  public function __construct(MainService $main_service, array $configuration, $plugin_id, $plugin_definition,) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->mainService = $main_service;
  }

  /**
   * {@inheritDoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Container.
   * @param array $configuration
   *   Configuration.
   * @param string $plugin_id
   *   Plugin_id.
   * @param mixed $plugin_definition
   *   Plugin_definition.
   *
   * @return static
   */
  public static function create(
        ContainerInterface $container,
        array $configuration,
        $plugin_id,
        $plugin_definition
    ) {
    return new static(
          $container->get('main.service'),
          $configuration,
          $plugin_id,
          $plugin_definition,
      );
  }

  /**
   * {@inheritDoc}
   */
  public function build() {
    $categories = $this->mainService->getTaxonomyTerms('categories');
    $chart = $this->mainService->getTaxonomyTerms('chart');
    return [
      '#theme' => 'rich_content_menu',
      '#data' => [
        "categories" => $categories,
        "chart" => $chart,
      ],
    ];
  }

}
