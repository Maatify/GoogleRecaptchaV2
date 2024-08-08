<?php
/**
 * @PHP       Version >= 8.0
 * @Liberary  HCaptcha
 * @Project   HCaptcha
 * @copyright ©2024 Maatify.dev
 * @see       https://www.maatify.dev Visit Maatify.dev
 * @link      https://github.com/Maatify/HCaptcha View project on GitHub
 * @link      https://docs.hcaptcha.com/ Visit hCaptcha Website
 * @since     2023-08-07 11:00 PM
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @Maatify   HCaptcha :: HCaptchaPublisherProValidation
 * @note      This Project using for Call HCaptcha Validation
 *
 * This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 *
 */

namespace Maatify\GoogleRecaptchaV2;

use Maatify\Json\Json;

class GoogleReCaptchaV2PublisherProValidation extends GoogleReCaptchaV2RequestCall
{
    const E_HOSTNAME_INVALID = 'invalid-hostname';
    const E_MISSING_INPUT_RESPONSE = 'missing-input-response';
    public ?bool $success = null;
    public array $response = [];

    private static ?self $instance = null;
    private string $remote_ip = '';
    private string $hostname = '';

    public static function getInstance(string $secret_key = ''): self
    {
        if (null === self::$instance) {
            self::$instance = new self($secret_key);
        }

        return self::$instance;
    }

    public function setRemoteIp(string $remote_ip): static
    {
        $this->remote_ip = $remote_ip;
        return $this;
    }

    public function setHostname(string $hostname): static
    {
        $this->hostname = $hostname;
        return $this;
    }

    private function hasError(array $errors): void
    {
        $this->success = false;
        $this->response = ['success' => false, 'error-codes' => $errors];
    }

    private function curlValidation(): bool
    {
        $validation_errors = array();
        if(empty($_POST['g-recaptcha-response'])) {
            $validation_errors[] = self::E_MISSING_INPUT_RESPONSE;
            $this->hasError($validation_errors);
            return false;
        }else{
            $params = array(
                'secret'   => $this->secret_key,
                'response' => $_POST['g-recaptcha-response'],
            );

//            if(!empty($this->remote_ip)){
//                $params['remoteip'] = $this->remote_ip;
//            }


            $response_data = $this->curlPost($params);
            $this->response = (array)$response_data;

            $this->success = (isset($response_data->success) && $response_data->success);

            if(!empty($this->success)){
                if(!empty($this->hostname)){
                    if($response_data->hostname != $this->hostname){
                        $this->success = false;
                        $validation_errors[] = self::E_HOSTNAME_INVALID;
                    }
                }
            }

            if(!empty($validation_errors)){
                $this->hasError($validation_errors);
                return false;
            }else{
                return $this->success;
            }
        }
    }

    private function validate(): bool
    {
        if ($this->success === null) {
            $this->success = $this->curlValidation();
        }

        return $this->success;
    }

    public function jsonErrors(): void
    {
        if (empty($_POST['g-recaptcha-response'])) {
            Json::Missing('g-recaptcha-response');
        }
        if (! $this->validate()) {
            Json::Invalid('g-recaptcha-response', Json::JsonFormat($this->response));
        }
    }

    public function isSuccess(): bool
    {
        return $this->validate();
    }

    public function getResponse(): array
    {
        $this->validate();
        return $this->response;
    }

    public function getHostname(): string
    {
        return $this->hostname;
    }
}