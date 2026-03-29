// web/modules/custom/custom_events/js/custom-events.js

/* global Drupal */

'use strict';

(function ($, Drupal) {

  /**
   * Fetches a fresh Drupal CSRF session token.
   *
   * @return {Promise<string>}
   */
  /**
   * Displays a toast message inside the card's toast container.
   *
   * @param {HTMLElement} toastEl  - The .ce-card__toast element.
   * @param {string}      message  - Text to display.
   * @param {'error'|'success'} type
   */
  function showToast(toastEl, message, type) {
    toastEl.textContent = message;
    toastEl.className   = 'ce-card__toast ce-card__toast--' + type;
    toastEl.removeAttribute('hidden');

    // Auto-dismiss after 5 seconds.
    setTimeout(function () {
      toastEl.setAttribute('hidden', '');
      toastEl.textContent = '';
      toastEl.className   = 'ce-card__toast';
    }, 5000);
  }

  /**
   * Puts the button into a loading state.
   *
   * @param {HTMLButtonElement} btn
   */
  function setLoading(btn) {
    const spinner = btn.querySelector('.ce-card__spinner');
    const label   = btn.querySelector('.ce-card__btn-text');
    btn.disabled        = true;
    btn.setAttribute('aria-busy', 'true');
    if (label)   label.textContent = Drupal.t('Procesando…');
    if (spinner) spinner.removeAttribute('hidden');
  }

  /**
   * Marks the button as "already registered".
   *
   * @param {HTMLButtonElement} btn
   */
  function setRegistered(btn) {
    const spinner = btn.querySelector('.ce-card__spinner');
    const label   = btn.querySelector('.ce-card__btn-text');
    btn.disabled = true;
    btn.removeAttribute('aria-busy');
    btn.classList.add('ce-card__register-btn--done');
    if (label)   label.textContent = Drupal.t('Ya registrado ✓');
    if (spinner) spinner.setAttribute('hidden', '');
  }

  /**
   * Resets the button to its initial state after an error.
   *
   * @param {HTMLButtonElement} btn
   */
  function resetButton(btn) {
    const spinner = btn.querySelector('.ce-card__spinner');
    const label   = btn.querySelector('.ce-card__btn-text');
    btn.disabled = false;
    btn.removeAttribute('aria-busy');
    if (label)   label.textContent = Drupal.t('Registrarse');
    if (spinner) spinner.setAttribute('hidden', '');
  }

  /**
   * Updates the registration count badge inside a card.
   *
   * @param {number} eventId
   * @param {number} count
   */
  function updateCountBadge(eventId, count) {
    const badge = document.getElementById('reg-count-' + eventId);
    if (!badge) return;
    // Preserve the SVG icon, update the text node only.
    const nodes = badge.childNodes;
    for (let i = nodes.length - 1; i >= 0; i--) {
      if (nodes[i].nodeType === Node.TEXT_NODE) {
        badge.removeChild(nodes[i]);
      }
    }
    const text = count === 1
      ? Drupal.t('1 registrado')
      : Drupal.formatPlural(count, '1 registrado', '@count registrados');
    badge.appendChild(document.createTextNode(' ' + text));
  }

  /**
   * Handles a single registration button click.
   *
   * @param {MouseEvent} event
   */
  async function handleRegisterClick(event) {
    const btn        = /** @type {HTMLButtonElement} */ (event.currentTarget);
    const eventId    = btn.dataset.eventId;
    const registerUrl = btn.dataset.registerUrl;
    const card       = btn.closest('.ce-card');
    const toastEl    = card ? card.querySelector('.ce-card__toast') : null;

    if (!eventId || !registerUrl) return;

    const csrfToken = btn.dataset.csrfToken;
    if (!csrfToken) return;

    setLoading(btn);

    try {
      const response = await fetch(registerUrl, {
        method:      'POST',
        credentials: 'same-origin',
        headers: {
          'X-CSRF-Token': csrfToken,
          'Content-Type': 'application/json',
          'Accept':       'application/json',
        },
      });

      const data = await response.json();

      if (response.ok && data.status === 'success') {
        setRegistered(btn);
        updateCountBadge(eventId, data.count);
        if (toastEl) showToast(toastEl, data.message || Drupal.t('Registro exitoso.'), 'success');
      }
      else {
        resetButton(btn);
        const errorMsg = data.message || Drupal.t('No fue posible completar el registro. Inténtalo de nuevo.');
        if (toastEl) showToast(toastEl, errorMsg, 'error');
      }
    }
    catch (err) {
      resetButton(btn);
      const networkMsg = Drupal.t('Error de conexión. Verifica tu red e inténtalo de nuevo.');
      if (toastEl) showToast(toastEl, networkMsg, 'error');
      // eslint-disable-next-line no-console
      console.error('[custom_events] Registration error:', err);
    }
  }

  /**
   * Drupal behavior — attaches one listener per register button.
   */
  Drupal.behaviors.customEventsRegistration = {
    attach: function (context) {
      const buttons = context.querySelectorAll('.js-register-btn:not([data-ce-bound])');
      buttons.forEach(function (btn) {
        btn.setAttribute('data-ce-bound', '1');
        btn.addEventListener('click', handleRegisterClick);
      });
    },
    detach: function (context) {
      const buttons = context.querySelectorAll('.js-register-btn[data-ce-bound]');
      buttons.forEach(function (btn) {
        btn.removeEventListener('click', handleRegisterClick);
        btn.removeAttribute('data-ce-bound');
      });
    },
  };

}(jQuery, Drupal));
