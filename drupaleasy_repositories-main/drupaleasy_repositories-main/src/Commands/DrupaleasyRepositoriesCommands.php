<?php

namespace Drupal\drupaleasy_repositories\Commands;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\drupaleasy_repositories\DrupaleasyRepositoriesBatch;
use Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService;
use Drush\Commands\DrushCommands;
use Drush\Attributes as CLI;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class DrupaleasyRepositoriesCommands extends DrushCommands {

  /**
   * The DrupalEasy repositories manager service.
   *
   * @var \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService
   */
  protected DrupaleasyRepositoriesService $repositoriesService;

  /**
   * The Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The DrupalEasy repositories batch service.
   *
   * @var \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesBatch
   */
  protected DrupaleasyRepositoriesBatch $drupaleasyRepositoriesBatch;

  /**
   * The invalidator for cache tags.
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected CacheTagsInvalidatorInterface $cacheInvalidator;

  /**
   * Constructs a DrupaleasyRepositories object.
   *
   * @param \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService $repositories_service
   *   The DrupalEasyRepositories service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity_type.manager service.
   * @param \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesBatch $drupaleasy_repositories_batch
   *   The DrupalEasy repositories batch service.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_invalidator
   *   The cache invalidator.
   */
  public function __construct(DrupaleasyRepositoriesService $repositories_service, EntityTypeManagerInterface $entity_type_manager, DrupaleasyRepositoriesBatch $drupaleasy_repositories_batch, CacheTagsInvalidatorInterface $cache_invalidator) {
    parent::__construct();
    $this->repositoriesService = $repositories_service;
    $this->entityTypeManager = $entity_type_manager;
    $this->drupaleasyRepositoriesBatch = $drupaleasy_repositories_batch;
    $this->cacheInvalidator = $cache_invalidator;
  }

  #[CLI\Command(name: 'der:update-repositories', aliases: ['der:ur'])]
  #[CLI\Option(name: 'uid', description: 'The user ID of the user to update.')]
  #[CLI\Help(description: 'Update user repositories.', synopsis: 'This command will update all user repositories or all repositories for a single user.')]
  #[CLI\Usage(name: 'der:update-repositories --uid=2', description: 'Update a user\'s repositories.')]
  #[CLI\Usage(name: 'der:update-repositories', description: 'Update all user repositories.')]
  public function updateRepositories(array $options = ['uid' => NULL]): void {
    if (!empty($options['uid'])) {
      /** @var \Drupal\user\UserStorageInterface $user_storage */
      $user_storage = $this->entityTypeManager->getStorage('user');

      $account = $user_storage->load($options['uid']);
      if ($account) {
        if ($this->repositoriesService->updateRepositories($account)) {
          $this->logger()->notice(dt('Repositories updated.'));
        }
      }
      else {
        $this->logger()->critical(dt('User does not exist.'));
      }
    }
    else {
      if ($options['uid'] === '0') {
        $this->logger()->critical(dt('You may not use anonymous user.'));
      }
      $this->drupaleasyRepositoriesBatch->updateAllUserRepositories(TRUE);
    }
    $this->cacheInvalidator->invalidateTags(['drupaleasy_repositories']);
  }


}
