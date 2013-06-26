<?php namespace Softlabs\Docmail;

use \Config as Config;
use \Exception as Exception;
use Softlabs\Docmail\DocmailAPI as DocmailAPI;

class Docmail {

    private $mailingGUID;
    private $templateGUID;

    // Complex methods (multiple API calls)

    public static function sendToSingelAddress($options = []) {
        $options = self::processParameterNames($options);

        try {

            DocmailAPI::validateCall(['CreateMailing'], $options);
            $mailingGUID = DocmailAPI::CreateMailing();
            $options["MailingGUID"] = $mailingGUID;

            DocmailAPI::validateCall(['AddAddress', 'AddTemplateFile', 'ProcessMailing'], $options);
            $result = DocmailAPI::AddAddress($options);

            $templateGUID = DocmailAPI::AddTemplateFile($options);

            $result = DocmailAPI::ProcessMailing($options);

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $result;

    }

    public function getMailingGUID() {
        return $this->mailingGUID;
    }

    public function getTemplateGUID() {
        return $this->templateGUID;
    }

    private static function processParameterNames($parameters) {

        // Names that should be changed to fit our standards
        $namesToConvert = [
            'PrintColour' => function($value){ return ["IsMono" => !$value]; },
            'PrintDuplex' => function($value){ return ["IsDuplex" => $value]; },
            'FirstClass'  => function($value){ return $value == true ? ["DeliveryType" => "First"] : []; },
        ];

        // Convert names to UpperCamelCase
        $processedParameters = [];
        foreach ($parameters as $key => $value) {
            $newKey = mb_strtoupper(mb_substr($key, 0, 1)) . mb_substr($key, 1);
            $processedParameters[$newKey] = $value;
        }

        // Convert names 
        foreach ($namesToConvert as $key => $func) {
            if (array_key_exists($key, $processedParameters) ) {
                $value = $processedParameters[$key];
                unset($processedParameters[$key]);
                $processedParameters = array_merge($processedParameters, $func($value));
            }
        }

        return $processedParameters;
    }

}



