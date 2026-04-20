<?php

namespace Drupal\htmx_demo\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\ClientInterface;

class HtmxDemoController extends ControllerBase {
	
 protected $httpClient;

  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
  }
  
   public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client')
    );
  }
  
  public function page() {

    return [
      '#theme' => 'htmx_demo',
      '#attached' => [
        'library' => ['htmx_demo/htmx'],
      ],
    ];
  }

  public function data() {
	  
     try {
      $response = $this->httpClient->get('https://jsonplaceholder.typicode.com/todos');
      $data = json_decode($response->getBody()->getContents(), TRUE);
      $html = '';
	  foreach ($data as $todo) {
		 $html .= '<div>' . $todo['title'] . '</div>';
	  }
	  return new \Symfony\Component\HttpFoundation\Response($html);
    }
    catch (\Exception $e) {
      return [
        '#markup' => 'Error: ' . $e->getMessage(),
      ];
    }
  }

}
