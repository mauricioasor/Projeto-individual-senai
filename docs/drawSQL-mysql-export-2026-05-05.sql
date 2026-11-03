CREATE TABLE `USUARIO`(
    `id_usuario` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `senha` VARCHAR(255) NOT NULL,
    `tipo_perfil` VARCHAR(255) NOT NULL,
    `data_cadastro` DATETIME NOT NULL
);
CREATE TABLE `EMPRESTIMO`(
    `id_emprestimo` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `id_usuario` INT NOT NULL,
    `data_emprestimo` DATETIME NOT NULL,
    `data_prevista_dev` DATETIME NOT NULL,
    `data_devolucao` DATETIME NOT NULL,
    `status` VARCHAR(255) NOT NULL,
    `id_livro` INT NOT NULL
);
CREATE TABLE `LIVRO`(
    `id_livro` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `titulo` VARCHAR(255) NOT NULL,
    `isbn` VARCHAR(255) NOT NULL,
    `ano_publicacao` INT NOT NULL,
    `quantidade_total` INT NOT NULL,
    `quantidade_disponivel` INT NOT NULL,
    `id_autor` INT NOT NULL,
    `id_categoria` INT NOT NULL
);
CREATE TABLE `AUTOR`(
    `id_autor` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `nome_autor` VARCHAR(255) NOT NULL
);
CREATE TABLE `CATEGORIA`(
    `id_categoria` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `nome_categoria` VARCHAR(255) NOT NULL
);
ALTER TABLE
    `EMPRESTIMO` ADD CONSTRAINT `emprestimo_id_livro_foreign` FOREIGN KEY(`id_livro`) REFERENCES `LIVRO`(`id_livro`);
ALTER TABLE
    `LIVRO` ADD CONSTRAINT `livro_id_categoria_foreign` FOREIGN KEY(`id_categoria`) REFERENCES `CATEGORIA`(`id_categoria`);
ALTER TABLE
    `EMPRESTIMO` ADD CONSTRAINT `emprestimo_id_usuario_foreign` FOREIGN KEY(`id_usuario`) REFERENCES `USUARIO`(`id_usuario`);
ALTER TABLE
    `LIVRO` ADD CONSTRAINT `livro_id_autor_foreign` FOREIGN KEY(`id_autor`) REFERENCES `AUTOR`(`id_autor`);