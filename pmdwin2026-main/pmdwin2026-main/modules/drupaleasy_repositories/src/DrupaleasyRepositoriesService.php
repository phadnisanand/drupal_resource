<?php

declare(strict_types=1);

namespace Drupal\drupaleasy_repositories;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * DrupalEasy Repositories main service class.
 */
final class DrupaleasyRepositoriesService {
  use StringTranslationTrait;

  /**
   * Constructs a DrupalEasy Repositories service class.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $pluginManagerDrupaleasyRepositories
   *   The DrupalEasy Repositories plugin manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The Drupal core configuration factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The Drupal core logger factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Drupal core entity type manager service.
   * @param bool $dryRun
   *   When TRUE, do not save or delete nodes.
   */
  public function __construct(
    protected PluginManagerInterface $pluginManagerDrupaleasyRepositories,
    protected ConfigFactoryInterface $configFactory,
    protected LoggerChannelFactoryInterface $loggerFactory,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected bool $dryRun = FALSE,
  ) {}

  /**
   * Return help text based on enabled plugins.
   *
   * @return string
   *   The help text string.
   */
  public function getValidatorHelpText(): string {
    $repository_plugins = [];

    // Get list of plugins and their status from config.
    $repository_plugin_ids = $this->configFactory->get('drupaleasy_repositories.settings')->get('repositories_plugins') ?? [];

    // Loop around enabled plugins and instantiate each.
    foreach ($repository_plugin_ids as $repository_plugin_id) {
      if (!empty($repository_plugin_id)) {
        try {
          $repository_plugins[] = $this->pluginManagerDrupaleasyRepositories->createInstance($repository_plugin_id);
        }
        catch (PluginException) {
          // @todo Add t() to make fully safe.
          $this->loggerFactory->get('drupaleasy_repositories')->error("The plugin $repository_plugin_id failed to instantiate.");
          continue;
        }
      }
    }

    $help = [];
    // Loop around each plugin and call their validateHelpText() method.
    /** @var \Drupal\drupaleasy_repositories\DrupaleasyRepositories\DrupaleasyRepositoriesInterface $repository_plugin */
    foreach ($repository_plugins as $repository_plugin) {
      $help[] = $repository_plugin->validateHelpText();
    }

    // Concatenate and return help texts in a single string.
    return implode(' ', $help);
  }

  /**
   * Validate repository URLs.
   *
   * Validate the URLs are valid based on the enabled plugins and ensure they
   * haven't been added by another user.
   *
   * @param array<mixed> $urls
   *   The urls to be validated.
   * @param int $uid
   *   The user id of the user submitting the URLs.
   *
   * @return string
   *   Errors reported by plugins.
   */
  public function validateRepositoryUrls(array $urls, int $uid): string {
    $errors = [];
    $repository_plugins = [];

    // Get IDs all DrupaleasyRepository plugins (enabled or not).
    $repository_plugin_ids = $this->configFactory->get('drupaleasy_repositories.settings')->get('repositories_plugins') ?? [];

    // Instantiate each enabled DrupaleasyRepository plugin (and confirm that
    // at least one is enabled).
    $atLeastOne = FALSE;
    foreach ($repository_plugin_ids as $repository_plugin_id) {
      if (!empty($repository_plugin_id)) {
        $atLeastOne = TRUE;
        $repository_plugins[] = $this->pluginManagerDrupaleasyRepositories->createInstance($repository_plugin_id);
      }
    }
    if (!$atLeastOne) {
      return 'There are no enabled repository plugins';
    }

    // Loop around each Repository URL and attempt to validate.
    foreach ($urls as $url) {
      if (is_array($url)) {
        if ($uri = trim($url['uri'])) {
          $is_valid_url = FALSE;
          // Check to see if the URI is valid for any enabled plugins.
          /** @var \Drupal\drupaleasy_repositories\DrupaleasyRepositories\DrupaleasyRepositoriesInterface $repository_plugin */
          foreach ($repository_plugins as $repository_plugin) {
            if ($repository_plugin->validate($uri)) {
              $is_valid_url = TRUE;
              $repo_metadata = $repository_plugin->getRepo($uri);
              if ($repo_metadata) {
                if (!$this->isUnique($repo_metadata, $uid)) {
                  $errors[] = $this->t('The repository at the url %uri has been added by another user.', ['%uri' => $uri]);
                }
              }
              else {
                $errors[] = $this->t('The repository at the url %uri was not found.', ['%uri' => $uri]);
              }
            }
          }
          if (!$is_valid_url) {
            $errors[] = $this->t('The repository url %uri is not valid.', ['%uri' => $uri]);
          }
        }
      }
    }

    if ($errors) {
      return implode(' ', $errors);
    }
    // No errors found.
    return '';
  }

  /**
   * Check to see if a given repository is unique to the site.
   *
   * @param array<string, array<string, string|int>> $repo_info
   *   The repository metadata to check.
   * @param int $uid
   *   The user ID of the form submitter.
   *
   * @return bool
   *   TRUE if unique.
   */
  protected function isUnique(array $repo_info, int $uid): bool {
    $node_storage = $this->entityTypeManager->getStorage('node');

    $repo_metadata = array_pop($repo_info);

    $query = $node_storage->getQuery();
    $results = $query->condition('type', 'repository')
      ->condition('field_url', $repo_metadata['url'])
      ->condition('uid', $uid, 'NOT IN')
      ->accessCheck(FALSE)
      ->execute();

    return !count($results);
  }

