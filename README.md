
需要环境：
php >= 5.4 (支持php5.4+、php7.*、php8.*)

服务器配置

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
rewrite ^/(.*)/$ /$1 redirect;

if (!-e $request_filename){
	rewrite ^(.*)$ /index.php break;
}

```

