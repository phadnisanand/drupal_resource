<?php

namespace Drupal\drupaleasy_repositories;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleUninstallValidatorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Prevents DrupalEasy Repositories from being uninstalled if data exists.
 *
 * If any Repository URL field values exist, do not allow the module to be
 * uninstalled.
 */
class DrupalEasyRepositoriesUninstallValidator implements ModuleUninstallValidatorInterface {
  use StringTranslationTrait;

  /**
   * Constructs a new DrupaleasyRepositoriesUninstallValidator.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    TranslationInterface $string_translation
  ) {
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function validate(mixed $module): array
  {
    $reasons = [];
    if ($module == 'drupaleasy_repositories') {
      if ($this->hasRepositoryUrlData()) {
        $reasons[] = $this->t('To uninstall DrupalEasy Repositories, delete all Repository URL values from user profiles');
      }
    }

    return $reasons;
  }

  /**
   * Check if any user profiles
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function hasRepositoryUrlData(): bool {
    $users = $this->entityTypeManager->getStorage('user')->getQuery()
      ->condition('field_repository_url', NULL, 'IS NOT NULL')
      ->accessCheck(FALSE)
      ->range(0, 1)
      ->execute();
    return !empty($users);
  }

}
