<!DOCTYPE html>
<html lang="pt-BR">
<body style="font-family: Arial, sans-serif; color: #0F172A; background: #F8FAFC; padding: 24px;">
  <div style="max-width: 520px; margin: 0 auto; background: #fff; border: 1px solid #E2E8F0; border-radius: 12px; padding: 28px;">
    <h2 style="color: #4F46E5; margin-top: 0;">MR Biblioteca</h2>
    <p>Olá, {{ $usuario->primeiroNome() }}.</p>
    <p>Recebemos uma solicitação para redefinir a sua senha. Clique no botão abaixo para criar uma nova senha:</p>
    <p style="text-align: center; margin: 28px 0;">
      <a href="{{ $link }}" style="background: #4F46E5; color: #fff; padding: 12px 22px; border-radius: 8px; text-decoration: none; font-weight: bold;">Criar nova senha</a>
    </p>
    <p style="font-size: 13px; color: #475569;">O link é válido por {{ $minutos }} minutos e pode ser usado uma única vez. Se você não fez esta solicitação, ignore este e-mail: sua senha continua a mesma.</p>
    <p style="font-size: 12px; color: #94A3B8; word-break: break-all;">Se o botão não funcionar, copie e cole este endereço no navegador: {{ $link }}</p>
  </div>
</body>
</html>
