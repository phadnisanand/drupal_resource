<?php

declare(strict_types=1);

namespace Drupal\drupaleasy_repositories\DrupaleasyRepositories;

/**
 * Interface for drupaleasy_repositories plugins.
 */
interface DrupaleasyRepositoriesInterface {

  /**
   * Returns the translated plugin label.
   *
   * @return string
   *   Returns the plugin label.
   */
  public function label(): string;

  /**
   * URL validator.
   *
   * @param string $uri
   *   The URI to validate.
   *
   * @return bool
   *   Returns TRUE if valid.
   */
  public function validate(string $uri): bool;

  /**
   * Returns help text for the plugin's URL required pattern.
   *
   * @return string
   *   Returns the url_help_text attribute value.
   */
  public function validateHelpText(): string;

  /**
   * Queries the repository source for metadata about the repository.
   *
   * @param string $uri
   *   The URI for the respository.
   *
   * @return array<string, array<string, string|int>>
   *   An array of repository metadata.
   */
  public function getRepo(string $uri): array;

}
