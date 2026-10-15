# MR Biblioteca — Front-end

Front-end estático do sistema de biblioteca. Cada tela é um arquivo HTML
independente; CSS e JS são compartilhados entre as telas.

## Estrutura

```
biblioteca/
├── index.html              # Tela de login (entrada)
├── dashboard.html          # Painel principal
├── catalogo.html           # Catálogo de livros
├── emprestimos.html        # Gestão de empréstimos
├── usuarios.html           # Gestão de usuários
├── assets/
│   ├── css/
│   │   ├── tokens.css      # Variáveis de design (cores, fontes, espaços)
│   │   ├── base.css        # Reset + overrides do Bootstrap
│   │   ├── layout.css      # Sidebar, topbar, header de página
│   │   ├── components.css  # Botões, cards, badges, KPIs, tabelas, listas
│   │   └── login.css       # Estilos exclusivos da tela de login
│   └── js/
│       ├── sidebar.js      # Geração e toggle da sidebar
│       ├── topbar.js       # Geração da topbar
│       └── app.js          # Inicialização, tabs e estado da sidebar
└── README.md
```

## Como usar

Basta abrir `index.html` no navegador. Não há dependência de servidor.
Bootstrap, Bootstrap Icons e a fonte Inter são carregados via CDN.

## Próximos passos para o back-end

1. Trocar os dados estáticos das tabelas por chamadas `fetch()` a uma API REST.
2. Mover os HTMLs para um motor de templates (ex.: PHP `include`, EJS, Blade).
3. Extrair `sidebar.js` / `topbar.js` para parciais server-side se preferir.
4. Implementar autenticação real no submit do formulário em `index.html`.

## Convenções

- Cores e espaçamento ficam em `tokens.css` — alterar sempre por lá.
- Componentes reutilizáveis vivem em `components.css`.
- Estilos específicos de uma tela ficam em arquivo próprio (ex.: `login.css`).
- Nada de estilos inline novos: criar classe em `components.css`.
