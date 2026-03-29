<?php
// web/modules/custom/custom_events/src/Entity/EventRegistration.php

declare(strict_types=1);

namespace Drupal\custom_events\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityConstraintViolationList;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the EventRegistration entity.
 *
 * @ContentEntityType(
 *   id = "event_registration",
 *   label = @Translation("Event Registration"),
 *   label_collection = @Translation("Event Registrations"),
 *   label_singular = @Translation("event registration"),
 *   label_plural = @Translation("event registrations"),
 *   label_count = @PluralTranslation(
 *     singular = "@count event registration",
 *     plural = "@count event registrations",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *     "form" = {
 *       "add"    = "Drupal\custom_events\Form\EventRegistrationForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "event_registration",
 *   admin_permission = "administer events",
 *   entity_keys = {
 *     "id"   = "id",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "canonical"   = "/event-registration/{event_registration}",
 *     "add-form"    = "/event-registration/add",
 *     "delete-form" = "/event-registration/{event_registration}/delete",
 *     "collection"  = "/admin/content/event-registrations",
 *   },
 *   constraints = {
 *     "UniqueEventRegistration" = {}
 *   }
 * )
 */
class EventRegistration extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['event_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Event'))
      ->setDescription(t('The event this registration belongs to.'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'custom_event')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label'  => 'above',
        'type'   => 'entity_reference_label',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type'   => 'entity_reference_autocomplete',
        'weight' => 0,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size'           => '60',
          'placeholder'    => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Registrant'))
      ->setDescription(t('The user who registered for the event.'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label'  => 'above',
        'type'   => 'entity_reference_label',
        'weight' => 5,
      ])
      ->setDisplayOptions('form', [
        'type'   => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size'           => '60',
          'placeholder'    => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The Unix timestamp when the registration was created.'));

    return $fields;
  }

  /**
   * Returns the event entity referenced by this registration.
   */
  public function getEvent(): ?Event {
    return $this->get('event_id')->entity;
  }

  /**
   * Returns the UID of the registrant.
   */
  public function getOwnerId(): int {
    return (int) $this->get('uid')->target_id;
  }

}
