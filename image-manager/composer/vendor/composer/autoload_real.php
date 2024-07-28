<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInitComposerAutoloaderInit7ec9931e7e5ae7803c6eb8f933f2b236
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        require __DIR__ . '/platform_check.php';

        spl_autoload_register(array('ComposerAutoloaderInitComposerAutoloaderInit7ec9931e7e5ae7803c6eb8f933f2b236', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInitComposerAutoloaderInit7ec9931e7e5ae7803c6eb8f933f2b236', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInitComposerAutoloaderInit7ec9931e7e5ae7803c6eb8f933f2b236::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
