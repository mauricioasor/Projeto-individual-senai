-- MR Biblioteca - script de criação do banco (MySQL 8)
-- Coerente com o DER (Figura 3) e o dicionário de dados (Quadros 2 a 9)

CREATE DATABASE IF NOT EXISTS biblioteca_db
  DEFAULT CHARACTER SET utf8mb4 DEFAULT COLLATE utf8mb4_unicode_ci;
USE biblioteca_db;

CREATE TABLE USUARIO (
  id_usuario        INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Identificador do usuário',
  nome              VARCHAR(120) NOT NULL COMMENT 'Nome completo do colaborador',
  email             VARCHAR(160) NOT NULL COMMENT 'E-mail corporativo (login), em minúsculas',
  senha             CHAR(60)     NOT NULL COMMENT 'Hash bcrypt da senha',
  tipo_perfil       ENUM('ADMIN','USUARIO') NOT NULL DEFAULT 'USUARIO' COMMENT 'ADMIN = Administrador; USUARIO = colaborador',
  situacao          ENUM('ATIVO','INATIVO') NOT NULL DEFAULT 'ATIVO' COMMENT 'ATIVO = pode acessar; INATIVO = sem acesso. Pendências (atraso/contribuição) são verificadas na operação',
  tentativas_login  TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Tentativas consecutivas de login inválidas',
  bloqueado_ate     DATETIME NULL COMMENT 'Bloqueio temporário após 5 tentativas inválidas',
  data_cadastro     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora do cadastro',
  PRIMARY KEY (id_usuario),
  UNIQUE KEY uq_usuario_email (email)
) ENGINE=InnoDB COMMENT='Colaboradores com acesso ao sistema';

CREATE TABLE AUTOR (
  id_autor    INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Identificador do autor',
  nome_autor  VARCHAR(120) NOT NULL COMMENT 'Nome do autor',
  PRIMARY KEY (id_autor),
  UNIQUE KEY uq_autor_nome (nome_autor)
) ENGINE=InnoDB COMMENT='Autores dos livros';

CREATE TABLE CATEGORIA (
  id_categoria    INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Identificador da categoria',
  nome_categoria  VARCHAR(60) NOT NULL COMMENT 'Nome da categoria temática',
  PRIMARY KEY (id_categoria),
  UNIQUE KEY uq_categoria_nome (nome_categoria)
) ENGINE=InnoDB COMMENT='Categorias temáticas do acervo';

