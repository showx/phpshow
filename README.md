
```
      _               _
 _ __ | |__  _ __  ___| |__   _____      __
| '_ \| '_ \| '_ \/ __| '_ \ / _ \ \ /\ / /
| |_) | | | | |_) \__ \ | | | (_) \ V  V /
| .__/|_| |_| .__/|___/_| |_|\___/ \_/\_/
|_|         |_|
```
# phpshow
phpshow,轻量简单易用的高性能php框架，默认启动workerman模式，抗大并发能力。

# 服务器环境
1. Nginx
2. php7以上

### 相关扩展

1. pcntl
2. libevent
3. seaslog(选用)
4. swoole(选用)

## 安装
统一使用phpcomposer安装
composer require showx/phpshow

## 协议
phpshow 的开源协议为 Apache-2.0，详情参见[LICENSE](LICENSE)

## php配置
### 项目config/site.php配置
详见参考事例

```
return [
    //框架里的模式 [0普通启动|1 workerman模式];
    'type' => 1,
    // 绑定的地址
    'host' => '0.0.0.0',
	// 启动的端口
    'port' => 8080,
	//进程worker数量
    'count' => 4,
    //数据库池的数量
    'mysql_pool_num' => 6,
    //调试模式
    'debug' => 1,
    //开发模式 [dev 查看加载异常|dev2 查看接口使用内存等]
    'dev' => 1,
    'dev2' => 0,
];
```


### php.ini
建议短标记
1.  short_open_tag = On  ;php短标记打开 <? ?>,模板要使用这样的标记

### nginx配置
nginx正常模式
``` 
server{
    ...
	location / {
		if ( !-e $request_filename) {
			rewrite ^(.*)$ /index.php?s=/$1 last;
			break;
		}
		try_files $uri $uri/ /index.html;
	}
	location ~ [^/]\.php(/|$) {
		fastcgi_pass 127.0.0.1:9000;
		fastcgi_index index.php;
		include fastcgi_params;
		fastcgi_split_path_info       ^(.+\.php)(.*)$;
		fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
		fastcgi_param PATH_INFO       $fastcgi_path_info;
	}
	...
}
```
高性能workerman模式
```
	location / {
		proxy_pass http://127.0.0.1:8080;
	    proxy_http_version 1.1;
	    proxy_set_header X-Real-IP $remote_addrs;
		
	}

```

# 框架教程
composer之后
./vender/showx/phpshow/app 项目示例
./vender/showx/phpshow/src  核心代码文件

简单的mvc模式
主要逻辑在于/app文件夹中
入口在/public文件夹(详情查询./vender/showx/phpshow/app)

### 路由
路由采用简单的定义方式 $url/{$ct}/{$ac},http://www.baidu.com/index/index,默认ct和ac为index.
支持path_info /index.php/index/index

### 配置文件  
配置文件主要饮食site(基础配置)database(数据库配置)route_rule(路由规则配置)
放在app项目下的config文件夹

### 核心类的介绍
1. \phpshow\request 获取数据类
2. \phpshow\response 输出类
3. \phpshow\loader 核心加载类
4. \phpshow\control 基层控制器
5. \phpshow\model	基层模型

### 工具库
1. \phpshow\lib\redis redis类
2. \phpshow\lib\http http请求类
3. \phpshow\lib\mysql 数据库驱动
4. \phpshow\lib\debug 页面调试
5. \phpshow\lib\jwt jwt会话验证
6. \phpshow\lib\log 日志类
7. \phpshow\lib\pool 进程池
8. \phpshow\helper\util 辅助函数库

### 模型层
继承\phpshow\model即可调用相关函数
1. insert 新增数据
2. update 更新数据
3. get_one 获取一条数据
4. get_all 获取所有数据

### 模板引擎
使用纯php输出，模板里简单用<? ?> 作为标签
vue带领大家，前后端分离，没需要用到模拟引擎

### 简单缓存
使用
<li> 设置缓存 \phpshow\loader::set($key,$value);</li>
<li> 获取缓存 \phpshow\loader::get($key); </li>

## 建议与反馈
联系本人 9448923#qq.com