<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/RateLimitService.php';
require_once __DIR__ . '/../models/AccountLockoutService.php';
require_once __DIR__ . '/../models/LoginAttemptLogger.php';
require_once __DIR__ . '/../models/AdminAlertService.php';
require_once __DIR__ . '/AuthSecurityContext.php';
require_once __DIR__ . '/SystemClock.php';
require_once __DIR__ . '/GoogleCaptchaVerifier.php';
require_once __DIR__ . '/SecurityAlertServiceInterface.php';

class AuthSecurityService
{
    private $rateLimitService;
    private $accountLockoutService;
    private $loginAttemptLogger;
    private $alertService;
    private $captchaVerifier;
    private $clock;

    public function __construct(
        $db,
        LoginAttemptLogger $loginAttemptLogger = null,
        RateLimitService $rateLimitService = null,
        AccountLockoutService $accountLockoutService = null,
        SecurityAlertServiceInterface $alertService = null,
        CaptchaVerifierInterface $captchaVerifier = null,
        ClockInterface $clock = null
    ) {
        $this->clock = $clock ?: new SystemClock();
        $this->loginAttemptLogger = $loginAttemptLogger ?: new LoginAttemptLogger($db, $this->clock);
        $this->rateLimitService = $rateLimitService ?: new RateLimitService($db, $this->clock);
        $this->accountLockoutService = $accountLockoutService ?: new AccountLockoutService($db, $this->clock);
        $this->alertService = $alertService ?: new AdminAlertService($db, $this->clock);
        $this->captchaVerifier = $captchaVerifier ?: new GoogleCaptchaVerifier();
    }

    public function guard(AuthSecurityContext $context)
    {
        if (!$this->isEnabled()) {
            return ['allowed' => true, 'captcha_required' => false];
        }

        $policy = $this->getPolicy($context->scope);
        $ipCheck = $this->rateLimitService->checkIpRateLimit($context->ipAddress);
        if (!empty($ipCheck['blocked'])) {
            return [
                'allowed' => false,
                'response' => [
                    'success' => false,
                    'code' => 'rate_limit_exceeded',
                    'retry_after' => (int) ($ipCheck['retry_after'] ?? 60),
                    'message' => $policy['ip_limit_message'],
                ],
            ];
        }

        if (!empty($policy['lockout']) && $context->hasLockoutKey()) {
            $lockCheck = $this->accountLockoutService->isAccountLocked($context->lockoutKey);
            if (!empty($lockCheck['locked'])) {
                return [
                    'allowed' => false,
                    'response' => [
                        'success' => false,
                        'code' => 'account_locked',
                        'retry_after' => (int) ($lockCheck['retry_after'] ?? 900),
                        'message' => $policy['lockout_message'],
                    ],
                ];
            }
        }

        if ($context->hasIdentifier() && $policy['identifier_limit'] > 0) {
            $attemptCount = $this->countWindowEvents(
                $context,
                $policy['identifier_metric'],
                $policy['identifier_window_minutes']
            );

            if ($attemptCount >= $policy['identifier_limit']) {
                $retryAfter = $this->loginAttemptLogger->getIdentifierWindowRetryAfter(
                    $context->getScopedIdentifier(),
                    $policy['identifier_window_minutes'],
                    $policy['identifier_metric'],
                    $context->scope
                );

                return [
                    'allowed' => false,
                    'response' => [
                        'success' => false,
                        'code' => 'rate_limit_exceeded',
                        'retry_after' => (int) $retryAfter,
                        'message' => $policy['identifier_limit_message'],
                    ],
                ];
            }
        }

        $captchaRequired = $context->hasIdentifier() && $this->shouldRequireCaptcha($context, $policy);
        if ($captchaRequired && !$this->captchaVerifier->verify($context->captchaToken, $context->ipAddress, $context->captchaAction)) {
            return [
                'allowed' => false,
                'response' => [
                    'success' => false,
                    'code' => 'captcha_required',
                    'message' => 'Additional verification is required. Please complete the reCAPTCHA challenge.',
                ],
            ];
        }

        return ['allowed' => true, 'captcha_required' => $captchaRequired];
    }

