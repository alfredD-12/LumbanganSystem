<?php

class FakePasswordResetMailer implements PasswordResetMailerInterface
{
    public $shouldSucceed = true;
    public $sent = [];

    public function sendResetCode(array $user, $code)
    {
        $this->sent[] = [
            'user' => $user,
            'code' => $code,
        ];

        return $this->shouldSucceed;
    }
}
