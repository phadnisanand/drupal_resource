<?php

declare(strict_types=1);

namespace Drupal\drupaleasy_repositories\Plugin\DrupaleasyRepositories;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\drupaleasy_repositories\Attribute\DrupaleasyRepositories;
use Drupal\drupaleasy_repositories\DrupaleasyRepositories\DrupaleasyRepositoriesPluginBase;
use Drupal\Component\Serialization\Yaml;

/**
 * Plugin implementation of the drupaleasy_repositories.
 */
#[DrupaleasyRepositories(
  id: 'yml_remote',
  label: new TranslatableMarkup('Remote .yml file'),
  url_help_text: new TranslatableMarkup('https://anything.anything/anything/anything.yml (or http or yaml.)'),
  description: new TranslatableMarkup('Remote .yml file that includes repository metadata.'),
)]
final class YmlRemote extends DrupaleasyRepositoriesPluginBase {

  /**
   * {@inheritdoc}
   */
  public function validate(string $uri): bool {
    $pattern = '|^https?://[a-zA-Z0-9.\-]+/[a-zA-Z0-9_\-.%/]+\.ya?ml$|';
    return preg_match($pattern, $uri) === 1;
  }

  /**
   * {@inheritdoc}
   */
  public function getRepo(string $uri): array {
    // Temporarily set the PHP error handler to this custom one that does
    // not do anything if a PHP E_WARNING is thrown.
    // This is basically telling PHP that we are going handle errors of type
    // E_WARNING until we say otherwise.
    set_error_handler(function () {
      // If FALSE is returned, then the default PHP error handler is run.
      return TRUE;
    },
    E_WARNING);

    // If (file($uri)) {
    // restore_error_handler();
    // Get and read the file.
    if ($file_contents = file_get_contents($uri)) {
      restore_error_handler();
      // Parse the file.
      $repo_info = Yaml::decode($file_contents);
      $machine_name = array_key_first($repo_info);
      $repo_metadata = reset($repo_info);
      $label = $repo_metadata['label'];
      $description = $repo_metadata['description'];
      $number_of_open_issues = $repo_metadata['num_open_issues'];
      return $this->mapToCommonFormat($machine_name, $label, $description, $number_of_open_issues, $uri);
    }
    // }
    restore_error_handler();
    return [];
  }

}
