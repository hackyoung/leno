#LenoPHP
```
|---------|
| LenoPHP |
|---|-----|
    |
    |---Database
    |    |---------------|
    |---ORM              |
    |            |初     |
    |---Worker---|始    |业
    |            |化    |务
    |                   |
    |            |路    |逻
    |---Router---|由    |辑
    |                    |
    |---Controller-------|
    |
    |---View----|试图
```

###ORM
```PHP
namespace Test;

use \Test\Entity\BookEntity as Book;
use \Test\Entity\UserEntity as Author;

$author = new Author;
$author->setName('hackyoung')
    ->setAge(24);

$book = new Book;
$book->setName('Javascript从入门到放弃')
    ->setPublished(new \Datetime);

$author->setBook($book);

$book = new Book;
$book->setName('教你如何放弃敲代码')
    ->setPublished(new \Datetime);

$author->addBook($book);

$author->save(); // 两本书一起被保存了，yeah ^_^
```
假设刚刚保存的用户ID是'id'
```php
namespace Test;

use \Test\Entity\BookEntity as Book;
use \Test\Entity\UserEntity as Author;

$author = Author::find('id');

// 查出刚刚添加的书
$author->getBook(function($selector) {
    return $selector->order('published', 'ASC');
});

// 只查Javascript从入门到放弃
$author->getBook(false, function($selector) {
    return $selector->byNameLike('javascript');
});

```
###Database

selector
```PHP
namespace Test;

use \Test\Entity\UserEntity as User;

$user = User::selector()->byNameLike('young')
    ->byAgeGt(24)
    ->findOne();

$books = Book::selector()->byAuthor($user)->find();

// complex
$users = User::selector()
    ->quoteBegin()
     ->byNameLike('young')
     ->or()
     ->byNameLike('hack')
    ->quoteEnd()
    ->byAgeGt(24);

// with join

$user_selector = User::selector()->field([
    'name' => 'author_name'
])->byAge(24); // byAge === byAgeEq

$book_selector = Book::selector();

$books_array = $book_selector->join(
    $user_selector->onId($book_selector->getFieldExpr('author_id'))
)->execute()->fetchAll();

```

table
```php
namespace Test;

$table = new Table('hello_world');

$table->setPrimaryKey('hello_id')
    ->field('hello_id', ['type' => 'uuid'])
    ->field('content', ['type' => 'varchar(64)'])
    ->field('user_id', ['type' => 'uuid'])
    ->field('created', ['type' => 'datetime', 'is_nullable' => true])
    ->setUniqueKeys(['content_unique' => ['content']])
    ->setForeignKeys(['user' => ['foreign_table' => 'user', 'relation' => [
        'user_id' => 'user_id'
    ]]);

// 不仅仅save，还会比对和数据库中的变化，同步字段及约束
$table->save();

// 也许你仅仅需要执行一条 vendor/bin/leno build db --entity-dir /path/to/entity --namespace /namespace/of/entity
// 程序会帮你同步所有的表结构及约束进数据库

```
###View
```xml
<extend name="leno._layout.default">
    <!--实现body-->
    <fragment name="body">
        <!--使用组件-->
        <view name="global.another.implements" />
        <div>{$user.hello}</div>
        <ul>
        <llist name="{$llists_test}" id="item">
            <li>{$item}</li>
        </llist>
        </ul>
    </fragment>
</extend>
```
控件

```xml
<!--
<style></style>, <script></script>, <singleton></singleton>
内的内容永远都只有一份
无论你包含了多少次该控件
-->
<style>
    .atl-item {
    }
</style>
<script>
    console.log('just one');
</script>
<div class="atl-item">
    <h2>{$title}</h2>
    <p class="overview">
        {$overview}
    </p>
    <p class="foobar">
        <a>删除</a>
        <a>编辑</a>
    </p>
</div>
```


###Controller

```php
namespace Controller;

use \Leno\Controller as LenoController;

class Article extends LenoController
{
    // RESTFUL POST的默认处理方法
    // POST /article
    public function add()
    {
        // 获取前端输入
        $username = $this->input('username', ['type' => 'email']);
        $user_data = $this->inputs([
            'username' => ['type' => 'email'],
            'password' => ['type' => 'string', 'extra' => [
                'max_length' => 64
            ]],
            'nickname' => ['type' => 'string', 'extra' => [
                'max_length' => 32
            ]]
        ]);

        try {
            $this->getService('xxx')->setUserName($username)
                ->execute();
        } catch (\Exception $ex) {
            // handle exception
        }

        return $this->output('操作成功');
    }

    // RESTFUL PUT的默认处理方法
    // PUT /article
    public function modify()
    {
    }

    // RESTFUL DELETE的默认处理方法
    // DELETE /article
    public function remove()
    {
    }

    // RESTFUL INDEX的默认处理方法
    // GET /article
    public function index($id = null)
    {
        if ($id) {
            // return Article of id;
        }
        // return Article list
    }
}
```

#介绍 LenoPHP是一个简单干净的PHP框架，有如下特性:
* 支持Mysql,和Pgsql,基于PDO的ORM,且方便拓展
* 支持继承,嵌套,条件输出,标签的模板系统
* 设计清晰的路由,可自定义规则,支持RESTFUL路由风格,可自定义action绑定
* 命令行自动化工具,自动安装应用
* "强制service",合理化业务逻辑类的粒度
* 简单的配置系统

#安装
LenoPHP通过composer进行管理,如果你不知道什么是composer,不要担心,10分钟就能掌握它

```shell
composer require hackyoung/leno
```

之后你会看到一个vendor目录,且已经支持psr0,psr4. 然后就是自动化初始化环境

```shell
cd path/to/project_dir && vendor/bin/leno hello_world --root .
```

#文档
编辑进行中...
