<?php

namespace Drupal\panels\Plugin\PanelsAccess;

use Drupal\Core\Form\FormStateInterface;

/**
 * The user role control access.
 *
 * @PanelsAccess(
 *   id = "user_role",
 *   label = @Translation("User role")
 * )
 */
class UserRolePanelsAccess extends PanelsAccessBase {

  /**
   * {@inheritdoc}
   */
  public function access() {
    if (array_intersect(\Drupal::currentUser()->getRoles(), $this->configuration['roles'])) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [
      'module' => ['user']
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['roles'] = [
      '#type' => 'checkboxes',
      '#title' => t('Role'),
      '#description' => t('Only the checked roles will be granted access'),
      '#options' => user_role_names(),
      '#default_value' => isset($this->configuration['roles']) ? $this->configuration['roles'] : [],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $form_state->setValue('roles', array_keys(array_filter($form_state->getValue('roles'))));

    parent::submitConfigurationForm($form, $form_state);
  }

}
