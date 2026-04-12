<?php

interface RegistrationMailSenderInterface
{
    public function sendVerificationCode($email, $firstName, $code);
}
