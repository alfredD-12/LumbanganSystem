<?php

interface RegistrationSmsSenderInterface
{
    public function sendVerificationCode($mobile, $code);
}
