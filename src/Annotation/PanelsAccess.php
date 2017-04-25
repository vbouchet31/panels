<?php

namespace Drupal\panels\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a panels access annotation object.
 *
 * @Annotation
 */
class PanelsAccess extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the panels access.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

}
