<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabelas do acervo: AUTOR, CATEGORIA, LIVRO e LIVRO_AUTOR (Quadros 3 a 6).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('AUTOR', function (Blueprint $table) {
            $table->comment('Autores dos livros');
            $table->increments('id_autor')->comment('Identificador do autor');
            $table->string('nome_autor', 120)->unique()->comment('Nome do autor');
        });

        Schema::create('CATEGORIA', function (Blueprint $table) {
            $table->comment('Categorias temáticas do acervo');
            $table->increments('id_categoria')->comment('Identificador da categoria');
            $table->string('nome_categoria', 60)->unique()->comment('Nome da categoria temática');
        });

        Schema::create('LIVRO', function (Blueprint $table) {
            $table->comment('Títulos do acervo');
            $table->increments('id_livro')->comment('Identificador do livro');
            $table->string('titulo', 200)->comment('Título do livro');
            $table->string('isbn', 13)->unique()->comment('ISBN com 10 ou 13 dígitos, sem hífens');
            $table->unsignedSmallInteger('ano_publicacao')->nullable()->comment('Ano de publicação da edição');
            $table->unsignedInteger('quantidade_total')->comment('Total de exemplares (mínimo 1)');
            $table->unsignedInteger('quantidade_disponivel')->comment('Exemplares disponíveis (0 a quantidade_total)');
            $table->unsignedInteger('id_categoria')->comment('FK para CATEGORIA');

            $table->foreign('id_categoria')->references('id_categoria')->on('CATEGORIA')
                ->restrictOnDelete()->cascadeOnUpdate();
        });

        Schema::create('LIVRO_AUTOR', function (Blueprint $table) {
            $table->comment('Associação N:N entre livros e autores');
            $table->unsignedInteger('id_livro')->comment('FK para LIVRO');
            $table->unsignedInteger('id_autor')->comment('FK para AUTOR');
            $table->primary(['id_livro', 'id_autor']);

            $table->foreign('id_livro')->references('id_livro')->on('LIVRO')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('id_autor')->references('id_autor')->on('AUTOR')
                ->restrictOnDelete()->cascadeOnUpdate();
        });

        // RN10 – regras de quantidade garantidas também pelo banco (MySQL 8.0.16+)
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE LIVRO ADD CONSTRAINT ck_livro_total CHECK (quantidade_total >= 1)');
            DB::statement('ALTER TABLE LIVRO ADD CONSTRAINT ck_livro_disp CHECK (quantidade_disponivel <= quantidade_total)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('LIVRO_AUTOR');
        Schema::dropIfExists('LIVRO');
        Schema::dropIfExists('CATEGORIA');
        Schema::dropIfExists('AUTOR');
    }
};
