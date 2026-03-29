<?php
// web/modules/custom/custom_events/src/Service/EventRegistrationService.php

declare(strict_types=1);

namespace Drupal\custom_events\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\custom_events\Entity\EventRegistration;

/**
 * Business logic for event registrations.
 */
class EventRegistrationService {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly AccountInterface $currentUser,
  ) {}

  /**
   * Registers the current user for the given event.
   *
   * @param int $eventId
   *   The ID of the custom_event entity.
   *
   * @return \Drupal\custom_events\Entity\EventRegistration
   *   The newly created and saved EventRegistration entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   When saving the entity fails.
   * @throws \RuntimeException
   *   When the user is already registered for the event.
   */
  public function register(int $eventId): EventRegistration {
    $uid = (int) $this->currentUser->id();

    if ($this->isRegistered($eventId, $uid)) {
      throw new \RuntimeException(
        sprintf('User %d is already registered for event %d.', $uid, $eventId)
      );
    }

    /** @var \Drupal\custom_events\Entity\EventRegistration $registration */
    $registration = $this->entityTypeManager
      ->getStorage('event_registration')
      ->create([
        'event_id' => $eventId,
        'uid'      => $uid,
      ]);

    $registration->save();

    return $registration;
  }

  /**
   * Checks whether a specific user is registered for a given event.
   *
   * @param int $eventId
   *   The ID of the custom_event entity.
   * @param int|null $uid
   *   The user ID to check. Defaults to the current user when NULL.
   *
   * @return bool
   *   TRUE if the user already has a registration for this event.
   */
  public function isRegistered(int $eventId, ?int $uid = NULL): bool {
    $uid ??= (int) $this->currentUser->id();

    $ids = $this->entityTypeManager
      ->getStorage('event_registration')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('event_id', $eventId)
      ->condition('uid', $uid)
      ->range(0, 1)
      ->execute();

    return !empty($ids);
  }

  /**
   * Returns the total number of registrations for a given event.
   *
   * @param int $eventId
   *   The ID of the custom_event entity.
   *
   * @return int
   *   The registration count.
   */
  public function getCount(int $eventId): int {
    return (int) $this->entityTypeManager
      ->getStorage('event_registration')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('event_id', $eventId)
      ->count()
      ->execute();
  }

}
