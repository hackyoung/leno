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

