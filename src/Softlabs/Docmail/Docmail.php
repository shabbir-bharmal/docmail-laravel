<?php namespace Softlabs\Docmail;

use \Config as Config;
use \Exception as Exception;
use Softlabs\Docmail\DocmailAPI as DocmailAPI;

class Docmail {

    private $mailingGUID;
    private $templateGUID;

    // Complex methods (multiple API calls)

    public static function sendToSingelAddress($options = []) {

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

}



