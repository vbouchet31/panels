<?php

/**
 * @file
 */

namespace Drupal\panels\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for editing a block access rule.
 */
class PanelsEditBlockAccessForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'panels_edit_block_access_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // TODO: Implement buildForm() method.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement submitForm() method.
  }
}
