<?php
/**
 * HTTP method wrappers around curl
 */

/**
 * Class Http
 */
class Http
{
    public static function post($strUrl, $arrPostData)
    {
        $bSuccess = false;
        $strFailReason = '';

        $strContent = implode('&', self::flattenContent($arrPostData));

        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $strUrl);

//        curl_setopt($handle, CURLOPT_PROXY, $host);
//        curl_setopt($handle, CURLOPT_PROXYPORT, $port);

        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

//    curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 2);
//    curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, true);

        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($handle, CURLOPT_VERBOSE, false);
        curl_setopt($handle, CURLOPT_HEADER, false);
        curl_setopt($handle, CURLOPT_POST, 1);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $strContent);

        curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($handle, CURLOPT_MAXREDIRS, 3);
        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($handle, CURLOPT_POSTREDIR, 3);

        // Force TLS 1
        if ('https://' === strtolower(substr($strUrl, 0, 8))) {
            curl_setopt($handle, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
        }

        $data = curl_exec($handle);

        if (curl_errno($handle)) {
            $strFailReason = curl_error($handle);
        } else {
            $iHttpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            switch ($iHttpCode) {
                case 200:
                    $bSuccess = true;
                    break;

                default:
                    $strFailReason = $iHttpCode;
                    break;
            }
        }

        curl_close($handle);

        return [
            'success' => $bSuccess,
            'reason'  => $strFailReason,
            'data'    => $data
        ];
    }

    private static function flattenContent($postData, $prefix = '')
    {
        $response = [];

        foreach ($postData as $key => $value) {
            if (is_array($value)) {
                $response = array_merge($response, self::flattenContent($value, $key));
            } else {
                $response[] = (0 === strlen($prefix) ? $key : $prefix . ucwords($key)) . '=' . urlencode($value);
            }
        }

        return $response;
    }
}
