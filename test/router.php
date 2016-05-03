<?php
namespace Test;

require_once(__DIR__ . '/boot.php');

use \Leno\Http\Request;
use \Leno\Http\Response;

class Router extends \Leno\Routing\Router
{
	protected $base = 'test';

	public function __construct()
	{
		$uri = 'test';

		$method = 'get';

		parent::__construct(
			(new Request(
				$method, $uri, getallheaders()
			))->withAttribute('path', $uri),
			new Response
		);
	}
}

$router = new Router;
$response = $router->route();
