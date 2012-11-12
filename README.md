# David's PHP Web Framework

An MVC-style PHP web framework embracing [the "no-framework" ideology][nff]
coined by PHP's original creator, Rasmus Lerdorf. I use it as a starting
point for most of my web projects, and it has slowly grown to support all my
basic needs. Features include:

  * Request routing `(class.router.php)`
  * PHP templating `(class.page.php)`
  * CSRF protection `(class.csrf.php)`
  * JS + CSS minification with LESS support `(./tools/build.sh)`
  * MySQL database wrapper `(class.database.php)`
  * Database sessions `(class.dbsession.php)`
  * Database-backed objects `(class.dbobject.php)`
  * Cache wrapper for APC/memcached `(class.cache.php)`
  * Configuration with dev/staging/production overrides `(config.ini)`

## Dependencies

PHP 5.3 is the only hard dependency. For JS + CSS minification,
[yuicompressor][yuic] must be installed in the `libraries` directory. For LESS
support, [lessc][lessc] must be installed on the system.

## Directory Tree

The framework is setup to be added as a submodule to your existing project, but
requires a specific surrounding directory structure.

       .
       ├── config.ini
       ├── framework
       │   ├── LICENSE
       │   ├── README.md
       │   ├── class.cache.php
       │   ├── class.config.php
       │   ├── class.csrf.php
       │   ├── class.database.php
       │   ├── class.dbobject.php
       │   ├── class.dbsession.php
       │   ├── class.minifier.php
       │   ├── class.page.php
       │   ├── class.router.php
       │   ├── config.sample.ini
       │   ├── inc.functions.php
       │   ├── inc.master.php
       │   └── tools
       │       ├── build.sh
       │       └── dev-server.sh
       ├── handlers
       ├── libraries
       ├── objects
       ├── templates
       │   ├── error.tpl.php
       │   └── layout.tpl.php
       └── web
           ├── index.php
           ├── scripts
           └── styles

## Model

Class objects are autoloaded from the `objects` directory, whose filenames
must case-sensitively match the class name. For example, a `User` class
belongs at `./objects/User.php`.

## View

Page-specific PHP template files are merged into the base 
`./templates/layout.tpl.php` file along with relevant JS or CSS sources.

## Controller

Incoming requests are automatically directed to the appropriate file in the
`./handlers` directory based on matching URI tokens with filenames. Any
unmatched tokens are then passed along as arguments to the handler. For
example, given a request for "/item/1/foo", the router will make the following
attempts to find a handler:

  1. ./handlers/item/1/foo.php (fail)
  2. ./handlers/item/1.php (fail)
  3. ./handlers/item.php (match, arguments: ['1', 'foo'])

This behavior allows the use of pretty URLs without any prior configuration.

## Getting Started

Check out the [example project][dmphp-example].

[nff]: http://toys.lerdorf.com/archives/38-The-no-framework-PHP-MVC-framework.html
[yuic]: https://github.com/yui/yuicompressor
[lessc]: https://github.com/cloudhead/less.js
[dmphp-example]: https://github.com/dmpatierno/dmphp-example

