
需要环境：
php >= 7.2 

服务器配置

定时器新增配置可以实现git自动pull
*/1 * * * * /usr/bin/sh {你的项目路径}/pull
* */72 * * * rm -rf {你的项目路径}/runtime/log/pull.log


````

.htaccess(Apache):

```
RewriteEngine On
RewriteBase /


# Allow any files or directories that exist to be displayed directly
RewriteCond %{REQUEST_FILENAME} -s [OR]
RewriteCond %{REQUEST_FILENAME} -l [OR]
RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d [OR]
RewriteCond %{REQUEST_URI} ^.*(.css|.js|.gif|.png|.jpg|.jpeg|.ico|.swf)$
RewriteRule ^.*$ - [NC,L]
RewriteRule ^(.*)$ index.php?$1 [QSA,L]
```

.htaccess(Nginx):

```
if (!-e $request_filename) {
rewrite ^(.*)$ /index.php$1 last;
break;
}

```

