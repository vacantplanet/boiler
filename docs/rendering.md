---
title: Rendering Templates
---
Rendering Templates
===================

After you created an [`\Conia\Boiler\Engine`](engine.md) object, you can render
templates using its `render()` method.

Throughout this document we assume the following directory structure:

```text
path
`-- to
    |-- templates
    |   |-- layout.php
    |   |-- more.php
    |   `-- page.php
    `-- additional
        |-- subdir
        |   `-- subtemplate.php
        `-- template.php
```
