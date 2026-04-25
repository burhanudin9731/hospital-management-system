// ============================================================
// Hospital Management System - Client-Side Validation Library
// frontend/js/validation.js
// Used by: login.html, register.html and all other forms
// ============================================================

'use strict';

/* ── Helpers ──────────────────────────────────────────────── */

function setError(inputEl, errorId, message) {
  inputEl.classList.remove('valid');
  inputEl.classList.add('error');
  const errEl = document.getElementById(errorId);
  if (errEl) { errEl.textContent = message; errEl.classList.add('visible'); }
  return false;
}

function setValid(inputEl, errorId) {
  inputEl.classList.remove('error');
  inputEl.classList.add('valid');
  const errEl = document.getElementById(errorId);
  if (errEl) { errEl.textContent = ''; errEl.classList.remove('visible'); }
  return true;
}

function clearFieldError(inputEl, errorId) {
  inputEl.classList.remove('error', 'valid');
  const errEl = document.getElementById(errorId);
  if (errEl) { errEl.textContent = ''; errEl.classList.remove('visible'); }
}

/* ── Core validators ──────────────────────────────────────── */

/**
 * Validate email format
 * @param {HTMLInputElement} inputEl
 * @param {string} errorId
 * @returns {boolean}
 */
function validateEmail(inputEl, errorId) {
  const value = inputEl.value.trim();
  if (!value) return setError(inputEl, errorId, 'Email is required.');
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!re.test(value)) return setError(inputEl, errorId, 'Please enter a valid email address.');
  return setValid(inputEl, errorId);
}

/**
 * Validate minimum string length
 * @param {HTMLInputElement} inputEl
 * @param {string} errorId
 * @param {number} min
 * @param {string} fieldName
 * @returns {boolean}
 */
function validateMinLength(inputEl, errorId, min, fieldName) {
  const value = inputEl.value.trim();
  if (!value) return setError(inputEl, errorId, `${fieldName} is required.`);
  if (value.length < min) return setError(inputEl, errorId, `${fieldName} must be at least ${min} characters.`);
  return setValid(inputEl, errorId);
}

/**
 * Validate username: 4-30 chars, letters/numbers/underscores only
 * @param {HTMLInputElement} inputEl
 * @param {string} errorId
 * @returns {boolean}
 */
function validateUsername(inputEl, errorId) {
  const value = inputEl.value.trim();
  if (!value) return setError(inputEl, errorId, 'Username is required.');
  if (value.length < 4) return setError(inputEl, errorId, 'Username must be at least 4 characters.');
  if (value.length > 30) return setError(inputEl, errorId, 'Username cannot exceed 30 characters.');
  const re = /^[a-zA-Z0-9_]+$/;
  if (!re.test(value)) return setError(inputEl, errorId, 'Username can only contain letters, numbers, and underscores.');
  return setValid(inputEl, errorId);
}

/**
 * Validate password strength: min 8 chars, 1 uppercase, 1 digit
 * @param {HTMLInputElement} inputEl
 * @param {string} errorId
 * @returns {boolean}
 */
function validatePassword(inputEl, errorId) {
  const value = inputEl.value;
  if (!value) return setError(inputEl, errorId, 'Password is required.');
  if (value.length < 8) return setError(inputEl, errorId, 'Password must be at least 8 characters.');
  if (!/[A-Z]/.test(value)) return setError(inputEl, errorId, 'Password must contain at least one uppercase letter.');
  if (!/[0-9]/.test(value)) return setError(inputEl, errorId, 'Password must contain at least one number.');
  return setValid(inputEl, errorId);
}

/**
 * Validate that confirm password matches password
 * @param {HTMLInputElement} passwordEl
 * @param {HTMLInputElement} confirmEl
 * @param {string} errorId
 * @returns {boolean}
 */
function validateConfirmPassword(passwordEl, confirmEl, errorId) {
  const value = confirmEl.value;
  if (!value) return setError(confirmEl, errorId, 'Please confirm your password.');
  if (value !== passwordEl.value) return setError(confirmEl, errorId, 'Passwords do not match.');
  return setValid(confirmEl, errorId);
}

/**
 * Validate that a field is not empty
 * @param {HTMLInputElement} inputEl
 * @param {string} errorId
 * @param {string} fieldName
 * @returns {boolean}
 */
function validateRequired(inputEl, errorId, fieldName) {
  const value = inputEl.value.trim();
  if (!value) return setError(inputEl, errorId, `${fieldName} is required.`);
  if (value.length < 2) return setError(inputEl, errorId, `${fieldName} must be at least 2 characters.`);
  return setValid(inputEl, errorId);
}

