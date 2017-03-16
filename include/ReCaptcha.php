<?php
require_once 'Http.php';

/**
 * Class ReCaptcha
 */
class ReCaptcha
{
    /**
     * Verify ReCaptcha verification value
     *
     * @param array $config            ReCaptcha configuration
     * @param array $verificationValue Verification value
     *
     * @return bool
     */
    public static function verify($config, $verificationValue)
    {
        if (!isset($config['enabled']) || !$config['enabled']) {
            return false;
        }

        $response = false;

        $data = [
            'secret'   => $config['secret'],
            'response' => $verificationValue,
            'remoteIp' => self::getUserIpAddress(true)
        ];

        $arrHttpResponse = Http::post('https://www.google.com/recaptcha/api/siteverify', $data);

        if ($arrHttpResponse['success']) {
            $verifyResponse = json_decode($arrHttpResponse['data']);

            if ($verifyResponse->success) {
                $response = true;
            }
        }

        return $response;
    }

    private static function getUserIpAddress($bUserOnly = false)
    {
        $strProxyIp = '';

        if (isset($_SERVER['HTTP_X_REAL_IP'])) {
            $strUserIp = $_SERVER['HTTP_X_REAL_IP'];
        } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                $strProxyIp = $_SERVER["HTTP_CLIENT_IP"] . ',';
            } else {
                $strProxyIp = $_SERVER["REMOTE_ADDR"] . ',';
            }

            $strUserIp = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else {
            if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                $strUserIp = $_SERVER["HTTP_CLIENT_IP"];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $strUserIp = $_SERVER["REMOTE_ADDR"];
            } else {
                $strUserIp = 'None';
            }
        }

        $strFullIp = $strProxyIp . $strUserIp;
        if (substr($strFullIp, 0, 10) == '127.0.0.1,') {
            $strFullIp = substr($strFullIp, 10);
        }

        if ($bUserOnly) {
            $arrFullIp = explode(',', $strFullIp);
            $strUserIp = $arrFullIp[count($arrFullIp) - 1];

            return $strUserIp;
        } else {
            return $strFullIp;
        }
    }
}
