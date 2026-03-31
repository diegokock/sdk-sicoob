<?php

namespace Dassis\SdkSicoob\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Dassis\SdkSicoob\Configuration;

class CallApi
{
    protected Configuration $config;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }

    /**
     * Busca o token OAuth2 via client_credentials + mTLS.
     * Usa cache — só vai ao Sicoob quando o token estiver expirado.
     *
     * @throws GuzzleException
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
                'scope'      => 'cco_consulta cco_transferencias',
            ],
        ]);

        $token = json_decode($response->getBody()->getContents());

        TokenCache::set($this->config->getClientId(), $token);

        return $token;
    }

    /**
     * Executa uma chamada autenticada à API do Sicoob.
     *
     * @param string      $endpoint Caminho relativo ex: /conta-corrente/v4/extrato/3/2026
     * @param array|null  $query    Parâmetros de query string
     * @param array|null  $body     Body para requisições POST
     * @param string      $method   GET, POST, etc.
     *
     * @throws GuzzleException
     */
    public function call(
        string  $endpoint,
        ?array  $query  = null,
        ?array  $body   = null,
        string  $method = 'GET'
    ): object {
        $token       = $this->accessToken();
        $certOptions = $this->buildCertOptions();
        $client      = new Client($certOptions);

        $options = [
            'headers' => [
                'Authorization'   => "Bearer {$token->access_token}",
                'Content-Type'    => 'application/json',
                'client_id'       => $this->config->getClientId(),
                'X-IBM-Client-Id' => $this->config->getClientId(),
            ],
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

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Converte o .pfx para arquivos PEM temporários.
     * O Guzzle não aceita .pfx diretamente — precisa de cert + key separados.
     * Os temporários são apagados automaticamente no shutdown do PHP.
     */
    private function buildCertOptions(): array
    {
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

        $certTemp = tempnam(sys_get_temp_dir(), 'sicoob_cert_');
        $keyTemp  = tempnam(sys_get_temp_dir(), 'sicoob_key_');

        file_put_contents($certTemp, $certs['cert']);
        file_put_contents($keyTemp, $certs['pkey']);

        // Garante limpeza dos temporários ao final da execução
        register_shutdown_function(function () use ($certTemp, $keyTemp) {
            @unlink($certTemp);
            @unlink($keyTemp);
        });

        return [
            'cert'    => $certTemp,
            'ssl_key' => $keyTemp,
        ];
    }
}