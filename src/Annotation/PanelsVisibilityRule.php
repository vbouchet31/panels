<?php

namespace Drupal\panels\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Define a PanelsVisibilityRule annotation object.
 *
 * @Annotation
 */
class PanelsVisibilityRule extends Plugin {

  // @TODO.

  /**
   * @var string
   */
  public $title = '';

  /**
   * @var string
   */
  public $description = '';

}