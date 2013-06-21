<?php namespace Softlabs\Docmail;

use \Config as Config;
use \Exception as Exception;
use \Validator as Validator;

require_once("Docmailing/Docmailing.php");

class DocmailAPI {

    private static $validateOnly = false;

    /**
     * Default option values
     *
     * @var array
     */
    private static $defaults = [
        "timeout"           => 240,
        "DocumentType"      => "A4Letter",
        "ProductType"       => "A4Letter",
        "IsMono"            => true,
        "IsDuplex"          => false,
        "DeliveryType"      => "Second",
        "AddressNameFormat" => "Full Name",
        "TestMode"          => true,
    ];

    /**
     * Validation rules for option items that are always required
     *
     * @var array
     */
    private static $validationRules = array(
        'Username'            => 'required|max:100',
        'Password'            => 'required|max:100',
        'wsdl_test'           => 'required|max:100',
        'wsdl_live'           => 'required|max:100',
        'timeout'             => 'required|max:100',
        "CustomerApplication" => "max:50",
        "TestMode"            => "required",
    );

    /**
     * Validation messages for option items that are always required
     *
     * @var array
     */
    private static $validationMessages = array(
        'Username'            => 'Username is required',
        'Password'            => 'Password is required',
        'wsdl_test'           => 'Test WSDL is required',
        'wsdl_live'           => 'Live WSDL is required',
        'timeout'             => 'Timeout is required',
        "CustomerApplication" => "CustomerApplication should contain less than 50 characters",
        "TestMode"            => "TestMode is Required",
    );

    // Single API call methods

    /**
     * CreateMailing API call
     *
     * @param  array   $options 
     * @return string  Mailing GUID
     */
    public static function CreateMailing($options = []) {

        $messages = array(
            'MailingName'       => 'MailingName is required',
            'IsMono'            => 'IsMono is required',
            'IsDuplex'          => 'IsDuplex is required',
            'DeliveryType'      => 'DeliveryType is required',
            'AddressNameFormat' => 'AddressNameFormat is required',
            'ProductType'       => 'ProductType is required',

        );

        $rules = array(
            'MailingName'       => 'required',
            'IsMono'            => 'required',
            'IsDuplex'          => 'required',
            'DeliveryType'      => 'required',
            'AddressNameFormat' => 'required',
            'ProductType'       => 'required',
        );

        $result = self::apiCall("CreateMailing", $options);
        $mailingGUID = self::GetFld($result["CreateMailingResult"],"MailingGUID");
        return $mailingGUID;

    }

    /**
     * GetStatus API call
     *
     * @param  array   $options 
     * @return string  Status
     */
    public static function GetStatus($options) {

        $messages = array(
            'MailingGUID' => 'MailingGUID is required',
        );

        $rules = array(
            'MailingGUID'     => 'required',
        );

        if (is_array($options) == false) {
            $options = ["MailingGUID" => $options];
        }

        $result = self::apiCall("GetStatus", $options, $rules, $messages);
        $status = self::GetFld($result["GetStatusResult"],"Status");
        return $status;
    }

    /**
     * AddAddress API call
     *
     * @param  array   $options 
     * @return bool  True is successful
     */
    public static function AddAddress($options) {

        $messages = array(
            'MailingGUID' => 'MailingGUID is required',
            'LastName' => 'LastName is required',
            'Address1' => 'Address1 is required',
            'PostCode' => 'PostCode is required',
        );

        $rules = array(
            'MailingGUID'     => 'required',
            'LastName' => 'required',
            'Address1' => 'required',
            'PostCode' => 'required',
        );

        $result = self::apiCall("AddAddress", $options, $rules, $messages);
        $success = self::GetFld($result["AddAddressResult"],"Success");
        return $success === 'True';
    }

    /**
     * AddTemplateFile API call
     *
     * @param  array   $options 
     * @return string  Template GUID
     */
    public static function AddTemplateFile($options) {

        $messages = array(
            'MailingGUID' => 'MailingGUID is required',
            "FileData" => 'FileData is required',
            "DocumentType" => 'DocumentType is required',
            "FileName" => 'FileName is required',
            "TemplateName" => 'TemplateName is required',
        );

        $rules = array(
            'MailingGUID'     => 'required',
            "FileData" => 'required',
            "DocumentType" => 'required',
            "FileName" => 'required',
            "TemplateName" => 'required',
        );

        if (array_key_exists('FilePath', $options)) {
            $hdl = fopen($options['FilePath'], "rb");
            $content = base64_encode(fread($hdl, filesize($options['FilePath'])));
            fclose($hdl);

            $options['FileData'] = $content;
            $pathinfo = pathinfo($options['FilePath']);
            $options['FileName'] = $pathinfo['filename'] . ( $pathinfo['extension'] === null ? "" : "." . $pathinfo['extension']);

            unset($options['FilePath']);
        }

        $result = self::apiCall("AddTemplateFile", $options, $rules, $messages);

        $templateGUID = self::GetFld($result["AddTemplateFileResult"],"TemplateGUID");
        return $templateGUID;
    }

