const { chromium } = require('playwright');
const fs = require('fs');
const BASE = 'http://127.0.0.1:8080';
const W = 1600, H = 900;
const DIR = '/tmp/claude-0/video/';

const INIT = `
(() => {
  const css = \`
  #demo-cursor{position:fixed;z-index:2147483647;width:22px;height:22px;margin:-3px 0 0 -3px;pointer-events:none;
    background:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='22' height='22' viewBox='0 0 24 24'><path d='M3 2l7 19 2.5-7.5L20 11z' fill='%23111' stroke='white' stroke-width='1.5'/></svg>") no-repeat;transition:transform .1s}
  #demo-cursor.down{transform:scale(.85)}
  #demo-leg{position:fixed;left:50%;top:12px;transform:translateX(-50%);z-index:2147483646;pointer-events:none;
    background:rgba(15,23,42,.88);color:#fff;font:600 22px/1.35 Inter,Arial,sans-serif;padding:12px 26px;border-radius:12px;
    box-shadow:0 8px 24px rgba(0,0,0,.25);max-width:80%;text-align:center}
  #demo-leg small{display:block;font-weight:500;font-size:15px;color:#C7D2FE;letter-spacing:.06em;text-transform:uppercase;margin-bottom:2px}\`;
  const add = () => {
    if (!document.body || document.getElementById('demo-cursor')) return;
    const s = document.createElement('style'); s.textContent = css; document.head.appendChild(s);
    const c = document.createElement('div'); c.id = 'demo-cursor'; document.body.appendChild(c);
    const pos = JSON.parse(sessionStorage.getItem('demo-pos') || '[800,450]');
    c.style.left = pos[0] + 'px'; c.style.top = pos[1] + 'px';
    document.addEventListener('mousemove', e => { c.style.left = e.clientX + 'px'; c.style.top = e.clientY + 'px'; sessionStorage.setItem('demo-pos', JSON.stringify([e.clientX, e.clientY])); }, true);
    document.addEventListener('mousedown', () => c.classList.add('down'), true);
    document.addEventListener('mouseup', () => c.classList.remove('down'), true);
    const leg = sessionStorage.getItem('demo-leg');
    if (leg) { const l = document.createElement('div'); l.id = 'demo-leg'; l.innerHTML = leg; document.body.appendChild(l); }
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', add); else add();
})();`;

const CARD = (n, titulo, sub) => `<!doctype html><meta charset="utf-8"><body style="margin:0;height:100vh;display:flex;align-items:center;justify-content:center;
  background:#004CA3;font-family:Inter,Arial,sans-serif;color:#fff"><div style="text-align:center">
  ${n ? `<div style="font-size:30px;font-weight:600;color:#FDBA74;letter-spacing:.1em">${n}</div>` : ''}
  <div style="font-size:72px;font-weight:800;margin:10px 0 18px">${titulo}</div>
  <div style="font-size:30px;color:#DBEAFE">${sub}</div></div></body>`;

