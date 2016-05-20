#介绍
LenoPHP是一个简单干净的PHP框架，有如下特性:
    - 支持Mysql,和Pgsql,基于PDO的ORM,且方便拓展
    - 支持继承,嵌套,条件输出,标签的模板系统
    - 设计清晰的路由,可自定义规则,支持RESTFUL路由风格,可自定义action绑定
    - 命令行自动化工具,自动安装应用
    - "强制service",合理化业务逻辑类的粒度
    - 简单的配置系统

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

