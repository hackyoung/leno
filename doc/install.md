#安装
    LenoPHP采用composer包管理器进行依赖管理，且其源代码在github上托管。通过composer安装LenoPHP是一件很简单的事情。如果你不喜欢composer安装，可通过github直接获取源代码，但是其依赖有可能不是最新的。我们通过一个hello world项目的例子来示例怎么构建一个LenoPHP应用，其示例的需求如下

>>>需要在目录/srv/http/hello-world中建立一个LenoPHP的hello-world项目,
>>>我们能通过访问www.hello-world.com访问到我们的项目

##服务器环境
    LenoPHP是单入口PHP框架，任何web访问都是从index.php文件开始执行的，你可以配置rewrite模块，将所有的请求都指向index.php文件，也可以在url上显式的指明访问的php文件为index.php，推荐使用rewrite, 我们也会以rewrite的方式来示例。
    完成配置需要两步
    1. 配置hosts文件，将www.hello-world.com指向本机
    2. 配置服务器软件
配置hosts, 在hosts文件的最后一行加入
```bash
127.0.0.1       www.hello-world.com
```

你所使用的http服务器软件不一样则配置方式不一样，我们会介绍nginx服务器软件的配置

###nginx配置
找到nginx.conf文件，在http{}中加入下面的代码
```json
server {
    listen 80;
    server_name www.hello-world.com;

    root /srv/http/hello-world/public;
    index index.php;

    location @default {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php7.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME /var/www/html/hello-world/index.php;
    }

    location / {
        try_files $uri @default;
    }
}
```
至此，服务器环境准备就绪，我们开始建立我们的代码

##composer安装
composer是PHP的包管理器，其官方网站：https://getcomposer.org, 中文网站：http://www.phpcomposer.com。composer具体怎么安装和使用，通过官方网站学习。
我们假设你已经安装好了composer。我们分四步建立一个可运行的hello world程序

1. 创建名为hello-world的文件夹，作为项目目录, 并进入hello-world目录
```bash
mkdir hello-world && cd hello-world
```
2. 编写composer.json文件
```php
{
    "name" : "example/hello-world",
    "require" : {
        "hackyoung/leno" : "v0.2.0.x-dev",
        "leno/view" : "v0.4.0.x-dev"
    }
}
```
3. 执行composer安装或者更新命令安装依赖
```bash
composer install
```
执行完毕后会生成vendor目录，该目录下保存这所有的依赖包
4. 执行初始化代码
```bash
vendor/bin/leno hello_world --root .
```
至此，代码已经准备就绪
