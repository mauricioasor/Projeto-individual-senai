<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Empréstimos em formato mestre-detalhe: EMPRESTIMO e ITEM_EMPRESTIMO (Quadros 7 e 8).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('EMPRESTIMO', function (Blueprint $table) {
            $table->comment('Empréstimos (mestre)');
            $table->increments('id_emprestimo')->comment('Identificador do empréstimo');
            $table->unsignedInteger('id_usuario')->comment('Colaborador que retirou os livros');
            $table->dateTime('data_emprestimo')->useCurrent()->comment('Data e hora da retirada');
            $table->date('data_prevista_dev')->comment('Data prevista de devolução (+14 dias; +14 na renovação)');
            $table->dateTime('data_devolucao')->nullable()->comment('Data em que o último livro foi devolvido');
            $table->unsignedTinyInteger('qtd_renovacoes')->default(0)->comment('Renovações realizadas (0 ou 1)');
            $table->enum('status', ['ATIVO', 'DEVOLVIDO'])->default('ATIVO')->comment('ATIVO = há livros em posse; DEVOLVIDO = todos devolvidos. Atraso é calculado');
            $table->decimal('valor_contribuicao', 8, 2)->default(0)->comment('Soma das contribuições dos itens (R$)');
            $table->dateTime('data_pagamento_contribuicao')->nullable()->comment('Pagamento da contribuição; nulo se pendente');

            $table->foreign('id_usuario')->references('id_usuario')->on('USUARIO')
                ->restrictOnDelete()->cascadeOnUpdate();
            $table->index(['status', 'data_prevista_dev']);
        });

        Schema::create('ITEM_EMPRESTIMO', function (Blueprint $table) {
            $table->comment('Livros de cada empréstimo (detalhe)');
            $table->increments('id_item')->comment('Identificador do item');
            $table->unsignedInteger('id_emprestimo')->comment('FK para EMPRESTIMO');
            $table->unsignedInteger('id_livro')->comment('FK para LIVRO');
            $table->dateTime('data_devolucao')->nullable()->comment('Devolução deste livro; nulo enquanto em posse');
            $table->unsignedSmallInteger('dias_atraso')->default(0)->comment('Dias de atraso apurados na devolução');
            $table->decimal('valor_contribuicao_item', 8, 2)->default(0)->comment('dias_atraso x R$ 1,00');

            $table->unique(['id_emprestimo', 'id_livro']);
            $table->foreign('id_emprestimo')->references('id_emprestimo')->on('EMPRESTIMO')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('id_livro')->references('id_livro')->on('LIVRO')
                ->restrictOnDelete()->cascadeOnUpdate();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE EMPRESTIMO ADD CONSTRAINT ck_emprestimo_renov CHECK (qtd_renovacoes <= 1)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ITEM_EMPRESTIMO');
        Schema::dropIfExists('EMPRESTIMO');
    }
};
