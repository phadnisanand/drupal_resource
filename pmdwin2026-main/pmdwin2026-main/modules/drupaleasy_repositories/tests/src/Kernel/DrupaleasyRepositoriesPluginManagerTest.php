<?php

declare(strict_types=1);

namespace Drupal\Tests\drupaleasy_repositories\Kernel;

use Drupal\drupaleasy_repositories\DrupaleasyRepositories\DrupaleasyRepositoriesPluginManager;
use Drupal\KernelTests\KernelTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Test description.
 */
#[Group('drupaleasy_repositories')]
final class DrupaleasyRepositoriesPluginManagerTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'drupaleasy_repositories',
    'key',
  ];

  /**
   * Our plugin manager.
   *
   * @var \Drupal\drupaleasy_repositories\DrupaleasyRepositories\DrupaleasyRepositoriesPluginManager
   */
  protected DrupaleasyRepositoriesPluginManager $manager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->manager = $this->container->get('plugin.manager.drupaleasy_repositories');
  }

  /**
   * Test callback.
   */
  public function testYmlRemoteInstance(): void {
    /** @var \Drupal\drupaleasy_repositories\DrupaleasyRepositories\DrupaleasyRepositoriesPluginBase $yml_remote_instance */
    $yml_remote_instance = $this->manager->createInstance('yml_remote');
    $plugin_def = $yml_remote_instance->getPluginDefinition();

    $this->assertInstanceOf('\Drupal\drupaleasy_repositories\Plugin\DrupaleasyRepositories\YmlRemote', $yml_remote_instance, 'Plugin type does not match.');
    $this->assertInstanceOf('\Drupal\drupaleasy_repositories\DrupaleasyRepositories\DrupaleasyRepositoriesPluginBase', $yml_remote_instance, 'Plugin parent type does not match.');
    $this->assertArrayHasKey('label', $plugin_def, 'The "Label" array key does not exist.');
    $this->assertTrue((string) $plugin_def['label'] === 'Remote .yml file', 'The "Label value is not correct.');
  }

}
