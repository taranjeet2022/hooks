Hooks
=========

The PHP Hooks Class is a fork of the WordPress filters hook system rolled in to a class to be ported into any php based system  
*  This class is heavily based on the WordPress plugin API and most (if not all) of the code comes from there.

How to install?
=====

```shell
composer require krypton/hooks
```

How to use?
=====

We start with a simple example ...

```php
<?php

use Krypton\Hooks\Hooks;

function print_user_save()
{
    echo "User is Saved";
}

function print_user_added()
{
    echo "User is Added to the System";
}

$hooks = Hooks::getInstance();

$hooks->getAction()->add($hooks->getFilter(), 'user_save', 'print_user_save');

$hooks->getAction()->add($hooks->getFilter(), 'user_save', 'print_user_added');

$hooks->getFilter()->add('uppercase', 'strtoupper');



```    

then all that is left for you is to call the hooked function when you want anywhere in your application, EX:

```php
<?php

$hooks = Hooks::getInstance();

echo '<div id="extra_header">';

echo $hooks->getFilter()->apply('uppercase', 'taranjeet');

$hooks->getAction()->do($hooks->getFilter(), 'user_save');

echo '</div>';
```

License
=======

Since this class is derived from the WordPress Plugin API so are the license and they are GPL http://www.gnu.org/licenses/gpl.html

  [1]: https://github.com/bainternet/PHP-Hooks/zipball/master
  [2]: https://github.com/bainternet/PHP-Hooks/tarball/master
  [3]: http://bainternet.github.com/PHP-Hooks/
