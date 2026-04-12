<?php

interface SecurityAlertServiceInterface
{
    public function evaluateAndSend($ipAddress, $target, $scope, LoginAttemptLogger $logger, $justLocked = false);
}
