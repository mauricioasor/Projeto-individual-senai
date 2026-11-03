<?php

/*
|--------------------------------------------------------------------------
| Regras de negócio do MR Biblioteca
|--------------------------------------------------------------------------
|
| Valores definidos na documentação (seção 4.3 – Regras de Negócio).
| Mantidos em um só lugar para facilitar ajustes pela empresa.
|
*/

return [

    // RN04 – autocadastro só com e-mail do domínio corporativo
    'dominio_email' => env('BIBLIOTECA_DOMINIO_EMAIL', 'empresa.com.br'),

    // RN15 / RN16 – prazo e renovação
    'prazo_dias' => 14,
    'max_renovacoes' => 1,

    // RN13 / RN14 – limite de livros por empréstimo e em posse
    'max_livros' => 3,

    // RN21 – contribuição por dia de atraso, por livro (R$)
    'valor_dia_atraso' => 1.00,

    // RN06 – bloqueio após tentativas inválidas
    'max_tentativas_login' => 5,
    'bloqueio_minutos' => 15,

    // RN07 – validade do link de redefinição de senha
    'token_minutos' => 30,

    // R.N.F. 13 – paginação
    'itens_por_pagina' => 10,

];
