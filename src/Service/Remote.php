<?php
namespace Leno\Service;

class Remote extends \Leno\Service
{
    protected $url;

    protected $method = 'POST';

    protected $async = false;

    protected $after_async;

    protected $base_uri;

    protected $timeout;

    protected $post = [];

    protected $result = new \Leno\Service\Remote\Parameter;

    protected $parameter = new \Leno\Service\Remote\Result;

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }

    public function setAsync(bool $async = true)
    {
        $this->async = $async;
        return $this;
    }

    protected setAfterAsync($callback)
    {
        if(is_callable($callback)) {
            trigger_error('callback Is Not A Cabllable Will Be Ignore', E_USER_WARNING);
            return $this;
        }
        $this->after_async = $callback;
        return $this;
    }

    protected function afterAsync($response)
    {
        if(is_callable($this->after_async)) {
            return call_user_func($this->after_async, $response);
        }
    }

    public function setOptions(array $opts)
    {
        $this->options = $opts;
        return $this;
    }

    public function setPostParameters(array $params)
    {
        $this->post = $params;
        return $this;
    }

    public function setParameter($parameter)
    {
        if(!$parameter instanceof \Leno\Service\Parameter) {
            $this->parameter
        }
        return $this;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function setBaseUri($uri)
    {
        $this->base_uri = $uri;
        return $this;
    }

    public function execute()
    {
        $options = [];
        if($this->method === 'POST') {
            $options = $this->options ?? [];
            if($options['form_params']) && $old = $options['form_params'];
            $options['form_params'] = array_merge_recursive($old ?? [], $this->post);
        }
        $client = new GuzzleHttp\Client([
            'base_uri' => $this->base_uri,
            'timeout' => $this->timeout,
        ]);
    }
}
