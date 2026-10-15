/* ======================================================
   topbar.js — Geração da topbar (busca + ações + perfil)
   Uso: <div data-topbar></div>
   ====================================================== */

(function () {
  function makeTopbar() {
    return `
      <div class="topbar">
        <div class="topbar-actions">
          <button class="btn-icon" title="Notificações">
            <i class="bi bi-bell"></i>
            <span class="dot"></span>
          </button>
          <button class="btn-icon" title="Ajuda">
            <i class="bi bi-question-circle"></i>
          </button>
          <div class="topbar-user-wrap">
            <div class="user-avatar">M</div>
            <div class="topbar-user-info">
              <b>Mauricio</b>
              <small>Admin geral</small>
            </div>
            <button class="btn-icon" title="Menu">
              <i class="bi bi-chevron-down"></i>
            </button>
          </div>
        </div>
      </div>
    `;
  }

  function init() {
    document.querySelectorAll('[data-topbar]').forEach(el => {
      el.outerHTML = makeTopbar();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
