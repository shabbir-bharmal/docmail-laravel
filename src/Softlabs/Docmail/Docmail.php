<?php namespace Softlabs\Docmail;

use \Config as Config;
use \Exception as Exception;
use Softlabs\Docmail\DocmailAPI as DocmailAPI;

class Docmail {

    private $mailingGUID;
    private $templateGUID;

    // Complex methods (multiple API calls)

    public function sendToSingelAddress($options = []) {

        DocmailAPI::validateCall(['CreateMailing'], $options);
        $this->mailingGUID = DocmailAPI::CreateMailing();
        $options["MailingGUID"] = $this->mailingGUID;

        DocmailAPI::validateCall(['AddAddress', 'AddTemplateFile'], $options);
        $result = DocmailAPI::AddAddress($options);

        $this->templateGUID = DocmailAPI::AddTemplateFile($options);

    }

    public function getMailingGUID() {
        return $this->mailingGUID;
    }

    public function getTemplateGUID() {
        return $this->templateGUID;
    }

}



