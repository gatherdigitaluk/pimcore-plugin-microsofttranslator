<?php

/**
 * Translator
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md
 * file distributed with this source code.
 *
 * @copyright  Copyright (c) 2014-2016 Gather Digital Ltd (https://www.gatherdigital.co.uk)
 * @license    https://www.gatherdigital.co.uk/license     GNU General Public License version 3 (GPLv3)
 */

namespace MicrosoftTranslator;

class Translator
{


    /**
     * Authorisation client id
     * @var string $cliendId
     */
    private $clientId;

    /**
     * Authorisation client secret
     * @var string $clientSecret
     */
    private $clientSecret;

    /**
     * Some API constants
     */
    const AUTH_URL      = "https://datamarket.accesscontrol.windows.net/v2/OAuth2-13/";
    const SCOPE_URL     = "http://api.microsofttranslator.com";
    const TRANSLATE_URL = 'http://api.microsofttranslator.com/v2/Http.svc/Translate';
    const GRANT_TYPE    = "client_credentials";

    /**
     * @var string $accessToken
     */
    private $accessToken;

    /**
     * The default translation language on all subsequent calls
     * @var string $defaultTo
     */
    private $defaultTo;

    /**
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string $defaultTo
     * @throws \Exception
     */
    public function __construct($clientId, $clientSecret, $defaultTo)
    {
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;

        //get an authorisation header
        try {
            $this->authorize();
        } catch (\Exception $e) {
            $string = 'Could not Authorise with the Microsoft Translate API ->'. $e->getMessage();
            if (PIMCORE_DEBUG) {
                throw new \Exception($string);
            }
            \Logger::error($string);
        }

        $this->defaultTo = $defaultTo;

    }//construct

    /**
     * Performs a request to MS to retrieve a translation.
     * @param string $text
     * @param string $languageTo
     * @param string $languageFrom
     * @return string | false on failure
     */
    public function translate($text, $to=null, $from=null, $contentType="text/plain", $category="general")
    {
        $translation = false;

        if (!$to) {
            $to = $this->defaultTo;
        }

        $req = new \Zend_Http_Client();
        $req->setUri(self::TRANSLATE_URL);
        $req->setHeaders('Authorization', 'Bearer '.$this->accessToken);
        $req->setHeaders('Content-Type', 'text/xml');
        $req->setMethod(\Zend_Http_Client::GET);
        $req->setParameterGet('text', $text);
        $req->setParameterGet('from', $from);
        $req->setParameterGet('to', $to);
        $req->setParameterGet('contentType', $contentType);
        $req->setParameterGet('category', $category);

        $result = $req->request();

        if ($result) {

            if ($result->getStatus() != 200) {
                \Logger::error('Microsoft translator: Could not retrieve translation.'.$result->getBody());
                return $text;
            }

            $xmlObj = simplexml_load_string($result->getBody());
            foreach((array)$xmlObj[0] as $val) {
                $translation = $val;
            }

            return $translation;
        }


        return false;
    }

    private function authorize()
    {

        $ah = new \Zend_Http_Client();

        $paramArr = array (
            'grant_type'    => self::GRANT_TYPE,
            'scope'         => self::SCOPE_URL,
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret
        );

        $paramArr = http_build_query($paramArr);

        $ah->setUri(self::AUTH_URL);
        $ah->setMethod(\Zend_Http_Client::POST);
        $ah->setRawData($paramArr);

        $response = $ah->request();
        if(!$response) {
            throw new \Exception('MicrosoftTranslate Http Error');
        }

        $obj_response = json_decode($response->getBody());

        if($obj_response->error) {
            throw new \Exception($obj_response->error_description);
        }

        $this->accessToken = $obj_response->access_token;
    }

}