<?php

namespace Drupal\panels_ipe\Form;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Plugin\ContextAwarePluginAssignmentTrait;
use Drupal\Core\Render\RendererInterface;
use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;
use Drupal\panels_ipe\PanelsIPEBlockRendererTrait;
use Drupal\user\SharedTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for editing a block access rule.
 */
class PanelsIPEBlockPluginAccessForm extends FormBase {

  use ContextAwarePluginAssignmentTrait;

  use PanelsIPEBlockRendererTrait;

  /**
   * @var \Drupal\Component\Plugin\PluginManagerInterface $blockManager
   */
  protected $blockManager;

  /**
   * @var \Drupal\Core\Render\RendererInterface $renderer
   */
  protected $renderer;

  /**
   * @var \Drupal\user\SharedTempStore
   */
  protected $tempStore;

  /**
   * The Panels storage manager.
   *
   * @var \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant
   */
  protected $panelsDisplay;

  /**
   * The PanelsAccess storage manager.
   *
   * @var \Drupal\panels\Plugin\PanelsAccess\PanelsAccessManager
   */
  protected $accessPlugin;

  /**
   * Constructs a new PanelsIPEBlockPluginForm.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $block_manager
   * @param \Drupal\Core\Plugin\Context\ContextHandlerInterface $context_handler
   * @param \Drupal\Core\Render\RendererInterface $renderer
   * @param \Drupal\user\SharedTempStoreFactory $temp_store_factory
   */
  public function __construct(PluginManagerInterface $block_manager, ContextHandlerInterface $context_handler, RendererInterface $renderer, SharedTempStoreFactory $temp_store_factory) {
    $this->blockManager = $block_manager;
    $this->contextHandler = $context_handler;
    $this->renderer = $renderer;
    $this->tempStore = $temp_store_factory->get('panels_ipe');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.block'),
      $container->get('context.handler'),
      $container->get('renderer'),
      $container->get('user.shared_tempstore')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'panels_ipe_block_plugin_access_form';
  }

