<?php

declare(strict_types=1);

namespace Drupal\Tests\drupaleasy_repositories\Traits;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;

/**
 * Provides helper methods for creating necessary IA for tests.
 */
trait RepositoryContentTypeTrait {

  /**
   * Create a repository content type with fields.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createRepositoryContentType(): void {
    // Create the content type.
    NodeType::create(['type' => 'repository', 'name' => 'Repository'])->save();

    // Add fields to the content type.
    // Description.
    FieldStorageConfig::create([
      'field_name' => 'field_description',
      'entity_type' => 'node',
      'type' => 'text_long',
      'cardinality' => 1,
    ])->save();
    FieldConfig::create([
      'entity_type' => 'node',
      'bundle' => 'repository',
      'field_name' => 'field_description',
      'label' => 'Description',
    ])->save();

    // Hash.
    FieldStorageConfig::create([
      'field_name' => 'field_hash',
      'entity_type' => 'node',
      'type' => 'string',
      'cardinality' => 1,
    ])->save();
    FieldConfig::create([
      'entity_type' => 'node',
      'bundle' => 'repository',
      'field_name' => 'field_hash',
      'label' => 'Hash',
    ])->save();

    // Machine name.
    FieldStorageConfig::create([
      'field_name' => 'field_machine_name',
      'entity_type' => 'node',
      'type' => 'string',
      'cardinality' => 1,
    ])->save();
    FieldConfig::create([
      'entity_type' => 'node',
      'bundle' => 'repository',
      'field_name' => 'field_machine_name',
      'label' => 'Machine name',
    ])->save();

    // Number of open issues.
    FieldStorageConfig::create([
      'field_name' => 'field_number_of_issues',
      'entity_type' => 'node',
      'type' => 'integer',
      'cardinality' => 1,
    ])->save();
    FieldConfig::create([
      'entity_type' => 'node',
      'bundle' => 'repository',
      'field_name' => 'field_number_of_issues',
      'label' => 'Number of open issues',
    ])->save();

    // Source.
    FieldStorageConfig::create([
      'field_name' => 'field_source',
      'entity_type' => 'node',
      'type' => 'string',
      'cardinality' => 1,
    ])->save();
    FieldConfig::create([
      'entity_type' => 'node',
      'bundle' => 'repository',
      'field_name' => 'field_source',
      'label' => 'Source',
    ])->save();

    // URL.
    FieldStorageConfig::create([
      'field_name' => 'field_url',
      'entity_type' => 'node',
      'type' => 'link',
      'cardinality' => 1,
    ])->save();
    FieldConfig::create([
      'entity_type' => 'node',
      'bundle' => 'repository',
      'field_name' => 'field_url',
      'label' => 'URL',
    ])->save();

    // @todo Configure display modes of the content type?
  }

  /**
   * Creates a User Repository URL field.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createUserRepositoryUrlField(): void {
    FieldStorageConfig::create([
      'field_name' => 'field_repository_url',
      'entity_type' => 'user',
      'type' => 'link',
      'cardinality' => -1,
    ])->save();
    FieldConfig::create([
      'entity_type' => 'user',
      'bundle' => 'user',
      'field_name' => 'field_repository_url',
      'label' => 'Repository URL',
    ])->save();
  }

  /**
   * Helper function to return sample repository metadata.
   *
   * @param string $repo_name
   *   The machine name for the repository to get.
   *
   * @return array<string, array<string, string|int>>
   *   The repository metadata.
   */
  protected function getTestRepo(string $repo_name): array {
    switch ($repo_name) {
      case 'wonder-woman-repository':
        $repo_info = [
          'label' => 'The Wonder Woman repository',
          'description' => 'This is where Wonder Woman keeps all of her crime-fighting code',
          'num_open_issues' => 1,
          'source' => 'yml_remote',
          'url' => 'http://example.com/ww-repo.yml',
        ];
        $repo_info['hash'] = md5(serialize($repo_info));
        return [
          'wonder-woman-repository' => $repo_info,
        ];

      default:
        $repo_info = [
          'label' => 'The Aquaman repository',
          'description' => 'This is where Aquaman keeps all of his crime-fighting code',
          'num_open_issues' => 6,
          'source' => 'yml_remote',
          'url' => 'http://example.com/aquaman-repo.yml',
        ];
        $repo_info['hash'] = md5(serialize($repo_info));
        return [
          'aquaman-repository' => $repo_info,
        ];
    }
  }

}