/**
 * Validate Turkish/international phone: 10-15 digits, optional leading +
 * @param {HTMLInputElement} inputEl
 * @param {string} errorId
 * @returns {boolean}
 */
function validatePhone(inputEl, errorId) {
  const value = inputEl.value.trim();
  if (!value) return setError(inputEl, errorId, 'Phone number is required.');
  const re = /^\+?[0-9]{10,15}$/;
  if (!re.test(value.replace(/[\s\-()]/g, '')))
    return setError(inputEl, errorId, 'Enter a valid phone number (10-15 digits).');
  return setValid(inputEl, errorId);
}

/**
 * Validate date of birth: must be in the past, user must be ≥ 1 year old
 * @param {HTMLInputElement} inputEl
 * @param {string} errorId
 * @returns {boolean}
 */
function validateDOB(inputEl, errorId) {
  const value = inputEl.value;
  if (!value) return setError(inputEl, errorId, 'Date of birth is required.');
  const dob  = new Date(value);
  const now  = new Date();
  if (isNaN(dob.getTime())) return setError(inputEl, errorId, 'Invalid date.');
  if (dob >= now) return setError(inputEl, errorId, 'Date of birth must be in the past.');
  const age = (now - dob) / (1000 * 60 * 60 * 24 * 365.25);
  if (age < 1)   return setError(inputEl, errorId, 'Patient must be at least 1 year old.');
  if (age > 120) return setError(inputEl, errorId, 'Please enter a valid date of birth.');
  return setValid(inputEl, errorId);
}

/**
 * Validate appointment date/time: must be in the future
 * @param {HTMLInputElement} inputEl
 * @param {string} errorId
 * @returns {boolean}
 */
function validateFutureDate(inputEl, errorId) {
  const value = inputEl.value;
  if (!value) return setError(inputEl, errorId, 'Date is required.');
  const selected = new Date(value);
  const now      = new Date();
  now.setHours(0, 0, 0, 0);
  if (selected < now) return setError(inputEl, errorId, 'Please select a future date.');
  return setValid(inputEl, errorId);
}

/**
 * Validate that a <select> has a chosen value
 * @param {HTMLSelectElement} selectEl
 * @param {string} errorId
 * @param {string} fieldName
 * @returns {boolean}
 */
function validateSelect(selectEl, errorId, fieldName) {
  if (!selectEl.value) return setError(selectEl, errorId, `Please select a ${fieldName}.`);
  return setValid(selectEl, errorId);
}

/**
 * Validate a positive number (for quantities, fees, etc.)
 * @param {HTMLInputElement} inputEl
 * @param {string} errorId
 * @param {string} fieldName
 * @param {number} min
 * @returns {boolean}
 */
function validatePositiveNumber(inputEl, errorId, fieldName, min = 0) {
  const value = parseFloat(inputEl.value);
  if (isNaN(value)) return setError(inputEl, errorId, `${fieldName} must be a number.`);
  if (value <= min) return setError(inputEl, errorId, `${fieldName} must be greater than ${min}.`);
  return setValid(inputEl, errorId);
}

/* ── AJAX helper ──────────────────────────────────────────── */

/**
 * Generic AJAX POST using Fetch API — returns parsed JSON
 * @param {string} url
 * @param {FormData|object} data
 * @returns {Promise<object>}
 */
async function ajaxPost(url, data) {
  const body = data instanceof FormData ? data : (() => {
    const fd = new FormData();
    Object.entries(data).forEach(([k, v]) => fd.append(k, v));
    return fd;
  })();

  const res = await fetch(url, { method: 'POST', body });

  if (!res.ok && res.status !== 422 && res.status !== 401) {
    throw new Error(`HTTP ${res.status}`);
  }
  return res.json();
}

/**
 * Generic AJAX GET using Fetch API — returns parsed JSON
 * @param {string} url
 * @returns {Promise<object>}
 */
async function ajaxGet(url) {
  const res = await fetch(url);
  if (!res.ok) throw new Error(`HTTP ${res.status}`);
  return res.json();
}

/* ── Exported for use across all pages ────────────────────── */
// All functions are globally available (no module bundler needed)
// validateEmail, validatePassword, validateConfirmPassword,
// validateUsername, validateMinLength, validateRequired,
// validatePhone, validateDOB, validateFutureDate,
// validateSelect, validatePositiveNumber,
// setError, setValid, clearFieldError,
// ajaxPost, ajaxGet
