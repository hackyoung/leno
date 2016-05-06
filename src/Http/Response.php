<?php
namespace Leno\Http;

class Response extends \GuzzleHttp\Psr7\Response
{
    public function redirect($url, $params = [])
    {
        if(!empty($params)) {
            $thep = [];
            foreach($params as $k=>$v) {
                $thep[] = $k .'='.$v;
            }
            $url .= '?'.implode('&', $thep);
        }
        (new \Leno\Validator([
            'type' => ['uri', 'url']
        ], 'redirect_url'))->check($url);
        header('location: '.$url);
        exit;
    }

    public function write($string)
    {
        $this->getBody()->write($string);
        return $this;
    }

    public function send()
    {
        if (!headers_sent()) {
            $code = $this->getStatusCode();
            $version = $this->getProtocolVersion();
            if ($code !== 200 || $version !== '1.1') {
                header(sprintf('HTTP/%s %d %s', $version, $code, $this->getReasonPhrase()));
            }
            $header = $this->getHeaders();
            foreach ($header as $key => $value) {
                $key = ucwords(strtolower($key), '-');
                if (is_array($value)) {
                    $value = implode(',', $value);
                }
                header(sprintf('%s: %s', $key, $value));
            }
        }
        $body = $this->getBody();
        if ($body instanceof \Owl\Http\IteratorStream) {
            foreach ($body->iterator() as $string) {
                echo $string;
            }
        } else {
            echo (string)$body;
        }
    }
}
