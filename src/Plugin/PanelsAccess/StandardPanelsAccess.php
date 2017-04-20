<?php

/**
 * @file
 */

namespace Drupal\panels\Plugin\PanelsAccess;

use Drupal\Core\Form\FormStateInterface;
use Drupal\panels\Plugin\PanelsAccess\PanelsAccessBase;

/**
 * The user role control access.
 *
 * @PanelsAccess(
 *   id = "standard",
 *   label = @Translation("Standard")
 * )
 */
class StandardPanelsAccess extends PanelsAccessBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['description'] = [
      '#markup' => t('This visibility rule does not have specific settings. Everyone can view this block.')
    ];

    return $form;
  }
}