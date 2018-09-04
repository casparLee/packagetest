#packagetest
##lumen 使用
#packagetest

1、执行 安装 composer require moonamiamj/packagetest 

2、如果出现无法获取安装 请在项目根 composer.json 添加如下
{
  "require": {
    "moonamiamj/packagetest": "dev-master"
  }
}
并添加 repositories 镜像源

"repositories": {
    "tencent-im": {
        "type": "vcs",
        "url": "https://github.com/EddieLau0402/Tencent.git"
    }
},

"repositories": {
"packagetest-dev": {
            "type": "vcs",
            "url": "https://github.com/moonamiamj/packagetest.git"
        }
},


3 在bootstrap目录下 app.php 添加
$app->register(Caspar\Packagetest\PackagetestServiceProvider::class);
 class_alias('Caspar\Packagetest\Facades\Packagetest', 'Packagetest');
 //添加门脸类别名 
 
 4、执行数据迁移 ：php artisan migrate
 
 5、composer dump-autoload
 如果提示找不到类  尝试在composer.json中添加
  "App\\": "app/",
  "Caspar\\Packagetest\\": "vendor/moonamiamj/packagetest/src/"
 在执行命令：
  composer clear-cache 和
 composer dump-autoload

 
 $router->get('test', 'Controller@test');
 ---------------------------------------------------------------------

1、在vendor目录中新增caspar目录;
2、caspar目录下 执行
> git clone https://github.com/casparLee/packagetest.git

3、在config目录下 app.php配置
>   Caspar\Packagetest\PackagetestServiceProvider::class,
 
  providers列表中添加
>  'Packagetest' => Caspar\Packagetest\Facades\Packagetest::class,

composer.json中autoload-》psr-4 添加
> "Caspar\\Packagetest\\":"vendor/caspar/packagetest/src/",

> composer dump-autoload

执行数据迁移
 php artisan migrate
 
 导入测试goods数据 goods.sql
