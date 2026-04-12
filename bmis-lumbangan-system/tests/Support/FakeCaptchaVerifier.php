<?php

class FakeCaptchaVerifier implements CaptchaVerifierInterface
{
    public $defaultResult = true;
    public $calls = [];
    private $tokenResults = [];

    public function setTokenResult($token, $result)
    {
        $this->tokenResults[(string) $token] = (bool) $result;
    }

    public function verify($token, $ipAddress, $expectedAction = 'login')
    {
        $this->calls[] = [
            'token' => $token,
            'ip_address' => $ipAddress,
            'expected_action' => $expectedAction,
        ];

        if (array_key_exists((string) $token, $this->tokenResults)) {
            return $this->tokenResults[(string) $token];
        }

        if ((string) $token === '') {
            return false;
        }

        return $this->defaultResult;
    }
}
