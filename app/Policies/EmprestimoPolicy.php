<?php

namespace App\Policies;

use App\Models\Emprestimo;
use App\Models\Usuario;

/**
 * Quem pode ver e renovar um empréstimo (R.F. 09 e R.F. 16):
 * o Administrador pode tudo; o colaborador, apenas os próprios empréstimos.
 */
class EmprestimoPolicy
{
    public function ver(Usuario $usuario, Emprestimo $emprestimo): bool
    {
        return $usuario->isAdmin() || $emprestimo->id_usuario === $usuario->id_usuario;
    }

    public function renovar(Usuario $usuario, Emprestimo $emprestimo): bool
    {
        return $this->ver($usuario, $emprestimo);
    }
}
