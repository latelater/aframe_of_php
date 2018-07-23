<?php

class Core {
    private static $classMap = [
        'App' => APP,
    ];

    const CONTROLLER = 'Controller';
    const MODEL = 'Model';

    // 类自动加载
    public static function load($className) {
        if (isset(self::$classMap[$className])) {
            return true;
        }

        $filePath = self::getFilePathFromFullClassName($className);
        if ($filePath == '') {
            return false;
        }
        include_once $filePath;
    }

    // 请求分发
    public static function run() {
        $controller = '';
        $action = '';
        if (self::isCli() == true) {
            $requestPath = explode('&', $_SERVER['argv'][1]);
            $controller = explode('=', $requestPath[0])[1];
            $action = explode('=', $requestPath[1])[1];
        } else {
            $controller = $_REQUEST['c'];
            $action = $_REQUEST['a'];
        }

        $controller =  'App\Controller\\' . ucfirst($controller) . 'Controller';

        $entity = new $controller();
        $entity->$action();
    }

    // 是否以命令行模式在运行
    private static function isCli() {
        if(defined('STDIN')) {
            return true;
        }
        
        if(empty($_SERVER['REMOTE_ADDR']) && !isset($_SERVER['HTTP_USER_AGENT']) && count($_SERVER['argv']) > 0) {
            return true;
        }
        
        return false;
    }

    private static function getFilePathFromFullClassName($className) {
        if ($className == '') {
            return '';
        }

        // 取出顶级命名空间
        $topNamespace = substr($className, 0, strpos($className, '\\'));
        // 取出剩下的命名空间转换为文件路径
        $lessNamespace = substr($className, strlen($topNamespace));
        $lessFilePath = dirname(str_replace('\\', '/', $lessNamespace));

        // 取出类名转换为文件名
        $classFilePath = substr($className, strrpos($className, '\\') + 1) . '.php';
        $filePath = self::$classMap[$topNamespace] . $lessFilePath . '/' . $classFilePath;
        if (!is_file($filePath)) {
            return '';
        }
        return $filePath;
    }

}

spl_autoload_register('Core::load');
Core::run();
