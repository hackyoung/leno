<?php
// 注册名字空间
$namespaces = [
    'Model'         => '/model',
    'Controller'    => '/controller',
    'Shell'         => '/shell',
];

foreach($namespaces as $namespace => $dir) {
    \Leno\AutoLoader::register($namespace, $dir);
}

\Leno\AutoLoader::instance()->execute();

