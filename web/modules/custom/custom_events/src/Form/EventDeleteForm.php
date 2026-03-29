<?php
// web/modules/custom/custom_events/src/Form/EventDeleteForm.php

declare(strict_types=1);

namespace Drupal\custom_events\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a confirmation form for deleting an Event entity.
 *
 * Cascade deletion of registrations is handled by
 * custom_events_node_predelete() / the entity's predelete hook in
 * custom_events.module.
 */
class EventDeleteForm extends ContentEntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getQuestion(): \Drupal\Core\StringTranslation\TranslatableMarkup {
    return $this->t(
      'Are you sure you want to delete the event %label?',
      ['%label' => $this->entity->label()],
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(): \Drupal\Core\StringTranslation\TranslatableMarkup {
    return $this->t(
      'All registrations for this event will be permanently deleted. This action cannot be undone.'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl(): Url {
    return Url::fromRoute('custom_events.event_list');
  }

  /**
   * {@inheritdoc}
   */
  protected function getRedirectUrl(): Url {
    return Url::fromRoute('custom_events.event_list');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    parent::submitForm($form, $form_state);

    $this->messenger()->addStatus(
      $this->t('Event %label and all its registrations have been deleted.', [
        '%label' => $this->entity->label(),
      ])
    );
  }

}
