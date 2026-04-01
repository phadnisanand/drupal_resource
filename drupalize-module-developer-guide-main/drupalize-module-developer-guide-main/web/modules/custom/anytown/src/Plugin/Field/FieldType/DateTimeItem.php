<?php

namespace Drupal\anytown\Plugin\Field\FieldType;

use Drupal\Core\Field\Attribute\FieldType;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Copied from: namespace Drupal\datetime\Plugin\Field\FieldType, NOT A USABLE CLASS JUST FOR LEARNING PURPOSES.*/
/**
 * Plugin implementation of the 'datetime' field type.
 */
#[FieldType(
  id: "datetime",
  label: new TranslatableMarkup("Date"),
  description: [
    new TranslatableMarkup("Ideal when date and time needs to be input by users, like event dates and times"),
    new TranslatableMarkup("Date or date and time stored in a readable string format"),
    new TranslatableMarkup("Easy to read and understand for humans"),
  ],
  category: "date_time",
  default_widget: "datetime_default",
  default_formatter: "datetime_default",
  constraints: ["DateTimeFormat" => []]
)]
class DateTimeItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
      'datetime_type' => 'datetime',
    ] + parent::defaultStorageSettings();
  }

  /**
   * Value for the 'datetime_type' setting: store only a date.
   */
  const DATETIME_TYPE_DATE = 'date';

  /**
   * Value for the 'datetime_type' setting: store a date and time.
   */
  const DATETIME_TYPE_DATETIME = 'datetime';

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('datetime_iso8601')
      ->setLabel(new TranslatableMarkup('Date value'))
      ->setRequired(TRUE);

    $properties['date'] = DataDefinition::create('any')
      ->setLabel(new TranslatableMarkup('Computed date'))
      ->setDescription(new TranslatableMarkup('The computed DateTime object.'))
      ->setComputed(TRUE)
      ->setClass('\Drupal\datetime\DateTimeComputed')
      ->setSetting('date source', 'value');

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'description' => 'The date value.',
          'type' => 'varchar',
          'length' => 20,
        ],
      ],
      'indexes' => [
        'value' => ['value'],
      ],
    ];
  }

}
