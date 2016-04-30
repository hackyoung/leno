<?php
namespace Leno\Routing;

/**
 * Router 通过一个Uri路由到正确的controller and action
 * 这个Router可以通过规则路由到其他Router，也可以路由到controller
 */
class Router
{
    const TYPE_ROUTER = 'Router';

    const TYPE_CONTROLLER = 'Controller';

    const MOD_NORMAL = 0;

    const MOD_RESTFUL = 1;

	const MOD_MIX = 2;

    protected $request;

    protected $response;

    protected $path;

    protected $rules = [];

    protected $base = 'controller';

    protected $mode = self::MOD_RESTFUL;

    protected $restful = [
        'GET' => 'index',
        'POST' => 'add',
        'DELETE' => 'delete',
        'PUT' => 'modify',
    ];

    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->path = $this->getPath();
    }


    public function route()
    {
        $this->beforeRoute();
        foreach($this->rules as $reg => $rule) {
            if(preg_match($reg, $this->path)) {
                return $this->handleRule($reg, $rule);
            }
        }
		if($this->mode === self::MOD_MIX) {
			$this->mode = self::MOD_RESTFUL;
			$target = $this->getTarget();
			try {
				$this->response = $this->invoke($target);
			} catch(\Exception $e) {
				$this->mode = self::MOD_NORMAL;
				$target = $this->getTarget();
				try {
					$this->response = $this->invoke($target);
				} catch(\Exception $e) {
					$response = $this->response->withStatus($target->getCode());
					$response->getBody()->write('<h1><center> '.$target->getCode() .' ' . $response->getReasonPhrase().'</center></h1>');
        			$this->response = $response;
				}
			}
			$this->mode = self::MOD_MIX;
		} else {
			$target = $this->getTarget();
			try {
				$this->response = $this->invoke($target);
			} catch(\Leno\Exception\HttpException $e) {
				$response = $this->response->withStatus($e->getCode());
				$response->getBody()->write('<h1><center> '.$e->getCode() .' ' . $response->getReasonPhrase().'</center></h1>');
				$this->response = $response;
			}
		}
        $this->afterRoute();
        $this->send($this->response);
    }


    protected function handleResult($response)
    {
        if($response === null) {
            return true;
        } elseif($response instanceof \Leno\Http\Response) {
            $this->response = $response;
        } elseif($response instanceof \Psr\Http\Message\StreamInterface) {
            $this->response = $this->response->withBody($response);
        } elseif(is_string($response)) {
            $this->response->write($reponse);
        } else {
            throw new \Leno\Exception\DataTypeException('Controller returned a "'.gettype($response).'" but not supported.');
        }
        return true;
    }

    protected function beforeRoute()
    {
    }

    protected function afterRoute()
    {
    }

    private function normalizeRule($rule)
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

    private function send($response)
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
        if($this->request->hasAttribute('path')) {
            $tmpath = $this->request->getAttribute('path');
        } else {
            $tmpath = strtolower((string)$this->request->getUri());
        }
        $path = trim(preg_replace(
            '/^.*index\.php/U', '', $tmpath
        ), '\/') ?: (
            ($this->mode === self::MOD_RESTFUL) ?
            'index' : 'index/index'
        );
        return $path;
    }

    private function getTarget()
    {
        $patharr = array_merge(
            explode('/', $this->base),
            explode('/', $this->path)
        );
        $path = array_filter(array_map(function($p) {
            return \camelCase($p, true, '-');
        }, $patharr));
        if($this->mode == self::MOD_RESTFUL) {
            return $this->getRestFulTarget($path);
        } else {
            $target = [
                'controller' => false,
                'action' => false,
                'parameters' => [],
            ];
            $target['action'] = preg_replace_callback('/^[A-Z]/', function($matches) {
                if(isset($matches[0])) {
                    return strtolower($matches[0]);
                }
            }, preg_replace('/\..*$/', '', array_pop($path)));
            $target['controller'] = implode('\\', $path);
            return $target;
        }
    }

    private function getRestfulTarget($path)
    {
        $method =strtoupper(
            isset($_POST['_method']) ? 
            $_POST['_method'] : $this->request->getMethod()
        );
        if(!isset($this->restful[$method])) {
            throw new \Leno\Exception($method . ' not supported!');
        }
        $target = [
            'controller' => implode('\\', $path),
            'action' => $this->restful[$method],
            'parameters' => [],
        ];
        return $target;
    }

    private function _404()
    {
		throw new \Leno\Exception\HttpException('not found', 404);
    }

    private function handleRule($regexp, $rule)
    {
        $rule = $this->normalizeRule($rule);
        if($rule['type'] == self::TYPE_ROUTER) {
            $request = clone $this->request;
            $request->withAttribute('path', preg_replace(
                $regexp, '', $this->path
            ));
            try {
                $rc = new \ReflectionClass($rule['target']);
            } catch(\Exception $e) {
                throw new \Leno\Exception(
                    'router:'.$rule['target'].' not found'
                );
            }
            $rc->getMethod('route')->invoke(
                $rc->newInstance($request, $this->response)
            );
        }
    }

    private function invoke($target)
    {
        try {
            $rs = new \ReflectionClass($target['controller']);
        } catch(\Exception $e) {
            return $this->_404();
        }
        $controller = $rs->newInstance($this->request, $this->response);
        if(!$rs->hasMethod($target['action'])) {
            return $this->_404();
        }
        if($rs->hasMethod('beforeExecute')) {
            $rs->getMethod('beforeExecute')->invoke($controller);
        }
        $this->invokeMethod(
            $controller, 
            $rs->getMethod($target['action']),
            $target['parameters']
        );
        if($rs->hasMethod('afterExecute')) {
            $rs->getMethod('afterExecute')->invoke($controller);
        }
        return $this->response;
    }

    private function invokeMethod($controller, $action, $parameters)
    {
        ob_start();
        $response = $action->invoke($controller, $parameters);
        $content = ob_get_contents();
        ob_end_clean();
        if($this->handleResult($response)) {
            $this->response->write($content);
        }
    }
}
