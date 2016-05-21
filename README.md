#介绍 LenoPHP是一个简单干净的PHP框架，有如下特性:
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

至此,安装完成。先泡杯咖啡,看看新闻放松放松。劳逸结合，轻松学习。下一小节我们会通过一个简单的博客系统介绍如何编写业务逻辑<font size=7>^-^</font>
#例子
好吧,喝完一杯咖啡之后发现,还是先描述我们的例子比较靠谱
我们的例子[后文统统叫sample,别问我为什么,任性]是一个宇宙无敌巨简单的博客系统，有一个列表页，详情页，以及撰写页。提供展示列表，查看详情，撰写功能（这是废话）
放心，我不会设计数据库，因为我们用不着设计数据库，我们仅仅需要设计Entity。在project_dir/model/Entity/下面创建一个PHP文件，名为Blog.php,然后写如下内容
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
##业务逻辑分析
个人认为，controller部分应该是轻量级的，它的作用是把各个业务逻辑组合起来为前端提供特定接口功能的一个层。当开发一个系统我们应该关注接口，更应该关注实现接口的功能，在设计这个框架之初，我就开始把业务逻辑封装到service中。
对于sample来说，它需要支持三个功能，列表，详情，撰写。下面我们对业务逻辑进行抽象
在model/Service目录下创建一个Blog的目录,在Blog里面创建三个文件，分别为Collect.php, Detail.php, Write.php

Collect.PHP
```PHP
namespace Model\Service\Blog;

use \Model\Entity\Blog;

class Collect extends \Model\Service
{
    protected $name;

    protected $page;

    protected $page_size;

    public function execute(callable $callable = null)
    {
        $selector = Blog::selector()->limit($page, $page_size);
        if($this->name) {
            $selector->byLikeName($this->name);
        }
        return $selector->get();
    }
}
```

Detail.php
```php
namespace Model\Service\Blog;

use \Model\Entity\Blog;

class Detail extends \Model\Service
{
    protected $id;

    public function execute(callable $callable = null)
    {
        if(!$this->id) {
            throw new \Leno\Exception('setId before');
        }
        return Blog::findOrFail($this->id);
    }
}
```

Write.php
```php
namespace Model\Service\Blog;

use \Model\Entity\Blog;

class Write extends \Model\Service
{
    public function execute(callable $callable)
    {
        return call_user_func($callable);
    }
}
```

业务逻辑也写好了，然后我们需要提供页面和接口,我们使用restful的风格来提供我们的接口(资源)
在controller目录下面新建两个文件, Blog.php, Blogs.php
Blog.php
```php
namespace Controller;

use \Leno\Http\Exception as HttpException;
use \Model\Entity\Blog;

class Blog extends Controller\App
{
    public function index()
    {
        $page = $this->input('page', [
            'type' => 'int', 'extra' => [
                'min' => 0
            ], 'required' => false
        ]) ?? 1;
        $page_size = $this->input('page_size', [
            'type' => 'int', 'extra' => [
                'min' => 0
            ], 'required' => false
        ]) ?? 10;
        $name = $this->input('name') ?? false;
        $blogs = $this->getService('user.blog.collect')
            ->setPage($page)
            ->setPageSize($page_size)
            ->setName($name)
            ->execute();
        $this->set('blogs', $blogs);
        $this->render('blog.index');
    }

    public function modify()
    {
        $id = $this->input('id', [
            'type' => 'uuid'
        ], '错误的请求，id无效');
        $data = $this->inputs([
            'name' => ['type' => 'string', 'extra' => [
                'max_length' => 64
            ], 'message' => '名字太长或者忘了给我'],
            'content' => ['type' => 'string'],
            'description',
        ]);
        try {
            $blog = Blog::findOrFail($id);
        } catch(\Exception $ex) {
            throw new HttpException(500, '没有找到博客');
        }
        try {
            $blog->setAll($data)->save();
        } catch(\Exception $ex) {
            throw new HttpException(500, '博客保存失败');
        }
        return '操作成功';
    }

    public function add()
    {
        $data = $this->inputs([
            'name' => ['type' => 'string', 'extra' => [
                'max_length' => 64
            ], 'message' => '名字太长或者忘了给我'],
            'content' => ['type' => 'string'],
            'description',
        ]);
        $blog = new Blog;
        try {
            $blog->setAll($data)->save();
        } catch(\Exception $ex) {
            throw new HttpException(500, '博客保存失败');
        }
        return '操作成功';
    }

    public function remove()
    {
        $id = $this->input('id', [
            'type' => 'uuid'
        ], '错误的请求，id无效');
        try {
            $blog = Blog::findOrFail($id);
        } catch(\Exception $ex) {
            throw new HttpException(500, '没有找到博客');
        }
        try {
            $blog->remove();
        } catch(\Exception $ex) {
            throw new HttpException(500, '操作失败');
        }
        return '操作成功';
    }
}
```
