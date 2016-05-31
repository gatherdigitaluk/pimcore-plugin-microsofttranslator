<?php

/**
 * Frontend
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md
 * file distributed with this source code.
 *
 * @copyright  Copyright (c) 2014-2016 Gather Digital Ltd (https://www.gatherdigital.co.uk)
 * @license    https://www.gatherdigital.co.uk/license     GNU General Public License version 3 (GPLv3)
 */

namespace MicrosoftTranslator;

use Pimcore\Tool\Transliteration;
use MicrosoftTranslator\Translator as MSTranslator;

class Frontend
{

    /**
     * @var array $config
     */
    private $config;

    /**
     * @var string $currentLocale
     */
    private $currentLocale;

    /**
     * @var Translator $translator
     */
    private $translator;

    /**
     * MicrosoftTranslator_Frontend constructor.
     */
    public function __construct()
    {

        if (\Zend_Registry::isRegistered('microsoft_translator_plugin_settings')) {
            $this->config = \Zend_Registry::get('microsoft_translator_plugin_settings');
        } else {

            try {
                $this->config = \Pimcore\Db::get()->fetchPairs("SELECT name,value from `plugin_microsoft_translator`");
            } catch(\Exception $e) {
                \Logger::alert($e->getMessage());
            }

            \Zend_Registry::set('microsoft_translator_plugin_settings', $this->config);
        }

        //get the default language of the current request
        if (\Zend_Registry::isRegistered('Zend_Locale')) {
            $this->currentLocale = Zend_Registry::get('Zend_Locale')->getLanguage();
        } else {
            throw new \Exception('No default locale specified!');
        }


        if (isset($this->config['clientId']{0}) && isset($this->config['clientSecret']{0})) {

            //dont create an instance of the translator if the current locale of the site is the default locale of the content.
            if($this->currentLocale == $this->config['defaultContentLanguage']) {
                $this->translator = null;
            } else {
                //create a new translator instance, set the translation TO parameter to the current locale of the request
                $this->translator = new MSTranslator($this->config['clientId'], $this->config['clientSecret'], $this->currentLocale);
            }

        } else {
            throw new \Exception('No Microsoft Translate API access details found. See plugin Settings');
        }


    }

    /**
     * Provides access to the front end for translating text into the language specified.
     * If no API access is available then the returned text will just be stripped of its tags.
     * @param String $lang
     * @return string
     */
    public function translate($input, $isHtml=false)
    {
        if (!$this->translator) {
            return $input; //return the input, no need to translate
        }

        if ($isHtml) {
            $contentType = 'text/html';
        } else {
            $input = Transliteration::toASCII(trim(strip_tags($input)));
            $contentType = 'text/plain';
        }

        $translation = $this->translator->translate($input, null, null, $contentType);

        if ($translation !== FALSE) {
            return $translation;
        }

        return $input;
    }


}