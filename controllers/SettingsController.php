<?php

/**
 * MicrosoftTranslator_SettingsController
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md
 * file distributed with this source code.
 *
 * @copyright  Copyright (c) 2014-2016 Gather Digital Ltd (https://www.gatherdigital.co.uk)
 * @license    https://www.gatherdigital.co.uk/license     GNU General Public License version 3 (GPLv3)
 */

use Pimcore\Controller\Action\Admin as PimcoreAction;

class MicrosoftTranslator_SettingsController extends PimcoreAction
{

    public function getSettingsAction()
    {
        $this->disableViewAutoRender();

        try {
            $settings = \Pimcore\Db::get()->fetchPairs("SELECT `name`,`value` from `plugin_microsoft_translator`");

            $this->_helper->json($settings, true);
        } catch(Exception $e) {
            \Logger::alert($e->getMessage());
        }
    }

    public function setSettingsAction()
    {
        $this->disableViewAutoRender();
        $data = $this->_request->getParam('data');
        $data = json_decode($data);
        $response = array();

        //explicitly save only the settings listed here
        $saveSettings = array(
            'clientId' => $data->clientId,
            'clientSecret' => $data->clientSecret,
            'defaultContentLanguage' => $data->defaultContentLanguage
        );

        try {

            //save the settings
            $db = \Pimcore\Db::get();

            foreach ($saveSettings as $key=>$value) {
                $sql = 'INSERT INTO `plugin_microsoft_translator` (`name`,`value`) VALUES (?,?) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `value`=VALUES(`value`)';
                $db->query($sql, array($key, $value));
            } unset($key, $value);

            $response['success'] = true;

        } catch(Exception $e) {
            $response['error']  = true;
            $response['message'] = $e->getMessage();
        }

        $this->_helper->json($response, true);
    }

}