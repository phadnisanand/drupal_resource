<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
  $rectorConfig->paths([
    __DIR__ . '/web/modules/custom',
  ]);

  // ✅ Modern approach
  $rectorConfig->sets([
    SetList::TYPE_DECLARATION,
    SetList::CODE_QUALITY,
  ]);
};