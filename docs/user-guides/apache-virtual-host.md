# Apache virtual host

This is a minimalist configuration sample for Apache2.

```apacheconfig
<VirtualHost *:80>
    ServerName      demo.sonerezh.bzh
    DocumentRoot    /var/www/sonerezh

    <Directory /var/www/sonerezh>
        Options -Indexes
        AllowOverride All

        # Apache 2.2.x
        <IfModule !mod_authz_core.c>
            Order Allow,Deny
            Allow from all
        </IfModule>

        # Apache 2.4.x
        <IfModule mod_authz_core.c>
            Require all granted
        </IfModule>
    </Directory>

    CustomLog   /var/log/apache2/demo.sonerezh.bzh-access.log "Combined"
    ErrorLog    /var/log/apache2/demo.sonerezh.bzh-error.log
</VirtualHost>
```