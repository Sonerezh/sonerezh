# Command-line tool

A simple tool available for automation.

---

Since version 1.1.0, a command-line tool is available to process big music
libraries or add some automation. This tool is built on the [CakePHP Shell] and
can be used as below.

## Import songs with the CLI

The CLI can be used to import a single audio file:

```text
sonerezh/app $ Console/cake sonerezh import /home/user/Music/file.mp3
Welcome to CakePHP v2.8.1 Console
---------------------------------------------------------------
App : app
Path: /var/www/sonerezh/app/
---------------------------------------------------------------
[INFO] You asked to import /home/user/Music/file.mp3. Continue? (yes/no)
[yes] >
[INFO] Run import: [100%] [#############################################]
```

It can also scan a directory:

```text
sonerezh/app $ Console/cake sonerezh import /home/user/Music/an-album
Welcome to CakePHP v2.8.1 Console
---------------------------------------------------------------
App : app
Path: /var/www/sonerezh/app/
---------------------------------------------------------------
[INFO] Scan /home/user/Music/an-album...
[INFO] Found 13 audio files (0 already in the database). Continue? (yes/no)
[yes] >
[INFO] Run import: [100%] [#############################################]
```

Or you can scan a complete folder tree using the ``--recursive`` option:

```text
sonerezh/app $ Console/cake sonerezh import -r /home/user/Music
Welcome to CakePHP v2.8.1 Console
---------------------------------------------------------------
App : app
Path: /var/www/sonerezh/app/
---------------------------------------------------------------
[INFO] Scan /home/user/Music...
[INFO] Found 614 audio files (13 already in the database). Continue? (yes/no)
[yes] >
[INFO] Run import: [100%] [#############################################]
```

The imported files are immediately available on the web interface.

[CakePHP Shell]: http://book.cakephp.org/2.0/en/console-and-shells.html 