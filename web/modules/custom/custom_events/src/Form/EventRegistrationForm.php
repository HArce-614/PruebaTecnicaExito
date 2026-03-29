<?php
// web/modules/custom/custom_events/src/Form/EventRegistrationForm.php

declare(strict_types=1);

namespace Drupal\custom_events\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\custom_events\Service\EventRegistrationService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * AJAX-driven registration form embedded on the event listing/detail page.
 */
class EventRegistrationForm extends ContentEntityForm {

  private EventRegistrationService $registrationService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    $instance = parent::create($container);
    $instance->registrationService = $container->get('custom_events.event_registration_service');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'event_registration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state): array {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\custom_events\Entity\EventRegistration $registration */
    $registration = $this->entity;
    $eventId = (int) $registration->get('event_id')->target_id;
    $uid     = (int) $this->currentUser()->id();

    $form['#attributes']['class'][] = 'event-registration-form';

    // Hide the auto-generated entity reference widgets; values are set in save().
    $form['event_id']['#access'] = FALSE;
    $form['uid']['#access']      = FALSE;

    // Status message placeholder for AJAX feedback.
    $form['status_message'] = [
      '#type'       => 'container',
      '#attributes' => ['id' => 'registration-status-' . $eventId, 'class' => ['registration-status']],
    ];

    $isRegistered = $uid && $this->registrationService->isRegistered($eventId, $uid);
    $count        = $this->registrationService->getCount($eventId);

    $form['registration_count'] = [
      '#markup' => '<p class="registration-count" id="reg-count-' . $eventId . '">'
        . $this->formatPlural($count, '1 person registered', '@count people registered')
        . '</p>',
    ];

    $form['actions'] = [
      '#type'   => 'actions',
      'submit'  => [
        '#type'       => 'submit',
        '#value'      => $isRegistered
          ? $this->t('Already Registered')
          : $this->t('Register Now'),
        '#disabled'   => $isRegistered,
        '#attributes' => [
          'class'             => ['btn-register'],
          'data-event-id'     => $eventId,
          'data-drupal-route' => Url::fromRoute(
            'custom_events.register',
            ['event_id' => $eventId],
          )->toString(),
        ],
      ],
    ];

    $form['#attached']['library'][] = 'custom_events/event_registration';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    parent::validateForm($form, $form_state);

    if ($this->currentUser()->isAnonymous()) {
      $form_state->setError(
        $form,
        $this->t('You must be logged in to register for events.')
      );
    }

    $eventId = (int) $this->entity->get('event_id')->target_id;
    if ($this->registrationService->isRegistered($eventId, (int) $this->currentUser()->id())) {
      $form_state->setError(
        $form['actions']['submit'],
        $this->t('You are already registered for this event.')
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $status = parent::save($form, $form_state);
    $this->messenger()->addStatus($this->t('You have successfully registered for this event.'));
    return $status;
  }

}
