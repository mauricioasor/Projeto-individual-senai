<?php

namespace Database\Seeders;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Emprestimo;
use App\Models\Livro;
use App\Models\Usuario;
use App\Services\EmprestimoService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Dados de exemplo para demonstração do sistema.
 *
 * Acessos criados (domínio configurado em BIBLIOTECA_DOMINIO_EMAIL):
 *  - Administrador: admin@empresa.com.br / Admin@123
 *  - Colaboradores: <nome>@empresa.com.br / Senha@123
 */
class DatabaseSeeder extends Seeder
{
    public function run(EmprestimoService $servico): void
    {
        $dominio = config('biblioteca.dominio_email');

        Usuario::create([
            'nome' => 'Mauricio Rosa',
            'email' => "admin@{$dominio}",
            'senha' => 'Admin@123',
            'tipo_perfil' => Usuario::ADMIN,
        ]);

        $colaboradores = collect([
            ['Cristopher Silva', 'cristopher.silva'], ['Gustavo Zella', 'gustavo.zella'], ['Vinicius Baggio', 'vinicius.baggio'],
            ['Enzo Junior', 'enzo.junior'], ['Ana Carolina', 'ana.carolina'], ['Lia Cardoso', 'lia.cardoso'],
            ['Gustavo Esteves', 'gustavo.esteves'],
        ])->map(fn ($c) => Usuario::create(['nome' => $c[0], 'email' => "{$c[1]}@{$dominio}", 'senha' => 'Senha@123']));

        $categorias = collect(['Tecnologia', 'Gestão', 'Autoajuda', 'Desenvolvimento comportamental', 'História', 'Fantasia'])
            ->mapWithKeys(fn ($nome) => [$nome => Categoria::create(['nome_categoria' => $nome])]);

        $acervo = [
            ['Procrastinando com eficiência', '9788502000549', 2024, 3, 'Autoajuda', ['Carlos M. Andrade']],
            ['Seja Dev sem saber programar', '9788502000778', 2025, 12, 'Tecnologia', ['Júlia Reis']],
            ['Código sujo', '9788502000884', 2022, 5, 'Tecnologia', ['Robert C. Mortinho']],
            ['Como farmar aura em 2026', '9788502001214', 2026, 3, 'Fantasia', ['Enzo Júnior']],
            ['Não foi só pelo diploma', '9788502000983', 2023, 9, 'História', ['Lia Cardoso']],
            ['Formação falta zero', '9788502001412', 2024, 9, 'Autoajuda', ['Vinicius Baggio']],
            ['Gestão ágil na prática', '9788502001528', 2021, 4, 'Gestão', ['Marina Teixeira', 'Paulo Freitas']],
            ['Comunicação não violenta no trabalho', '9788502001634', 2020, 3, 'Desenvolvimento comportamental', ['Helena Prado']],
            ['Arquitetura limpa para iniciantes', '9788502001740', 2023, 2, 'Tecnologia', ['Robert C. Mortinho', 'Júlia Reis']],
            ['Liderança servidora', '9788502001856', 2019, 3, 'Gestão', ['Paulo Freitas']],
        ];

        $livros = [];
        foreach ($acervo as [$titulo, $isbn, $ano, $qtd, $categoria, $autores]) {
            $livro = Livro::create([
                'titulo' => $titulo,
                'isbn' => $isbn,
                'ano_publicacao' => $ano,
                'quantidade_total' => $qtd,
                'quantidade_disponivel' => $qtd,
                'id_categoria' => $categorias[$categoria]->id_categoria,
            ]);
            $livro->autores()->sync(collect($autores)->map(fn ($n) => Autor::firstOrCreate(['nome_autor' => $n])->id_autor));
            $livros[] = $livro;
        }

        // Empréstimos de exemplo, registrados pelas mesmas regras do sistema
        $registrar = function (Usuario $u, array $idx, int $diasAtras) use ($servico, $livros) {
            Carbon::setTestNow(now()->subDays($diasAtras));
            $e = $servico->registrar($u, array_map(fn ($i) => $livros[$i]->id_livro, $idx));
            Carbon::setTestNow();

            return $e;
        };

        // Em dia
        $registrar($colaboradores[0], [0, 1], 3);
        $registrar($colaboradores[1], [2], 5);

        // Devolvido com atraso e contribuição pendente
        $devolvido = $registrar($colaboradores[4], [5], 25);
        $servico->devolver($devolvido, $devolvido->itens->pluck('id_item')->all(), now()->subDays(4));

        // Devolvido no prazo
        $noPrazo = $registrar($colaboradores[3], [6], 12);
        $servico->devolver($noPrazo, $noPrazo->itens->pluck('id_item')->all(), now()->subDays(2));

        // Atrasados
        $registrar($colaboradores[2], [4], 20);
        $registrar($colaboradores[6], [4, 8], 18);

        $this->command?->info('Dados de exemplo criados. Admin: admin@'.$dominio.' / Admin@123 · Colaboradores: Senha@123');
        $this->command?->info('Empréstimos: '.Emprestimo::count().' (atrasados: '.Emprestimo::atrasados()->count().')');
    }
}
