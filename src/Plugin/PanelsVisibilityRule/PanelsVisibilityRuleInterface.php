<?php

namespace Drupal\panels\Plugin\PanelsVisibilityRule;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;

/**
 * Provides interface for discovery & instantiation of visibility rule plugins.
 */
interface PanelsVisibilityRuleInterface extends ConfigurablePluginInterface, PluginManagerInterface {

  public function access(PanelsDisplayVariant $display, BlockPluginInterface $block);
}