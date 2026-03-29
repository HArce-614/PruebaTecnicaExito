<?php
// web/modules/custom/custom_events/src/Plugin/Validation/Constraint/UniqueEventRegistrationConstraintValidator.php

declare(strict_types=1);

namespace Drupal\custom_events\Plugin\Validation\Constraint;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the UniqueEventRegistration constraint.
 */
class UniqueEventRegistrationConstraintValidator extends ConstraintValidator {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $entity, Constraint $constraint): void {
    /** @var \Drupal\custom_events\Entity\EventRegistration $entity */
    if (!($entity instanceof \Drupal\custom_events\Entity\EventRegistration)) {
      return;
    }

    $eventId = (int) $entity->get('event_id')->target_id;
    $uid     = (int) $entity->get('uid')->target_id;

    if (!$eventId || !$uid) {
      return;
    }

    $query = $this->entityTypeManager
      ->getStorage('event_registration')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('event_id', $eventId)
      ->condition('uid', $uid);

    // Exclude the entity itself on updates.
    if (!$entity->isNew()) {
      $query->condition('id', (int) $entity->id(), '<>');
    }

    $ids = $query->range(0, 1)->execute();

    if (!empty($ids)) {
      /** @var \Drupal\custom_events\Plugin\Validation\Constraint\UniqueEventRegistrationConstraint $constraint */
      $this->context->addViolation(
        $constraint->message,
        ['%uid' => $uid, '%event_id' => $eventId],
      );
    }
  }

}
