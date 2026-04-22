<?php

namespace Drupal\my_module\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;

class MyService {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected MessengerInterface $messenger,
  ) {}

  public function doSomething(int $nodeId): ?string {
    $storage = $this->entityTypeManager->getStorage('node');
    $node = $storage->load($nodeId);

    if (!$node) {
      $this->messenger->addWarning("Node $nodeId not found.");
      return NULL;
    }
	//dump( $node);
    $this->messenger->addStatus("Loaded: " . $node->label());
    return $node->label();
  }

}
