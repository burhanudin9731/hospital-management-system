// ============================================================
// main.js - Shared layout loader + utilities
// ============================================================
'use strict';

// Load sidebar + navbar into every page
async function loadComponents() {
  const sidebarEl = document.getElementById('sidebarContainer');
  const navbarEl  = document.getElementById('navbarContainer');
  const root      = document.querySelector('meta[name="root"]')?.content || '../../';

  if (sidebarEl) {
    const r = await fetch(root + 'components/sidebar.html');
    sidebarEl.innerHTML = await r.text();
    // Mark active nav item
    const page = document.body.dataset.page;
    document.querySelectorAll('.nav-item').forEach(a => {
      if (a.dataset.page === page) a.classList.add('active');
    });
    // Sidebar user info
    const u = getSession();
    if (u) {
      const el = s => document.getElementById(s);
      if (el('sidebarUsername')) el('sidebarUsername').textContent = u.username || 'User';
      if (el('sidebarRole'))     el('sidebarRole').textContent     = capitalize(u.role || 'staff');
      if (el('sidebarAvatar'))   el('sidebarAvatar').textContent   = (u.username||'U')[0].toUpperCase();
    }
    // Logout
    document.getElementById('logoutBtn')?.addEventListener('click', e => {
      e.preventDefault();
      sessionStorage.clear(); localStorage.clear();
      window.location.href = root + 'pages/auth/login.html';
    });
    // Mobile toggle
    document.getElementById('menuToggle')?.addEventListener('click', () => {
      document.getElementById('sidebar')?.classList.toggle('open');
    });
  }

  if (navbarEl) {
    const r = await fetch(root + 'components/navbar.html');
    navbarEl.innerHTML = await r.text();
    const title = document.title.split('–')[0].trim();
    const el = s => document.getElementById(s);
    if (el('navbarTitle'))  el('navbarTitle').textContent  = title;
    if (el('navDate'))      el('navDate').textContent      = new Date().toLocaleDateString('en-GB',{weekday:'short',day:'numeric',month:'short',year:'numeric'});
    const u = getSession();
    if (u) {
      if (el('navUsername')) el('navUsername').textContent = u.username || 'User';
      if (el('navAvatar'))   el('navAvatar').textContent   = (u.username||'U')[0].toUpperCase();
    }
  }
}

// Simple client-side session helper (PHP sets actual session)
function getSession() {
  try { return JSON.parse(sessionStorage.getItem('hms_user')||'null'); } catch { return null; }
}
function setSession(data) { sessionStorage.setItem('hms_user', JSON.stringify(data)); }

function capitalize(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }

// Toast notification
function showToast(msg, type='success', duration=3000) {
  let t = document.getElementById('toast');
  if (!t) { t = document.createElement('div'); t.id='toast'; document.body.appendChild(t); }
  t.textContent = (type==='success'?'✅ ':type==='error'?'❌ ':'ℹ️ ') + msg;
  t.className   = `show ${type}`;
  setTimeout(() => t.classList.remove('show'), duration);
}

// AJAX helpers
async function apiPost(url, formData) {
  const res  = await fetch(url, { method:'POST', body: formData });
  return res.json();
}
async function apiGet(url) {
  const res = await fetch(url);
  return res.json();
}

// Format date nicely
function fmtDate(d) {
  if (!d) return '—';
  return new Date(d).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'});
}

// Badge HTML by status
function statusBadge(status) {
  const map = {
    'Scheduled':'badge-blue','Completed':'badge-green','Cancelled':'badge-red',
    'No-Show':'badge-gray','Paid':'badge-green','Unpaid':'badge-red','Partial':'badge-orange',
    'Available':'badge-green','Occupied':'badge-red','Maintenance':'badge-gray'
  };
  return `<span class="badge ${map[status]||'badge-gray'}">${status}</span>`;
}

// Build initials avatar
function initials(name) {
  return (name||'?').split(' ').map(w=>w[0]).join('').slice(0,2).toUpperCase();
}

// Confirm dialog
function confirmDialog(msg) { return confirm(msg); }

// Populate a <select> from API data
async function populateSelect(selectId, url, valueKey, labelKey, placeholder='Select...') {
  const sel  = document.getElementById(selectId);
  if (!sel) return;
  sel.innerHTML = `<option value="">${placeholder}</option>`;
  const data = await apiGet(url);
  if (data.success) {
    data.data.forEach(row => {
      const opt = document.createElement('option');
      opt.value       = row[valueKey];
      opt.textContent = row[labelKey];
      sel.appendChild(opt);
    });
  }
}

document.addEventListener('DOMContentLoaded', loadComponents);