    /**
     * Make a Docmal API call
     *
     * @param  string  $func
     * @param  array   $options Option array that had been passed to the caller method
     * @param  array   $rules Validation rules for Validator
     * @param  array   $messages Validation messages for Validator
     * @return array   Result of the API call
     */
    public static function apiCall($func, $options, $rules = [], $messages = [])
    {

        // Merge default values into $options array
        $options = self::expandOptions($options, $rules);

        // Validate options
        self::validateOptions($options, $rules, $messages);

        if (self::$validateOnly) {
            return true;
        }

        $client = new \nusoap_client($options['TestMode'] ? $options['wsdl_test'] : $options['wsdl_live'], true);

        // Increase soap client timeout
        $client->timeout = $options['timeout'];
        // Increase php script server timeout
        set_time_limit($options['timeout']);


        $result = $client->call($func, $options);

        // var_dump($result);

        self::CheckError($result[$func . "Result"]);   //parse & check error fields from result as described above

        return $result;

    }

    // Low level methods

    /**
     * Validates options against API functions.
     *
     * @param  array  $options
     * @param  array  $rules
     * @return array
     */
    public static function validateCall($funcs, $options) {
        self::$validateOnly = true;
        foreach ($funcs as $func) {
            self::$func($options);
        }
        self::$validateOnly = false;
    }

    /**
     * Validates options.
     *
     * @param  array  $options
     * @param  array  $rules
     * @return array
     */
    private static function validateOptions($options, $rules, $messages) {

        // Add default validation messages to $messages parameter array
        $messages = $messages + self::$validationMessages;

        // Validate options against rules
        $validator = Validator::make($options, $rules, $messages);
        if ($validator->fails()) {
            $messages = $validator->messages();
            throw new Exception("Validation error: " . print_r($messages->all(), true), 1);
        }
    }


    /**
     * Get options and try to add default values if required item is missing.
     *
     * @param  array  $options
     * @param  array  $rules
     * @return array
     */
    private static function expandOptions($options, $rules) {

        // Add default validation rules to $rules parameter array
        $rules = $rules + self::$validationRules;

        foreach ($rules as $key => $ruleString) {

            $$key = (array_key_exists($key, $options) ? $options[$key] : Config::get('Softlabs/docmail.' . $key));

            if ($$key === null and array_key_exists($key, self::$defaults)) {
                $$key = self::$defaults[$key];
            }

            if ($$key === null) {
                unset($options[$key]);
            } else {
                $options[$key] = $$key;
            }

        }

        return $options;
    }

    // These functions are copied from the example code

    private static function CheckError($Res){
        // print "Checking for errors<br>";
        //check for  the keys 'Error code', 'Error code string' and 'Error message' to test/report errors
        $errCode = self::GetFld($Res,"Error code");
        if ($errCode) {
            $errName = self::GetFld($Res,"Error code string");
            $errMsg = self::GetFld($Res,"Error message");
            // print 'ErrCode '; print_r($errCode); print "<br>";
            // print 'ErrName '; print_r($errName); print "<br>";
            // print 'ErrMsg '; print_r($errMsg); print "<br>";

            var_dump($Res);

            throw new Exception("Softlabs Docmail error - Code: " . $errCode . "; Message:" . $errName." - ".$errMsg);

        }
        // print "No error<br>";
    }

    private static function GetFld($FldList,$FldName){
        // calls return a multi-line string structured as :
        // [KEY]: [VALUE][carriage return][line feed][KEY]: [VALUE][carriage return][line feed][KEY]: [VALUE][carriage return][line feed][KEY]: [VALUE]
        //explode lines
        //print "Looking for Field '".$FldName."'<br>";
        $lines = explode("\n",$FldList);
        for ( $lineCounter=0;$lineCounter < count($lines); $lineCounter+=1){
            //explode field/value
            $fields = explode(":",$lines[$lineCounter]);
            //find matching field name
            if ($fields[0]==$FldName)   {
                //print "'".$FldName."' Value: ".ltrim($fields[1], " ")."<br>";
                return ltrim($fields[1], " "); //return value
            }
        }
        //print "'".$FldName."' NOT found<br>";
    }


}