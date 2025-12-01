<?php
/**
 * Twilio SMS Configuration
 * Store your Twilio credentials here
 */

// Twilio Configuration
define('TWILIO_ACCOUNT_SID', getenv('TWILIO_ACCOUNT_SID') ?: ''); // Replace with your Account SID
define('TWILIO_AUTH_TOKEN', getenv('TWILIO_AUTH_TOKEN') ?: ''); // Replace with your Auth Token
define('TWILIO_PHONE_NUMBER', getenv('TWILIO_PHONE_NUMBER') ?: ''); // Replace with your Twilio phone number

// SMS Settings
define('SMS_ENABLED', true); // Set to true when ready to send SMS
define('SMS_DEBUG_MODE', false); // Set to false in production

/**
 * SETUP INSTRUCTIONS:
 * 
 * 1. Get your Twilio credentials from https://console.twilio.com/
 *    - Account SID
 *    - Auth Token
 *    - Twilio Phone Number
 * 
 * 2. For Trial Account:
 *    - Go to Account → Trial Numbers / Verified Caller IDs
 *    - Verify phone numbers that will receive SMS
 *    - You can only send to verified numbers during trial
 * 
 * 3. Replace the credentials above with your actual values OR
 *    Set environment variables (recommended for security):
 * 
 *    Windows (PowerShell):
 *    $env:TWILIO_ACCOUNT_SID="your_account_sid"
 *    $env:TWILIO_AUTH_TOKEN="your_auth_token"
 *    $env:TWILIO_PHONE_NUMBER="+1234567890"
 * 
 *    Linux/Mac (bash):
 *    export TWILIO_ACCOUNT_SID=your_account_sid
 *    export TWILIO_AUTH_TOKEN=your_auth_token
 *    export TWILIO_PHONE_NUMBER=+1234567890
 * 
 * 4. No need to install Twilio SDK - we use cURL directly
 * 
 * 5. Set SMS_ENABLED to true when ready to send real SMS
 */
