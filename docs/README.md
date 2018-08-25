# Sonerezh documentation

This is the documentation for the Sonerezh project.

## How to build it

The documentation is built with [MkDocs], a static site generator that's geared
towards building project documentation.

Documentation source files are written in Markdown, and configured with a
single YAML configuration file.

### Install MkDocs

On Debian or Ubuntu platforms, just run:

```text
$ pip install mkdocs
```

### Get the documentation and contribute

Clone this repository wherever you want to, and start writing.

```text
$ git clone https://github.com/Sonerezh/sonerezh.git
$ cd sonerezh
$ mkdocs serve
```

Then MkDocs builds the doc, and serves it on ``http://127.0.0.1:8000``.

_Note: the Sonerezh theme for MkDocs just replaces the Google Analytics header
with Matomo (formerly Piwik)_

[MkDocs]: (https://www.mkdocs.org)
