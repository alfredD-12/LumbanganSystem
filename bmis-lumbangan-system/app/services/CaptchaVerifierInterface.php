<?php

interface CaptchaVerifierInterface
{
    public function verify($token, $ipAddress, $expectedAction = 'login');
}
