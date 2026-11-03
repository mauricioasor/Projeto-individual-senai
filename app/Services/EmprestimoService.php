<?php

namespace App\Services;

use App\Exceptions\RegraNegocioException;
use App\Models\Emprestimo;
use App\Models\ItemEmprestimo;
use App\Models\Livro;
use App\Models\Usuario;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Regras de negócio dos empréstimos (RN12 a RN22).
 *
 * As operações que alteram a quantidade disponível dos livros são feitas
 * em transação (R.N.F. 12), com bloqueio das linhas envolvidas.
 */
class EmprestimoService
{
    /**
     * RN17 – pendências verificadas no momento da operação (não ficam gravadas no usuário).
     *
     * @return list<string>
     */
    public function pendencias(Usuario $usuario): array
    {
        $pendencias = [];

        if ($usuario->emprestimos()->atrasados()->exists()) {
            $pendencias[] = 'possui empréstimo em atraso';
        }

        if ($usuario->emprestimos()->comContribuicaoPendente()->exists()) {
            $pendencias[] = 'possui contribuição por atraso pendente de pagamento';
        }

        return $pendencias;
    }

    /** RN14 – quantidade de livros em posse do colaborador. */
    public function livrosEmPosse(Usuario $usuario): int
    {
        return ItemEmprestimo::query()
            ->whereNull('data_devolucao')
            ->whereHas('emprestimo', fn ($q) => $q->where('id_usuario', $usuario->id_usuario))
            ->count();
    }

    /**
     * R.F. 08 – registra um empréstimo (mestre) com seus livros (detalhe).
     *
     * @param  array<int>  $idsLivros
     */
    public function registrar(Usuario $colaborador, array $idsLivros): Emprestimo
    {
        $idsLivros = array_values(array_unique(array_map('intval', $idsLivros)));
        $max = (int) config('biblioteca.max_livros');

        if (! $colaborador->isAtivo()) {
            throw new RegraNegocioException('O colaborador está inativo e não pode realizar empréstimos.');
        }

        // RN13 – de um a três livros diferentes por empréstimo
        if (count($idsLivros) < 1 || count($idsLivros) > $max) {
            throw new RegraNegocioException("Um empréstimo deve conter de 1 a {$max} livros diferentes.");
        }

        // RN17 – pendências do colaborador
        if ($pendencias = $this->pendencias($colaborador)) {
            throw new RegraNegocioException('Empréstimo não permitido: o colaborador '.implode(' e ', $pendencias).'.');
        }

        return DB::transaction(function () use ($colaborador, $idsLivros, $max) {
            // RN14 – no máximo três livros em posse
            $emPosse = $this->livrosEmPosse($colaborador);
            if ($emPosse + count($idsLivros) > $max) {
                $restante = max(0, $max - $emPosse);
                throw new RegraNegocioException($restante === 0
                    ? "Limite de {$max} livros em posse: o colaborador já possui {$emPosse} e precisa devolver um livro antes de um novo empréstimo."
                    : "Limite de {$max} livros em posse: o colaborador já possui {$emPosse} e pode retirar no máximo mais {$restante}.");
            }

            /** @var Collection<int, Livro> $livros */
            $livros = Livro::whereIn('id_livro', $idsLivros)->lockForUpdate()->get()->keyBy('id_livro');

            foreach ($idsLivros as $id) {
                $livro = $livros->get($id);
                if (! $livro) {
                    throw new RegraNegocioException('Livro não encontrado.');
                }
                // RN12 – só livros com exemplar disponível
                if ($livro->quantidade_disponivel < 1) {
                    throw new RegraNegocioException("O livro \"{$livro->titulo}\" não tem exemplares disponíveis.");
                }
            }

            $agora = now();
            $emprestimo = Emprestimo::create([
                'id_usuario' => $colaborador->id_usuario,
                'data_emprestimo' => $agora,
                // RN15 – prazo de 14 dias corridos
                'data_prevista_dev' => $agora->copy()->startOfDay()->addDays((int) config('biblioteca.prazo_dias')),
                'status' => Emprestimo::ATIVO,
            ]);

            foreach ($idsLivros as $id) {
                $emprestimo->itens()->create(['id_livro' => $id]);
                // RN19 – reduz a quantidade disponível
                $livros->get($id)->decrement('quantidade_disponivel');
            }

            return $emprestimo->load('itens.livro');
        });
    }

