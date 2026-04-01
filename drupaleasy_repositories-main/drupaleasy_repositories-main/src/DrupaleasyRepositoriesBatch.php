<?php

namespace Drupal\drupaleasy_repositories;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService;


/**
 * Batch service class to integrate with Batch API.
 */
class DrupaleasyRepositoriesBatch {

  /**
   * The drupaleasy_repositories.service service.
   *
   * @var \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService
   */
  protected $service;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The module extension list.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $extensionListModule;

  /**
   * Constructs a DrupaleasyRepositoriesBatch object.
   *
   * @param \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService $service
   *   The drupaleasy_repositories.service service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Extension\ModuleExtensionList $extension_list_module
   *   The module extension list.
   */
  public function __construct(DrupaleasyRepositoriesService $service, EntityTypeManagerInterface $entity_type_manager, ModuleExtensionList $extension_list_module) {
    $this->service = $service;
    $this->entityTypeManager = $entity_type_manager;
    $this->extensionListModule = $extension_list_module;
  }

  /**
   * Updates all user repositories using the Batch API.
   *
   * @param bool $drush
   *   True if called from Drush.
   */
  public function updateAllUserRepositories(bool $drush = FALSE): void {
    $operations = [];

    // Get all active users.
    $user_storage = $this->entityTypeManager->getStorage('user');
    $query = $user_storage->getQuery();
    $query->condition('status', '1');
    $users = $query->accessCheck(FALSE)->execute();

    // Create a Batch API item for each user.
    foreach ($users as $uid => $user) {
      $operations[] = ['drupaleasy_update_repositories_batch_operation', [$uid]];
    }
    $batch = [
      'operations' => $operations,
      'finished' => 'drupaleasy_update_all_repositories_finished',
      'file' => $this->extensionListModule->getPath('drupaleasy_repositories') . '/drupaleasy_repositories.batch.inc',
    ];

    // Submit the batch for processing.
    batch_set($batch);
    if ($drush) {
      drush_backend_batch_process();
    }
  }

  /**
   * Calls the correct method responsible for handling a given batch operation.
   *
   * @param int $uid
   *   User ID to update.
   * @param array|\ArrayAccess $context
   *   Batch API context.
   */
  function drupaleasy_update_repositories_batch_operation(int $uid, array|\ArrayAccess &$context): void {
    if (empty($context['results']['num'])) {
      $context['results']['num'] = 0;
    }
    /** @var Drupal\drupaleasy_repositories\DrupaleasyRepositoriesBatch $drupaleasy_repositories_batch */
    $drupaleasy_repositories_batch = \Drupal::service('drupaleasy_repositories.batch');
    $drupaleasy_repositories_batch->updateRepositoriesBatch($uid, $context);
  }

}