  /**
   * Builds a form that constructs a unsaved instance of a Block for the IPE.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param string $plugin_id
   *   The requested Block Plugin ID.
   * @param \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant $panels_display
   *   The current PageVariant ID.
   * @param string $uuid
   *   An optional Block UUID, if this is an existing Block.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $plugin_id = NULL, PanelsDisplayVariant $panels_display = NULL, $uuid = NULL) {
    // We require these default arguments.
    if (!$plugin_id || !$panels_display) {
      return FALSE;
    }

    // Save the panels display for later.
    $this->panelsDisplay = $panels_display;

    // If $uuid is present, a block should exist.
    if ($uuid) {
      /** @var \Drupal\Core\Block\BlockBase $block_instance */
      $block_instance = $panels_display->getBlock($uuid);
    }
    else {
      // Create an instance of this Block plugin.
      /** @var \Drupal\Core\Block\BlockBase $block_instance */
      $block_instance = $this->blockManager->createInstance($plugin_id);
    }

    // Load the current access rule.
    $this->accessPlugin = $this->panelsDisplay->getBlockAccess($block_instance);

    // Some Block Plugins rely on the block_theme value to load theme settings.
    // @see \Drupal\system\Plugin\Block\SystemBrandingBlock::blockForm().
    $form_state->set('block_theme', $this->config('system.theme')->get('default'));

    // Wrap the form so that our AJAX submit can replace its contents.
    $form['#prefix'] = '<div id="panels-ipe-block-plugin-form-wrapper">';
    $form['#suffix'] = '</div>';

    // Add our various card wrappers.
    $form['flipper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => 'flipper',
      ],
    ];

    $form['flipper']['front'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => 'front',
      ],
    ];

    $form['flipper']['back'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => 'back',
      ],
    ];

    $form['#attributes']['class'][] = 'flip-container';

    // Get the base configuration form for this block.
    $form['flipper']['front']['settings'] = $this->accessPlugin->buildConfigurationForm([], $form_state);
    $form['flipper']['front']['settings']['context_mapping'] = $this->addContextAssignmentElement($block_instance, $this->panelsDisplay->getContexts());
    $form['flipper']['front']['settings']['#tree'] = TRUE;

    // Add the block ID, variant ID to the form as values.
    $form['plugin_id'] = ['#type' => 'value', '#value' => $plugin_id];
    $form['variant_id'] = ['#type' => 'value', '#value' => $panels_display->id()];
    $form['uuid'] = ['#type' => 'value', '#value' => $uuid];

    // Add an add button, which is only used by our App.
    $form['submit'] = [
      '#type' => 'button',
      '#value' => $this->t('Save'),
      '#ajax' => [
        'callback' => '::submitForm',
        'wrapper' => 'panels-ipe-block-plugin-access-form-wrapper',
        'method' => 'replace',
        'progress' => [
          'type' => 'throbber',
          'message' => '',
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Return early if there are any errors.
    if ($form_state->hasAnyErrors()) {
      return $form;
    }

    // If a temporary configuration for this variant exists, use it.
    $temp_store_key = $this->panelsDisplay->getTempStoreId();
    if ($variant_config = $this->tempStore->get($temp_store_key)) {
      $this->panelsDisplay->setConfiguration($variant_config);
    }

    $block_instance = $this->getBlockInstance($form_state);
    $block_config = $block_instance->getConfiguration();

    $uuid = $form_state->getValue('uuid');

    $access_settings = (new FormState())->setValues($form_state->getValue('settings', []));
    $this->accessPlugin->submitConfigurationForm($form['flipper']['front']['settings'], $access_settings);
    $access_configuration = $this->accessPlugin->getConfiguration();

    $this->panelsDisplay->setBlockAccess($block_instance, $this->accessPlugin, $access_configuration);
    $form_state->setValue('settings', $access_settings);

    // Set the tempstore value.
    $this->tempStore->set($this->panelsDisplay->getTempStoreId(), $this->panelsDisplay->getConfiguration());

    // @TODO: Find a way to update the display based on the new access rule.
    // Assemble data required for our App.
    $build = $this->buildBlockInstance($block_instance, $this->panelsDisplay);

    // Bubble Block attributes to fix bugs with the Quickedit and Contextual
    // modules.
    $this->bubbleBlockAttributes($build);

    // Add our data attribute for the Backbone app.
    $build['#attributes']['data-block-id'] = $uuid;

    $plugin_definition = $block_instance->getPluginDefinition();

    $block_model = [
      'uuid' => $uuid,
      'label' => $block_instance->label(),
      'id' => $block_instance->getPluginId(),
      'region' => $block_config['region'],
      'provider' => $block_config['provider'],
      'plugin_id' => $plugin_definition['id'],
      'html' => $this->renderer->render($build),
    ];

    $form['build'] = $build;

    // Add Block metadata and HTML as a drupalSetting.
    $form['#attached']['drupalSettings']['panels_ipe']['updated_block'] = $block_model;

    return $form;
  }

  /**
   * Loads or creates a Block Plugin instance suitable for rendering or testing.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\Core\Block\BlockPluginInterface
   *   The Block Plugin instance.
   */
  protected function getBlockInstance(FormStateInterface $form_state) {
    // If a UUID is provided, the Block should already exist.
    if ($uuid = $form_state->getValue('uuid')) {
      // If a temporary configuration for this variant exists, use it.
      $temp_store_key = $this->panelsDisplay->getTempStoreId();
      if ($variant_config = $this->tempStore->get($temp_store_key)) {
        $this->panelsDisplay->setConfiguration($variant_config);
      }

      // Load the existing Block instance.
      $block_instance = $this->panelsDisplay->getBlock($uuid);
    }
    else {
      // Create an instance of this Block plugin.
      /** @var \Drupal\Core\Block\BlockBase $block_instance */
      $block_instance = $this->blockManager->createInstance($form_state->getValue('plugin_id'));
    }

    return $block_instance;
  }

}
