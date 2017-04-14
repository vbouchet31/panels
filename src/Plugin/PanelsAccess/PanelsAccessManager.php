<?php

namespace Drupal\panels\Plugin\PanelsAccess;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manager for Panels Access plugins.
 */
class PanelsAccessManager extends DefaultPluginManager {

  /**
   * Constructs a new PanelsAccessManager.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/PanelsAccess', $namespaces, $module_handler, 'Drupal\panels\Plugin\PanelsAccess\PanelsAccessInterface', 'Drupal\panels\Annotation\PanelsAccess');

    $this->alterInfo('panels_access_info');
    $this->setCacheBackend($cache_backend, 'panels_access');
  }

}
