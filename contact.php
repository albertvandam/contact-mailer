<?php
require_once 'include/Config.php';
require_once 'include/ReCaptcha.php';

// Only accept POST and OPTIONS methods
if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'OPTIONS'])) {
    endRequest([
        'error' => 'Bad Request'
    ], 'HTTP/1.1 400 Bad Request');
}

// Check origin matches the configured regex
$strOrigin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'No Origin Set';
$strPattern = Config::getConfig('origin');
if (1 !== preg_match($strPattern, $strOrigin)) {
    endRequest([
        'error' => 'Forbidden'
    ], 'HTTP/1.1 403 Forbidden');
}

// Set CORS headers
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Origin: ' . $strOrigin);
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Max-Age: 86400');

// Nothing should happen further on a OPTIONS method
if ('OPTIONS' === $_SERVER['REQUEST_METHOD']) {
    die();
}

// Read data - Expecting JSON as RAW HTTP content
$content = json_decode(file_get_contents('php://input'), true);

// check that captcha value is set if we expect it
$captchaConfig = Config::getConfig('recaptcha', []);
if (isset($captchaConfig['enabled']) && $captchaConfig['enabled']) {
    if (!isset($content['captcha'])) {
        error_log('Captcha not set! ' . var_export($content, true));
        endRequest([
            'error' => 'Bad Request'
        ], 'HTTP/1.1 400 Bad Request');
    }
}

// check other fields are set
if (!isset($content['name']) || !isset($content['email']) || !isset($content['message'])) {
    error_log('Name/Email/Message not set! ' . var_export($content, true));
    endRequest([
        'error' => 'Bad Request'
    ], 'HTTP/1.1 400 Bad Request');
}

// Check ReCaptcha
if (!ReCaptcha::verify($captchaConfig, $content['captcha'])) {
    endRequest([
        'ok' => false
    ]);
}

// load SwiftMailer
require_once 'vendor/autoload.php';

// get SMTP config
$smtpConfig = Config::getConfig('smtp');

// Setup
$transport = Swift_SmtpTransport::newInstance($smtpConfig['server'], $smtpConfig['port'], "ssl")
    ->setUsername($smtpConfig['user'])
    ->setPassword($smtpConfig['password']);
$mailer = Swift_Mailer::newInstance($transport);

// create body from template
$body = str_replace([
    '{subject}',
    '{name}',
    '{email}',
    '{message}'
], [
    htmlentities(Config::getConfig('subject', 'Test Email')),
    htmlentities($content['name']),
    htmlentities($content['email']),
    nl2br(htmlentities($content['message']))
],
    file_get_contents(__DIR__ . '/config/email.template.html')
);

// create message
$message = Swift_Message::newInstance(Config::getConfig('subject', 'Test Email'))
    ->setFrom([Config::getConfig('sender')])
    ->setTo([Config::getConfig('recipient')])
    ->setBody($body, 'text/html');

// send
endRequest([
    'ok' => 1 === $mailer->send($message)
]);


/**
 * Ends the request
 *
 * @param array  $arrData Response data. Will be JSON encoded
 * @param string $strType Response type
 */
function endRequest($arrData, $strType = 'HTTP/1.1 200 OK')
{
    $strResponse = json_encode($arrData);

    header($strType, true);
    header('Content-Type: application/json');
    header('Content-Length: ' . strlen($strResponse));
    header('Content-Transfer-Encoding: ascii');
    header('Cache-Control: no-cache');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT', true);
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT', true);
    echo $strResponse;
    die();
}
