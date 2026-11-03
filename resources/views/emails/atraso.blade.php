<!DOCTYPE html>
<html lang="pt-BR">
<body style="font-family: Arial, sans-serif; color: #0F172A; background: #F8FAFC; padding: 24px;">
  <div style="max-width: 560px; margin: 0 auto; background: #fff; border: 1px solid #E2E8F0; border-radius: 12px; padding: 28px;">
    <h2 style="color: #4F46E5; margin-top: 0;">MR Biblioteca</h2>
    <p>Olá, {{ $emprestimo->usuario->primeiroNome() }}.</p>
    <p>O empréstimo <b>{{ $emprestimo->codigo() }}</b> deveria ter sido devolvido em <b>{{ $emprestimo->data_prevista_dev->format('d/m/Y') }}</b> e está com <b>{{ $emprestimo->diasAtraso() }} dia(s) de atraso</b>. Livros ainda em sua posse:</p>
    <ul>
      @foreach ($itens as $item)
        <li>{{ $item->livro->titulo }}</li>
      @endforeach
    </ul>
    <p>Por favor, devolva os livros na biblioteca o quanto antes. Cada dia de atraso gera uma contribuição de R$ {{ number_format($valorDia, 2, ',', '.') }} por livro, destinada às ações sociais apoiadas pela empresa. Enquanto houver atraso, novos empréstimos e renovações ficam bloqueados.</p>
    <p style="font-size: 12px; color: #94A3B8;">Esta é uma mensagem automática do MR Biblioteca.</p>
  </div>
</body>
</html>
