/* ======================================================
   app.js — Comportamentos de interface do MR Biblioteca
   ====================================================== */

(function () {
  'use strict';

  // ---------- Menu lateral recolhível (R.N.F. 02) ----------
  function aplicarMenuRecolhido(recolhido) {
    document.querySelectorAll('.sidebar').forEach(s => s.classList.toggle('collapsed', recolhido));
    document.querySelectorAll('.main').forEach(m => m.classList.toggle('expanded', recolhido));
    document.querySelectorAll('[data-alternar-menu] i').forEach(i => {
      i.classList.toggle('bi-chevron-double-left', !recolhido);
      i.classList.toggle('bi-chevron-double-right', recolhido);
    });
  }

  function lerPreferencia() {
    try { return localStorage.getItem('mr-sidebar-collapsed') === '1'; } catch (e) { return false; }
  }

  function salvarPreferencia(valor) {
    try { localStorage.setItem('mr-sidebar-collapsed', valor ? '1' : '0'); } catch (e) { /* sem armazenamento */ }
  }

  function iniciarMenu() {
    aplicarMenuRecolhido(lerPreferencia() && window.innerWidth > 992);

    document.querySelectorAll('[data-alternar-menu]').forEach(botao => {
      botao.addEventListener('click', () => {
        const recolher = !document.querySelector('.sidebar').classList.contains('collapsed');
        aplicarMenuRecolhido(recolher);
        salvarPreferencia(recolher);
      });
    });

    // Telas pequenas: menu abre por cima do conteúdo
    const sidebar = document.getElementById('sidebar');
    const fundo = document.querySelector('.sidebar-backdrop');
    const alternarMobile = (abrir) => {
      if (!sidebar) return;
      sidebar.classList.toggle('aberta', abrir);
      if (fundo) fundo.classList.toggle('aberta', abrir);
    };
    document.querySelectorAll('[data-abrir-menu]').forEach(b => b.addEventListener('click', () => alternarMobile(true)));
    document.querySelectorAll('[data-fechar-menu]').forEach(b => b.addEventListener('click', () => alternarMobile(false)));
  }

  // ---------- Confirmação antes de excluir (R.N.F. 14) ----------
  function iniciarConfirmacoes() {
    document.addEventListener('submit', (evento) => {
      const form = evento.target;
      const mensagem = form.getAttribute('data-confirmar');
      if (mensagem && !window.confirm(mensagem)) {
        evento.preventDefault();
      }
    });
  }

  // ---------- Mostrar/ocultar senha ----------
  function iniciarSenhas() {
    document.querySelectorAll('[data-mostrar-senha]').forEach(botao => {
      botao.addEventListener('click', () => {
        const campo = document.getElementById(botao.getAttribute('data-mostrar-senha'));
        if (!campo) return;
        const mostrar = campo.type === 'password';
        campo.type = mostrar ? 'text' : 'password';
        const icone = botao.querySelector('i');
        if (icone) {
          icone.classList.toggle('bi-eye', !mostrar);
          icone.classList.toggle('bi-eye-slash', mostrar);
        }
      });
    });
  }

  // ---------- Formulário mestre-detalhe do empréstimo (R.F. 08) ----------
  function iniciarMestreDetalhe() {
    const lista = document.querySelector('[data-detalhe-lista]');
    const modelo = document.getElementById('modelo-linha-livro');
    const adicionar = document.querySelector('[data-detalhe-adicionar]');
    if (!lista || !modelo || !adicionar) return;

    const maximo = parseInt(lista.getAttribute('data-maximo') || '3', 10);

    function renumerar() {
      const linhas = lista.querySelectorAll('.detalhe-linha');
      linhas.forEach((linha, i) => {
        const num = linha.querySelector('.num');
        if (num) num.textContent = String(i + 1);
        const remover = linha.querySelector('[data-detalhe-remover]');
        if (remover) remover.disabled = linhas.length === 1;
      });
      adicionar.disabled = linhas.length >= maximo;
      atualizarOpcoes();
    }

    // Um mesmo livro não pode ser escolhido duas vezes (RN13)
    function atualizarOpcoes() {
      const selects = Array.from(lista.querySelectorAll('select'));
      const escolhidos = selects.map(s => s.value).filter(Boolean);
      selects.forEach(select => {
        Array.from(select.options).forEach(opcao => {
          if (!opcao.value) return;
          opcao.disabled = opcao.value !== select.value && escolhidos.includes(opcao.value);
        });
      });
    }

    adicionar.addEventListener('click', () => {
      if (lista.querySelectorAll('.detalhe-linha').length >= maximo) return;
      lista.appendChild(modelo.content.firstElementChild.cloneNode(true));
      renumerar();
    });

    lista.addEventListener('click', (evento) => {
      const botao = evento.target.closest('[data-detalhe-remover]');
      if (!botao) return;
      const linhas = lista.querySelectorAll('.detalhe-linha');
      if (linhas.length > 1) {
        botao.closest('.detalhe-linha').remove();
        renumerar();
      }
    });

    lista.addEventListener('change', atualizarOpcoes);
    renumerar();
  }

  function iniciar() {
    iniciarMenu();
    iniciarConfirmacoes();
    iniciarSenhas();
    iniciarMestreDetalhe();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', iniciar);
  } else {
    iniciar();
  }
})();
