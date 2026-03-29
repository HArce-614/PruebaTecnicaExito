<?php
// web/modules/custom/custom_events/src/Controller/EventController.php

declare(strict_types=1);

namespace Drupal\custom_events\Controller;

use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Controller\ControllerBase;
use Drupal\custom_events\Service\EventRegistrationService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for event listing and AJAX registration.
 *
 * Note: entityTypeManager and currentUser are NOT injected here because
 * ControllerBase already declares those as non-readonly protected properties
 * and provides lazy accessor methods. Redefining them as readonly in a child
 * class causes a PHP fatal error.
 */
class EventController extends ControllerBase {

  public function __construct(
    private readonly EventRegistrationService $registrationService,
    private readonly CsrfTokenGenerator $csrfToken,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('custom_events.event_registration_service'),
      $container->get('csrf_token'),
    );
  }

  /**
   * Renders the public event listing page.
   *
   * @return array
   *   A render array using the event_list theme.
   */
  public function listEvents(): array {
    $storage = $this->entityTypeManager()->getStorage('custom_event');

    $ids = $storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('status', 1)
      ->sort('event_date', 'DESC')
      ->execute();

    $events = $ids ? $storage->loadMultiple($ids) : [];

    $currentUser = $this->currentUser();
    $event_items = [];
    foreach ($events as $event) {
      $event_items[] = [
        'entity'             => $event,
        'id'                 => $event->id(),
        'title'              => $event->label(),
        'description'        => $event->get('description')->view('full'),
        'country'            => $event->get('country')->value,
        'event_date'         => $event->get('event_date')->value,
        'registered_count'   => $this->registrationService->getCount((int) $event->id()),
        'user_is_anonymous'   => $currentUser->isAnonymous(),
        'user_is_registered' => !$currentUser->isAnonymous()
          && $this->registrationService->isRegistered(
              (int) $event->id(),
              (int) $currentUser->id(),
            ),
        'register_url'       => \Drupal\Core\Url::fromRoute(
          'custom_events.register',
          ['event_id' => $event->id()],
        )->toString(),
        'csrf_token'         => $this->csrfToken->get(
          'custom_events_register_' . $event->id()
        ),
      ];
    }

    return [
      '#theme'       => 'event_list',
      '#events'      => $event_items,
      '#total_count' => count($event_items),
      '#is_admin'    => $currentUser->hasPermission('administer events'),
      '#add_event_url' => \Drupal\Core\Url::fromRoute('entity.custom_event.add_form')->toString(),
      '#cache'       => [
        'tags'     => ['custom_event_list'],
        'contexts' => ['user'],
      ],
    ];
  }

  /**
   * Handles the AJAX POST registration request.
   *
   * @param int $event_id
   *   The event entity ID from the route.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current HTTP request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function registerForEvent(int $event_id, Request $request): JsonResponse {
    $currentUser = $this->currentUser();

    // Require authenticated user.
    if ($currentUser->isAnonymous()) {
      return new JsonResponse(
        ['status' => 'error', 'message' => $this->t('You must be logged in to register.')->render()],
        403,
      );
    }

    // Validate CSRF token sent in the X-CSRF-Token header or request body.
    $token = $request->headers->get('X-CSRF-Token')
      ?? $request->request->get('csrf_token', '');

    if (!$this->csrfToken->validate((string) $token, 'custom_events_register_' . $event_id)) {
      return new JsonResponse(
        ['status' => 'error', 'message' => $this->t('Invalid security token.')->render()],
        403,
      );
    }

    // Verify the event exists and is published.
    $event = $this->entityTypeManager()
      ->getStorage('custom_event')
      ->load($event_id);

    if (!$event || !(bool) $event->get('status')->value) {
      return new JsonResponse(
        ['status' => 'error', 'message' => $this->t('Event not found or not available.')->render()],
        404,
      );
    }

    try {
      $this->registrationService->register($event_id);
      $count = $this->registrationService->getCount($event_id);

      return new JsonResponse([
        'status'  => 'success',
        'message' => $this->t('You have successfully registered for this event.')->render(),
        'count'   => $count,
      ]);
    }
    catch (\RuntimeException $e) {
      return new JsonResponse(
        ['status' => 'error', 'message' => $e->getMessage()],
        409,
      );
    }
    catch (\Exception $e) {
      $this->getLogger('custom_events')->error(
        'Registration failed for event @id: @msg',
        ['@id' => $event_id, '@msg' => $e->getMessage()],
      );

      return new JsonResponse(
        ['status' => 'error', 'message' => $this->t('An unexpected error occurred. Please try again.')->render()],
        500,
      );
    }
  }

}
