<?php

namespace Dassis\SdkSicoob;

class Configuration
{
    const URL_PRODUCTION = 'https://api.sicoob.com.br';
    const URL_SANDBOX    = 'https://sandbox.sicoob.com.br/sicoob/sandbox';
    const URL_AUTH       = 'https://auth.sicoob.com.br/auth/realms/cooperado/protocol/openid-connect/token';

    private string $clientId;
    private string $certPath;
    private string $certPassword;
    private bool   $sandbox;
    private string $scope;

    /**
     * @param string $clientId     Client ID gerado no portal developers.sicoob.com.br
     * @param string $certPath     Caminho absoluto para o arquivo .pfx
     * @param string $certPassword Senha do certificado .pfx
     * @param bool   $sandbox      true = ambiente de testes, false = produção
     */
    public function __construct(
        string $clientId,
        string $certPath,
        string $certPassword,
        bool   $sandbox = false,
        string $scope   = 'cco_consulta cco_transferencias'
    ) {
        $this->clientId     = $clientId;
        $this->certPath     = $certPath;
        $this->certPassword = $certPassword;
        $this->sandbox      = $sandbox;
        $this->scope        = $scope;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getCertPath(): string
    {
        return $this->certPath;
    }

    public function getCertPassword(): string
    {
        return $this->certPassword;
    }

    public function getAuthUrl(): string
    {
        return self::URL_AUTH;
    }

    public function getBaseUrl(): string
    {
        return $this->sandbox ? self::URL_SANDBOX : self::URL_PRODUCTION;
    }

    public function isSandbox(): bool
    {
        return $this->sandbox;
    }
}