/* ======================================================
   sidebar.js — Geração e controle da sidebar
   Uso: <div data-sidebar="dashboard"></div>
   ====================================================== */

(function () {
  const ITEMS = [
    { key: 'dashboard',   label: 'Dashboard',           icon: 'bi-speedometer2',     href: 'dashboard.html' },
    { key: 'catalog',     label: 'Catálogo de Livros',  icon: 'bi-book',             href: 'catalogo.html' },
    { key: 'loans',       label: 'Empréstimos',         icon: 'bi-arrow-left-right', href: 'emprestimos.html', badge: '12' },
    { key: 'users',       label: 'Usuários',            icon: 'bi-people',           href: 'usuarios.html' },
    { key: 'quick',       label: 'Cadastro Rápido',     icon: 'bi-plus-circle',      href: 'catalogo.html' }
  ];

  function makeSidebar(activeKey) {
    return `
      <aside class="sidebar">
        <div class="sidebar-header">
          <div class="sidebar-logo">MR</div>
          <div class="sidebar-brand">
            <b>MR Biblioteca</b>
            <small>Sistema de Gestão</small>
          </div>
          <button class="toggle-btn" onclick="toggleSidebar(this)" title="Recolher menu">
            <i class="bi bi-chevron-double-left"></i>
          </button>
        </div>

        <div class="sidebar-section">Menu Principal</div>
        <nav class="sidebar-nav">
          ${ITEMS.map(it => `
            <a href="${it.href}" class="menu-item ${it.key === activeKey ? 'active' : ''}" title="${it.label}">
              <i class="bi ${it.icon}"></i>
              <span>${it.label}</span>
              ${it.badge ? `<span class="menu-badge">${it.badge}</span>` : ''}
            </a>
          `).join('')}
        </nav>

        <div class="sidebar-section">Geral</div>
        <nav class="sidebar-nav">
          <a href="#" class="menu-item" title="Configurações">
            <i class="bi bi-gear"></i><span>Configurações</span>
          </a>
          <a href="#" class="menu-item" title="Ajuda">
            <i class="bi bi-question-circle"></i><span>Ajuda &amp; Suporte</span>
          </a>
        </nav>

        <div class="sidebar-footer">
          <div class="user-card">
            <div class="user-avatar">M</div>
            <div class="user-info">
              <b>Mauricio</b>
              <small>Admin geral</small>
            </div>
            <a href="index.html" class="logout-btn" title="Sair">
              <i class="bi bi-box-arrow-right"></i>
            </a>
          </div>
        </div>
      </aside>
    `;
  }

  function syncToggleIcons(collapsed) {
    document.querySelectorAll('.toggle-btn i').forEach(i => {
      i.classList.toggle('bi-chevron-double-left', !collapsed);
      i.classList.toggle('bi-chevron-double-right', collapsed);
    });
    document.querySelectorAll('.toggle-btn').forEach(b => {
      b.title = collapsed ? 'Expandir menu' : 'Recolher menu';
    });
  }

  // Exposto globalmente porque é chamado pelo onclick do template
  window.toggleSidebar = function (btn) {
    const sb = btn.closest('.sidebar');
    const collapsed = !sb.classList.contains('collapsed');
    document.querySelectorAll('.sidebar').forEach(s => s.classList.toggle('collapsed', collapsed));
    document.querySelectorAll('.main').forEach(m => m.classList.toggle('expanded', collapsed));
    syncToggleIcons(collapsed);
    localStorage.setItem('mr-sidebar-collapsed', collapsed ? '1' : '0');
  };

  // Injeta sidebars marcadas com data-sidebar="<key>"
  function init() {
    document.querySelectorAll('[data-sidebar]').forEach(el => {
      el.outerHTML = makeSidebar(el.dataset.sidebar);
    });
    // Restaura estado salvo
    const startCollapsed = localStorage.getItem('mr-sidebar-collapsed') === '1';
    if (startCollapsed) {
      document.querySelectorAll('.sidebar').forEach(s => s.classList.add('collapsed'));
      document.querySelectorAll('.main').forEach(m => m.classList.add('expanded'));
    }
    syncToggleIcons(startCollapsed);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
