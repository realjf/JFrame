# JFrame
 a php framework

### 安装准备
框架中使用了composer依赖包管理工具，请先确保安装
不清楚composer是什么或者不会安装的，可以查看[composer中文网](http://docs.phpcomposer.com/00-intro.html)，里面有详细的介绍

1. 在项目根目录下新建文件composer.json，并添加如下内容：
```
{
  "name": "php/JFrame",
  "description": "php/JFrame The PHP Framework",
  "version": "1.0.0-beta",
  "keywords": ["php", "php framework"],
  "require": {
    "php":">=5.6.15",
    "filp/whoops":"*",
    "hassankhan/config":"0.10.0"
  },
  "repositories":{
    "packagist":{
      "type":"composer",
      "url":"https://packagist.phpcomposer.com"
    }
  }
}
```
保存退出

2. 请确认composer添加到环境变量中，在项目目录根目录下运行composer install，进行依赖包的安装

3. 依赖包安装完成后，即可进行项目部署


