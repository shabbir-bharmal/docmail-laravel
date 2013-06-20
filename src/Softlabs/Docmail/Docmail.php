<?php namespace Softlabs\Docmail;

use \Config as Config;

require_once("Docmailing/Docmailing.php");

class Docmail {

    public static $description = "I am the docmail package 3.";

    public static function sendLetter()
    {

        $docmailing = new \Docmailing();

        $params = [
            "sUsr"              => Config::get("Softlabs/docmail.username"),
            "sPwd"              => Config::get("Softlabs/docmail.password"),
            "wsdl"              => Config::get('Softlabs/docmail.wsdl'),

            "sMailingName"      => "Test03",
            "NameTitle"         => "Bbb",
            "FirstName"         => "Ccc",
            "LastName"          => "Ddd",
            "sAddress1"         => "Eee",
            "sAddress2"         => "Fff",
            "sAddress3"         => "Ggg",
            "sAddress4"         => "Hhh",
            "sAddress5"         => "Iii",
            "sPostCode"         => "Jjj",

            "sTemplateFileName" => "Sample.pdf",
            "TemplateFile"      => "../Sample.pdf",
            "bColour"           => "true",
            "bDuplex"           => "true",

        ];

var_dump(file_exists($params['TemplateFile']));

        // set up docmail object
        $docmailing->sUsr              = $params['sUsr'];
        $docmailing->sPwd              = $params['sPwd'];
        $docmailing->sMailingName      = $params['sMailingName'];
        $docmailing->NameTitle         = $params['NameTitle'];
        $docmailing->FirstName         = $params['FirstName'];
        $docmailing->LastName          = $params['LastName'];
        $docmailing->sAddress1         = $params['sAddress1'];
        $docmailing->sAddress2         = $params['sAddress2'];
        $docmailing->sAddress3         = $params['sAddress3'];
        $docmailing->sAddress4         = $params['sAddress4'];
        $docmailing->sAddress5         = $params['sAddress5'];
        $docmailing->sPostCode         = $params['sPostCode'];
        $docmailing->sTemplateFileName = $params['sTemplateFileName'];
        $docmailing->TemplateFile      = $params['TemplateFile'];
        $docmailing->bColour           = $params['bColour'];
        $docmailing->bDuplex           = $params['bDuplex'];
        $docmailing->wsdl              = $params['wsdl'];

        // send the invoice to docmail returning the guid and the status
        list($MailingGUID, $status) = $docmailing->senddocmail();

        unset($docmailing);

        print_r($MailingGUID);
        print_r($status);


        return [$params];
    }
}



