// ============================================================
// main.js - Shared layout loader + utilities
// frontend/js/main.js
// ============================================================
'use strict';

// Load sidebar + navbar into every page
async function loadComponents() {
  const sidebarEl = document.getElementById('sidebarContainer');
  const navbarEl  = document.getElementById('navbarContainer');
  const root      = document.querySelector('meta[name="root"]')?.content || '../../';

  if (sidebarEl) {
    try {
      // credentials:'include' sends the PHP session cookie with every fetch
      const r = await fetch(root + 'components/sidebar.html', { credentials: 'include' });
      sidebarEl.innerHTML = await r.text();

      // Mark active nav item
      const page = document.body.dataset.page;
      document.querySelectorAll('.nav-item').forEach(a => {
        if (a.dataset.page === page) a.classList.add('active');
      });

      // Hide nav items and section labels the current role cannot access
      const role = u?.role || '';
      document.querySelectorAll('[data-roles]').forEach(el => {
      const allowed = el.dataset.roles.split(',').map(r => r.trim());
        if (!allowed.includes(role)) {
        el.style.display = 'none';
        }
      });

      // Sidebar user info from sessionStorage
      const u = getSession();
      if (u) {
        const el = id => document.getElementById(id);
        if (el('sidebarUsername')) el('sidebarUsername').textContent = u.username || 'User';
        if (el('sidebarRole'))     el('sidebarRole').textContent     = capitalize(u.role || 'staff');
        if (el('sidebarAvatar'))   el('sidebarAvatar').textContent   = (u.username || 'U')[0].toUpperCase();
      }

      // Logout button
      document.getElementById('logoutBtn')?.addEventListener('click', e => {
        e.preventDefault();
        sessionStorage.clear();
        localStorage.clear();
        const root = document.querySelector('meta[name="root"]')?.content || '../../';
        window.location.href = root + 'pages/auth/login.html';
      });

      // Mobile menu toggle
      document.getElementById('menuToggle')?.addEventListener('click', () => {
        document.getElementById('sidebar')?.classList.toggle('open');
      });
    } catch (err) {
      console.warn('Could not load sidebar:', err);
    }
  }

  if (navbarEl) {
    try {
      const r = await fetch(root + 'components/navbar.html', { credentials: 'include' });
      navbarEl.innerHTML = await r.text();

      const title = document.title.split('–')[0].trim();
      const el    = id => document.getElementById(id);
      if (el('navbarTitle')) el('navbarTitle').textContent = title;
      if (el('navDate'))     el('navDate').textContent     = new Date().toLocaleDateString('en-GB', {
        weekday: 'short', day: 'numeric', month: 'short', year: 'numeric'
      });

      const u = getSession();
      if (u) {
        if (el('navUsername')) el('navUsername').textContent = u.username || 'User';
        if (el('navAvatar'))   el('navAvatar').textContent   = (u.username || 'U')[0].toUpperCase();
      }
    } catch (err) {
      console.warn('Could not load navbar:', err);
    }
  }
}

// ── Session helpers (JS side — PHP session handled by cookie) ──
function getSession() {
  try { return JSON.parse(sessionStorage.getItem('hms_user') || 'null'); } catch { return null; }
}
function setSession(data) {
  sessionStorage.setItem('hms_user', JSON.stringify(data));
}

function capitalize(s) {
  return s ? s.charAt(0).toUpperCase() + s.slice(1) : '';
}

// ── Toast notification ─────────────────────────────────────────
function showToast(msg, type = 'success', duration = 3500) {
  let t = document.getElementById('toast');
  if (!t) {
    t = document.createElement('div');
    t.id = 'toast';
    document.body.appendChild(t);
  }
  const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';
  t.textContent = icon + ' ' + msg;
  t.className   = 'show ' + type;
  clearTimeout(t._timer);
  t._timer = setTimeout(() => t.classList.remove('show'), duration);
}

// ── AJAX helpers — always send session cookie ──────────────────
async function apiPost(url, formData) {
  try {
    const res  = await fetch(url, {
      method: 'POST',
      body: formData,
      credentials: 'include'   // ← sends PHP session cookie
    });
    const text = await res.text();
    try {
      return JSON.parse(text);
    } catch {
      console.error('Non-JSON response from', url, ':', text.slice(0, 200));
      return { success: false, message: 'Server returned an unexpected response.' };
    }
  } catch (err) {
    console.error('Network error calling', url, err);
    return { success: false, message: 'Network error. Is XAMPP running?' };
  }
}

async function apiGet(url) {
  try {
    const res  = await fetch(url, { credentials: 'include' }); // ← sends PHP session cookie
    const text = await res.text();
    try {
      return JSON.parse(text);
    } catch {
      console.error('Non-JSON response from', url, ':', text.slice(0, 200));
      return { success: false, message: 'Server returned an unexpected response.', data: [] };
    }
  } catch (err) {
    console.error('Network error calling', url, err);
    return { success: false, message: 'Network error. Is XAMPP running?', data: [] };
  }
}

// ── Formatting helpers ─────────────────────────────────────────
function fmtDate(d) {
  if (!d) return '—';
  return new Date(d).toLocaleDateString('en-GB', {
    day: '2-digit', month: 'short', year: 'numeric'
  });
}

function statusBadge(status) {
  const map = {
    'Scheduled'  : 'badge-blue',
    'Completed'  : 'badge-green',
    'Cancelled'  : 'badge-red',
    'No-Show'    : 'badge-gray',
    'Paid'       : 'badge-green',
    'Unpaid'     : 'badge-red',
    'Partial'    : 'badge-orange',
    'Available'  : 'badge-green',
    'Occupied'   : 'badge-red',
    'Maintenance': 'badge-gray'
  };
  return `<span class="badge ${map[status] || 'badge-gray'}">${status}</span>`;
}

function initials(name) {
  return (name || '?').split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();
}

function confirmDialog(msg) {
  return confirm(msg);
}

// ── Auto-init on every page ────────────────────────────────────
document.addEventListener('DOMContentLoaded', loadComponents);
