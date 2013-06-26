docmail-laravel
===============

This package contains two classes:

- Docmail
- DocmaiAPI

DocmailAPI class
----------------

This class allows making Docmail API calls. Every public function in DocmailAPI class is mapped to a single Docmail API call.

    $templateGUID = DocmailAPI::AddTemplateFile($options);

This code send a AddTemplateFile call to the API with parameters $option and results the template GUID.

Docmail class
-------------
Docmail class contains complex methods, not only single API calls. For example the following method Docmail::sendToSingelAddress creates a new mailing, adds an address and uploads a template file:

    public function sendToSingelAddress($options = []) {

        $this->mailingGUID = DocmailAPI::CreateMailing();
        $options["MailingGUID"] = $this->mailingGUID;

        $result = DocmailAPI::AddAddress($options);

        $this->templateGUID = DocmailAPI::AddTemplateFile($options);

    }

In your code you can combine the two classes, like:

            $dm = new SoftlabsDocmail();
            $dm->sendToSingelAddress([
                "Address1" => "address line 1",
                "FilePath" => "../sample.pdf",
            ]);
            $satus = DocmailAPI::GetStatus($dm->getMailingGUID());

API parameter defaults
----------------------

API call parameters can get its values from various sources (in ascending priority order):

- method parameters

        DocmailAPI::GetStatus($dm->getMailingGUID())

- docmail config file (/app/config/Softlabs/docmail.php)

        return array(

            'username'      => 'nevergivein',
            'password'      => 'nevergivein111',
            'wsdl'          => 'https://www.cfhdocmail.com/TestAPI2/DMWS.asmx?WSDL',

            'productType'   => "A4Letter",
            'printColor'    => false,
            'printDuplex'   => false,
            'deliveryType'  => "Standard",
            'despatchASAP'  => true,

        );

- defalut values set in DocmailAPI class

        private static $defaults = [
            "Username" => null,
            "Password" => null,
            "wsdl" => null,
            "timeout" => 240,
            "DocumentType" => "A4Letter"
        ];

Exaple code how to send a docmail
---------------------------------

        $options = [
            "LastName"     => "lastname",
            "Address1"     => "address line 1",
            "PostCode"     => "PostCode",
            "FilePath"     => "../sample.pdf",
            "TemplateName" => "Sample Template 01",
            "Submit"       => true,
        ];

        $result = SoftlabsDocmail::sendToSingelAddress($options);

