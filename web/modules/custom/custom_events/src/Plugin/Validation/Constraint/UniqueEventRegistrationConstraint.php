<?php
// web/modules/custom/custom_events/src/Plugin/Validation/Constraint/UniqueEventRegistrationConstraint.php

declare(strict_types=1);

namespace Drupal\custom_events\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Ensures a user cannot register for the same event more than once.
 *
 * @Constraint(
 *   id = "UniqueEventRegistration",
 *   label = @Translation("Unique Event Registration", context = "Validation"),
 *   type = "entity:event_registration"
 * )
 */
class UniqueEventRegistrationConstraint extends Constraint {

  /**
   * Violation message when a duplicate registration is detected.
   */
  public string $message = 'User %uid is already registered for event %event_id.';

}
