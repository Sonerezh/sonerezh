# Settings Reference

Guide to all available configuration settings.

---

## About the configuration

The settings can be configured on the ``/settings`` page. You will find some
statistics related to the current installation and buttons used to manage the
database and the caches.

### Music root directory

Here you can specify the absolute path of the folder in which Sonerezh must look
for your music.

!!! Note

    Make sure Sonerezh can read this folder recursively.
    
!!! Warning
    
    We strongly recommend to **NOT** store any audio file into the Sonerezh
    application directory.
    
### Email notifications

Sonerezh can send email to new users, or allow them to retrieve a forgotten
password. Make sure PHP can send emails before enable this option.

Just like the database configuration, email configuration can be centralized in
a class called ``EmailConfig``, in ``app/Config/email.php``. The
``app/Config/email.php.default`` has an example for this file.

You can follow the [official CakePHP documentation] to configure the different
transport methods to send email. The following configuration should work in most
cases (if you already have a MTA available).

```php
class EmailConfig {
    public $default = array(
        'transport' => 'Mail',
        'from' => 'no-reply@sonerezh.bzh',
        'charset' => 'utf-8',
        'headerCharset' => 'utf-8',
    );
} 
```

In this example, Sonerezh will send email using the PHP function ``mail()``.

### Automatic track conversion

If your library contains tracks which can not be read by your browser, Sonerezh
can convert them to OGG/Vorbis or MP3.

!!! Note

    Sonerezh requires ``avconv`` or ``ffmpeg`` to convert the tracks.
    
### Database management

The settings page allows you to make some maintenance operations on Sonerezh,
its database and its caches:

* Database update: run the import process. Useful if you have recently added new
  songs
* Clear the cache : empty the cache (converted tracks and resized covers)
* Reset the database : reset the database. All songs and playlists
  **WILL BE LOST**. Don't panic, Sonerezh will never modify or delete any file
  on the filesystem

[official CakePHP documentation]: http://book.cakephp.org/2.0/en/core-utility-libraries/email.html