<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use RuntimeException;

class CidadesSeeder extends Seeder
{
    public function run()
    {
        // Caminhos dos arquivos CSV
        $pastaDados = ROOTPATH . '../dados/';

        $arquivoEstados = $pastaDados . 'estados.csv';
        $arquivoMunicipios = $pastaDados . 'municipios.csv';

        // Verifica se os arquivos existem
        if (!is_readable($arquivoEstados) || !is_readable($arquivoMunicipios)) {
            throw new RuntimeException('Arquivos CSV não encontrados.');
        }

        // Relaciona o código do estado à sigla UF
        $estados = [];

        $handle = fopen($arquivoEstados, 'r');

        if ($handle === false) {
            throw new RuntimeException('Não foi possível abrir estados.csv.');
        }

        fgetcsv($handle, 0, ',', '"', '');

        while (($linha = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            if (count($linha) < 2) {
                continue;
            }

            $estados[$linha[0]] = $linha[1];
        }

        fclose($handle);

        // Lê os municípios e prepara os registros
        $handle = fopen($arquivoMunicipios, 'r');

        if ($handle === false) {
            throw new RuntimeException('Não foi possível abrir municipios.csv.');
        }

        fgetcsv($handle, 0, ',', '"', '');

        $cidades = [];

        while (($linha = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            if (count($linha) < 6) {
                continue;
            }

            $codigoUf = $linha[5];

            if (!isset($estados[$codigoUf])) {
                throw new RuntimeException(
                    'Estado não encontrado: ' . $codigoUf
                );
            }

            $cidades[] = [
                'codigo_ibge' => (int) $linha[0],
                'nome'        => $linha[1],
                'uf'          => $estados[$codigoUf],
                'latitude'    => $linha[2],
                'longitude'   => $linha[3],
            ];
        }

        fclose($handle);

        // Insere as cidades em lotes
        $tabela = $this->db->table('cidades');

        $this->db->transStart();

        foreach (array_chunk($cidades, 500) as $lote) {
            $tabela->ignore(true)->insertBatch($lote);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Falha ao importar as cidades.');
        }

        echo count($cidades) . " municípios processados.\n";
    }
}