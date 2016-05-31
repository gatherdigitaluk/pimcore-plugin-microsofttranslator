<?php

/**
 * Plugin
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md
 * file distributed with this source code.
 *
 * @copyright  Copyright (c) 2014-2016 Gather Digital Ltd (https://www.gatherdigital.co.uk)
 * @license    https://www.gatherdigital.co.uk/license     GNU General Public License version 3 (GPLv3)
 */

namespace MicrosoftTranslator;

use Pimcore\API\Plugin\AbstractPlugin;
use Pimcore\API\Plugin\PluginInterface;

class Plugin extends AbstractPlugin implements PluginInterface
{

    public static function install()
    {

        $sql = "CREATE TABLE IF NOT EXISTS `plugin_microsoft_translator`
                (
                    `name` varchar(32) DEFAULT NULL,
                    `value` varchar(255) DEFAULT NULL,
                    PRIMARY KEY  (`name`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

        $result = \Pimcore\Db::get()->query($sql);

        return 'Microsoft Translator Installed Successfully!';
    }

    public static function needsReloadAfterInstall()
    {
        return true;
    }

    public static function uninstall()
    {
        $sql = "DROP TABLE `plugin_microsoft_translator`";
        $result = \Pimcore\Db::get()->query($sql);

        return 'Microsoft Translator Un-installed Successfully!';
    }

    public static function isInstalled()
    {
        return \Pimcore\Db::get()->query("SHOW TABLES LIKE 'plugin_microsoft_translator'")->rowCount() > 0;
    }

}