<?php

namespace PrivatePackagist\ApiClient\HttpClient\Plugin;

use Http\Client\Common\Plugin;
use Psr\Http\Message\RequestInterface;

class RequestSignature implements Plugin
{
    /** @var string */
    private $token;
    /** @var string */
    private $secret;

    /**
     * @param string $token
     * @param string $secret
     */
    public function __construct($token, $secret)
    {
        $this->token = $token;
        $this->secret = $secret;
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        $params = [];
        $headers = [
            'PRIVATE-PACKAGIST-API-TOKEN' => $params['key'] = $this->token,
            'PRIVATE-PACKAGIST-API-TIMESTAMP' => $params['timestamp'] = $this->getTimestamp(),
            'PRIVATE-PACKAGIST-API-NONCE' => $params['cnonce'] = $this->getNonce(),
        ];

        foreach ($headers as $header => $value) {
            $request = $request->withHeader($header, $value);
        }

        $content = $request->getBody()->getContents();
        if ($content) {
            $params['body'] = $content;
        }

        $request = $request->withHeader('PRIVATE-PACKAGIST-API-SIGNATURE', $this->generateSignature($request, $params));

        return $next($request);
    }

    protected function getTimestamp()
    {
        return (int) gmdate('U');
    }

    protected function getNonce()
    {
        return bin2hex(random_bytes(20));
    }

    private function generateSignature(RequestInterface $request, array $params)
    {
        $data = $request->getMethod() . "\n"
            . $request->getUri()->getHost() . "\n"
            . $request->getUri()->getPath() . "\n"
            . $this->normalizeParameters($params);

        return \base64_encode(
            \hash_hmac('sha256', $data, $this->secret, true)
        );
    }

    private function normalizeParameters(array $params)
    {
        uksort($params, 'strcmp');

        return http_build_query($params, null, '&', PHP_QUERY_RFC3986);
    }
}