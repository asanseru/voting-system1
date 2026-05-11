// ============================================================
// VoteSecure — Global JavaScript
// ============================================================

document.addEventListener('DOMContentLoaded', () => {

  // ── Auto-dismiss alerts ──────────────────────────────────
  document.querySelectorAll('.alert[data-auto-dismiss]').forEach(el => {
    setTimeout(() => el.remove(), 4000);
  });

  // ── Modal handling ───────────────────────────────────────
  document.querySelectorAll('[data-modal-open]').forEach(btn => {
    btn.addEventListener('click', () => {
      const target = document.getElementById(btn.dataset.modalOpen);
      if (target) target.classList.add('open');
    });
  });

  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
      if (e.target === overlay) overlay.classList.remove('open');
    });
  });

  document.querySelectorAll('.modal-close').forEach(btn => {
    btn.addEventListener('click', () => {
      btn.closest('.modal-overlay')?.classList.remove('open');
    });
  });

  // ── Candidate card selection (voting page) ───────────────
  const voteForm = document.getElementById('vote-form');
  if (voteForm) {
    document.querySelectorAll('.candidate-card').forEach(card => {
      card.addEventListener('click', () => {
        document.querySelectorAll('.candidate-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        const radio = card.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;

        const btn = document.getElementById('submit-vote-btn');
        if (btn) {
          btn.disabled = false;
          btn.textContent = '🗳 Cast My Vote';
        }
      });
    });

    voteForm.addEventListener('submit', e => {
      const checked = voteForm.querySelector('input[type="radio"]:checked');
      if (!checked) {
        e.preventDefault();
        showToast('Please select a candidate before voting.', 'error');
        return;
      }
      const confirmed = confirm('⚠️ Are you sure? Your vote cannot be changed once submitted.');
      if (!confirmed) e.preventDefault();
    });
  }

  // ── Animate progress bars on page load ───────────────────
  document.querySelectorAll('.progress-fill[data-width]').forEach(bar => {
    setTimeout(() => { bar.style.width = bar.dataset.width + '%'; }, 300);
  });

  // ── Confirm delete ───────────────────────────────────────
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
      if (!confirm(el.dataset.confirm || 'Are you sure?')) e.preventDefault();
    });
  });

  // ── Character counter for textareas ──────────────────────
  document.querySelectorAll('textarea[maxlength]').forEach(ta => {
    const hint = document.createElement('small');
    hint.className = 'form-hint char-count';
    ta.parentNode.appendChild(hint);
    const update = () => { hint.textContent = `${ta.value.length}/${ta.maxLength} characters`; };
    ta.addEventListener('input', update);
    update();
  });

  // ── Toast notifications ──────────────────────────────────
  window.showToast = (message, type = 'info') => {
    const colors = { info:'var(--accent)', success:'var(--accent2)', error:'var(--danger)', warning:'var(--warning)' };
    const toast = document.createElement('div');
    toast.style.cssText = `
      position:fixed; bottom:2rem; right:2rem; z-index:9999;
      padding:0.85rem 1.5rem; border-radius:8px;
      background:var(--card); border:1px solid ${colors[type]};
      color:${colors[type]}; font-size:0.875rem; font-weight:500;
      box-shadow:0 8px 24px rgba(0,0,0,0.4);
      animation:fadeIn 0.3s ease;
      max-width:340px; word-wrap:break-word;
    `;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
  };

  // ── Sidebar active link ───────────────────────────────────
  const currentPath = window.location.pathname;
  document.querySelectorAll('.sidebar-nav a').forEach(a => {
    if (a.getAttribute('href') && currentPath.endsWith(a.getAttribute('href').split('/').pop())) {
      a.classList.add('active');
    }
  });

  // ── Live clock ───────────────────────────────────────────
  const clockEl = document.getElementById('live-clock');
  if (clockEl) {
    const tick = () => { clockEl.textContent = new Date().toLocaleTimeString(); };
    tick();
    setInterval(tick, 1000);
  }
});
