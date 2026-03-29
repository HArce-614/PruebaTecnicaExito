<?php
// web/modules/custom/custom_events/src/EventListBuilder.php

declare(strict_types=1);

namespace Drupal\custom_events;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Provides the admin list table for Event entities.
 */
class EventListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['id']         = $this->t('ID');
    $header['title']      = $this->t('Title');
    $header['country']    = $this->t('Country');
    $header['event_date'] = $this->t('Event Date');
    $header['status']     = $this->t('Published');
    $header['created']    = $this->t('Created');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    /** @var \Drupal\custom_events\Entity\Event $entity */
    $row['id']      = $entity->id();
    $row['title']   = Link::fromTextAndUrl(
      $entity->label(),
      $entity->toUrl(),
    );
    $row['country']    = $entity->get('country')->value ?? '—';
    $row['event_date'] = $entity->get('event_date')->value
      ? (new \DateTime($entity->get('event_date')->value))
          ->format('Y-m-d H:i')
      : '—';
    $row['status']  = $entity->get('status')->value
      ? $this->t('Yes')
      : $this->t('No');
    $row['created'] = \Drupal::service('date.formatter')
      ->format((int) $entity->get('created')->value, 'short');

    return $row + parent::buildRow($entity);
  }

}
