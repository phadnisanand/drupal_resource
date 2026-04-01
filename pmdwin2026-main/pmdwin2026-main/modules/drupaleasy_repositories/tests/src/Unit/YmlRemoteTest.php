<?php

declare(strict_types=1);

namespace Drupal\Tests\drupaleasy_repositories\Unit;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\drupaleasy_repositories\Plugin\DrupaleasyRepositories\YmlRemote;
use Drupal\key\KeyRepositoryInterface;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test description.
 */
#[Group('drupaleasy_repositories')]
final class YmlRemoteTest extends UnitTestCase {

  /**
   * The .yml Remote plugin.
   *
   * @var \Drupal\drupaleasy_repositories\Plugin\DrupaleasyRepositories\YmlRemote
   */
  protected YmlRemote $ymlRemote;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected MessengerInterface|MockObject $messenger;

  /**
   * The Key repository service.
   *
   * @var \Drupal\key\KeyRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected KeyRepositoryInterface|MockObject $keyRepository;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->messenger = $this->createMock(MessengerInterface::class);
    $this->keyRepository = $this->createMock(KeyRepositoryInterface::class);

    $this->ymlRemote = new YmlRemote([], 'yml_remote', [], $this->messenger, $this->keyRepository);
  }

  /**
   * Data provider for testValidate method.
   *
   * @return array<int, array<int, string|bool>>
   *   Array of test strings and expected results.
   */
  public static function validateProvider(): array {
    return [
      [
        'A test string',
        FALSE,
      ],
      [
        'https://pmdwin2026.ddev.site/modules/custom/drupaleasy_repositories/tests/assets/batman-repo.yml',
        TRUE,
      ],
      [
        'http://www.mysite.com/anything.yml',
        TRUE,
      ],
      [
        'https://www.mysite.com/anything.yml',
        TRUE,
      ],
      [
        'https://www.mysite.com/anything.yaml',
        TRUE,
      ],
      [
        '/var/www/html/anything.yaml',
        FALSE,
      ],
      [
        'https://www.mysite.com/some%20directory/anything.yml',
        TRUE,
      ],
      [
        'https://www.my-site.com/some%20directory/anything.yaml',
        TRUE,
      ],
      [
        'https://localhost/some%20directory/anything.yaml',
        TRUE,
      ],
      [
        'https://dev.www.mysite.com/anything.yml',
        TRUE,
      ],
    ];
  }

  /**
   * Test that the URL validator works.
   *
   * @param string $test_string
   *   The test uri string.
   * @param bool $expected
   *   The expected result.
   */
  #[DataProvider('validateProvider')]
  public function testValidate(string $test_string, bool $expected): void {
    $this->assertEquals($expected, $this->ymlRemote->validate($test_string), "'{$test_string}' does not return '{$expected}'");
  }

  /**
   * Test the getRepo() method.
   */
  public function testGetRepo(): void {
    $file_path = __DIR__ . '/../../assets/batman-repo.yml';
    $repo = $this->ymlRemote->getRepo($file_path);
    $machine_name = array_key_first($repo);

    // Assertions to confirm that the $repo array is what we expect.
    self::assertEquals('batman-repo', $machine_name, "The expected machine name does not match what was provided: '{$machine_name}'.");
    $repo = reset($repo);
    self::assertEquals('The Batman repository', $repo['label'], "The expected label does not match what was provided: '{$repo['label']}'.");
    self::assertEquals('This is where Batman keeps all of his crime-fighting code.', $repo['description'], "The expected description does not match what was provided: '{$repo['description']}'.");
    self::assertEquals(6, $repo['num_open_issues'], "The expected number of open issues does not match what was provided: '{$repo['num_open_issues']}'.");
    self::assertEquals('yml_remote', $repo['source'], "The expected source does not match what was provided: '{$repo['source']}'.");
    self::assertEquals($file_path, $repo['url'], "The expected url does not match what was provided: '{$repo['url']}'.");
  }

}
