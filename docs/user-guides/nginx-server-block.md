# NGINX server-block

This is a minimalist configuration sample for NGINX.

```nginx
upstream php-fpm {                                                              
    server unix:/var/run/php-fpm.sock;                                          
}

server {
    listen      80;
    server_name demo.sonerezh.bzh;
    root        /var/www/sonerezh/app/webroot;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$args;
        expires 14d;
        add_header Cache-Control 'public';
    }

    # The section below handle the thumbnails cache, on the client (browser)
    # side (optional but recommended)
    location ~* /([^/]+_[0-9]+x[0-9]+(@[0-9]+x)?\.[a-z]+)$ {
        try_files /img/resized/$1 /index.php?$args;
        add_header Cache-Control 'public';
        expires 14d;
        access_log off;
    }

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_index index.php;
        fastcgi_pass php-fpm;
        include fastcgi.conf;

        # If fastcgi.conf is not available on your platform you may want to
        # uncomment the following line
        #fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

To run Sonerezh on a subfolder instead of a subdomain, here is an other example.

```nginx
upstream php-fpm {                                                              
    server unix:/var/run/php-fpm.sock;                                          
}  

server {
    listen      80;
    server_name demo.sonerezh.bzh/sonerezh;

    index index.php;

    location /sonerezh/ {
        alias /var/www/sonerezh/app/webroot/;
        try_files $uri $uri/ /sonerezh//sonerezh/index.php?$args;

        # The section below handle the thumbnails cache, on the client (browser)
        # side (optional but recommended)
        location ~* /([^/]+_[0-9]+x[0-9]+(@[0-9]+x)?\.[a-z]+)$ {
            try_files /img/resized/$1 /index.php?$args;
            add_header Cache-Control 'public';
            expires 14d;
            access_log off;
        }

        location ~ ^/sonerezh/(.+\.php)$ {
            alias /var/www/sonerezh/app/webroot/$1;
            fastcgi_pass php-fpm;
            include fastcgi.conf;

            # If fastcgi.conf is not available on your platform you may want to
            # uncomment the following line
            #fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        }
    }
}
```
