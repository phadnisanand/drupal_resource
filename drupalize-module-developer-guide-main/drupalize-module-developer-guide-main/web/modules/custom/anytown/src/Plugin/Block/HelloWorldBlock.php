<?php

declare(strict_types=1);

namespace Drupal\anytown\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Provides a hello world block.
 */
#[Block(
  id: 'anytown_hello_world',
  admin_label: new TranslatableMarkup('Hello World'),
  category: new TranslatableMarkup('Custom')
)]
class HelloWorldBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $current_time = date('h:i:sa');

    $items = [];
    $items[] = $this->t('Hello, World!');
    $items[] = $this->t('The current time is @time.', ['@time' => $current_time]);
    $items[] = $this->t('This is another line of text.');

    $build['content'] = [
      '#theme' => 'item_list',
      '#items' => $items,
    ];
    return $build;
  }

}