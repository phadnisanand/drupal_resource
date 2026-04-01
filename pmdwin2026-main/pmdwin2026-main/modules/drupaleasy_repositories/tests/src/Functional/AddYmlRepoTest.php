<?php

declare(strict_types=1);

namespace Drupal\Tests\drupaleasy_repositories\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\drupaleasy_repositories\Traits\RepositoryContentTypeTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * Test description.
 */
#[Group('drupaleasy_repositories')]
final class AddYmlRepoTest extends BrowserTestBase {
  use RepositoryContentTypeTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'drupaleasy_repositories',
  ];

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   If the user creation fails.
   */
  protected function setUp(): void {
    parent::setUp();

    // Enable the yml_remote plugin.
    $config = $this->config('drupaleasy_repositories.settings');
    $config->set('repositories_plugins', ['yml_remote' => 'yml_remote']);
    $config->save();

    // Create and login as a Drupal user with permission to access the
    // DrupalEasy Repositories Settings page. This is UID=2 because UID=1 is
    // created by
    // web/core/lib/Drupal/Core/Test/FunctionalTestSetupTrait::initUserSession().
    // This root user can be accessed via $this->rootUser.
    $admin_user = $this->drupalCreateUser(['configure drupaleasy repositories']);
    $this->drupalLogin($admin_user);

    // Add the repository content type.
    // $this->createRepositoryContentType();
    // Add the User Repository URL field.
    // @phpcs:ignore
    // $this->createUserRepositoryUrlField();

    // Ensure that the new Repository URL field is visible in the existing
    // user entity form mode.
    /** @var \Drupal\Core\Entity\EntityDisplayRepository $entity_display_repository  */
    $entity_display_repository = \Drupal::service('entity_display.repository');
    $entity_display_repository->getFormDisplay('user', 'user', 'default')
      ->setComponent('field_repository_url', ['type' => 'link_default'])
      ->save();
  }

  /**
   * Test that the settings page can be reached and works as expected.
   *
   * This tests that an admin user can access the settings page, select a
   * plugin to enable, and submit the page successfully.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  #[Test]
  public function testSettingsPage(): void {
    // Get a handle on the browsing session from the setup().
    $session = $this->assertSession();

    // Navigate to the DrupalEasy Repositories Settings page and confirm we can
    // reach it.
    $this->drupalGet('/admin/config/services/repositories');
    $session->statusCodeEquals(200);

    // Select the "Yml remote" checkbox.
    $edit = [
      'edit-repositories-plugins-yml-remote' => 'yml_remote',
    ];

    // Submit the form.
    $this->submitForm($edit, 'Save configuration');

    // Ensure no errors.
    $session->statusCodeEquals(200);
    $session->responseContains('The configuration options have been saved.');

    // Ensure that the proper checkbox is actually checked.
    $session->checkboxChecked('edit-repositories-plugins-yml-remote');
    // $session->checkboxNotChecked('edit-repositories-plugins-github');
  }

  /**
   * Test that the settings page cannot be reached without permission.
   *
   * @return void
   *   Returns nothing.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  #[Test]
  public function testUnprivilegedSettingsPage(): void {
    // Get a handle on the browsing session from the setup().
    $session = $this->assertSession();

    // Create and login as an unprivileged user.
    $authenticated_user = $this->drupalCreateUser(['access content']);
    $this->drupalLogin($authenticated_user);

    // Navigate to the DrupalEasy Repositories Settings page.
    $this->drupalGet('/admin/config/services/repositories');
    $session->statusCodeEquals(403);
  }

  /**
   * End-to-end test of adding a yml_remote repository.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  #[Test]
  public function testAddYmlRepo(): void {
    // Create and login as an unprivileged user.
    $user = $this->drupalCreateUser(['access content']);
    $this->drupalLogin($user);

    // Get a handle on the browsing session.
    $session = $this->assertSession();

    // Navigate to user profile edit page.
    $this->drupalGet('/user/' . $user->id() . '/edit');
    $session->statusCodeEquals(200);

    // Get the full path to the batman-repo.yml file.
    /** @var \Drupal\Core\Extension\ModuleHandlerInterface $module_handler */
    $module_handler = \Drupal::service('module_handler');
    $module = $module_handler->getModule('drupaleasy_repositories');
    $module_full_path = \Drupal::request()->getUri() . $module->getPath();

    // Add the .yml file to the proper field.
    $edit = [
      'edit-field-repository-url-0-uri' => $module_full_path . '/tests/assets/batman-repo.yml',
    ];

    // Submit the form.
    $this->submitForm($edit, 'Save');

    // Ensure no errors.
    $session->statusCodeEquals(200);
    $session->responseContains('The changes have been saved.');

    // Find the new repository node.
    $query = \Drupal::entityQuery('node');
    $query->condition('type', 'repository');
    $results = $query->accessCheck(FALSE)->execute();
    $session->assert(count($results) === 1, 'Either 0 or more than 1 repository nodes were found.');

    // Get the repository node that was just created.
    $entity_type_manager = \Drupal::entityTypeManager();
    $node_storage = $entity_type_manager->getStorage('node');
    $node = $node_storage->load(reset($results));

    // Check repository node values.
    $session->assert($node->field_machine_name->value === 'batman-repo', 'Machine name does not match.');
    $session->assert($node->field_source->value === 'yml_remote', 'Source does not match.');
    $session->assert($node->title->value === 'The Batman repository', 'Title does not match.');
    $session->assert($node->field_description->value === 'This is where Batman keeps all of his crime-fighting code.', 'Description does not match.');
    // @todo check to ensure === works for number of open issues.
    $session->assert((int) $node->field_number_of_open_issues->value === 6, 'Number of open issues does not match.');
  }

  /**
   * End-to-end test of removing a yml_remote repository.
   */
  #[Test]
  public function testRemoveYmlRepo(): void {
    // Create and login as an unprivileged user.
    $user = $this->drupalCreateUser(['access content']);
    $this->drupalLogin($user);

    // Get a handle on the browsing session.
    $session = $this->assertSession();

    // Navigate to user profile edit page.
    $this->drupalGet('/user/' . $user->id() . '/edit');
    $session->statusCodeEquals(200);

    // Get the full path to the batman-repo.yml file.
    /** @var \Drupal\Core\Extension\ModuleHandlerInterface $module_handler */
    $module_handler = \Drupal::service('module_handler');
    $module = $module_handler->getModule('drupaleasy_repositories');
    $module_full_path = \Drupal::request()->getUri() . $module->getPath();

    // Add the .yml file to the proper field.
    $edit = [
      'edit-field-repository-url-0-uri' => $module_full_path . '/tests/assets/batman-repo.yml',
    ];

    // Submit the form.
    $this->submitForm($edit, 'Save');

    // Ensure no errors.
    $session->statusCodeEquals(200);
    $session->responseContains('The changes have been saved.');

    // Find the new repository node.
    $query = \Drupal::entityQuery('node');
    $query->condition('type', 'repository');
    $results = $query->accessCheck(FALSE)->execute();
    $session->assert(count($results) === 1, 'Either 0 or more than 1 repository nodes were found.');

    // Add the .yml file to the proper field.
    $edit = [
      'edit-field-repository-url-0-uri' => '',
    ];

    // Submit the form.
    $this->submitForm($edit, 'Save');

    // Ensure no errors.
    $session->statusCodeEquals(200);
    $session->responseContains('The changes have been saved.');

    // Confirm there are no repository nodes.
    $query = \Drupal::entityQuery('node');
    $query->condition('type', 'repository');
    $results = $query->accessCheck(FALSE)->execute();
    $session->assert(count($results) === 0, 'The repository node did not get removed.');
  }

}