CREATE TABLE LIVRO (
  id_livro               INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Identificador do livro',
  titulo                 VARCHAR(200) NOT NULL COMMENT 'Título do livro',
  isbn                   VARCHAR(13)  NOT NULL COMMENT 'ISBN com 10 ou 13 dígitos, sem hífens',
  ano_publicacao         SMALLINT UNSIGNED NULL COMMENT 'Ano de publicação da edição',
  quantidade_total       INT UNSIGNED NOT NULL COMMENT 'Total de exemplares (mínimo 1)',
  quantidade_disponivel  INT UNSIGNED NOT NULL COMMENT 'Exemplares disponíveis (0 a quantidade_total)',
  id_categoria           INT UNSIGNED NOT NULL COMMENT 'FK para CATEGORIA',
  PRIMARY KEY (id_livro),
  UNIQUE KEY uq_livro_isbn (isbn),
  CONSTRAINT ck_livro_total CHECK (quantidade_total >= 1),
  CONSTRAINT ck_livro_disp  CHECK (quantidade_disponivel <= quantidade_total),
  CONSTRAINT fk_livro_categoria FOREIGN KEY (id_categoria) REFERENCES CATEGORIA (id_categoria) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB COMMENT='Títulos do acervo';

CREATE TABLE LIVRO_AUTOR (
  id_livro  INT UNSIGNED NOT NULL COMMENT 'FK para LIVRO',
  id_autor  INT UNSIGNED NOT NULL COMMENT 'FK para AUTOR',
  PRIMARY KEY (id_livro, id_autor),
  CONSTRAINT fk_livro_autor_livro FOREIGN KEY (id_livro) REFERENCES LIVRO (id_livro) ON DELETE CASCADE  ON UPDATE CASCADE,
  CONSTRAINT fk_livro_autor_autor FOREIGN KEY (id_autor) REFERENCES AUTOR (id_autor) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB COMMENT='Associação N:N entre livros e autores';

CREATE TABLE EMPRESTIMO (
  id_emprestimo         INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Identificador do empréstimo (mestre)',
  id_usuario            INT UNSIGNED NOT NULL COMMENT 'Colaborador que retirou os livros',
  data_emprestimo       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora da retirada',
  data_prevista_dev     DATE NOT NULL COMMENT 'Data prevista de devolução (+14 dias; +14 na renovação)',
  data_devolucao        DATETIME NULL COMMENT 'Data em que o último livro foi devolvido',
  qtd_renovacoes        TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Renovações realizadas (0 ou 1)',
  status                ENUM('ATIVO','DEVOLVIDO') NOT NULL DEFAULT 'ATIVO' COMMENT 'ATIVO = há livros em posse; DEVOLVIDO = todos devolvidos. Atraso é calculado',
  valor_contribuicao    DECIMAL(8,2) NOT NULL DEFAULT 0.00 COMMENT 'Soma das contribuições dos itens (R$)',
  data_pagamento_contribuicao DATETIME NULL COMMENT 'Pagamento da contribuição; nulo se pendente',
  PRIMARY KEY (id_emprestimo),
  CONSTRAINT ck_emprestimo_renov CHECK (qtd_renovacoes <= 1),
  CONSTRAINT fk_emprestimo_usuario FOREIGN KEY (id_usuario) REFERENCES USUARIO (id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB COMMENT='Empréstimos (mestre)';

CREATE TABLE ITEM_EMPRESTIMO (
  id_item           INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Identificador do item (detalhe)',
  id_emprestimo     INT UNSIGNED NOT NULL COMMENT 'FK para EMPRESTIMO',
  id_livro          INT UNSIGNED NOT NULL COMMENT 'FK para LIVRO',
  data_devolucao    DATETIME NULL COMMENT 'Devolução deste livro; nulo enquanto em posse',
  dias_atraso       SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Dias de atraso apurados na devolução',
  valor_contribuicao_item DECIMAL(8,2) NOT NULL DEFAULT 0.00 COMMENT 'dias_atraso x R$ 1,00',
  PRIMARY KEY (id_item),
  UNIQUE KEY uq_item_emprestimo_livro (id_emprestimo, id_livro),
  CONSTRAINT fk_item_emprestimo FOREIGN KEY (id_emprestimo) REFERENCES EMPRESTIMO (id_emprestimo) ON DELETE CASCADE  ON UPDATE CASCADE,
  CONSTRAINT fk_item_livro      FOREIGN KEY (id_livro)      REFERENCES LIVRO (id_livro)           ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB COMMENT='Livros de cada empréstimo (detalhe)';

CREATE TABLE RECUPERACAO_SENHA (
  id_recuperacao  INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Identificador da solicitação',
  id_usuario      INT UNSIGNED NOT NULL COMMENT 'FK para USUARIO',
  token_hash      CHAR(64) NOT NULL COMMENT 'SHA-256 do token enviado por e-mail',
  data_expiracao  DATETIME NOT NULL COMMENT 'Validade do link (30 minutos)',
  utilizado       TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 = não utilizado; 1 = utilizado',
  data_criacao    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora da solicitação',
  PRIMARY KEY (id_recuperacao),
  UNIQUE KEY uq_recuperacao_token (token_hash),
  CONSTRAINT fk_recuperacao_usuario FOREIGN KEY (id_usuario) REFERENCES USUARIO (id_usuario) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB COMMENT='Tokens de redefinição de senha';
