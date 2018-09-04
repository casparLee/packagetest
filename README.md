#packagetest
##lumen 使用

composer require --prefer-dist  moonamiamj/packagetest
composer dump-autoload

$app->register(Caspar\Packagetest\PackagetestServiceProvider::class);
class_alias('Caspar\Packagetest\Facades\Packagetest', 'Packagetest');//添加门脸类别名
 php artisan migrate
 
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
