# MR Biblioteca

Sistema web de gestão da biblioteca interna de uma empresa, desenvolvido como **Projeto Individual** do curso de Análise e Desenvolvimento de Sistemas da UniSENAI Joinville/SC.

O sistema substitui a planilha compartilhada e o caderno de anotações por um controle centralizado do acervo e dos empréstimos: disponibilidade em tempo real, prazos, renovações, devoluções, contribuições por atraso (destinadas a ações sociais), notificações por e-mail e relatórios.

A documentação completa está em [`docs/Relatorio_MR_Biblioteca_v1.3.docx`](docs/Relatorio_MR_Biblioteca_v1.3.docx).

## Tecnologias

| Camada | Tecnologia |
|---|---|
| Back-end | PHP 8.3 + Laravel 13 (MVC, Eloquent, Form Requests, Policies) |
| Front-end | Views Blade, HTML5, CSS3, JavaScript e Bootstrap 5.3 |
| Banco de dados | MySQL 8.0 (SQLite nos testes) |
| Relatórios | CSV nativo e PDF com `barryvdh/laravel-dompdf` |
| Ambiente | Docker Compose (PHP-FPM, Nginx, MySQL e Mailpit) |

## Como executar

### Com Docker (recomendado)

```bash
cp .env.example .env
docker compose up -d --build
```

Na primeira execução o contêiner instala as dependências, gera a chave da aplicação, cria as tabelas e carrega os dados de exemplo (pode levar alguns minutos).

- Sistema: http://localhost:8000
- Caixa de e-mails de teste (Mailpit): http://localhost:8025, onde chegam os e-mails de recuperação de senha e de atraso.
- MySQL: `localhost:3307` (usuário `biblioteca`, senha `biblioteca`)

Se a porta 8000 estiver ocupada no seu computador (o navegador mostra "conexão redefinida"), defina outra no `.env`, por exemplo `APP_PORT=8080` e `APP_URL=http://localhost:8080`, e rode `docker compose up -d` de novo. No Windows, se `localhost` não abrir, use `http://127.0.0.1:<porta>`.

### Sem Docker

Requer PHP 8.3+ e Composer. Para um teste rápido com SQLite:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

No `.env`, troque `DB_CONNECTION=mysql` por `DB_CONNECTION=sqlite` (e apague as linhas `DB_HOST` a `DB_PASSWORD`) e `MAIL_MAILER=smtp` por `MAIL_MAILER=log`. Depois:

```bash
php artisan migrate --seed
php artisan serve
```

Os e-mails ficam registrados em `storage/logs/laravel.log`.

### Acessos de exemplo

| Perfil | E-mail | Senha |
|---|---|---|
| Administrador | `admin@empresa.com.br` | `Admin@123` |
| Colaborador | `ana.carolina@empresa.com.br` (e outros) | `Senha@123` |

O domínio aceito no autocadastro é definido em `BIBLIOTECA_DOMINIO_EMAIL` (padrão: `empresa.com.br`).

## Testes

Os casos de teste do Quadro 12 da documentação (CT01 a CT13) estão automatizados em `tests/Feature`:

```bash
php artisan test
```

| Caso | Teste |
|---|---|
| CT01, CT02 | `AutenticacaoTest` |
| CT03 | `CadastroTest` |
| CT04 | `LivroTest` |
| CT05, CT06, CT07 | `EmprestimoTest` |
| CT08 | `DevolucaoTest` |
| CT09 | `RecuperacaoSenhaTest` |
| CT10, CT11, CT13 | `SegurancaTest` |
| CT12 | `RelatorioTest` |

As evidências da execução (saída dos testes e capturas de tela de cada caso) estão em [`docs/evidencias/`](docs/evidencias/README.md).

## Requisitos funcionais e onde estão no código

