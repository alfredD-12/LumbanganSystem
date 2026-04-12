<?php

class FakeRegistrationSmsSender implements RegistrationSmsSenderInterface
{
    public $shouldSucceed = true;
    public $sent = [];

    public function sendVerificationCode($mobile, $code)
    {
        $this->sent[] = [
            'mobile' => $mobile,
            'code' => $code,
        ];

        return $this->shouldSucceed;
    }
}
