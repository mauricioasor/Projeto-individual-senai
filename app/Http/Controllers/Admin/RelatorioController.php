<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Usuario;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * R.F. 17 – relatório de empréstimos por período, com exportação em CSV e PDF.
 * Cada linha corresponde a um livro emprestado (item do empréstimo).
 */
class RelatorioController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $this->filtros($request);
        $consulta = $this->consulta($filtros);

        return view('admin.relatorios.emprestimos', [
            'filtros' => $filtros,
            'linhas' => (clone $consulta)->paginate((int) config('biblioteca.itens_por_pagina'))->withQueryString(),
            'totais' => $this->totais($consulta),
            'usuarios' => Usuario::orderBy('nome')->get(['id_usuario', 'nome']),
            'categorias' => Categoria::orderBy('nome_categoria')->get(),
        ]);
    }

    public function exportar(Request $request, string $formato): Response
    {
        $filtros = $this->filtros($request);
        $consulta = $this->consulta($filtros);
        $nome = 'relatorio-emprestimos-'.$filtros['inicio'].'-a-'.$filtros['fim'];

        if ($formato === 'pdf') {
            $pdf = Pdf::loadView('admin.relatorios.pdf', [
                'filtros' => $filtros,
                'linhas' => $consulta->get(),
                'totais' => $this->totais($consulta),
                'usuario' => isset($filtros['usuario']) ? Usuario::find($filtros['usuario']) : null,
                'categoria' => isset($filtros['categoria']) ? Categoria::find($filtros['categoria']) : null,
            ])->setPaper('a4', 'landscape');

            return $pdf->download($nome.'.pdf');
        }

        return $this->csv($consulta, $nome.'.csv');
    }

    private function filtros(Request $request): array
    {
        $dados = $request->validate([
            'inicio' => ['nullable', 'date_format:Y-m-d'],
            'fim' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:inicio'],
            'usuario' => ['nullable', 'integer'],
            'categoria' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:ativo,atrasado,devolvido'],
        ]);

        $dados['inicio'] ??= now()->startOfMonth()->toDateString();
        $dados['fim'] ??= now()->toDateString();

        return array_filter($dados, fn ($v) => $v !== null && $v !== '');
    }

    private function consulta(array $f): Builder
    {
        $hoje = today()->toDateString();

        return DB::table('ITEM_EMPRESTIMO as i')
            ->join('EMPRESTIMO as e', 'e.id_emprestimo', '=', 'i.id_emprestimo')
            ->join('USUARIO as u', 'u.id_usuario', '=', 'e.id_usuario')
            ->join('LIVRO as l', 'l.id_livro', '=', 'i.id_livro')
            ->join('CATEGORIA as c', 'c.id_categoria', '=', 'l.id_categoria')
            ->select([
                'e.id_emprestimo', 'u.nome as colaborador', 'l.titulo', 'c.nome_categoria as categoria',
                'e.data_emprestimo', 'e.data_prevista_dev', 'i.data_devolucao',
                'i.dias_atraso', 'i.valor_contribuicao_item', 'e.data_pagamento_contribuicao',
            ])
            ->whereDate('e.data_emprestimo', '>=', $f['inicio'])
            ->whereDate('e.data_emprestimo', '<=', $f['fim'])
            ->when($f['usuario'] ?? null, fn ($q, $id) => $q->where('e.id_usuario', $id))
            ->when($f['categoria'] ?? null, fn ($q, $id) => $q->where('l.id_categoria', $id))
            ->when(($f['status'] ?? null) === 'devolvido', fn ($q) => $q->whereNotNull('i.data_devolucao'))
            ->when(($f['status'] ?? null) === 'ativo', fn ($q) => $q->whereNull('i.data_devolucao')->whereDate('e.data_prevista_dev', '>=', $hoje))
            ->when(($f['status'] ?? null) === 'atrasado', fn ($q) => $q->whereNull('i.data_devolucao')->whereDate('e.data_prevista_dev', '<', $hoje))
            ->orderBy('e.data_emprestimo')
            ->orderBy('e.id_emprestimo');
    }

    private function totais(Builder $consulta): array
    {
        return [
            'livros' => (clone $consulta)->count(),
            'emprestimos' => (clone $consulta)->distinct()->count('e.id_emprestimo'),
            'contribuicoes' => (float) (clone $consulta)->sum('i.valor_contribuicao_item'),
        ];
    }

    /** Situação de cada linha do relatório. */
    public static function situacao(object $linha): string
    {
        if ($linha->data_devolucao !== null) {
            return 'Devolvido';
        }

        return Carbon::parse($linha->data_prevista_dev)->startOfDay()->lt(today()) ? 'Atrasado' : 'Ativo';
    }

    /** Dias de atraso: gravados na devolução ou, para livros ainda em posse, calculados até hoje. */
    public static function diasAtraso(object $linha): int
    {
        if ($linha->data_devolucao !== null) {
            return (int) $linha->dias_atraso;
        }

        $prevista = Carbon::parse($linha->data_prevista_dev)->startOfDay();

        return $prevista->lt(today()) ? (int) $prevista->diffInDays(today()) : 0;
    }

    /** CSV separado por ponto e vírgula, com BOM, para abrir corretamente no Excel (R.N.F. 15). */
    private function csv(Builder $consulta, string $arquivo): StreamedResponse
    {
        return response()->streamDownload(function () use ($consulta) {
            $saida = fopen('php://output', 'w');
            fwrite($saida, "\xEF\xBB\xBF");
            fputcsv($saida, ['Código', 'Colaborador', 'Livro', 'Categoria', 'Data do empréstimo', 'Devolução prevista',
                'Data de devolução', 'Dias de atraso', 'Contribuição (R$)', 'Situação'], ';', '"', '\\');

            foreach ($consulta->cursor() as $l) {
                fputcsv($saida, [
                    '#'.str_pad((string) $l->id_emprestimo, 4, '0', STR_PAD_LEFT),
                    $l->colaborador,
                    $l->titulo,
                    $l->categoria,
                    Carbon::parse($l->data_emprestimo)->format('d/m/Y'),
                    Carbon::parse($l->data_prevista_dev)->format('d/m/Y'),
                    $l->data_devolucao ? Carbon::parse($l->data_devolucao)->format('d/m/Y') : '',
                    self::diasAtraso($l),
                    number_format((float) $l->valor_contribuicao_item, 2, ',', '.'),
                    self::situacao($l),
                ], ';', '"', '\\');
            }

            fclose($saida);
        }, $arquivo, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
