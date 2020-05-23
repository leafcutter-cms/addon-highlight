# Syntax highlighting demo site

This addon supports both inline syntax highlighting like
`class Addon extends \Leafcutter\Addons\AbstractAddon`
and block code like so:

```php
// this is a block of PHP code
<?php
namespace Leafcutter\Addons\Leafcutter\SyntaxHighlighting;

class Addon extends \Leafcutter\Addons\AbstractAddon
{
    const DEFAULT_CONFIG = [];
    public function activate(): void
    {
    }
}
```

It also supports language specification in twig, where you start a block with something like ````php`

<!--@meta 
name: Home
 -->
