<?php
define('TEST_MVC', false);

require __DIR__ . '/boot.php';

$image = new \Test\Model\Image;
$image->setAppId('hlkk')
    ->setName('U4341P115T41D244179F757DT20141029173846.jpg')
    ->setSize(1232)
    ->setWidth(100)
    ->setHeight(100)
    ->setType('image/jpg')
    ->setPathFile('/srv/http/leno_img_service/image/2016/05/08/8f1ae585ddb620c4603872e96b5e91db')
    ->setCreated(new \Datetime)
    ->save();
