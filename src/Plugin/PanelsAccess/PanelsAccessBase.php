<?php

namespace Drupal\panels\Plugin\PanelsAccess;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\panels\Plugin\PanelsAccess\PanelsAccessInterface;

abstract class PanelsAccessBase extends PluginBase implements PanelsAccessInterface {

  /**
   * The style configuration.
   *
   * @var array
   */
  protected $configuration = [];
  
  /**
   * {@inheritdoc}
   */
  public function access() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration = $form_state->getValues();
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return array_merge($this->defaultConfiguration(), $this->configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return isset($this->configuration['dependencies']) ? $this->configuration['dependencies'] : [];
  }
}
