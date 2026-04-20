<?php

namespace Drupal\my_fetch_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Url;

class DataController extends ControllerBase {

public function page() {
  return [
    '#theme' => 'my_fetch_template',
    '#attached' => [
      'library' => [
        'my_fetch_module/app',
      ],
      'drupalSettings' => [
        'myModule' => [
          'apiUrl' => Url::fromRoute('my-fetch-api')->toString(),
        ],
      ],
    ],
  ];
}

  public function getData() {
    $data = [
      ['name' => 'Anand', 'role' => 'Developer'],
      ['name' => 'John', 'role' => 'Tester'],
    ];

    return new JsonResponse($data);
  }

}
