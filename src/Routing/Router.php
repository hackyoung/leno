<?php
namespace Leno\Routing;

/**
 * Router 通过一个Uri路由到正确的controller and action
 */
class Router
{

    const TYPE_ROUTER = 'Router';

    const TYPE_CONTROLLER = 'Controller';

    protected $request;

    protected $response;

    protected $path;

    protected $rules = [];

    protected $base = 'controller';

    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->path = $this->getPath();
    }


    public function route()
    {
        $this->beforeRoute();
        $target = [
            'controller' => false,
            'action' => false,
            'parameters' => []
        ];
        foreach($this->rules as $reg => $rule) {
            if(preg_match($reg, $this->path)) {
                return $this->handleRule($reg, $rule);
            }
        }
        if(!$target['controller']) {
            $patharr = array_merge(
                explode('/', $this->base),
                explode('/', $this->path)
            );
            $path = array_filter(array_map(function($p) {
                return \camelCase($p);
            }, $patharr));
        
            $target['action'] = preg_replace_callback('/^\w/', function($matches) {
                if(isset($matches[0])) {
                    return strtolower($matches[0]);
                }
            }, preg_replace('/\..*$/', '', array_pop($path)));
            $target['controller'] = implode('\\', $path);
            var_dump($target);
            $this->response = $this->invoke($target);
        }
        $this->send($this->response);
        $this->afterRoute();
    }

    protected function invoke($target)
    {
        try {
            $rs = new \ReflectionClass($target['controller']);
        } catch(\Exception $e) {
            $response = $this->response->withStatus(404);
            $response->getBody()->write('<h1><center>404 '.$response->getReasonPhrase().'</center></h1>');
            return $response;
        }
        if(!$rs->hasMethod($target['action'])) {
            $response = $this->response->withStatus(404);
            $response->getBody()->write('<h1><center>404 '.$response->getReasonPhrase().'</center></h1>');
            return $response;
        }
        $action = $rc->getMethod($target['action']);
        $result = $action->invoke($controller, $target['parameters']);
        $this->response->getBody()->write($result);
        return $this->response;
    }

    protected function handleRule($regexp, $rule)
    {
        $rule = $this->normalizeRule($rule);
        if($rule['type'] == self::TYPE_ROUTER) {
            $request= $this->request->withUri(
                new \GuzzleHttp\Psr7\Uri(
                    preg_replace(
                        $regexp, '',
                        (string)$this->request->getUri()
                    )
                )
            );
            $rc = new \ReflectionClass($rule['target']);
            $rc->getMethod('route')->invoke(
                $rc->newInstance($request, $this->response)
            );
        }
    }

    protected function beforeRoute()
    {
    
    }

    protected function afterRoute()
    {
    
    }

    protected function normalizeRule($rule)
    {
        if(!isset($rule['type'])) {
            $ret = [
                'type' => self::TYPE_CONTROLLER,
                'target' => $rule
            ];
        } else {
            $ret = $rule;
        }
        return $ret;
    }

    protected function send($response)
    {
        if (!headers_sent()) {
            $code = $response->getStatusCode();
            $version = $response->getProtocolVersion();
            if ($code !== 200 || $version !== '1.1') {
                header(sprintf('HTTP/%s %d %s', $version, $code, $response->getReasonPhrase()));
            }

            $header = $response->getHeaders();
            foreach ($header as $key => $value) {
                $key = ucwords(strtolower($key), '-');
                if (is_array($value)) {
                    $value = implode(',', $value);
                }
                header(sprintf('%s: %s', $key, $value));
            }
        }

        $body = $response->getBody();
        if ($body instanceof \Owl\Http\IteratorStream) {
            foreach ($body->iterator() as $string) {
                echo $string;
            }
        } else {
            echo (string)$body;
        }
    }

    private function getPath()
    {
        $path = trim(preg_replace(
            '/^.*index\.php/U', '', 
            (string)$this->request->getUri()
        ), '\/') ?: 'index/index';
        return $path;
    }
}
