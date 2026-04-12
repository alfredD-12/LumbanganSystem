<?php

class FakeSecurityAlertService implements SecurityAlertServiceInterface
{
    public $calls = [];

    public function evaluateAndSend($ipAddress, $target, $scope, LoginAttemptLogger $logger, $justLocked = false)
    {
        $this->calls[] = [
            'ip_address' => $ipAddress,
            'target' => $target,
            'scope' => $scope,
            'just_locked' => (bool) $justLocked,
        ];
    }
}
