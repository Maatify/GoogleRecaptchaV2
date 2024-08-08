<?php
/**
 * @PHP       Version >= 8.0
 * @Liberary  HCaptcha
 * @Project   HCaptcha
 * @copyright ©2024 Maatify.dev
 * @see       https://www.maatify.dev Visit Maatify.dev
 * @link      https://github.com/Maatify/HCaptcha View project on GitHub
 * @link      https://docs.hcaptcha.com/ Visit hCaptcha Website
 * @since     2023-08-07 10:50 PM
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @Maatify   HCaptcha :: HCaptchaRequestCall
 * @note      This Project using for Call HCaptcha Validation
 *
 * This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 *
 */

namespace Maatify\GoogleRecaptchaV2;

use CurlHandle;
use Maatify\Functions\GeneralFunctions;
use Maatify\Logger\Logger;
use stdClass;

abstract class GoogleReCaptchaV2RequestCall
{
    protected string $secret_key;
    private string $url = 'https://www.google.com/recaptcha/api/siteverify';
    private false|CurlHandle $ch;
    private array $params;

    public function __construct(string $secret_key = '')
    {
        if(!empty($secret_key)){
            $this->secret_key = $secret_key;
        }
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($this->ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    }

    public function curlPost(array $params): stdClass
    {
        $this->params = $params;
        curl_setopt($this->ch, CURLOPT_POST, 1);
//        curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
//            'Content-Type: application/json',
            'Content-Type: application/x-www-form-urlencoded',
        ));
        return $this->call();
    }

    public function curlGet(): stdClass
    {
        $this->params = [];
        curl_setopt($this->ch, CURLOPT_POST, 0);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
//            'Content-Type: application/x-www-form-urlencoded',
        ));
        return $this->call();
    }

    private function call():stdClass
    {
        curl_setopt($this->ch, CURLOPT_URL, $this->url);

        $result = curl_exec($this->ch);
        $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $curl_errno = curl_errno($this->ch);
        $curl_error = curl_error($this->ch);
        curl_close($this->ch);
        if ($curl_errno > 0) {
            $error_message = "CURL Error #:" . $curl_errno . " - " . $curl_error;
        } else {
            if ($resultArray = json_decode($result)) {
                $this->logSuccess($resultArray);
                return $resultArray;
            } else {
                $error_message = ($httpCode != 200) ?
                    "Error header response " . $httpCode
                    :
                    "There is no response from server (err-" . __LINE__ . ")";

            }
        }
        $this->logError($error_message);
        $obj = new stdClass();
        $obj->success = false;
        return $obj;
    }

    private function logSuccess(array|stdClass $result): void
    {
        $this->log('success', success: $result);
    }

    private function logError(string $message): void
    {
        $this->log('failed', error_details: $message);
    }

    private function log(string $file_name, string $error_details = '', array|stdClass $success = []): void
    {
        if(!empty($error_details)){
            $log['error'] = $error_details;
        }
        if(!empty($success)){
            $log['response'] = (array)$success;
        }
        $log['params'] = $this->params;
        $log['url'] = $this->url;
        Logger::RecordLog($log, 'GoogleRecaptcha/v2/grecaptcha_' . $file_name . '_' . GeneralFunctions::CurrentMicroTimeStamp());
    }
}