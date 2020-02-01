# Apache Subfolder install

In case you want to run Sonerezh in a subfolder of your
existing website, here are some hints to get it work.

First extract sonerezh from archive, for example in `/var/www/sonerezh/`

In directory `/etc/apache2/conf.d/` create a file `sonerezh.conf`
with the following content.

```apache2
Alias "/sonerezh/" "/var/www/sonerezh/"

<Directory "/var/www/sonerezh/">
        Options FollowSymLinks
        AllowOverride All
</Directory>

```

Then update the files .htaccess to add directive `RewriteBase` for the subfolder.
* sonerezh/.htaccess
* sonerezh/app/.htaccess
* sonerezh/app/webroot/.htaccess

Be careful, as the three files are different below the `RewriteEngine` directive. 

```apache2
<IfModule mod_rewrite.c>
   RewriteEngine on
   RewriteBase /sonerezh
```

Once done, setup the permission for your apache2 user
`chown www-data:www-data -R /var/www/sonerezh/`

Reload Apache configuration with `service apache2 reload`

Access Sonerezh by using http://yoursite/sonerezh/
Access your website as usual
