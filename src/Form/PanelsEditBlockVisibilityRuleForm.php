<?php

/**
 * @file
 * Contains \Drupal\panels\Form\PanelsEditBlockVisibilityRuleForm.
 */

namespace Drupal\panels\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\panels\CachedValuesGetterTrait;
use Drupal\user\SharedTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for configuring block visibility rule..
 */
abstract class PanelsEditBlockVisibilityRuleForm extends FormBase {

  use CachedValuesGetterTrait;

  /**
   * Tempstore factory.
   *
   * @var \Drupal\user\SharedTempStoreFactory
   */
  protected $tempstore;

  /**
   * The tempstore id.
   *
   * @var string
   */
  protected $tempstore_id;

  /**
   * The variant plugin.
   *
   * @var \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant
   */
  protected $variantPlugin;

  /**
   * Constructs a new VariantPluginConfigureBlockFormBase.
   *
   * @param \Drupal\user\SharedTempStoreFactory $tempstore
   *   The tempstore factory.
   */
  public function __construct(SharedTempStoreFactory $tempstore) {
    $this->tempstore = $tempstore;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('user.shared_tempstore')
    );
  }

  /**
   * Get the tempstore id.
   *
   * @return string
   */
  protected function getTempstoreId() {
    return $this->tempstore_id;
  }

  /**
   * Get the tempstore.
   *
   * @return \Drupal\user\SharedTempStore
   */
  protected function getTempstore() {
    return $this->tempstore->get($this->getTempstoreId());
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'panels_block_edit_visibility_rule_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function submitText() {
    return $this->t('Update visibility rule');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $tempstore_id = NULL, $machine_name = NULL, $block_id = NULL) {
    $this->tempstore_id = $tempstore_id;
    $cached_values = $this->getCachedValues($this->tempstore, $tempstore_id, $machine_name);
    $this->variantPlugin = $cached_values['plugin'];

    $contexts = $this->variantPlugin->getPattern()->getDefaultContexts($this->tempstore, $this->getTempstoreId(), $machine_name);
    $this->variantPlugin->setContexts($contexts);
    $form_state->setTemporaryValue('gathered_contexts', $contexts);

    $form_state->set('machine_name', $machine_name);
    $form_state->set('block_id', $this->block->getConfiguration()['uuid']);

    $this->visibilityRule = $this->getVariantPlugin()->getVisibilityRule($block_id, $form_state->getValue('visibility_rule'));

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement submitForm() method.
  }

  /**
   * Gets the variant plugin for this page variant entity.
   *
   * @return \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant
   */
  protected function getVariantPlugin() {
    return $this->variantPlugin;
  }
}