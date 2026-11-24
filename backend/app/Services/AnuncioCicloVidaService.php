<?php
namespace App\Services;

class AnuncioCicloVidaService
{
    public static function situacao(array $a): ?string
    {
        if (($a['motivo_indisponibilidade'] ?? null) === 'moderacao') return 'moderacao';
        if ($a['status'] === 'expirado' || strtotime($a['data_expiracao']) <= time()) return 'expirado';
        if (!empty($a['deleted_at']) || !empty($a['motivo_indisponibilidade'])) return 'excluido';
        return null;
    }
    public static function acao(array $a): ?string
    {
        $motivo = $a['motivo_indisponibilidade'] ?? null;
        if ($motivo !== null && !in_array($motivo, ['exclusao_vendedor','exclusao_loja'], true)) return null;
        if (!empty($a['deleted_at']) && $motivo === null) return null;
        return match (self::situacao($a)) {
            'expirado' => 'republicar',
            'excluido' => $motivo !== null && !empty($a['deleted_at']) ? 'restaurar' : null,
            default => null,
        };
    }
}
