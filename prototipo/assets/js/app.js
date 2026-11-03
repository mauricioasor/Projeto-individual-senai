/* ======================================================
   app.js — Inicializações de UI gerais (tabs, etc.)
   ====================================================== */

(function () {
  function initTabs() {
    document.querySelectorAll('[data-tabs]').forEach(group => {
      group.querySelectorAll('.tab').forEach(t => {
        t.addEventListener('click', () => {
          group.querySelectorAll('.tab').forEach(x => x.classList.remove('active'));
          t.classList.add('active');
        });
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTabs);
  } else {
    initTabs();
  }
})();
