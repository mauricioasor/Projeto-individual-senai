<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabela USUARIO – Quadro 2 do dicionário de dados.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('USUARIO', function (Blueprint $table) {
            $table->comment('Colaboradores com acesso ao sistema');
            $table->increments('id_usuario')->comment('Identificador do usuário');
            $table->string('nome', 120)->comment('Nome completo do colaborador');
            $table->string('email', 160)->unique()->comment('E-mail corporativo (login), em minúsculas');
            $table->char('senha', 60)->comment('Hash bcrypt da senha');
            $table->enum('tipo_perfil', ['ADMIN', 'USUARIO'])->default('USUARIO')->comment('ADMIN = Administrador; USUARIO = colaborador');
            $table->enum('situacao', ['ATIVO', 'INATIVO'])->default('ATIVO')->comment('ATIVO = pode acessar; INATIVO = sem acesso. Pendências são verificadas na operação');
            $table->unsignedTinyInteger('tentativas_login')->default(0)->comment('Tentativas consecutivas de login inválidas');
            $table->dateTime('bloqueado_ate')->nullable()->comment('Bloqueio temporário após 5 tentativas inválidas');
            $table->dateTime('data_cadastro')->useCurrent()->comment('Data e hora do cadastro');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('USUARIO');
    }
};
