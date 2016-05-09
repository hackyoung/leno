<?php
namespace Leno\Service;

class Remote extends \Leno\Service
{
    const GET = 'GET';

    const POST = 'POST';

    protected $url;

    protected $method = self::GET;

    protected $timeout;

    protected $result;

    protected $parameter;

    public function setParameter(\Leno\Service\Remote\Parameter $parameter)
    {
        $this->parameter = $parameter;
        return $this;
    }

    public function setResult(\Leno\Service\Remote\Result $result)
    {
        $this->result = $result;
        return $this;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function execute()
    {
        $options = [
            'timeout' => $this->timeout ?? null
        ];
        $url = $this->url;
        if($this->method === self::POST && $this->parameter) {
            $options['form_params'] = array_merge_recursive(
                $options['form_params'] ?? [], $this->parameter->useIt()
            );
        }
        if($this->method === self::GET && $this->parameter) {
            $params = $this->parameter->useIt();
            $p = [];
            foreach($params as $key => $val) {
                $p[] = $key .'='. $val;
            }
            $url = $url . '?' . implode('&', $p);
        }
        $client = new \GuzzleHttp\Client();
        $response = $client->request($this->method, $url, $options);
        if($this->result) {
            return $this->result->getResult($response);
        }
        return $response;
    }
}
