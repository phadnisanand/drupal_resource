<?php

declare(strict_types=1);

namespace Drupal\Tests\drupaleasy_repositories\Kernel;

use Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService;
use Drupal\KernelTests\KernelTestBase;
use PHPUnit\Framework\Attributes\Group;
use Drupal\Tests\drupaleasy_repositories\Traits\RepositoryContentTypeTrait;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test description.
 */
#[Group('drupaleasy_repositories')]
final class DrupaleasyRepositoriesServiceTest extends KernelTestBase {
  use RepositoryContentTypeTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'drupaleasy_repositories',
    'node',
    'field',
    'user',
    'link',
    'text',
    'key',
  ];

  /**
   * The DrupalEasy repositories service class.
   *
   * @var \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService
   */
  protected DrupaleasyRepositoriesService $drupaleasyRepositoriesService;

  /**
   * The admin user property.
   *
   * @var \Drupal\user\UserInterface
   */
  protected UserInterface $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupaleasyRepositoriesService = $this->container->get('drupaleasy_repositories.service');

    $this->createRepositoryContentType();

    // Enable the .yml repository plugin.
    $config = $this->config('drupaleasy_repositories.settings');
    $config->set('repositories_plugins', ['yml_remote' => 'yml_remote']);
    $config->save();

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');

    $aquaman_repo = $this->getTestRepo('aquaman-repository');
    $repo = reset($aquaman_repo);

    $this->adminUser = User::create(['name' => $this->randomString()]);
    $this->adminUser->save();

    $node = Node::create([
      'type' => 'repository',
      'uid' => $this->adminUser->id(),
      'title' => $repo['label'],
      'field_description' => $repo['description'],
      'field_machine_name' => array_key_first($aquaman_repo),
      'field_number_of_open_issues' => $repo['num_open_issues'],
      'field_source' => $repo['source'],
      'field_url' => $repo['url'],
      'field_hash' => $repo['hash'],
    ]);
    $node->save();
  }

  /**
   * Test callback.
   */
  public function testValidateHelpText(): void {
    self::assertEquals($this->drupaleasyRepositoriesService->getValidatorHelpText(), 'https://anything.anything/anything/anything.yml (or http or yaml.)');
  }

  /**
   * PHPUnit data provider for testIsUnique().
   *
   * @return array<int, array<int, bool|array<mixed>|int>>
   *   The expected value and arguments for the testIsUnique() method.
   */
  public static function providerTestIsUnique(): array {
    $aquaman = [
      'aquaman-repository' => [
        'label' => 'The Aquaman repository',
        'description' => 'This is where Aquaman keeps all his crime-fighting code.',
        'num_open_issues' => 6,
        'source' => 'yml_remote',
        'url' => 'http://example.com/aquaman-repo.yml',
      ],
    ];

    $superman = [
      'superman-repository' => [
        'label' => 'The Superman repository',
        'description' => 'This is where Superman keeps all his fortress of solitude code.',
        'num_open_issues' => 0,
        'source' => 'yml_remote',
        'url' => 'http://example.com/superman-repo.yml',
      ],
    ];

    return [
      [TRUE, $aquaman, 1],
      [FALSE, $aquaman, 999],
      [TRUE, $superman, 1],
      [TRUE, $superman, 999],
    ];
  }

  /**
   * Test the ability for the service to ensure that repositories are unique.
   *
   * @param bool $expected
   *   The expected return value from isUnique().
   * @param array<mixed> $repo_info
   *   The repository metadata to test.
   * @param int $uid
   *   The current user ID to test.
   */
  #[DataProvider('providerTestIsUnique')]
  public function testIsUnique(bool $expected, array $repo_info, int $uid): void {
    $reflection_is_unique = new \ReflectionMethod($this->drupaleasyRepositoriesService, 'isUnique');

    $actual = $reflection_is_unique->invokeArgs(
      $this->drupaleasyRepositoriesService,
      [$repo_info, $uid]
    );

    $this->assertEquals($expected, $actual);
  }

  /**
   * PHPUnit data provider for testValidateRepositoryUrls().
   *
   * @return array<mixed>
   *   The expected value and arguments for the testValidateRepositoryUrls()
   *   method.
   */
  public static function providerTestValidateRepositoryUrls(): array {
    return [
      ['', [['uri' => '/tests/assets/batman-repo.yml']]],
      ['was not found', [['uri' => '/tests/assets/aquaman-repo.yml']]],
      ['is not valid', [['uri' => '/tests/assets/batman-repo.ym']]],
    ];
  }

  /**
   * Test the ability for the service to ensure repositories are valid.
   *
   * @param string $expected
   *   The expected return value of validateRepositoryUrls().
   * @param array<mixed> $urls
   *   The URL to test.
   */
  #[DataProvider('providerTestValidateRepositoryUrls')]
  public function testValidateRepositoryUrls(string $expected, array $urls): void {
    /** @var \Drupal\Core\Extension\ModuleHandlerInterface $module_handler */
    $module_handler = $this->container->get('module_handler');
    $module = $module_handler->getModule('drupaleasy_repositories');
    $module_full_path = \Drupal::request()->getUri() . $module->getPath();

    foreach ($urls as $key => $url) {
      if (isset($url['uri'])) {
        $urls[$key]['uri'] = $module_full_path . $url['uri'];
      }
    }

    $actual = $this->drupaleasyRepositoriesService->validateRepositoryUrls($urls, 999);
    $this->assertEquals((bool) $expected, (bool) mb_stristr($actual, $expected));
  }

}
