<?php

namespace Dassis\SdkSicoob\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Dassis\SdkSicoob\Configuration;

class CallApi
{
    protected Configuration $config;

    private ?string $certTemp = null;
    private ?string $keyTemp  = null;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }

    /**
     * Garante limpeza dos temporários ao destruir o objeto.
     */
    public function __destruct()
    {
        $this->cleanupTempFiles();
    }

    /**
     * Busca o token OAuth2 via client_credentials + mTLS.
     */
    public function accessToken(): object
    {
        $cached = TokenCache::get($this->config->getClientId());

        if ($cached !== null) {
            return $cached;
        }

        $certOptions = $this->buildCertOptions();
        $client      = new Client($certOptions);

        $response = $client->request('POST', $this->config->getAuthUrl(), [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id'  => $this->config->getClientId(),
                'scope'      => $this->config->getScope(),
            ],
        ]);

        $token = json_decode($response->getBody()->getContents());

        TokenCache::set($this->config->getClientId(), $token);

        return $token;
    }

    /**
     * Executa uma chamada autenticada à API do Sicoob.
     */
    public function call(
        string  $endpoint,
        ?array  $query   = null,
        ?array  $body    = null,
        string  $method  = 'GET',
        array   $headers = []
    ): object {
        $token       = $this->accessToken();
        $certOptions = $this->buildCertOptions(); // reutiliza os mesmos arquivos temp
        $client      = new Client($certOptions);

        $options = [
            'headers' => array_merge([
                'Authorization'   => "Bearer {$token->access_token}",
                'Content-Type'    => 'application/json',
                'client_id'       => $this->config->getClientId(),
                'X-IBM-Client-Id' => $this->config->getClientId(),
            ], $headers),
        ];

        if (!empty($query)) {
            $options['query'] = $query;
        }

        if (!empty($body)) {
            $options['json'] = $body;
        }

        $response = $client->request(
            $method,
            $this->config->getBaseUrl() . $endpoint,
            $options
        );

        // Limpa imediatamente após uso
        $this->cleanupTempFiles();

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Converte o .pfx para arquivos PEM temporários.
     * Reutiliza os mesmos arquivos se já foram criados nesta instância.
     */
    private function buildCertOptions(): array
    {
        // Reutiliza se já criados nesta instância
        if ($this->certTemp !== null && file_exists($this->certTemp) &&
            $this->keyTemp  !== null && file_exists($this->keyTemp)) {
            return [
                'cert'    => $this->certTemp,
                'ssl_key' => $this->keyTemp,
            ];
        }

        $pfxData = file_get_contents($this->config->getCertPath());

        if ($pfxData === false) {
            throw new \RuntimeException(
                "Não foi possível ler o certificado: {$this->config->getCertPath()}"
            );
        }

        $certs = [];
        if (!openssl_pkcs12_read($pfxData, $certs, $this->config->getCertPassword())) {
            throw new \RuntimeException(
                "Falha ao processar o certificado .pfx. Verifique o arquivo e a senha."
            );
        }

        $this->certTemp = tempnam(sys_get_temp_dir(), 'sicoob_cert_');
        $this->keyTemp  = tempnam(sys_get_temp_dir(), 'sicoob_key_');

        file_put_contents($this->certTemp, $certs['cert']);
        file_put_contents($this->keyTemp, $certs['pkey']);

        // Mantém o register_shutdown como segurança extra
        $certTemp = $this->certTemp;
        $keyTemp  = $this->keyTemp;
        register_shutdown_function(function () use ($certTemp, $keyTemp) {
            if (file_exists($certTemp)) @unlink($certTemp);
            if (file_exists($keyTemp))  @unlink($keyTemp);
        });

        return [
            'cert'    => $this->certTemp,
            'ssl_key' => $this->keyTemp,
        ];
    }

    /**
     * Remove os arquivos temporários imediatamente.
     */
    private function cleanupTempFiles(): void
    {
        if ($this->certTemp !== null && file_exists($this->certTemp)) {
            @unlink($this->certTemp);
            $this->certTemp = null;
        }
        if ($this->keyTemp !== null && file_exists($this->keyTemp)) {
            @unlink($this->keyTemp);
            $this->keyTemp = null;
        }
    }
}