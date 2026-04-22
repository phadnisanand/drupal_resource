<?php

namespace Drupal\my_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\my_module\Service\MyService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MyController extends ControllerBase {

  public function __construct(
    #[Autowire(service: 'my_module.my_service')]
    protected MyService $myService,
  ) {}


  public function index(): array {
    $result = $this->myService->doSomething(1);

    return [
      '#markup' => $result
        ? "<p>Result: $result</p>"
        : '<p>No result.</p>',
    ];
  }

}
