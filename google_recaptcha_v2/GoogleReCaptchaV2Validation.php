<?php
/**
 * @PHP       Version >= 8.0
 * @Liberary  HCaptcha
 * @Project   HCaptcha
 * @copyright ©2024 Maatify.dev
 * @see       https://www.maatify.dev Visit Maatify.dev
 * @link      https://github.com/Maatify/GoogleRecaptchaV2 View project on GitHub
 * @link      https://developers.google.com/recaptcha/docs/display Visit hCaptcha Website
 * @since     2023-08-08 5:19 PM
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @Maatify   GoogleRecaptchaV2 :: GoogleReCaptchaV2Validation
 * @note      This Project using for Call Google ReCaptcha V2 Validation
 *
 * This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 *
 */

namespace Maatify\GoogleRecaptchaV2;

use Maatify\Json\Json;

class GoogleReCaptchaV2Validation extends GoogleReCaptchaV2RequestCall
{
    const E_HOSTNAME_INVALID = 'invalid-hostname';
    const E_MISSING_INPUT_RESPONSE = 'missing-input-response';
    const E_ACTION_INVALID = 'invalid-action';
    const E_SCORE_INVALID = 'score-is-low';

    public ?bool $success = null;
    public array $response = [];

    private static ?self $instance = null;
    private string $remote_ip = '';
    private string $hostname = '';
    private string $action = '';
    private float $score = 0.1;

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

    public function setAction(string $action): static
    {
        $this->action = $action;
        return $this;
    }

    public function setScore(float $score): static
    {
        $this->score = $score;
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

                if(!empty($this->score) && isset($response_data->score)){
                    if($response_data->score < $this->score){
                        $this->success = false;
                        $validation_errors[] = self::E_SCORE_INVALID;
                    }
                }

                if(!empty($this->action) && isset($response_data->action)){
                    if($response_data->action !== $this->action){
                        $this->success = false;
                        $validation_errors[] = self::E_ACTION_INVALID;
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
            if(!empty($this->response['error-codes'][0])){
                Json::Invalid('g-recaptcha-response', $this->response['error-codes'][0]);
            }
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