#介绍
LenoPHP是一个简单干净的PHP框架，有如下特性:
* 支持Mysql,和Pgsql,基于PDO的ORM,且方便拓展
* 支持继承,嵌套,条件输出,标签的模板系统
* 设计清晰的路由,可自定义规则,支持RESTFUL路由风格,可自定义action绑定
* 命令行自动化工具,自动安装应用
* "强制service",合理化业务逻辑类的粒度
* 简单的配置系统

#安装
LenoPHP通过composer进行管理,如果你不知道什么是composer,不要担心,10分钟就能掌握它(放心,我不会提供连接给你,自己动手,丰衣足食)

```shell
composer require hackyoung/leno
```

之后你会看到一个vendor目录,且已经支持psr0,psr4. 然后就是自动化初始化环境

```shell
cd path/to/project_dir && vendor/bin/leno build init
```

之后你会看到生成了一系列文件,敲两个命令,你就可以直接编写业务逻辑了。

至此,安装完成。先泡杯咖啡,看看新闻放松放松。劳逸结合，轻松学习。下一小节我们会通过一个简单的博客系统介绍如何编写业务逻辑<font size=4>^-^</font>
#例子
好吧,喝完一杯咖啡之后发现,还是先描述我们的例子比较靠谱
我们的例子[后文统统叫sample,别问我为什么,任性]是一个宇宙无敌巨简单的博客系统，有一个列表页，详情页，以及撰写页。提供展示列表，查看详情，撰写功能（这是废话）
>放心，我不会设计数据库，因为我们用不着设计数据库，我们仅仅需要设计Entity。在project_dir/model/Entity/下面创建一个PHP文件，名为Blog.php,然后写如下内容
```PHP
namespace Model\Entity;

class Blog extends Model\Entity
{
    public static $attributes = [
        'blog_id' => ['type' => 'uuid'],
        'title' => ['type' => 'string', 'extra' => ['max_length' => 64]],
        'description' => ['type' => 'string', 'required' => false, 'extra' => [
            'max_length' => 512
        ]],
        'author' => ['type' => 'uuid',],
        'content' => ['type' => 'text'],
        'created' => ['type' => 'datetime'],
        'updated' => ['type' => 'datetime', 'required' => false],
        'deleted' => ['type' => 'datetime', 'required' => false],
    ];

    public static $primary = 'blog_id';

    public static $foreign = [
        'user' => [
            'class' => 'Model\\Entity\\User',
            'local' => 'author',
            'foreign' => 'user_id'
        ]
    ];
}
```

继续在project_dir/model/Entity里面创建第二个Entity---User.php, 内容如下:
```php
namespace Model\Entity;

class User extends Model\Entity
{
    public static $attributes = [
        'user_id' => ['type' => 'uuid'],
        'name' => ['type' => 'string', 'extra' => ['max_length' => 64]],
        'age' => ['type' => 'int', 'required' => false]
    ];

    public static $primary = 'user_id';

    public static $foreign = [
        'blogs' => [
            'class' => 'Model\\Entity\\Blog',
            'local' => 'user_id',
            'foreign' => 'author'
        ]
    ];
}
```
找到project_dir 下面的config里面的default.php,将数据库配置改成你的数据库配置

切换到你的shell，cd到你当前项目的根目录,敲入
```shell
vendor/bin/leno build db --entity-dir model/Entity --namespace Model\\Entity
```

回车之后，你的数据库就帮你自动创建好了
