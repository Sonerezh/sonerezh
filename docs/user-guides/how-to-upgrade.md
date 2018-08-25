# Upgrading

Just install it again…

---

We do not provide any automation tool to upgrade Sonerezh. The most efficient
way is to move your existent installation to another directory
(``sonerezh.old`` for instance), make a fresh installation and copy some
configuration data from the old directory.

Let's suppose our current installation is located into ``/srv/sonerezh``. First,
we rename it.

```text
$ mv /src/sonerezh /srv/sonerezh.old
```

Then we can download the latest release and _untar_ it.

```text
$ wget https://www.sonerezh.bzh/downloads/latest.tar.gz
$ tar -zxf latest.tar.gz
```

We need to copy:

- The database configuration file (``database.php``)
- The album artworks, and the user's avatars
- If you used the email function, the email configuration files (``email.php``)

```text
$ cp -a /srv/sonerezh.old/app/Config/database.php /srv/sonerezh/app/Config/database.php
$ cp -a /srv/sonerezh.old/webroot/img/thumbnails /srv/sonerezh.old/webroot/img/
$ cp -a /srv/sonerezh.old/webroot/img/resized /srv/sonerezh.old/webroot/img/
$ cp -a /srv/sonerezh.old/webroot/img/avatars /srv/sonerezh.old/webroot/img/
$ cp -a /srv/sonerezh.old/app/Config/email.php /srv/sonerezh/app/Config/email.php
```

Don't forget to check the permissions on the new folders.

```text
$ chown -R www-data: /srv/sonerezh
```