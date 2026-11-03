# Evidências de teste – MR Biblioteca

Evidências da execução dos casos de teste do Quadro 12 do relatório (CT01 a CT13), obtidas em 03/11/2026 com o sistema rodando localmente e os dados de exemplo (`php artisan migrate --seed`).

## Testes automatizados

- `resultado-testes.txt`: saída do PHPUnit (`php artisan test --testdox-text`), com 45 testes e 165 verificações aprovados.
- `testes-automatizados-45-aprovados.png`: execução no terminal (`docker compose exec app php artisan test`).

Para repetir: `docker compose exec app php artisan test`.

## Testes manuais (capturas de tela)

| Caso | Cenário | Evidência | Resultado |
|---|---|---|---|
| CT01 | Login com senha incorreta | `CT01-login-senha-incorreta.png` | Aprovado |
| CT02 | Cinco tentativas de login inválidas | `CT02-bloqueio-apos-5-tentativas.png` | Aprovado |
| CT03 | Autocadastro com e-mail fora do domínio | `CT03-cadastro-dominio-externo.png` | Aprovado |
| CT04 | Livro com ISBN repetido | `CT04-isbn-repetido.png` | Aprovado |
| CT05 | Livro sem exemplar disponível | `CT05-livro-sem-exemplar.png` | Aprovado |
| CT06 | Quarto livro para quem já tem três | `CT06-limite-tres-livros.png` | Aprovado |
| CT07 | Colaborador com empréstimo em atraso | `CT07-pendencia-atraso.png` | Aprovado |
| CT08 | Devolução com 3 dias de atraso | `CT08a` a `CT08d` | Aprovado |
| CT09 | Link de recuperação usado pela segunda vez | `CT09a` a `CT09c` | Aprovado |
| CT10 | Colaborador acessando URL do administrador | `CT10-colaborador-acessa-admin-403.png` | Aprovado |
| CT11 | Busca com `' OR 1=1 --` | `CT11-busca-injecao-sql.png` | Aprovado |
| CT12 | Exportação do relatório em CSV (e PDF) | `CT12a` a `CT12c`, `relatorio-emprestimos.csv` e `.pdf` | Aprovado |
| CT13 | Formulário enviado por outro site sem token CSRF | `CT13b-ataque-csrf-419.png` | Aprovado |

### Observações sobre os cenários

- **CT05:** o formulário de empréstimo só lista livros com exemplares disponíveis. Por isso o teste simula duas pessoas ao mesmo tempo: o formulário foi aberto com o último exemplar de "Arquitetura limpa para iniciantes" selecionado, e outro empréstimo levou esse exemplar antes do envio. O servidor recusou o segundo empréstimo.
- **CT08:** o empréstimo de Lia Cardoso venceu em 31/10/2026 e foi devolvido em 03/11/2026. As capturas mostram o empréstimo antes da devolução, o catálogo com 2/3 exemplares, a contribuição de R$ 3,00 registrada e o catálogo com 3/3 após a devolução.
- **CT13:** o Laravel 13 aceita formulários do próprio sistema quando o navegador informa que a requisição é da mesma origem (cabeçalho `Sec-Fetch-Site`), e exige o token CSRF nos demais casos. A evidência reproduz o ataque real: uma página em outro endereço (`http://localhost:9000`) envia o formulário de cadastro sem token, e o sistema responde HTTP 419 (página "Sessão expirada") sem criar a conta. Os testes automatizados `test_formulario_sem_token_csrf_e_recusado` e `test_requisicao_de_outro_site_sem_token_e_recusada` cobrem os dois casos.