    public function recordFailure(AuthSecurityContext $context, $failureReason)
    {
        if (!$this->isEnabled()) {
            return [
                'locked' => false,
                'retry_after' => 0,
                'attempts_remaining' => 0,
            ];
        }

        $this->rateLimitService->recordFailure($context->ipAddress);
        $this->loginAttemptLogger->logAttempt(
            $context->identifier,
            $context->ipAddress,
            $context->userAgent,
            'failure',
            $failureReason,
            $context->scope
        );

        $state = [
            'locked' => false,
            'retry_after' => 0,
            'attempts_remaining' => 0,
        ];

        if ($context->hasLockoutKey()) {
            $state = $this->accountLockoutService->recordFailure($context->lockoutKey);
        }

        $target = $context->hasLockoutKey() ? $context->lockoutKey : $context->getScopedIdentifier();
        $this->alertService->evaluateAndSend(
            $context->ipAddress,
            $target,
            $context->scope,
            $this->loginAttemptLogger,
            !empty($state['locked'])
        );

        return $state;
    }

    public function recordSuccess(AuthSecurityContext $context)
    {
        if (!$this->isEnabled()) {
            return;
        }

        $this->loginAttemptLogger->logAttempt(
            $context->identifier,
            $context->ipAddress,
            $context->userAgent,
            'success',
            null,
            $context->scope
        );

        if ($context->hasLockoutKey()) {
            $this->accountLockoutService->recordSuccess($context->lockoutKey);
        }
    }

    public function isEnabled()
    {
        return BRUTE_FORCE_PROTECTION_ENABLED === true;
    }

    private function shouldRequireCaptcha(AuthSecurityContext $context, array $policy)
    {
        if (!empty($policy['captcha_always'])) {
            return true;
        }

        $identifierCount = $this->countWindowEvents(
            $context,
            $policy['captcha_metric'],
            $policy['captcha_window_minutes']
        );

        $ipCount = $this->countIpWindowEvents(
            $context,
            $policy['captcha_metric'],
            $policy['captcha_window_minutes']
        );

        return $identifierCount >= $policy['captcha_trigger_threshold']
            || $ipCount >= $policy['captcha_trigger_threshold'];
    }
    private function countWindowEvents(AuthSecurityContext $context, $metric, $minutes)
    {
        if ($metric === 'failures_since_success') {
            return $this->loginAttemptLogger->countRecentFailuresSinceLastSuccessByIdentifier(
                $context->getScopedIdentifier(),
                $minutes,
                $context->scope
            );
        }

        if ($metric === 'successes') {
            return $this->loginAttemptLogger->countRecentSuccessesByIdentifier(
                $context->getScopedIdentifier(),
                $minutes,
                $context->scope
            );
        }

        if ($metric === 'attempts') {
            return $this->loginAttemptLogger->countRecentAttemptsByIdentifier(
                $context->getScopedIdentifier(),
                $minutes,
                $context->scope
            );
        }

        return $this->loginAttemptLogger->countRecentFailuresByIdentifier(
            $context->getScopedIdentifier(),
            $minutes,
            $context->scope
        );
    }

    private function countIpWindowEvents(AuthSecurityContext $context, $metric, $minutes)
    {
        if ($metric === 'failures_since_success') {
            return $this->loginAttemptLogger->countRecentFailuresSinceLastSuccessByIp(
                $context->ipAddress,
                $minutes,
                $context->scope
            );
        }

        if ($metric === 'successes') {
            return $this->loginAttemptLogger->countRecentSuccessesByIp(
                $context->ipAddress,
                $minutes,
                $context->scope
            );
        }

        if ($metric === 'attempts') {
            return $this->loginAttemptLogger->countRecentAttemptsByIp(
                $context->ipAddress,
                $minutes,
                $context->scope
            );
        }

        return $this->loginAttemptLogger->countRecentFailuresByIp(
            $context->ipAddress,
            $minutes,
            $context->scope
        );
    }

