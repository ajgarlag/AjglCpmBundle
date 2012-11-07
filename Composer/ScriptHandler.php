<?php
namespace Ajgl\Bundle\CpmBundle\Composer;

use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler as BaseScriptHandler;

class ScriptHandler
    extends BaseScriptHandler
{
    public static function installCpmPackages($event)
    {
        $options = self::getOptions($event);
        $appDir = $options['symfony-app-dir'];

        if (!is_dir($appDir)) {
            echo 'The symfony-app-dir ('.$appDir.') specified in composer.json was not found in '.getcwd().', can not install CPM packages.'.PHP_EOL;

            return;
        }

        static::installCpm($event);
        static::executeCommand($event, $appDir, 'cpm:install', $options['process-timeout']);
    }

    public static function installCpm($event)
    {
        $options = self::getOptions($event);
        $appDir = $options['symfony-app-dir'];

        if (!is_dir($appDir)) {
            echo 'The symfony-app-dir ('.$appDir.') specified in composer.json was not found in '.getcwd().', can not install the CPM.'.PHP_EOL;

            return;
        }

        static::executeCommand($event, $appDir, 'cpm:cpm:install', $options['process-timeout']);
    }
}