(async () => {
  const browser = await chromium.launch();
  const ctx = await browser.newContext({ viewport: { width: W, height: H }, recordVideo: { dir: DIR + 'raw', size: { width: W, height: H } }, locale: 'pt-BR' });
  await ctx.addInitScript(INIT);
  const p = await ctx.newPage();
  p.on('dialog', async d => { await p.waitForTimeout(900); await d.accept(); });
  const pausa = ms => p.waitForTimeout(ms);
  const leg = async (sec, txt) => { await p.evaluate(([s, t]) => { const h = t ? `<small>${s}</small>${t}` : ''; sessionStorage.setItem('demo-leg', h);
      let l = document.getElementById('demo-leg'); if (!t) { if (l) l.remove(); return; }
      if (!l) { l = document.createElement('div'); l.id = 'demo-leg'; document.body.appendChild(l); } l.innerHTML = h; }, [sec, txt]); };
  const mover = async sel => { const el = p.locator(sel).first(); await el.scrollIntoViewIfNeeded(); const b = await el.boundingBox();
      await p.mouse.move(b.x + Math.min(b.width / 2, 60), b.y + b.height / 2, { steps: 25 }); await pausa(250); return el; };
  const clicar = async (sel, nav = false) => { await mover(sel); if (nav) await Promise.all([p.waitForLoadState('load'), p.waitForNavigation(), p.mouse.down().then(() => p.mouse.up())]); else { await p.mouse.down(); await p.mouse.up(); } await pausa(500); };
  const digitar = async (sel, txt) => { await clicar(sel); await p.locator(sel).first().fill(''); await p.keyboard.type(txt, { delay: 55 }); await pausa(250); };
  const card = async (n, t, s, ms = 3200) => { await p.setContent(CARD(n, t, s)); await pausa(ms); };

  // Abertura
  await card('', 'MR Biblioteca', 'Demonstração das principais funcionalidades', 3500);

  // 1. Autenticação
  await card('1', 'Autenticação', 'Login, mensagens genéricas e controle de acesso por perfil');
  await p.goto(BASE + '/login'); await leg('Autenticação', 'Login com senha incorreta'); await pausa(1200);
  await digitar('#email', 'admin@empresa.com.br'); await digitar('#senha', 'SenhaErrada');
  await clicar('button[type=submit]', true);
  await leg('Autenticação', 'Mensagem genérica: não revela se o erro está no e-mail ou na senha'); await pausa(3200);
  await leg('Autenticação', 'Login do Administrador com a senha correta');
  await digitar('#senha', 'Admin@123'); await clicar('button[type=submit]', true);
  await leg('Autenticação', 'Painel do Administrador com os indicadores do acervo'); await pausa(3500);

  // 2. CRUD de livros
  await card('2', 'CRUD de livros', 'Cadastrar, consultar, editar e excluir');
  await p.goto(BASE + '/catalogo'); await leg('CRUD · Consultar', 'Catálogo com busca, filtros e exemplares disponíveis'); await pausa(2500);
  await leg('CRUD · Cadastrar', 'Novo livro com vários autores e categoria');
  await clicar('a:has-text("Novo livro")', true); await pausa(800);
  await digitar('#titulo', 'Engenharia de Software Moderna');
  await digitar('#isbn', '9788575228432');
  await digitar('#ano_publicacao', '2020');
  await digitar('#quantidade_total', '2');
  await mover('#id_categoria'); await p.selectOption('#id_categoria', { index: 1 }); await pausa(600);
  await clicar('input[name="autores[]"] >> nth=0'); await clicar('input[name="autores[]"] >> nth=2');
  await clicar('form[novalidate] button[type=submit]', true);
  await leg('CRUD · Cadastrar', 'Livro cadastrado e já disponível no catálogo'); await pausa(2500);
  await leg('CRUD · Consultar', 'Busca pelo título');
  await digitar('input[name=q]', 'Engenharia'); await Promise.all([p.waitForNavigation(), p.keyboard.press('Enter')]); await pausa(2500);
  await leg('CRUD · Editar', 'Alteração da quantidade de exemplares');
  await clicar('a[title="Editar"]', true); await pausa(800);
  await digitar('#quantidade_total', '3');
  await clicar('form[novalidate] button[type=submit]', true);
  await leg('CRUD · Editar', 'Alteração salva: agora com 3 exemplares'); await pausa(2600);
  await p.goto(BASE + '/catalogo?q=Engenharia'); await pausa(800);
  await leg('CRUD · Excluir', 'Exclusão com confirmação (só para livros sem empréstimos)');
  await clicar('form[data-confirmar] button', true);
  await leg('CRUD · Excluir', 'Livro excluído'); await pausa(2800);

  // 3. Empréstimo mestre-detalhe (bônus rápido)
  await card('3', 'Empréstimo', 'Formulário mestre-detalhe com validação das regras de negócio');
  await p.goto(BASE + '/emprestimos/novo'); await leg('Empréstimo', 'Mestre: colaborador e datas · Detalhe: até 3 livros'); await pausa(1500);
  const v = await p.$eval('#id_usuario', el => [...el.options].find(o => o.text.startsWith('Enzo')).value);
  await mover('#id_usuario'); await p.selectOption('#id_usuario', v); await pausa(700);
  await clicar('[data-detalhe-adicionar]');
  const sels = p.locator('select[name="livros[]"]');
  for (let i = 0; i < 2; i++) { await mover(`select[name="livros[]"] >> nth=${i}`); await sels.nth(i).selectOption({ index: i + 1 }); await pausa(600); }
  await clicar('form button[type=submit]:has-text("Registrar")', true);
  await leg('Empréstimo', 'Empréstimo registrado: estoque atualizado em uma única transação'); await pausa(3200);
  await leg('Empréstimo', 'Colaborador com atraso: o sistema recusa novo empréstimo');
  await p.goto(BASE + '/emprestimos/novo'); await pausa(600);
  const v2 = await p.$eval('#id_usuario', el => [...el.options].find(o => o.text.startsWith('Vinicius')).value);
  await mover('#id_usuario'); await p.selectOption('#id_usuario', v2); await pausa(500);
  await mover('select[name="livros[]"] >> nth=0'); await sels.nth(0).selectOption({ index: 1 }); await pausa(500);
  await clicar('form button[type=submit]:has-text("Registrar")', true); await pausa(3200);

  // 4. Relatórios
  await card('4', 'Relatório de empréstimos', 'Filtros e exportação em CSV e PDF');
  await p.goto(BASE + '/admin/relatorios/emprestimos'); await leg('Relatório', 'Relatório com filtros por período, colaborador, categoria e situação'); await pausa(1500);
  await clicar('#inicio'); await p.locator('#inicio').fill('2026-10-01'); await pausa(600);
  await clicar('button:has-text("Filtrar")', true);
  await leg('Relatório', 'Resultado do período com totais de atraso e contribuições'); await pausa(3200);
  const dl = async (fmt) => { const [d] = await Promise.all([p.waitForEvent('download'), clicar(`a[href*="exportar/${fmt}"]`)]); await d.saveAs(DIR + 'relatorio.' + fmt); };
  await leg('Relatório', 'Exportação em CSV (abre no Excel com acentuação correta)'); await dl('csv'); await pausa(1800);
  await leg('Relatório', 'Exportação em PDF'); await dl('pdf'); await pausa(1500);
  await leg('', '');
  // mostra o PDF gerado
  require('child_process').execSync(`pdftoppm -r 110 -png -f 1 -l 1 ${DIR}relatorio.pdf ${DIR}pdf`);
  const img = fs.readFileSync(DIR + 'pdf-1.png').toString('base64');
  await p.setContent(`<!doctype html><meta charset="utf-8"><body style="margin:0;background:#525659;display:flex;flex-direction:column;align-items:center;height:100vh;overflow:hidden;font-family:Arial">
    <div style="width:100%;background:#323639;color:#fff;font-size:16px;padding:10px 20px;box-sizing:border-box">relatorio-emprestimos.pdf</div>
    <img src="data:image/png;base64,${img}" style="height:calc(100vh - 70px);margin-top:14px;box-shadow:0 4px 18px rgba(0,0,0,.4)"></body>`);
  await pausa(4500);

  // Encerramento
  await card('', 'MR Biblioteca', 'Laravel 13 · MySQL · Docker — 46 testes automatizados aprovados', 4000);
  await ctx.close(); await browser.close();
})().catch(e => { console.error(e); process.exit(1); });