    /** R.F. 09 / RN16 – renova uma única vez por mais 14 dias, se não estiver em atraso. */
    public function renovar(Emprestimo $emprestimo): Emprestimo
    {
        if ($emprestimo->status !== Emprestimo::ATIVO) {
            throw new RegraNegocioException('Somente empréstimos ativos podem ser renovados.');
        }
        if ($emprestimo->estaAtrasado()) {
            throw new RegraNegocioException('Empréstimos em atraso não podem ser renovados.');
        }
        if ($emprestimo->qtd_renovacoes >= (int) config('biblioteca.max_renovacoes')) {
            throw new RegraNegocioException('Este empréstimo já foi renovado uma vez.');
        }
        if ($pendencias = $this->pendencias($emprestimo->usuario)) {
            throw new RegraNegocioException('Renovação não permitida: o colaborador '.implode(' e ', $pendencias).'.');
        }

        $emprestimo->update([
            'data_prevista_dev' => $emprestimo->data_prevista_dev->copy()->addDays((int) config('biblioteca.prazo_dias')),
            'qtd_renovacoes' => $emprestimo->qtd_renovacoes + 1,
        ]);

        return $emprestimo;
    }

    /**
     * R.F. 10 – registra a devolução de um ou mais livros, calculando a contribuição (RN18 a RN21).
     *
     * @param  array<int>  $idsItens
     */
    public function devolver(Emprestimo $emprestimo, array $idsItens, ?Carbon $data = null): Emprestimo
    {
        $data ??= now();
        $idsItens = array_map('intval', $idsItens);

        if ($emprestimo->status !== Emprestimo::ATIVO) {
            throw new RegraNegocioException('Este empréstimo já foi encerrado.');
        }
        if ($idsItens === []) {
            throw new RegraNegocioException('Selecione ao menos um livro para devolver.');
        }

        return DB::transaction(function () use ($emprestimo, $idsItens, $data) {
            $emprestimo = Emprestimo::whereKey($emprestimo->getKey())->lockForUpdate()->firstOrFail();
            $itens = $emprestimo->itens()->whereIn('id_item', $idsItens)->whereNull('data_devolucao')->get();

            if ($itens->count() !== count(array_unique($idsItens))) {
                throw new RegraNegocioException('Um ou mais livros selecionados já foram devolvidos ou não pertencem a este empréstimo.');
            }

            $prevista = $emprestimo->data_prevista_dev->copy()->startOfDay();
            $dias = (int) max(0, $prevista->diffInDays($data->copy()->startOfDay(), false));
            $valorDia = (float) config('biblioteca.valor_dia_atraso');
            $total = (float) $emprestimo->valor_contribuicao;

            foreach ($itens as $item) {
                $valor = round($dias * $valorDia, 2);
                $item->update([
                    'data_devolucao' => $data,
                    'dias_atraso' => $dias,
                    'valor_contribuicao_item' => $valor,
                ]);
                $total += $valor;
                // RN19 – devolve o exemplar ao acervo
                Livro::whereKey($item->id_livro)->lockForUpdate()->first()?->increment('quantidade_disponivel');
            }

            $restantes = $emprestimo->itens()->whereNull('data_devolucao')->count();
            $emprestimo->valor_contribuicao = round($total, 2);
            if ($restantes === 0) {
                // RN18 – encerrado quando todos os livros forem devolvidos
                $emprestimo->status = Emprestimo::DEVOLVIDO;
                $emprestimo->data_devolucao = $data;
            }
            $emprestimo->save();

            return $emprestimo->load('itens.livro');
        });
    }

    /** R.F. 11 / RN22 – registra o pagamento da contribuição, após a devolução. */
    public function registrarPagamento(Emprestimo $emprestimo): Emprestimo
    {
        if ($emprestimo->status !== Emprestimo::DEVOLVIDO) {
            throw new RegraNegocioException('O pagamento é registrado depois que todos os livros do empréstimo forem devolvidos.');
        }
        if (! $emprestimo->temContribuicaoPendente()) {
            throw new RegraNegocioException('Este empréstimo não tem contribuição pendente.');
        }

        $emprestimo->update(['data_pagamento_contribuicao' => now()]);

        return $emprestimo;
    }
}
