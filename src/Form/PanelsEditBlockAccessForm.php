<?php

/**
 * @file
 */

namespace Drupal\panels\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContextAwarePluginAssignmentTrait;
use Drupal\panels\CachedValuesGetterTrait;
use Drupal\user\SharedTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for editing a block access rule.
 */
class PanelsEditBlockAccessForm extends FormBase {

  use ContextAwarePluginAssignmentTrait;
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
   * The block plugin being configured.
   *
   * @var \Drupal\Core\Block\BlockPluginInterface
   */
  protected $blockConfiguration;

  /**
   * The access plugin being used.
   *
   * @var \Drupal\panels\Plugin\PanelsAccess\PanelsAccessInterface
   */
  protected $accessPlugin;

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
    return 'panels_edit_block_access_form';
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

    $display_configuration = $this->variantPlugin->getConfiguration();
    $this->blockConfiguration = !empty($display_configuration['blocks'][$block_id]) ? $display_configuration['blocks'][$block_id] : [];;


    // Load the block we are editing.
    $block = $this->getVariantPlugin()->getBlock($block_id);
    $this->accessPlugin = $this->getVariantPlugin()->getBlockAccess($block, $form_state->getValue('access'));

    $form_state->set('machine_name', $machine_name);
    $form_state->set('block_id', $block_id);

    $form['#tree'] = TRUE;

    // Get all access plugins to create the option list.
    $options = [];
    foreach (\Drupal::service('plugin.manager.panels.access')->getDefinitions() as $id => $plugin) {
      $options[$id] = $plugin['label'];
    }

    $form['access'] = [
      '#title' => $this->t('Visibility rule'),
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => $this->accessPlugin->getPluginId(),
      '#ajax' => [
        'callback' => '::updateAccessSettings',
        'wrapper' => 'edit-access-settings',
        'progress' => [
          'type' => 'throbber',
          'message' => t('Updating access settings...'),
        ],
      ],
    ];

    $form['access_settings'] = $this->accessPlugin->buildConfigurationForm([], (new FormState())->setValues($form_state->getValue('access_settings', [])));
    $form['access_settings']['#prefix'] = '<div id="edit-access-settings">';
    $form['access_settings']['#suffix'] = '</div>';
    $form['access_settings']['#tree'] = TRUE;

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->submitText(),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * Handles AJAX callbacks upon changes of access setting.
   *
   * @param array $form
   *   An associative array containing the structure of the plugin form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure of those parts of the form to replace access settings.
   */
  public function updateAccessSettings(array &$form, FormStateInterface $form_state) {
      return $form['access_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
      $access_settings = (new FormState())->setValues($form_state->getValue('access_settings', []));
      $this->accessPlugin->validateConfigurationForm($form['access_settings'], $access_settings);
      $form_state->setValue('access_settings', $access_settings->getValues());
    }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $display_configuration = $this->variantPlugin->getConfiguration();
    $this->variantPlugin->setConfiguration($display_configuration);

    $access_settings = (new FormState())->setValues($form_state->getValue('access_settings', []));
    $this->accessPlugin->submitConfigurationForm($form['access_settings'], $access_settings);
    $access_configuration = $this->accessPlugin->getConfiguration();

    $block = $this->getVariantPlugin()->getBlock($form_state->get('block_id'));
    $this->getVariantPlugin()->setBlockAccess($block, $this->accessPlugin, $access_configuration);
    $form_state->setValue('access_settings', $access_settings);

    $cached_values = $this->getCachedValues($this->tempstore, $this->tempstore_id, $form_state->get('machine_name'));
    $cached_values['plugin'] = $this->getVariantPlugin();

    // PageManager specific handling.
    if (isset($cached_values['page_variant'])) {
      $cached_values['page_variant']->getVariantPlugin()->setConfiguration($cached_values['plugin']->getConfiguration());
    }
    $this->getTempstore()->set($cached_values['id'], $cached_values);
  }

  /**
   * {@inheritdoc}
   */
  public function submitText() {
    return $this->t('Update access');
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
