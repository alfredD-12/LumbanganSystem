<?php

interface PasswordResetMailerInterface
{
    public function sendResetCode(array $user, $code);
}
