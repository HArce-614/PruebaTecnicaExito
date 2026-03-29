(function() {
  'use strict';

  document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.querySelector('.me-password-toggle');
    const passwordInput = document.querySelector('input[name="pass"]');

    if (toggleBtn && passwordInput) {
      toggleBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Toggle the SVG appearance (you can add a class for styling)
        toggleBtn.classList.toggle('is-visible');
      });
    }
  });
})();
