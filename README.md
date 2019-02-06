# Sonerezh

Sonerezh is a self-hosted web application which allows you to listen to your
music, from anywhere.

All you have to do is to specify where your music is stored, and Sonerezh will
build its database based on the audio file's metadata. Then you can browse your
music library through a simple and intuitive Web UI.

:arrow_right: Let's try the latest version on [sonerezh.bzh/demo]!

Follow us on [Twitter] if you like the project, and don't forget to [support it
making a donation].

The **standard installation instruction are available in the documentation** on
[sonerezh.bzh]. If you want to contribute to the project or if you prefer to use
Git and Composer you can follow the steps below.

## Installation using Git and Composer (for developers)

You must have [Composer] installed and ready to download the Sonerezh's
dependencies. You will also need PHP (obviously) and at least ``php-mysql`` or
``php-pgsql`` and ``php-gd``.

1. Download the sources:

    ```sh
    $ git clone https://github.com/Sonerezh/sonerezh.git
    ```

2. Download the dependencies:

    ```sh
    $ cd sonerezh
    $ composer install
    ```

3. You should be good to run Sonerezh using:

    ```sh
    $ cd app/webroot
    $ CAKEPHP_DEBUG=1 php -S localhost:8080
    ```

_Note: you may have some issues to display the cover arts using the built-in PHP
server._

[sonerezh.bzh/demo]: https://www.sonerezh.bzh/demo/login
[Twitter]: https://twitter.com/snrzh
[support it making a donation]: https://www.sonerezh.bzh/donate
[sonerezh.bzh]: https://www.sonerezh.bzh
[Composer]: https://getcomposer.org/