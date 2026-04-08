<?php

namespace Dassis\SdkSicoob\Contexts;

use GuzzleHttp\Exception\GuzzleException;
use Dassis\SdkSicoob\Configuration;
use Dassis\SdkSicoob\Helpers\CallApi;
use Dassis\SdkSicoob\Types\BoletoPagamento;

class CobrancaBancaria extends CallApi
{
    public function __construct(Configuration $config)
    {
        parent::__construct($config);
    }

    /**
     * Consulta um boleto pelo código de barras.
     *
     * @param string $codigoBarras  Código de barras com 44 posições
     * @param int    $numeroConta   Número da conta habilitada para pagamentos
     * @param string|null $dataPagamento Data no formato yyyy-MM-dd (opcional)
     *
     * @throws GuzzleException
     */
    public function consultarBoleto(
        string  $codigoBarras,
        int     $numeroConta,
        ?string $dataPagamento = null
    ): object {
        $query = array_filter([
            'numeroConta'   => $numeroConta,
            'dataPagamento' => $dataPagamento,
        ], fn($v) => !is_null($v));

        return $this->call(
            "/pagamentos/v3/boletos/{$codigoBarras}",
            $query
        );
    }

    /**
     * Efetua o pagamento ou agendamento de um boleto.
     *
     * @param string         $codigoBarras     Código de barras com 44 posições
     * @param string         $idempotencyKey   Chave única: {cooperativa}{conta}{uuid}
     * @param BoletoPagamento $boletoPagamento  Dados do pagamento
     *
     * @throws GuzzleException
     */
    public function pagarBoleto(
        string          $codigoBarras,
        string          $idempotencyKey,
        BoletoPagamento $boletoPagamento
    ): object {
        return $this->call(
            "/pagamentos/v3/boletos/pagamentos/{$codigoBarras}",
            null,
            $boletoPagamento->toArray(),
            'POST',
            ['x-idempotency-key' => $idempotencyKey]
        );
    }

    /**
     * Consulta o comprovante de um pagamento.
     *
     * @param string $idPagamento ID retornado no momento do pagamento
     *
     * @throws GuzzleException
     */
    public function consultarComprovante(string $idPagamento): object
    {
        return $this->call(
            "/pagamentos/v3/boletos/pagamentos/{$idPagamento}/comprovantes"
        );
    }
}