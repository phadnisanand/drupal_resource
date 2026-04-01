<?php

namespace Drupal\Tests\drupaleasy_repositories\Kernel;

use Drupal\Core\Extension\ModuleHandler;
use Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\drupaleasy_repositories\Traits\RepositoryContentTypeTrait;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;

/**
 * Test description.
 *
 * @group drupaleasy_repositories
 */
class DrupaleasyRepositoriesServiceTest extends KernelTestBase {

  use RepositoryContentTypeTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'drupaleasy_repositories',
    'node',
    'system',
    'field',
    'text',
    'link',
    'user',
    'key',
  ];

  /**
   * The drupaleasy_repositories service.
   *
   * @var \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService
   */
  protected DrupaleasyRepositoriesService $drupaleasyRepositoriesService;

  /**
   * The admin user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected UserInterface $adminUser;

  /**
   * The modules handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected ModuleHandler $moduleHandler;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupaleasyRepositoriesService = $this->container->get('drupaleasy_repositories.service');
    $this->createRepositoryContentType();

    $this->moduleHandler = $this->container->get('module_handler');

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');

    $aquaman_repo = $this->getRepo('aquaman');
    $repo = reset($aquaman_repo);

    $this->adminUser = User::create([
      'name' => $this->randomString(),
    ]);
    $this->adminUser->save();

    $node = Node::create([
      'type' => 'repository',
      'title' => $repo['label'],
      'field_machine_name' => array_key_first($aquaman_repo),
      'field_url' => $repo['url'],
      'field_hash' => '06ec2efe7005ae32f624a9c2d28febd5',
      'field_number_of_issues' => $repo['num_open_issues'],
      'field_source' => $repo['source'],
      'field_description' => $repo['description'],
      'user_id' => $this->adminUser->id(),
    ]);
    $node->save();

    // Enable the .yml repository plugin.
    $config = $this->config('drupaleasy_repositories.settings');
    $config->set('repositories', ['yml_remote' => 'yml_remote']);
    $config->save();
  }

  /**
   * Data provider for testIsUnique().
   *
   * @return array
   *   Test data and expected results.
   */
  public function providerTestIsUnique(): array {
    $unique_repo_info = $this->getRepo('superman');
    return [
      [FALSE, $this->getRepo('aquaman')],
      [TRUE, $unique_repo_info],
    ];
  }

  /**
   * Test the DrupaleasyRepositoriesService::isUnique method.
   *
   * @param bool $expected
   *   Whether we expect repo to be unique.
   * @param array $repo
   *   The repo to check.
   *
   * @covers \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService::isUnique
   * @dataProvider providerTestIsUnique
   * @test
   *
   * @throws \ReflectionException
   */
  public function testIsUnique(bool $expected, array $repo): void {
    // Use reflection to make isUnique() public.
    $reflection_is_unique = new \ReflectionMethod($this->drupaleasyRepositoriesService, 'isUnique');
    $reflection_is_unique->setAccessible(TRUE);
    $actual = $reflection_is_unique->invokeArgs(
      $this->drupaleasyRepositoriesService,
      // Use $uid = 999 to ensure it is different from $this->adminUser.
      [$repo, 999]
    );

    $this->assertEquals($expected, $actual);
  }

  /**
   * Test the ability for the service to ensure repositories are valid.
   *
   * @covers \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService::validateRepositoryUrls
   * @dataProvider providerValidateRepositoryUrls
   * @test
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function testValidateRepositoryUrls(string $expected, array $urls): void {
    $this->moduleHandler = \Drupal::service('module_handler');
    // Get the full path to the test .yml file.
    /** @var \Drupal\Core\Extension\Extension $module */
    $module = $this->moduleHandler->getModule('drupaleasy_repositories');
    $module_full_path = \Drupal::request()->getUri() . $module->getPath();

    foreach ($urls as $key => $url) {
      if (isset($url['uri'])) {
        $urls[$key]['uri'] = $module_full_path . $url['uri'];
      }
    }

    $actual = $this->drupaleasyRepositoriesService->validateRepositoryUrls($urls, 999);
    if ($expected) {
      $this->assertTrue((bool) mb_stristr($actual, $expected), "The URLs' validation does not match the expected value. Actual: {$actual}, Expected: {$expected}");
    }
    else {
      $this->assertEquals($expected, $actual, "The URLs' validation does not match the expected value. Actual: {$actual}, Expected: {$expected}");
    }
  }

  /**
   * Data provider for testValidateRepositoryUrls().
   *
   * @return array
   *   Test data and expected results.
   */
  public function providerValidateRepositoryUrls(): array {
    // This is run before setup() and other things so $this->container
    // isn't available here!
    return [
      ['', [['uri' => '/tests/assets/batman-repo.yml']]],
      ['is not valid', [['uri' => '/tests/assets/batman-repo.ym']]],
    ];
  }

  /**
   * Returns sample repository info.
   *
   * @return array
   *   The sample repository info.
   */
  protected function getRepo(string $repo): array {
    // The order of elements of this array matters when calculating the hash.
    return match($repo) {
      'aquaman' => [
        'aquaman-repository' => [
          'label' => 'The Aquaman repository',
          'description' => 'This is where Aquaman keeps all his crime-fighting code.',
          'num_open_issues' => 6,
          'source' => 'yml',
          'url' => 'http://example.com/aquaman-repo.yml',
        ],
      ],
      'superman' => [
        'superman-repo' => [
          'label' => 'The Superman repository',
          'description' => 'This is where Superman keeps all his crime-fighting code.',
          'num_open_issues' => 0,
          'source' => 'yml',
          'url' => 'https://example.com/superman-repo.yml',
        ],
      ],
      default => []
    };
  }

}
