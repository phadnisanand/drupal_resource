<?php

declare(strict_types=1);

namespace Drupal\anytown\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Logger\RfcLogLevel;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
//use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Anytown routes.
 */
class AnytownController extends ControllerBase {

  use AutowireTrait;

  /**
   * HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  private $httpClient;

  /**
   * Logging service, set to 'anytown' channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private $logger;

  /**
   * WeatherPage controller constructor.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   HTTP client.
   */
  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
    // getLogger() comes from ControllerBase class we're extending so no need to DI it
    $this->logger = $this->getLogger('anytown');
  }

  /**
   * {@inheritDoc}
   * Would be needed if not using AutowireTrait which handles DI for you. Behind the scenes this is what the
   * AutowireTrait does, initializing an instance of AnytownController class and passing in http_client service.
   * Would also need to remove use AutowireTrait statements
   * Comes from ContainerInjectionInterface which is implemented in ControllerBase which we're extending
   */
//  public static function create(ContainerInterface $container) {
//    return new static(
//      $container->get('http_client')
//    );
//  }

  /**
   * Returns a renderable array for Anytown weather page.
   *
   * Return []
   */
  public function build(string $style): array {
    // Style should be one of 'short', or 'extended'. And default to 'short'.
    $style = (in_array($style, ['short', 'extended'])) ? $style : 'short';

    $url = 'https://module-developer-guide-demo-site.ddev.site/modules/custom/anytown/data/weather_forecast.json';
    $data = NULL;

    try {
      $response = $this->httpClient->get($url);
      $data = json_decode($response->getBody()->getContents());
    }
    catch (RequestException $e) {
      $this->logger->log(RfcLogLevel::WARNING, $e->getMessage());
    }

    if ($data) {
      $forecast = '<ul>';
      foreach ($data->list as $day) {
        $weekday = ucfirst($day->day);
        $description = array_shift($day->weather)->description;
        // Convert units in Kelvin to Fahrenheit.
        $high = round(($day->main->temp_max - 273.15) * 9 / 5 + 32);
        $low = round(($day->main->temp_min - 273.15) * 9 / 5 + 32);
        $forecast .= "<li>$weekday will be <em>$description</em> with a high of $high and a low of $low.</li>";
      }
      $forecast .= '</ul>';
    }
    else {
      $forecast = '<p>Could not get the weather forecast. Dress for anything.</p>';
    }

    $output = "<p>Check out this weekend's weather forecast and come prepared. The market is mostly outside, and takes place rain or shine.</p>";
    $output .= $forecast;

    return [
      '#markup' => $output,
    ];
  }

}
