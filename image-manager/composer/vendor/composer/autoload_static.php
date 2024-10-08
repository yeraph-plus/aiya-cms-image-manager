<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit5e20dd22aaeaa4de4fca03b3afb5a727
{
    public static $prefixLengthsPsr4 = array (
        'I' => 
        array (
            'Imagine\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Imagine\\' => 
        array (
            0 => __DIR__ . '/..' . '/imagine/imagine/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit5e20dd22aaeaa4de4fca03b3afb5a727::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit5e20dd22aaeaa4de4fca03b3afb5a727::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit5e20dd22aaeaa4de4fca03b3afb5a727::$classMap;

        }, null, ClassLoader::class);
    }
}
