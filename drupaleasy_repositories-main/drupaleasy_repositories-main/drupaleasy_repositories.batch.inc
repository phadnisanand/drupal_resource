<?php

/**
 * @file
 * Batch-related functions for DrupalEasy Repositories module.
 */

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

