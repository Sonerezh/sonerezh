Sonerezh
========

A self-hosted, web-based application to stream your music, everywhere.

Some Handy Links
----------------

[Sonerezh official website](https://www.sonerezh.bzh).

[Sonerezh documentation](https://www.sonerezh.bzh/docs/).

[Sonerezh source code, on GitHub](https://github.com/sonerezh/sonerezh).

[Sonerezh on Twitter](https://twitter.com/snrzh).

Don't forget to [support us](https://www.sonerezh.bzh/support) !

development-new branch
----------------------

This is a branch to continue development while the main project is temporarily paused
Changes implemented:

- Merged commit in PR https://github.com/Sonerezh/sonerezh/pull/300 (Removed slow subquery from albums view)
- Merged commit in PR https://github.com/Sonerezh/sonerezh/pull/304 (Changed select grouping so that albums with the same name are listed)
- Merged commit in PR https://github.com/Sonerezh/sonerezh/pull/287 (Removed trailing slash in subdirectory path added by CakePHP for some folders)
- Merged commit in PR https://github.com/Sonerezh/sonerezh/pull/306 (Upgraded CakePHP to 2.9.8)
- Merged commit in PR https://github.com/Sonerezh/sonerezh/pull/293 (Implemented database cleaning)
- Merged commit in PR https://github.com/Sonerezh/sonerezh/pull/309 (Corrected Songs number in statistics)
- Made compatible with MySQL setting ONLY_FULL_GROUP_BY + removed old unecessary SQLite code
- Implemented re-parsing of metadata for modified files
- Fixed some magic numbers in code
- Merged commit in PR https://github.com/Sonerezh/sonerezh/pull/312 (Player now shows artist instead of band)
- Bugfix for https://github.com/gs11/sonerezh/issues/7 (Re-parsing of metadata for modified files)
