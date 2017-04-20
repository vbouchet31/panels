<?php

namespace Drupal\panels\Plugin\PanelsAccess;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Provides an interface for a Panels Visibility Rule.
 */
interface PanelsAccessInterface extends PluginInspectionInterface, ConfigurablePluginInterface, PluginFormInterface {

  /**
   * Check if the block is visible.
   *
   * @return bool
   *   TRUE if the widget is visible.
   */
  public function access();

  // @TODO: We can probably add a similar feature to regions.

}
