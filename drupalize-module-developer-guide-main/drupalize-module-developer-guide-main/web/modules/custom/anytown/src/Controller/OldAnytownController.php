<?php

declare(strict_types=1);

namespace Drupal\anytown\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Anytown routes.
 * This solution became outdated after lesson:
 * https://drupalize.me/tutorial/use-service-controller.
 */
class OldAnytownController extends ControllerBase {

  /**
   * Returns a renderable array for a Anytown weather page.
   *
   * Return []
   */
  public function build(string $style) {
    // Style should be one of 'short', or 'extended'. And default to 'short'.
    $style = (in_array($style, ['short', 'extended'])) ? $style : 'short';
    $location = 'City Market';
    $forecast = 'Sunny with a chance of meatballs.';

    $build['content'] = [
//    Previous solution ⬇️ (before using twig)
//      '#markup' => $this->t('The weather forecast for this week is sunny with a chance of meatballs.'),
//    Updated solution ⬇️ (using twig)
      '#theme' => 'anytown_weather', // coming from .module theme hook
      '#location' => $location,
      '#forecast' => $forecast,
      '#style' => $style,
    ];

//    Another way to dynamically change markup based on route params, other
//    way being passing variable directly like above and checking in twig
    if ($style === 'extended') {
      $build['content_extended'] = [
        '#type' => 'markup',
        '#markup' => '<p><strong>Bonus tip:</strong> Stock up on salt and firewood now.</p>',
      ];
    }

    return $build;
  }

}
