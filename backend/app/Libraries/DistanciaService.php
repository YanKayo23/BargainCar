<?php

namespace App\Libraries;

class DistanciaService
{
    // Quilômetros aproximados por grau de latitude.
    private const KM_POR_GRAU = 111.32;

    // Raio padrão das pesquisas do BargainCar.
    public const RAIO_PADRAO_KM = 100;

    /** Normaliza o mesmo filtro na navbar e na consulta de anúncios. */
    public static function normalizarRaio(mixed $valor): int
    {
        if (! is_string($valor) || ! ctype_digit($valor)) {
            return self::RAIO_PADRAO_KM;
        }

        $raio = filter_var($valor, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 9007199254740991],
        ]);

        return $raio === false ? self::RAIO_PADRAO_KM : $raio;
    }

    /**
     * Calcula a distância aproximada em linha reta
     * entre duas cidades.
     */
    public function calcular(
        float $latitudeOrigem,
        float $longitudeOrigem,
        float $latitudeDestino,
        float $longitudeDestino
    ): float {

        // Diferença entre as latitudes.
        $diferencaLatitude =
            $latitudeDestino - $latitudeOrigem;

        // Diferença entre as longitudes.
        $diferencaLongitude =
            $longitudeDestino - $longitudeOrigem;

        // Latitude média entre as duas cidades.
        $latitudeMedia = (
            $latitudeOrigem + $latitudeDestino
        ) / 2;

        // Distância aproximada no eixo norte-sul.
        $distanciaY = self::KM_POR_GRAU
            * $diferencaLatitude;

        // Distância aproximada no eixo leste-oeste.
        $distanciaX = self::KM_POR_GRAU
            * cos(deg2rad($latitudeMedia))
            * $diferencaLongitude;

        // Teorema de Pitágoras.
        $distancia = sqrt(
            pow($distanciaX, 2)
            + pow($distanciaY, 2)
        );

        return $distancia;
    }

    /**
     * Verifica se a distância está dentro
     * do raio permitido.
     */
    public function dentroDoRaio(
        float $distancia,
        float $raioKm = self::RAIO_PADRAO_KM
    ): bool {

        return $distancia <= $raioKm;
    }

    /**
     * Retorna a expressão SQL para calcular
     * a distância diretamente no MySQL/MariaDB.
     *
     * Os valores das coordenadas devem ser
     * fornecidos como parâmetros vinculados.
     */
    public function expressaoSql(
        string $aliasCidade = 'c'
    ): string {

        // Aceita somente aliases SQL simples.
        if (! preg_match(
            '/^[a-zA-Z_][a-zA-Z0-9_]*$/',
            $aliasCidade
        )) {
            throw new \InvalidArgumentException(
                'Alias de cidade inválido.'
            );
        }

        return "
            SQRT(
                POW(
                    111.32 * COS(
                        RADIANS(
                            (? + {$aliasCidade}.latitude) / 2
                        )
                    ) * (
                        {$aliasCidade}.longitude - ?
                    ),
                    2
                )
                +
                POW(
                    111.32 * (
                        {$aliasCidade}.latitude - ?
                    ),
                    2
                )
            )
        ";
    }
}
