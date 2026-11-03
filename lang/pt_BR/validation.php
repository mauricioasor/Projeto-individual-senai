<?php

/*
| Mensagens de validação em português (R.N.F. 14).
| Regras não listadas aqui usam o texto padrão em inglês do Laravel.
*/

return [
    'accepted' => 'O campo :attribute deve ser aceito.',
    'after_or_equal' => 'O campo :attribute deve ser uma data igual ou posterior a :date.',
    'array' => 'O campo :attribute deve ser uma lista.',
    'boolean' => 'O campo :attribute deve ser verdadeiro ou falso.',
    'confirmed' => 'A confirmação de :attribute não confere.',
    'date' => 'O campo :attribute deve ser uma data válida.',
    'date_format' => 'O campo :attribute deve estar no formato :format.',
    'distinct' => 'O campo :attribute tem um valor repetido.',
    'email' => 'Informe um :attribute válido.',
    'exists' => 'O :attribute selecionado é inválido.',
    'in' => 'O valor selecionado para :attribute é inválido.',
    'integer' => 'O campo :attribute deve ser um número inteiro.',
    'max' => [
        'array' => 'O campo :attribute pode ter no máximo :max itens.',
        'numeric' => 'O campo :attribute deve ser no máximo :max.',
        'string' => 'O campo :attribute deve ter no máximo :max caracteres.',
    ],
    'min' => [
        'array' => 'O campo :attribute deve ter pelo menos :min item(ns).',
        'numeric' => 'O campo :attribute deve ser no mínimo :min.',
        'string' => 'O campo :attribute deve ter pelo menos :min caracteres.',
    ],
    'numeric' => 'O campo :attribute deve ser um número.',
    'password' => [
        'letters' => 'A :attribute deve conter pelo menos uma letra.',
        'mixed' => 'A :attribute deve conter letras maiúsculas e minúsculas.',
        'numbers' => 'A :attribute deve conter pelo menos um número.',
        'symbols' => 'A :attribute deve conter pelo menos um símbolo.',
        'uncompromised' => 'Esta :attribute apareceu em um vazamento de dados. Escolha outra.',
    ],
    'regex' => 'O formato do campo :attribute é inválido.',
    'required' => 'O campo :attribute é obrigatório.',
    'size' => [
        'string' => 'O campo :attribute deve ter :size caracteres.',
    ],
    'string' => 'O campo :attribute deve ser um texto.',
    'unique' => 'Este :attribute já está em uso.',

    'custom' => [],

    'attributes' => [
        'nome' => 'nome',
        'email' => 'e-mail',
        'senha' => 'senha',
        'tipo_perfil' => 'perfil',
        'situacao' => 'situação',
        'nome_autor' => 'nome do autor',
        'nome_categoria' => 'nome da categoria',
        'titulo' => 'título',
        'isbn' => 'ISBN',
        'ano_publicacao' => 'ano de publicação',
        'quantidade_total' => 'quantidade de exemplares',
        'id_categoria' => 'categoria',
        'autores' => 'autores',
        'id_usuario' => 'colaborador',
        'livros' => 'livros',
        'itens' => 'livros',
        'inicio' => 'data inicial',
        'fim' => 'data final',
        'token' => 'link de recuperação',
    ],
];
