<?php

namespace Dassis\SdkSicoob\Contexts;

use GuzzleHttp\Exception\GuzzleException;
use Dassis\SdkSicoob\Configuration;
use Dassis\SdkSicoob\Helpers\CallApi;

class ContaCorrente extends CallApi
{
    public function __construct(Configuration $config)
    {
        parent::__construct($config);
    }

    /**
     * Consulta o extrato de uma conta corrente.
     *
     * @param int      $mes          Mês de referência (1-12)
     * @param int      $ano          Ano de referência ex: 2026
     * @param int      $numeroConta  Número da conta corrente
     * @param int|null $diaInicial   Dia inicial para filtrar (opcional)
     * @param int|null $diaFinal     Dia final para filtrar (opcional)
     * @param bool     $agrupaCNAB   Agrupar movimentos provenientes do CNAB
     *
     * @throws GuzzleException
     */
    public function extrato(
        int  $mes,
        int  $ano,
        int  $numeroConta,
        ?int $diaInicial  = null,
        ?int $diaFinal    = null,
        bool $agrupaCNAB  = false
    ): object {
        $query = array_filter([
            'diaInicial'          => $diaInicial,
            'diaFinal'            => $diaFinal,
            'agruparCNAB'         => $agrupaCNAB ? 'true' : null,
            'numeroContaCorrente' => $numeroConta,
        ], fn($v) => !is_null($v));

        return $this->call(
            "/conta-corrente/v4/extrato/{$mes}/{$ano}",
            $query
        );
    }

    /**
     * Consulta o saldo disponível da conta corrente.
     *
     * @param int $numeroConta Número da conta corrente
     *
     * @throws GuzzleException
     */
    public function saldo(int $numeroConta): object
    {
        return $this->call(
            '/conta-corrente/v4/saldo',
            ['numeroContaCorrente' => $numeroConta]
        );
    }
}