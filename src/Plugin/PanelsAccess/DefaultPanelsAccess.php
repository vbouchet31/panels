<?php

namespace Drupal\panels\Plugin\PanelsAccess;

use Drupal\Core\Form\FormStateInterface;
use Drupal\panels\Plugin\PanelsAccess\PanelsAccessBase;

/**
 * The default control access.
 *
 * @PanelsAccess(
 *   id = "default",
 *   label = @Translation("Default")
 * )
 */
class DefaultPanelsAccess extends PanelsAccessBase {

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