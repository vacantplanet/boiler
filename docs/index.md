---
title: Introduction
---
Boiler Template Engine for PHP
==============================

Boiler is a native >=PHP 8.1 template engine, which it is heavily inspired by
[Plates](https://platesphp.com/). Like *Plates*, Boiler does not introduce
a new template language and instead uses PHP itself. You simply use the PHP
statements you already know.

The main differences to *Plates* are:

* Boiler automatically escapes strings and
  [Stringable](https://www.php.net/manual/en/class.stringable.php) values. This
  is optional. You can turn it off globally or for single render calls.
* The template context is global by default. That means all values available in
  the main template are available in all included parts, like
  [sections](sections.md), [inserts](inserts.md) or [layouts](layouts.md).

## Features

* Autoescaping: Prevents XSS attacs from untrusted user input by passing all
  rendererd strings to PHP's {{php('htmlspecialchars')}} function.
* A simple API. Only one class, the [Engine](engine.md), is usually needed.
* Code reuse with template [inheritance](layouts.md) and
  [inclusion](inserts.md).
* You use plain PHP in your templates. No need to learn another syntax.
* Fully tested and statically analyzed with Psalm set to level 1.
* Reasonable performance.

Next: [*Quick Start*](quickstart.md) or [*The Engine*](engine.md).