  /**
   * Update the repository nodes for a given user.
   *
   * @param \Drupal\Core\Entity\EntityInterface $account
   *   The user.
   *
   * @return bool
   *   TRUE on success.
   */
  public function updateRepositories(EntityInterface $account): bool {
    $repos_metadata = [];

    // Get IDs all DrupaleasyRepository plugins (enabled or not).
    $repository_plugin_ids = $this->configFactory->get('drupaleasy_repositories.settings')->get('repositories_plugins') ?? [];

    // Instantiate each enabled DrupaleasyRepository plugin.
    foreach ($repository_plugin_ids as $repository_plugin_id) {
      if (!empty($repository_plugin_id)) {
        /** @var \Drupal\drupaleasy_repositories\DrupaleasyRepositories\DrupaleasyRepositoriesInterface $repository_plugin */
        $repository_plugin = $this->pluginManagerDrupaleasyRepositories->createInstance($repository_plugin_id);
        foreach ($account->field_repository_url ?? [] as $url) {
          // Confirm the current URL can be handled by the current plugin.
          if ($repository_plugin->validate($url->uri)) {
            // Get the repository metadata.
            if ($repo_metadata = $repository_plugin->getRepo($url->uri)) {
              $repos_metadata += $repo_metadata;
            }
          }
        }
      }
    }

    $repos_updated = $this->updateRepositoryNodes($repos_metadata, $account);
    $repos_deleted = $this->deleteRepositoryNodes($repos_metadata, $account);
    return $repos_updated || $repos_deleted;
  }

  /**
   * Updates repository nodes for a given user.
   *
   * @param array<string, array<string, string|int>> $repos_info
   *   The repository metadata.
   * @param \Drupal\Core\Entity\EntityInterface $account
   *   The user.
   *
   * @return bool
   *   TRUE if successful.
   */
  protected function updateRepositoryNodes(array $repos_info, EntityInterface $account): bool {
    if (!$repos_info) {
      return TRUE;
    }

    // Prepare the storage and query stuff.
    /** @var \Drupal\node\NodeStorageInterface $node_storage */
    $node_storage = $this->entityTypeManager->getStorage('node');

    // Loop around all repositories.
    foreach ($repos_info as $key => $repo_info) {
      // Calculate the hash value.
      $hash = md5(serialize($repo_info));

      // Find repository nodes from the same user with the same machine name.
      $query = $node_storage->getQuery();
      $query->condition('type', 'repository')
        ->condition('uid', $account->id())
        ->condition('field_machine_name', $key)
        ->condition('field_source', $repo_info['source'])
        ->accessCheck(FALSE);
      $results = $query->execute();

      // Add or update.
      if ($results) {
        // Check if we need to update.
        /** @var \Drupal\node\NodeInterface $node */
        $node = $node_storage->load(reset($results));
        if ($hash != $node->get('field_hash')->value) {
          // Hash has changed, so we need to update the node.
          $node->setTitle($repo_info['label']);
          $node->set('field_description', $repo_info['description']);
          $node->set('field_machine_name', $key);
          $node->set('field_number_of_open_issues', $repo_info['num_open_issues']);
          $node->set('field_source', $repo_info['source']);
          $node->set('field_url', $repo_info['url']);
          $node->set('field_hash', $hash);
          if (!$this->dryRun) {
            $node->save();
          }
        }
      }
      else {
        // Add new repository node.
        $node = $node_storage->create([
          'type' => 'repository',
          'uid' => $account->id(),
          'title' => $repo_info['label'],
          'field_description' => $repo_info['description'],
          'field_machine_name' => $key,
          'field_number_of_open_issues' => $repo_info['num_open_issues'],
          'field_source' => $repo_info['source'],
          'field_url' => $repo_info['url'],
          'field_hash' => $hash,
        ]);
        if (!$this->dryRun) {
          $node->save();
        }
      }
    }
    return TRUE;
  }

  /**
   * Deletes repository nodes that have been removed from a user profile.
   *
   * @param array<string, array<string, string|int>> $repos_info
   *   The repository metadata.
   * @param \Drupal\Core\Entity\EntityInterface $account
   *   The user.
   *
   * @return bool
   *   TRUE if successful.
   */
  protected function deleteRepositoryNodes(array $repos_info, EntityInterface $account): bool {
    /** @var \Drupal\node\NodeStorageInterface $node_storage */
    $node_storage = $this->entityTypeManager->getStorage('node');

    $query = $node_storage->getQuery();
    $query->condition('type', 'repository')
      ->condition('uid', $account->id())
      ->accessCheck(FALSE);
    if ($repos_info) {
      $query->condition('field_machine_name', array_keys($repos_info), 'NOT IN');
    }
    $results = $query->execute();

    if ($results) {
      $nodes = $node_storage->loadMultiple($results);
      foreach ($nodes as $node) {
        if (!$this->dryRun) {
          $node->delete();
        }
      }
    }
    return TRUE;
  }

}
