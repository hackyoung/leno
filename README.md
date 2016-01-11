Leno--简单干净的PHP框架
====
Leno是一个简单的PHP框架，该框架支持模板继承，自动同步数据库等功能
###特性
 1. 模板
  * <extend>标签继承一个模板
  * <imlement>标签实现父模板定义的<child>标签
  * {$hello} 输出hello变量，{!Hello.hello}输出Hello类的hello常量，{&hello.hello}输出hello对象的hello属性，{%Hello.hello}输出Hello类的hello静态变量
  * 支持的其他标签<llist>,<eq>,<neq>,<in>,<nin>,<dump>
  * 可拓展标签

 2. 模型
  * 自动同步数据库字段
  * debug模式下创建不同的数据库和线上版本区分
  * 基于PDO
 3. js编写的前端库
  * layer,窗口，以及一个美观的富文本编辑器
 4. 可配置性极强
  你可以配置框架行为，从项目目录树至自动加载类
###实例
基于Leno的一个简单的博客系统示例
 1.功能
  Leno博客提供一个文章列表，文章详情及发布页面，用户可发布博客，查看博客列表，查看博客详情
 2.Model
 file app/Model/Article.class.php
```php
 namespace Model;
 class Article extends \Leno\Model {
 	protected $_table = "article";

	protected $_field_prefix = "atl";

 	protected $_fields = array(
		'id'=array(
			'type'=>'int',
			'auto_increment'=>true,
			'primary_key'=>true
		),
		'title'=>array(
			'type'=>'nvarchar(64)',
			'null'=>false
		),
		'content'=>array(
			'type'=>'nvarchar(100000)',
			'null'=>false
		),
		'created'=>array(
			'type'=>'datetime',
			'null'=>false
		)
	);

	public function add($title, $content) {
		$this->data(array(
			'title'=>$title,
			'content'=>urlencode($content)
		))->create();
	}

	public function save($id, $title, $content) {
		$this->where(array(
			'id'=>$id
		))->data(array(
			'title'=>$title,
			'content'=>urlencode($content)
		))->save();
	}
 }
 ```
 3. Controller
  file: app/Controller/Article.class.php
```php
  namespace Controller;
  class Article extends \Leno\Controller {
  	public function index() {
		$m = $this->loadModel("Article", 'Model');
		$articles = $m->select();
		$this->set('articles', 'articles');
		$this->loadView();
	}

	public function save($id) {
		$title = $this->_post('title', '请填写title');
		$content = $this->_post('content', '请填写content');
		$this->loadModel('Article', 'Model');
		$this->Article->save($id, $title, $content);
		$this->success('保存文章成功');
	}
  }
  ```
 4. View
 	file: app/View/Article/index.lpt.php
```php
	<extend name="Layout.default">
		<implement name="content">
			<ul>
			<llist name="articles" id="article">
				<li>
					<a href="">{$article.title}</a>
					<span>{$article.created}</span>
				</li>
			</llist>
			</ul>
		</impelement>
	</extend>
	```
	file: app/View/Article/write.lpt.php
	```php
	<extend name="Layout.default">
		<implement name="content">
			<div class="cc">
				<input name="title" data-reg="^\s{0,}\S{1,}.*" placeholder="请输入文章名" />
				<div id="editor">
				</div>
				<button data-id="submit">保存</button>
			</div>
			<script>
				$(document).ready(function() {
					var editor = new leno.editor({
						id: 'editor'
					});
				});
				leno.form({
					id: 'submit_article',
					node: $('.cc'),
					url: {
						submit: 'Article/save',
						redirectUrl: 'Article/index'
					},
					callback: {
						beforeSubmit: function(data) {
							data.content = editor.getContent();
							return data;
						}
					}
				});
			</script>
		</implement>
	</extend>
	```
