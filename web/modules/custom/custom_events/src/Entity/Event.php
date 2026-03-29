<?php
// web/modules/custom/custom_events/src/Entity/Event.php

declare(strict_types=1);

namespace Drupal\custom_events\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the Event entity.
 *
 * @ContentEntityType(
 *   id = "custom_event",
 *   label = @Translation("Event"),
 *   label_collection = @Translation("Events"),
 *   label_singular = @Translation("event"),
 *   label_plural = @Translation("events"),
 *   label_count = @PluralTranslation(
 *     singular = "@count event",
 *     plural = "@count events",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\custom_events\EventListBuilder",
 *     "form" = {
 *       "add"     = "Drupal\custom_events\Form\EventForm",
 *       "edit"    = "Drupal\custom_events\Form\EventForm",
 *       "delete"  = "Drupal\custom_events\Form\EventDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "custom_event",
 *   admin_permission = "administer events",
 *   entity_keys = {
 *     "id"     = "id",
 *     "label"  = "title",
 *     "uuid"   = "uuid",
 *     "uid"    = "uid",
 *     "owner"  = "uid",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical"   = "/events/{custom_event}",
 *     "add-form"    = "/events/add",
 *     "edit-form"   = "/events/{custom_event}/edit",
 *     "delete-form" = "/events/{custom_event}/delete",
 *     "collection"  = "/admin/content/events",
 *   },
 * )
 */
class Event extends ContentEntityBase implements EntityOwnerInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title of the event.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label'    => 'hidden',
        'type'     => 'string',
        'weight'   => -10,
      ])
      ->setDisplayOptions('form', [
        'type'   => 'string_textfield',
        'weight' => -10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Description'))
      ->setDescription(t('A detailed description of the event.'))
      ->setDisplayOptions('view', [
        'label'  => 'above',
        'type'   => 'text_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type'   => 'text_textarea',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['country'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Country'))
      ->setDescription(t('The country where the event takes place (ISO 3166-1 alpha-2 code or full name).'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label'  => 'above',
        'type'   => 'string',
        'weight' => 5,
      ])
      ->setDisplayOptions('form', [
        'type'   => 'string_textfield',
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['event_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Event Date'))
      ->setDescription(t('The date and time when the event occurs.'))
      ->setRequired(TRUE)
      ->setSetting('datetime_type', 'datetime')
      ->setDisplayOptions('view', [
        'label'    => 'above',
        'type'     => 'datetime_default',
        'settings' => ['format_type' => 'medium'],
        'weight'   => 10,
      ])
      ->setDisplayOptions('form', [
        'type'   => 'datetime_default',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Author'))
      ->setDescription(t('The user who created the event.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback(static::class . '::getDefaultEntityOwner')
      ->setDisplayOptions('view', [
        'label'  => 'above',
        'type'   => 'entity_reference_label',
        'weight' => 15,
      ])
      ->setDisplayOptions('form', [
        'type'   => 'entity_reference_autocomplete',
        'weight' => 15,
        'settings' => [
          'match_operator'    => 'CONTAINS',
          'size'              => '60',
          'placeholder'       => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Published'))
      ->setDescription(t('Whether the event is published and visible to users.'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type'   => 'boolean_checkbox',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The Unix timestamp when the event was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The Unix timestamp when the event was last edited.'));

    return $fields;
  }

}