| Requisito | Rota / tela | Código principal |
|---|---|---|
| R.F. 01 – Registro de usuários | `/admin/usuarios` | `Admin/UsuarioController`, `UsuarioRequest` |
| R.F. 02 – Autocadastro | `/cadastro` | `Auth/CadastroController`, `CadastroRequest` |
| R.F. 03 – Autores | `/admin/autores` | `Admin/AutorController` |
| R.F. 04 – Categorias | `/admin/categorias` | `Admin/CategoriaController` |
| R.F. 05 – Livros | `/admin/livros/...` | `Admin/LivroController`, `LivroRequest` |
| R.F. 06 – Autenticação | `/login` | `Auth/LoginController` |
| R.F. 07 – Recuperação de senha | `/esqueci-senha` | `Auth/RecuperacaoSenhaController`, `RecuperacaoSenhaMail` |
| R.F. 08 – Empréstimo (mestre-detalhe) | `/emprestimos/novo` | `EmprestimoController`, `EmprestimoService::registrar` |
| R.F. 09 – Renovação | botão Renovar | `EmprestimoService::renovar`, `EmprestimoPolicy` |
| R.F. 10 – Devolução | `/admin/emprestimos/{id}` | `EmprestimoService::devolver` |
| R.F. 11 – Pagamento da contribuição | `/admin/emprestimos/{id}` | `EmprestimoService::registrarPagamento` |
| R.F. 12 – Notificação de atraso | botões Notificar | `Admin/NotificacaoController`, `AtrasoMail` |
| R.F. 13 – Painel do administrador | `/admin/painel` | `PainelController::admin` |
| R.F. 14 – Painel do colaborador | `/meu-painel` | `PainelController::colaborador` |
| R.F. 15 – Catálogo | `/catalogo` | `CatalogoController` |
| R.F. 16 – Histórico | `/meus-emprestimos`, `/admin/usuarios/{id}/historico` | `HistoricoController` |
| R.F. 17 – Relatório CSV/PDF | `/admin/relatorios/emprestimos` | `Admin/RelatorioController` |

As regras de negócio configuráveis (prazo, limite de livros, valor por dia de atraso, bloqueio de login e validade do link) estão em `config/biblioteca.php`.

## Segurança (seção 5.5 da documentação)

- Senhas com hash bcrypt (`BCRYPT_ROUNDS=12`) e nunca exibidas.
- Sessão do Laravel regenerada no login, cookie HttpOnly e expiração após 120 minutos de inatividade.
- Bloqueio de 15 minutos após 5 tentativas de login inválidas e mensagens genéricas de login e de recuperação de senha.
- Token de recuperação de uso único, válido por 30 minutos e guardado apenas como hash SHA-256.
- Controle de acesso no servidor: middleware `perfil:ADMIN` (HTTP 403) e `EmprestimoPolicy`.
- Validação no navegador e no servidor (Form Requests), consultas com parâmetros vinculados (Eloquent), escape automático do Blade e token CSRF em todos os formulários.
- Empréstimos e devoluções em transação, com bloqueio das linhas de livro (`lockForUpdate`).

## Estrutura

```
app/
├── Http/Controllers/        Controllers (Auth, Admin e telas do colaborador)
├── Http/Middleware/         VerificaPerfil (R.N.F. 06) e VerificaContaAtiva (RN01)
├── Http/Requests/           Validações (Form Requests)
├── Mail/                    E-mails de recuperação de senha e de atraso
├── Models/                  Usuario, Livro, Autor, Categoria, Emprestimo, ItemEmprestimo, RecuperacaoSenha
├── Policies/                EmprestimoPolicy
└── Services/                EmprestimoService (regras RN12 a RN22)
config/biblioteca.php        Parâmetros das regras de negócio
database/migrations/         Tabelas conforme o DER e o dicionário de dados
database/seeders/            Dados de exemplo
resources/views/             Telas Blade (layout por perfil)
public/assets/               CSS e JS do protótipo, reaproveitados
docs/                        Relatório, diagramas e script SQL
prototipo/                   Protótipo navegável em HTML (dados simulados)
tests/                       Testes automatizados (CT01 a CT13)
```