    private function getPolicy($scope)
    {
        $policies = [
            'login' => [
                'identifier_limit' => ACCOUNT_RATE_LIMIT_MAX_FAILURES,
                'identifier_metric' => 'failures',
                'identifier_window_minutes' => ACCOUNT_RATE_LIMIT_WINDOW_MINUTES,
                'identifier_limit_message' => 'Too many failed attempts for this account. Please try again later.',
                'captcha_metric' => 'failures',
                'captcha_window_minutes' => ACCOUNT_RATE_LIMIT_WINDOW_MINUTES,
                'captcha_trigger_threshold' => CAPTCHA_TRIGGER_THRESHOLD,
                'ip_limit_message' => 'Too many login attempts from your network. Please try again shortly.',
                'lockout' => true,
                'lockout_message' => 'Too many failed sign-in attempts. This account is temporarily locked.',
            ],
            'password_reset_request' => [
                'identifier_limit' => PASSWORD_RESET_REQUEST_MAX_ATTEMPTS,
                'identifier_metric' => 'successes',
                'identifier_window_minutes' => PASSWORD_RESET_REQUEST_WINDOW_MINUTES,
                'identifier_limit_message' => 'Too many password reset requests. Please try again later.',
                'captcha_metric' => 'successes',
                'captcha_window_minutes' => PASSWORD_RESET_REQUEST_WINDOW_MINUTES,
                'captcha_trigger_threshold' => PASSWORD_RESET_REQUEST_CAPTCHA_TRIGGER_THRESHOLD,
                'captcha_always' => false,
                'ip_limit_message' => 'Too many password reset attempts from your network. Please try again shortly.',
                'lockout' => false,
                'lockout_message' => '',
            ],
            'registration_request' => [
                'identifier_limit' => REGISTRATION_REQUEST_MAX_ATTEMPTS,
                'identifier_metric' => 'successes',
                'identifier_window_minutes' => REGISTRATION_REQUEST_WINDOW_MINUTES,
                'identifier_limit_message' => 'Too many registration verification requests. Please try again later.',
                'captcha_metric' => 'successes',
                'captcha_window_minutes' => REGISTRATION_REQUEST_WINDOW_MINUTES,
                'captcha_trigger_threshold' => 0,
                'captcha_always' => REGISTRATION_REQUEST_CAPTCHA_ALWAYS,
                'ip_limit_message' => 'Too many registration attempts from your network. Please try again shortly.',
                'lockout' => false,
                'lockout_message' => '',
            ],
            'registration_verify' => [
                'identifier_limit' => REGISTRATION_VERIFY_MAX_FAILURES,
                'identifier_metric' => 'failures_since_success',
                'identifier_window_minutes' => REGISTRATION_VERIFY_WINDOW_MINUTES,
                'identifier_limit_message' => 'Too many verification attempts. Please request a new code or try again later.',
                'captcha_metric' => 'failures_since_success',
                'captcha_window_minutes' => REGISTRATION_VERIFY_WINDOW_MINUTES,
                'captcha_trigger_threshold' => REGISTRATION_VERIFY_CAPTCHA_TRIGGER_THRESHOLD,
                'captcha_always' => false,
                'ip_limit_message' => 'Too many verification attempts from your network. Please try again shortly.',
                'lockout' => false,
                'lockout_message' => '',
            ],
            'password_reset_verify' => [
                'identifier_limit' => PASSWORD_RESET_VERIFY_MAX_FAILURES,
                'identifier_metric' => 'failures',
                'identifier_window_minutes' => PASSWORD_RESET_VERIFY_WINDOW_MINUTES,
                'identifier_limit_message' => 'Too many code verification attempts. Please try again later.',
                'captcha_metric' => 'failures',
                'captcha_window_minutes' => PASSWORD_RESET_VERIFY_WINDOW_MINUTES,
                'captcha_trigger_threshold' => PASSWORD_RESET_VERIFY_CAPTCHA_TRIGGER_THRESHOLD,
                'captcha_always' => false,
                'ip_limit_message' => 'Too many verification attempts from your network. Please try again shortly.',
                'lockout' => false,
                'lockout_message' => '',
            ],
            'password_reset_submit' => [
                'identifier_limit' => PASSWORD_RESET_SUBMIT_MAX_FAILURES,
                'identifier_metric' => 'failures',
                'identifier_window_minutes' => PASSWORD_RESET_SUBMIT_WINDOW_MINUTES,
                'identifier_limit_message' => 'Too many password reset attempts. Please try again later.',
                'captcha_metric' => 'failures',
                'captcha_window_minutes' => PASSWORD_RESET_SUBMIT_WINDOW_MINUTES,
                'captcha_trigger_threshold' => PASSWORD_RESET_SUBMIT_CAPTCHA_TRIGGER_THRESHOLD,
                'captcha_always' => false,
                'ip_limit_message' => 'Too many password reset attempts from your network. Please try again shortly.',
                'lockout' => false,
                'lockout_message' => '',
            ],
        ];

        return $policies[$scope] ?? $policies['login'];
    }
}
