<?php

require_once('vendor/autoload.php');

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

echo $hooks->getFilter()->apply('uppercase', 'taranjeet');

$hooks->getAction()->do($hooks->getFilter(), 'user_save');
