<?php


header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}


$input = json_decode(file_get_contents('php://input'), true);
$userMessage = trim($input['message'] ?? '');
$pageContext = $input['pageContext'] ?? null;

if ($userMessage === '') {
    echo json_encode(['error' => 'Empty message']);
    exit;
}

// Load API key from environment variable or shared config
if (file_exists(__DIR__ . '/../../.env')) {
    $env = parse_ini_file(__DIR__ . '/../../.env');
    $apiKey = $env['GEMINI_API_KEY'] ?? '';
} else {
    $apiKey = getenv('GEMINI_API_KEY') ?: '';
}

if (!$apiKey) {
    http_response_code(500);
    echo json_encode(['error' => 'API key not configured']);
    exit;
}


$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . urlencode($apiKey);


// Add page context to system instruction if available
$pageContextText = '';
if ($pageContext) {
    $pageContextText = "\n\nCURRENT PAGE CONTEXT:\n";
    $pageContextText .= "User is currently on: {$pageContext['pageName']}\n";
    $pageContextText .= "Page context: {$pageContext['context']}\n";
    $pageContextText .= "Provide relevant help based on the current page they're viewing.\n";
}

$systemInstruction = "You are the AI assistant for Barangay Lumbangan Information Management System (BMIS), Nasugbu, Batangas.

IMPORTANT RULES:
- Keep responses SHORT and conversational
- For simple greetings like 'hi', 'hello', 'kamusta', respond with a brief friendly greeting
- Only give detailed information when specifically asked
- Use simple Filipino/Taglish
- No bullet points or markdown formatting
- Maximum 2-3 sentences for simple questions
- If user is on a specific page, provide relevant help for that page" . $pageContextText . "

SYSTEM INFORMATION:
WEBSITE FEATURES:
- User Registration/Login: Residents can register and login to access services
- Dashboard: Personal dashboard showing pending requests, completed documents, and announcements
- Document Request System: Online application for various certificates
- Document Tracking: Users can track status of their requests (Pending, Approved, Released)
- Notification System: Real-time updates on document status
- Gallery: Community photos and events
- Announcements: Latest barangay news and updates

AVAILABLE DOCUMENTS:
- Barangay Clearance (most common)
- Certificate of Indigency
- Certificate of Residency
- Income Certificate
- Business Permit requirements
- Cedula processing assistance

DOCUMENT REQUEST PROCESS:
1. Register/Login to the website
2. Go to Document Request section
3. Select document type
4. Fill out purpose and upload proof documents (ID, photos, etc.)
5. Can request for someone else (with relation info)
6. Submit and wait for processing
7. Track status in dashboard
8. Get notification when ready for pickup

OFFICE INFORMATION:
- Location: Barangay Lumbangan, Nasugbu, Batangas
- Office Hours: Monday-Friday 8:00 AM - 5:00 PM, Saturday 8:00 AM - 12:00 PM
- Processing Time: 3-5 business days for most documents
- Pickup: At Barangay Hall during office hours

WEBSITE SECTIONS:
- Landing Page: Information about barangay, projects, officials
- User Dashboard: Personal document tracking and services
- Document Request: Apply for certificates online
- Gallery: Community events and activities
- Contact Information: Ways to reach the barangay office

If asked about specific requirements or procedures, provide accurate information based on the system. If unsure about specific details, advise to visit or call the barangay office.";


$payload = [
    'contents' => [
        [
            'parts' => [
                ['text' => $systemInstruction],
                ['text' => "User: " . $userMessage]
            ]
        ]
    ]
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
]);

$response = curl_exec($ch);

if ($response === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Request failed']);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);


if ($httpCode >= 400) {
    http_response_code(500);
    
    
    $errorMsg = 'Unknown error occurred';
    if (isset($data['error']['message'])) {
        $errorMsg = $data['error']['message'];
        if (strpos($errorMsg, 'API key not valid') !== false) {
            $errorMsg = 'Invalid API key. Please check your Gemini API key.';
        }
    }
    
    echo json_encode(['error' => $errorMsg, 'details' => $data]);
    exit;
}

if (empty($data['candidates'][0]['content']['parts'][0]['text'])) {
    http_response_code(500);
    echo json_encode(['error' => 'No response generated', 'details' => $data]);
    exit;
}

$reply = $data['candidates'][0]['content']['parts'][0]['text'];

echo json_encode([
    'reply' => $reply,
]);
?>