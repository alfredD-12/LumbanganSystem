<?php

require_once __DIR__ . '/../helpers/sms_helper.php';
require_once __DIR__ . '/RegistrationSmsSenderInterface.php';

class RegistrationSmsSender implements RegistrationSmsSenderInterface
{
    private $smsHelper;

    public function __construct($smsHelper = null)
    {
        $this->smsHelper = $smsHelper ?: new SMSHelper();
    }

    public function sendVerificationCode($mobile, $code)
    {
        if ($mobile === '') {
            return false;
        }

        $message = "Your Barangay Lumbangan verification code is: {$code}. Valid for 1 hour. Do not share this code.";
        $result = $this->smsHelper->sendSMS($mobile, $message);

        return !empty($result['success']);
    }
}
