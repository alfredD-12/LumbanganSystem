<?php

class FakeRegistrationMailSender implements RegistrationMailSenderInterface
{
    public $shouldSucceed = true;
    public $sent = [];

    public function sendVerificationCode($email, $firstName, $code)
    {
        $this->sent[] = [
            'email' => $email,
            'first_name' => $firstName,
            'code' => $code,
        ];

        return $this->shouldSucceed;
    }
}
