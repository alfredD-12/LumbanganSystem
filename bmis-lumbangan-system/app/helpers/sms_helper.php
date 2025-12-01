<?php
/**
 * SMS Helper using Twilio REST API (cURL)
 * Functions for sending SMS notifications - No SDK/Composer required
 */

require_once __DIR__ . '/../config/twilio_config.php';

class SMSHelper {
    private $accountSid;
    private $authToken;
    private $fromNumber;
    
    public function __construct() {
        $this->accountSid = TWILIO_ACCOUNT_SID;
        $this->authToken = TWILIO_AUTH_TOKEN;
        $this->fromNumber = TWILIO_PHONE_NUMBER;
    }
    
    /**
     * Send SMS to a single recipient using Twilio REST API
     * 
     * @param string $to Phone number in E.164 format (e.g., +639171234567)
     * @param string $message SMS message content (max 160 characters recommended)
     * @return array ['success' => bool, 'message' => string, 'sid' => string|null]
     */
    public function sendSMS($to, $message) {
        // Validate phone number format
        $to = $this->formatPhoneNumber($to);
        
        if (!$to) {
            return [
                'success' => false,
                'message' => 'Invalid phone number format. Use E.164 format (e.g., +639171234567)',
                'sid' => null
            ];
        }
        
        // Debug mode - log instead of sending
        if (SMS_DEBUG_MODE) {
            error_log("SMS DEBUG - To: $to, Message: $message");
            return [
                'success' => true,
                'message' => 'SMS logged (debug mode)',
                'sid' => 'DEBUG_' . uniqid()
            ];
        }
        
        // Check if SMS is enabled
        if (!SMS_ENABLED) {
            return [
                'success' => false,
                'message' => 'SMS sending is disabled. Enable in twilio_config.php',
                'sid' => null
            ];
        }
        
        // Check if cURL is available
        if (!function_exists('curl_init')) {
            return [
                'success' => false,
                'message' => 'cURL extension is not enabled in PHP',
                'sid' => null
            ];
        }
        
        try {
            // Twilio Messages API endpoint
            $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json";
            
            // Data to send
            $data = http_build_query([
                'From' => $this->fromNumber,
                'To'   => $to,
                'Body' => $message,
            ]);
            
            // Initialize cURL
            $ch = curl_init($url);
            
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, "{$this->accountSid}:{$this->authToken}");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            
            // Execute request
            $response = curl_exec($ch);
            
            // Check for cURL errors
            if ($response === false) {
                $error = curl_error($ch);
                curl_close($ch);
                error_log("Twilio cURL error: $error");
                return [
                    'success' => false,
                    'message' => "Failed to send SMS: $error",
                    'sid' => null
                ];
            }
            
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // Parse JSON response
            $responseData = json_decode($response, true);
            
            // Check if request was successful (HTTP 200 or 201)
            if ($httpCode >= 200 && $httpCode < 300) {
                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'sid' => $responseData['sid'] ?? 'unknown'
                ];
            } else {
                // Twilio error response
                $errorMessage = $responseData['message'] ?? 'Unknown error';
                error_log("Twilio API error (HTTP $httpCode): $errorMessage");
                return [
                    'success' => false,
                    'message' => "Failed to send SMS: $errorMessage",
                    'sid' => null
                ];
            }
        } catch (Exception $e) {
            error_log('Twilio SMS error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send SMS: ' . $e->getMessage(),
                'sid' => null
            ];
        }
    }
    
    /**
     * Send SMS to multiple recipients
     * 
     * @param array $recipients Array of phone numbers
     * @param string $message SMS message content
     * @return array ['success' => int, 'failed' => int, 'results' => array]
     */
    public function sendBulkSMS($recipients, $message) {
        $results = [
            'success' => 0,
            'failed' => 0,
            'results' => []
        ];
        
        foreach ($recipients as $recipient) {
            $result = $this->sendSMS($recipient, $message);
            $results['results'][] = [
                'phone' => $recipient,
                'status' => $result
            ];
            
            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
        }
        
        return $results;
    }
    
    /**
     * Format phone number to E.164 format
     * Converts Philippine mobile numbers to international format
     * 
     * @param string $phone Phone number
     * @return string|false Formatted phone number or false if invalid
     */
    private function formatPhoneNumber($phone) {
        // Remove spaces, dashes, parentheses
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
        
        // If already in E.164 format (+639...)
        if (preg_match('/^\+63\d{10}$/', $phone)) {
            return $phone;
        }
        
        // If starts with 09 (Philippine mobile format)
        if (preg_match('/^09\d{9}$/', $phone)) {
            return '+63' . substr($phone, 1);
        }
        
        // If starts with 639
        if (preg_match('/^639\d{9}$/', $phone)) {
            return '+' . $phone;
        }
        
        // If starts with 9 (without 0)
        if (preg_match('/^9\d{9}$/', $phone)) {
            return '+63' . $phone;
        }
        
        // If international format without +
        if (preg_match('/^\d{11,15}$/', $phone)) {
            return '+' . $phone;
        }
        
        return false;
    }
    
    /**
     * Send document approval notification
     */
    public function notifyDocumentApproved($phone, $documentType, $requestId) {
        $message = "Your {$documentType} request (#{$requestId}) has been APPROVED. You can now claim it at the Barangay Office. - Brgy. Lumbangan";
        return $this->sendSMS($phone, $message);
    }
    
    /**
     * Send document release notification
     */
    public function notifyDocumentReleased($phone, $documentType, $requestId) {
        $message = "Your {$documentType} (#{$requestId}) is now ready for pickup at Barangay Lumbangan Office. Please bring a valid ID. Thank you!";
        return $this->sendSMS($phone, $message);
    }
    
    /**
     * Send complaint status update
     */
    public function notifyComplaintUpdate($phone, $complaintTitle, $status) {
        $message = "Update on your complaint '{$complaintTitle}': Status changed to {$status}. - Barangay Lumbangan";
        return $this->sendSMS($phone, $message);
    }
    
    /**
     * Send announcement to residents
     */
    public function sendAnnouncement($phone, $title, $excerpt) {
        $message = "Barangay Announcement: {$title}\n{$excerpt}\nFor details, visit the barangay office or website.";
        return $this->sendSMS($phone, $message);
    }
}

/**
 * Quick helper functions
 */

function send_sms($to, $message) {
    $sms = new SMSHelper();
    return $sms->sendSMS($to, $message);
}

function send_bulk_sms($recipients, $message) {
    $sms = new SMSHelper();
    return $sms->sendBulkSMS($recipients, $message);
}
