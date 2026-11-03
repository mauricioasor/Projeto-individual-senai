<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Violação de uma regra de negócio (seção 4.3 da documentação).
 * A mensagem é exibida ao usuário como está, por isso deve ser clara (R.N.F. 14).
 */
class RegraNegocioException extends RuntimeException
{
}
