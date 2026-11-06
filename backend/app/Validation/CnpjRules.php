<?php

namespace App\Validation;

class CnpjRules
{
    public function cnpjValido(
        ?string $valor,
        ?string &$erro = null
    ): bool {
        // O CNPJ é opcional
        if ($valor === null || trim($valor) === '') {
            return true;
        }

        $cnpj = preg_replace('/\D/', '', $valor);

        if (strlen($cnpj) !== 14) {
            $erro = 'O CNPJ deve conter 14 dígitos.';
            return false;
        }

        // Rejeita sequências repetidas
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            $erro = 'Informe um CNPJ válido.';
            return false;
        }

        // Validação dos dois dígitos verificadores
        for ($tamanho = 12; $tamanho <= 13; $tamanho++) {
            $soma = 0;
            $peso = $tamanho - 7;

            for ($i = 0; $i < $tamanho; $i++) {
                $soma += (int) $cnpj[$i] * $peso;

                $peso--;

                if ($peso < 2) {
                    $peso = 9;
                }
            }

            $resto = $soma % 11;
            $digito = $resto < 2 ? 0 : 11 - $resto;

            if ((int) $cnpj[$tamanho] !== $digito) {
                $erro = 'O CNPJ informado é inválido.';
                return false;
            }
        }

        return true;
    }
}
