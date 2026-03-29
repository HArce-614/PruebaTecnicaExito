<?php
// web/modules/custom/custom_events/src/Form/EventForm.php

declare(strict_types=1);

namespace Drupal\custom_events\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\custom_events\Service\CountryService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EventForm extends ContentEntityForm {

  private CountryService $countryService;

  public static function create(ContainerInterface $container): static {
    $instance = parent::create($container);
    $instance->countryService = $container->get('custom_events.country_service');
    return $instance;
  }

  public function form(array $form, FormStateInterface $form_state): array {
    $form = parent::form($form, $form_state);
    $form['country']['#access'] = FALSE;

    try {
      $countries = $this->countryService->getCountries();
    }
    catch (\RuntimeException) {
      $countries = [];
    }

    $form['country_select'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Country'),
      '#options'       => ['' => $this->t('— Select a country —')] + $countries,
      '#default_value' => $this->entity->get('country')->value ?? '',
      '#required'      => TRUE,
      '#weight'        => -10,
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state): void {
    parent::validateForm($form, $form_state);

    if ((string) $form_state->getValue('country_select') === '') {
      $form_state->setErrorByName('country_select', $this->t('Please select a country.'));
    }
  }

  public function save(array $form, FormStateInterface $form_state): int {
    $this->entity->set('country', $form_state->getValue('country_select'));
    $status = parent::save($form, $form_state);
    $form_state->setRedirect('custom_events.event_list');
    return $status;
  }

}