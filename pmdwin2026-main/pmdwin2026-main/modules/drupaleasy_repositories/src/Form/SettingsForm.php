<?php

declare(strict_types=1);

namespace Drupal\drupaleasy_repositories\Form;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\drupaleasy_repositories\DrupaleasyRepositories\DrupaleasyRepositoriesPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure DrupalEasy repositories settings for this site.
 */
final class SettingsForm extends ConfigFormBase {

  /**
   * Constructs an SettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The factory for configuration objects.
   * @param \Drupal\drupaleasy_repositories\DrupaleasyRepositories\DrupaleasyRepositoriesPluginManager $repositoriesManager
   *   The DrupalEasy repositories manager service.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface|NULL $typedConfig
   *   The Drupal core typed configuration manager.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    protected DrupaleasyRepositoriesPluginManager $repositoriesManager,
    protected ?TypedConfigManagerInterface $typedConfig = NULL,
  ) {
    parent::__construct($configFactory, $typedConfig);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.drupaleasy_repositories'),
      $container->get('config.typed')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'drupaleasy_repositories_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['drupaleasy_repositories.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    // Use the PHP Null Coalescing Operator in case the config doesn't exist
    // yet.
    $repositories_config = $this->config('drupaleasy_repositories.settings')->get('repositories_plugins') ?? [];

    $repositories = $this->repositoriesManager->getDefinitions();
    uasort($repositories, function ($a, $b) {
      return Unicode::strcasecmp($a['label'], $b['label']);
    });
    $repository_options = [];
    foreach ($repositories as $repository => $definition) {
      $repository_options[$repository] = $definition['label'];
    }

    $form['repositories_plugins'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Repository plugins'),
      '#options' => $repository_options,
      '#default_value' => $repositories_config,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('drupaleasy_repositories.settings')
      ->set('repositories_plugins', $form_state->getValue('repositories_plugins'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
