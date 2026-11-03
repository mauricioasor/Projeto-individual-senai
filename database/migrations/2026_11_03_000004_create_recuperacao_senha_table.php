<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tokens de redefinição de senha – Quadro 9 do dicionário de dados.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('RECUPERACAO_SENHA', function (Blueprint $table) {
            $table->comment('Tokens de redefinição de senha');
            $table->increments('id_recuperacao')->comment('Identificador da solicitação');
            $table->unsignedInteger('id_usuario')->comment('FK para USUARIO');
            $table->char('token_hash', 64)->unique()->comment('SHA-256 do token enviado por e-mail');
            $table->dateTime('data_expiracao')->comment('Validade do link (30 minutos)');
            $table->boolean('utilizado')->default(false)->comment('0 = não utilizado; 1 = utilizado');
            $table->dateTime('data_criacao')->useCurrent()->comment('Data e hora da solicitação');

            $table->foreign('id_usuario')->references('id_usuario')->on('USUARIO')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('RECUPERACAO_SENHA');
    }
};
