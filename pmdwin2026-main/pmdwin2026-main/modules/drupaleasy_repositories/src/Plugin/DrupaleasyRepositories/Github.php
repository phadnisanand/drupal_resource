<?php

declare(strict_types=1);

namespace Drupal\drupaleasy_repositories\Plugin\DrupaleasyRepositories;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\drupaleasy_repositories\Attribute\DrupaleasyRepositories;
use Drupal\drupaleasy_repositories\DrupaleasyRepositories\DrupaleasyRepositoriesPluginBase;
use Github\Client;
use Github\AuthMethod;

/**
 * Plugin implementation of the drupaleasy_repositories.
 */
#[DrupaleasyRepositories(
  id: 'github',
  label: new TranslatableMarkup('GitHub'),
  url_help_text: new TranslatableMarkup('https://github.com/vendor/name'),
  description: new TranslatableMarkup('GitHub.com'),
)]
final class Github extends DrupaleasyRepositoriesPluginBase {

  /**
   * The KnpLabs GitHub client used to make API calls.
   *
   * @var \Github\Client
   */
  protected Client $client;

  /**
   * {@inheritdoc}
   */
  public function validate(string $uri): bool {
    $pattern = '|^https://github.com/[a-zA-Z0-9_\-]+/[a-zA-Z0-9_\-]+$|';
    return preg_match($pattern, $uri) === 1;
  }

  /**
   * {@inheritdoc}
   */
  public function getRepo(string $uri): array {
    // Parse $uri for vendor and name.
    $all_parts = parse_url($uri);
    $path_parts = explode('/', $all_parts['path']);

    // Set up authentication.
    $this->setAuthentication();

    // Make the API call and get the metadata.
    try {
      /** @var \Github\Api\Repo $repo */
      $repo = $this->client->api('repo');
      $repo_metadata = $repo->show($path_parts[1], $path_parts[2]);
    }
    catch (\Throwable $th) {
      $this->messenger->addMessage($this->t('GitHub error: @error', [
        '@error' => $th->getMessage(),
      ]));
      return [];
    }

    // Put the metadata in our common format.
    return $this->mapToCommonFormat(
      $repo_metadata['full_name'],
      $repo_metadata['name'],
      $repo_metadata['description'],
      $repo_metadata['open_issues_count'],
      $repo_metadata['html_url']
    );
  }

  /**
   * Add GitHub authentication info to $client object.
   */
  protected function setAuthentication(): void {
    $this->client = new Client();

    $github_key = $this->keyRepository->getKey('github')->getKeyValues();

    // The authenticate() method does not actually call the GitHub API,
    // rather it only stores the authentication info in $this->client for use
    // when $this->client makes an API call that requires authentication.
    $this->client->authenticate($github_key['username'], $github_key['personal_access_token'], AuthMethod::CLIENT_ID);
  }

}
