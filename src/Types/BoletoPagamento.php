<?php

namespace Dassis\SdkSicoob\Types;

class BoletoPagamento
{
    private string  $identificadorConsulta;
    private float   $valorBoleto;
    private float   $valorDescontoAbatimento;
    private float   $valorMultaMora;
    private ?string $descricaoObservacao;
    private bool    $aceitaValorDivergente;
    private string  $numeroCpfCnpjPortador;
    private string  $nomePortador;
    private float   $amount;
    private string  $date;
    private int     $debtorAccountIssuer;   // número da cooperativa
    private int     $debtorAccountNumber;   // número da conta
    private int     $debtorAccountType;     // 0 = Conta Corrente
    private int     $debtorAccountPersonType; // 0 = PF, 1 = PJ

    public function __construct(
        string  $identificadorConsulta,
        float   $valorBoleto,
        float   $valorDescontoAbatimento,
        float   $valorMultaMora,
        string  $numeroCpfCnpjPortador,
        string  $nomePortador,
        float   $amount,
        string  $date,
        int     $debtorAccountIssuer,
        int     $debtorAccountNumber,
        int     $debtorAccountType       = 0,
        int     $debtorAccountPersonType = 1,
        bool    $aceitaValorDivergente   = false,
        ?string $descricaoObservacao     = null
    ) {
        $this->identificadorConsulta    = $identificadorConsulta;
        $this->valorBoleto              = $valorBoleto;
        $this->valorDescontoAbatimento  = $valorDescontoAbatimento;
        $this->valorMultaMora           = $valorMultaMora;
        $this->numeroCpfCnpjPortador    = $numeroCpfCnpjPortador;
        $this->nomePortador             = $nomePortador;
        $this->amount                   = $amount;
        $this->date                     = $date;
        $this->debtorAccountIssuer      = $debtorAccountIssuer;
        $this->debtorAccountNumber      = $debtorAccountNumber;
        $this->debtorAccountType        = $debtorAccountType;
        $this->debtorAccountPersonType  = $debtorAccountPersonType;
        $this->aceitaValorDivergente    = $aceitaValorDivergente;
        $this->descricaoObservacao      = $descricaoObservacao;
    }

    public function toArray(): array
    {
        return array_filter([
            'identificadorConsulta'   => $this->identificadorConsulta,
            'valorBoleto'             => $this->valorBoleto,
            'valorDescontoAbatimento' => $this->valorDescontoAbatimento,
            'valorMultaMora'          => $this->valorMultaMora,
            'descricaoObservacao'     => $this->descricaoObservacao,
            'aceitaValorDivergente'   => $this->aceitaValorDivergente,
            'numeroCpfCnpjPortador'   => $this->numeroCpfCnpjPortador,
            'nomePortador'            => $this->nomePortador,
            'amount'                  => $this->amount,
            'date'                    => $this->date,
            'debtorAccount'           => [
                'issuer'      => $this->debtorAccountIssuer,
                'number'      => $this->debtorAccountNumber,
                'accountType' => $this->debtorAccountType,
                'personType'  => $this->debtorAccountPersonType,
            ],
        ], fn($v) => !is_null($v));
    }
}