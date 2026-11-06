<?php

namespace App\Validation;

class CpfRules
{
    public function cpf_valido(
        ?string $valor,
        ?string &$erro = null
    ): bool {
        $cpf = preg_replace('/\D/', '', $valor ?? '');

        if (strlen($cpf) !== 11) {
            return false;
        }

        // Rejeita sequências como 11111111111.
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        // Verifica os dois dígitos do CPF.
        for ($tamanho = 9; $tamanho < 11; $tamanho++) {
            $soma = 0;

            for ($i = 0; $i < $tamanho; $i++) {
                $soma += (int) $cpf[$i]
                    * ($tamanho + 1 - $i);
            }

            $resto = ($soma * 10) % 11;
            $digito = $resto === 10 ? 0 : $resto;

            if ($digito !== (int) $cpf[$tamanho]) {
                return false;
            }
        }

        return true;
    }




}
