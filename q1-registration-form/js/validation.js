/* =====================================================================
   validation.js - client-side validation for the registration form
   Checks: required fields, name / roll number format, e-mail format,
           password strength (with live meter), confirm password,
           file type and file size.
   NOTE: this is only for a better user experience. The same rules are
   enforced again on the server in PHP (q2-php-crud/includes/helpers.php),
   because JavaScript can always be bypassed.
   ===================================================================== */

(function () {
  'use strict';

  var form = document.getElementById('regForm');

  // ---------- Rules for every field ----------
  // Each rule receives the field element and returns an error string,
  // or "" when the value is fine.

  var rules = {
    name: function (el) {
      var v = el.value.trim();
      if (!v) return 'Full name is required.';
      // letters, spaces, dots and apostrophes only, at least 3 characters
      if (!/^[A-Za-z][A-Za-z .']{2,}$/.test(v)) return 'Enter a valid name (letters only, min 3 characters).';
      return '';
    },

    roll_number: function (el) {
      var v = el.value.trim();
      if (!v) return 'Roll number is required.';
      // letters + digits only, 6 to 15 characters (e.g. 22CSE101)
      if (!/^[A-Za-z0-9]{6,15}$/.test(v)) return 'Roll number must be 6-15 letters/digits, no spaces.';
      return '';
    },

    email: function (el) {
      var v = el.value.trim();
      if (!v) return 'Email address is required.';
      // simple but practical e-mail pattern: something@something.tld
      if (!/^[^\s@]+@[^\s@]+\.[A-Za-z]{2,}$/.test(v)) return 'Enter a valid email (e.g. name@example.com).';
      return '';
    },

    course: function (el) {
      return el.value === '' ? 'Please select a course.' : '';
    },

    password: function (el) {
      var v = el.value;
      if (!v) return 'Password is required.';
      if (passwordScore(v) < 5) return 'Password needs 8+ chars with upper, lower, number and symbol.';
      return '';
    },

    confirm_password: function (el) {
      if (!el.value) return 'Please confirm your password.';
      if (el.value !== document.getElementById('password').value) return 'Passwords do not match.';
      return '';
    },

    // File rules: first argument = allowed extensions, second = max size in MB
    profile_image: function (el) { return checkFile(el, ['jpg', 'jpeg', 'png', 'webp'], 2, 'Profile photo'); },
    id_proof:      function (el) { return checkFile(el, ['pdf', 'jpg', 'jpeg', 'png'], 5, 'ID proof'); }
  };

  // ---------- Helpers ----------

  // Returns a score from 0 to 5: one point each for length, lower, upper, digit, symbol
  function passwordScore(v) {
    var score = 0;
    if (v.length >= 8)        score++;
    if (/[a-z]/.test(v))      score++;
    if (/[A-Z]/.test(v))      score++;
    if (/[0-9]/.test(v))      score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;
    return score;
  }

  function checkFile(el, allowedExt, maxMB, label) {
    if (!el.files || el.files.length === 0) return label + ' is required.';
    var file = el.files[0];
    var ext = file.name.split('.').pop().toLowerCase();
    if (allowedExt.indexOf(ext) === -1) return label + ' must be: ' + allowedExt.join(', ').toUpperCase() + '.';
    if (file.size > maxMB * 1024 * 1024) return label + ' must be smaller than ' + maxMB + ' MB.';
    return '';
  }

  // Show / clear the message under a field and colour the border
  function showResult(el, message) {
    var box = el.closest('.field');
    box.querySelector('.error-msg').textContent = message;
    box.classList.toggle('invalid', message !== '');
    box.classList.toggle('valid', message === '');
  }

  function validateField(el) {
    var rule = rules[el.name];
    if (!rule) return true;
    var message = rule(el);
    showResult(el, message);
    return message === '';
  }

  // ---------- Password strength meter ----------
  var bar  = document.getElementById('meterBar');
  var text = document.getElementById('meterText');
  var levels = [
    { w: '10%',  c: '#dc2626', t: 'Very weak' },
    { w: '30%',  c: '#ea580c', t: 'Weak' },
    { w: '50%',  c: '#d97706', t: 'Fair' },
    { w: '75%',  c: '#65a30d', t: 'Good' },
    { w: '100%', c: '#16a34a', t: 'Strong' }
  ];

  function updateMeter() {
    var v = document.getElementById('password').value;
    if (!v) {
      bar.style.width = '0';
      text.textContent = 'Use 8+ characters with upper, lower, number and symbol.';
      return;
    }
    var level = levels[Math.max(passwordScore(v) - 1, 0)];
    bar.style.width = level.w;
    bar.style.background = level.c;
    text.textContent = 'Strength: ' + level.t;
  }

  // ---------- Wire up events ----------
  var fields = form.querySelectorAll('input[name], select[name]');

  fields.forEach(function (el) {
    // validate when the user leaves a field...
    el.addEventListener('blur', function () { validateField(el); });
    // ...and re-validate while typing once an error is already showing
    el.addEventListener('input', function () {
      if (el.closest('.field').classList.contains('invalid')) validateField(el);
    });
    // selects and file inputs fire "change" rather than "input"
    el.addEventListener('change', function () { validateField(el); });
  });

  document.getElementById('password').addEventListener('input', function () {
    updateMeter();
    // password changed, so the confirm field must be checked again
    var confirmEl = document.getElementById('confirm_password');
    if (confirmEl.value) validateField(confirmEl);
  });

  // Block submission if any field fails
  form.addEventListener('submit', function (event) {
    var firstBad = null;
    fields.forEach(function (el) {
      if (!validateField(el) && !firstBad) firstBad = el;
    });
    if (firstBad) {
      event.preventDefault();   // stop the POST request
      firstBad.focus();         // take the user to the first mistake
    }
  });

  // Clear button should also remove the red/green states
  document.getElementById('resetBtn').addEventListener('click', function () {
    setTimeout(function () {
      form.querySelectorAll('.field').forEach(function (f) {
        f.classList.remove('invalid', 'valid');
        f.querySelector('.error-msg').textContent = '';
      });
      updateMeter();
    }, 0);
  });
})();
