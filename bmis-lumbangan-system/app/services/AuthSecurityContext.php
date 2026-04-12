<?php

class AuthSecurityContext
{
    public $scope;
    public $identifier;
    public $ipAddress;
    public $userAgent;
    public $captchaToken;
    public $captchaAction;
    public $lockoutKey;

    public function __construct(
        $scope,
        $identifier,
        $ipAddress,
        $userAgent = 'unknown',
        $captchaToken = '',
        $captchaAction = '',
        $lockoutKey = null
    ) {
        $this->scope = (string) $scope;
        $this->identifier = trim((string) $identifier);
        $this->ipAddress = (string) $ipAddress;
        $this->userAgent = (string) $userAgent;
        $this->captchaToken = trim((string) $captchaToken);
        $this->captchaAction = $captchaAction !== '' ? (string) $captchaAction : (string) $scope;
        $this->lockoutKey = $lockoutKey === null ? null : trim((string) $lockoutKey);
    }

    public static function forLogin($username, $ipAddress, $userAgent = 'unknown', $captchaToken = '')
    {
        return new self('login', $username, $ipAddress, $userAgent, $captchaToken, 'login', $username);
    }

    public static function forPasswordResetRequest($email, $ipAddress, $userAgent = 'unknown', $captchaToken = '')
    {
        return new self('password_reset_request', $email, $ipAddress, $userAgent, $captchaToken, 'password_reset_request');
    }

    public static function forRegistrationRequest($identifier, $ipAddress, $userAgent = 'unknown', $captchaToken = '')
    {
        return new self('registration_request', $identifier, $ipAddress, $userAgent, $captchaToken, 'registration_request');
    }

    public static function forRegistrationVerify($identifier, $ipAddress, $userAgent = 'unknown', $captchaToken = '')
    {
        return new self('registration_verify', $identifier, $ipAddress, $userAgent, $captchaToken, 'registration_verify');
    }

    public static function forPasswordResetVerify($email, $ipAddress, $userAgent = 'unknown', $captchaToken = '')
    {
        return new self('password_reset_verify', $email, $ipAddress, $userAgent, $captchaToken, 'password_reset_verify');
    }

    public static function forPasswordResetSubmit($token, $ipAddress, $userAgent = 'unknown', $captchaToken = '')
    {
        return new self(
            'password_reset_submit',
            self::hashTokenIdentifier($token),
            $ipAddress,
            $userAgent,
            $captchaToken,
            'password_reset_submit'
        );
    }

    public function getScopedIdentifier()
    {
        $identifier = $this->identifier !== '' ? strtolower($this->identifier) : 'anonymous';
        return $this->scope . ':' . $identifier;
    }

    public function hasIdentifier()
    {
        return $this->identifier !== '';
    }

    public function hasLockoutKey()
    {
        return $this->lockoutKey !== null && $this->lockoutKey !== '';
    }

    public static function hashTokenIdentifier($token)
    {
        $token = trim((string) $token);
        if ($token === '') {
            return '';
        }

        return substr(hash('sha256', $token), 0, 24);
    }
}
