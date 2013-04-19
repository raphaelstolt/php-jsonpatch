<?php

$loader = @include __DIR__ . '/../vendor/autoload.php';

if (!$loader) {
    die(<<<'EOT'
You must set up the project dependencies by executing the following commands:

wget http://getcomposer.org/composer.phar
php composer.phar install

EOT
);
}

spl_autoload_register(function($class)
{
    $file = __DIR__ . '/../src/' . strtr($class, '\\', '/') . '.php';
    if (file_exists($file)) {
        require $file;
        return true;
    }
});