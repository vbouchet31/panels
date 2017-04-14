<?php

namespace Drupal\panels\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a panels visibility rule annotation object.
 *
 * @Annotation
 */
class PanelsVisibilityRule extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the panels visibility rule.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  // @TODO: Define the other data used by the plugin.

}
