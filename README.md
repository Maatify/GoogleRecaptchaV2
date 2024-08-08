[![Current version](https://img.shields.io/packagist/v/maatify/google-recaptcha-v2)][pkg]
[![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/maatify/google-recaptcha-v2)][pkg]
[![Monthly Downloads](https://img.shields.io/packagist/dm/maatify/google-recaptcha-v2)][pkg-stats]
[![Total Downloads](https://img.shields.io/packagist/dt/maatify/google-recaptcha-v2)][pkg-stats]
[![Stars](https://img.shields.io/packagist/stars/maatify/google-recaptcha-v2)](https://github.com/maatify/GoogleRecaptchaV2/stargazers)

[pkg]: <https://packagist.org/packages/maatify/google-recaptcha-v2>
[pkg-stats]: <https://packagist.org/packages/maatify/google-recaptcha-v2/stats>

# Installation

```shell
composer require maatify/google-recaptcha-v2
```

# Usage

```PHP
<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2024-08-08
 * Time: 5:19 PM
 * https://www.Maatify.dev
 */
 
use Maatify\GoogleRecaptchaV2\GoogleReCaptchaV2Validation;

require 'vendor/autoload.php';

$secret_key = '0x0000000000000000000000000000000000000000';

$google_recaptcha_v2 = GoogleReCaptchaV2Validation::getInstance($secret_key);

// ===== if you want to validate domain use
$google_recaptcha_v2->setHostname('maatify.dev');

// ===== if you want to validate score (invisible only)
$google_recaptcha_v2->setScore(0.5);

// ===== if you want to validate action (invisible only)
$google_recaptcha_v2->setAction('login');

// ===== if you want to validate domain use
$google_recaptcha_v2->setHostname('maatify.dev');

// ===== get result in array format
$result = $google_recaptcha_v2->getResponse();

// ====== get bool of validation 
$result = $google_recaptcha_v2->isSuccess();

// ====== using maatify json on error response with json code with die and if success there is no error
$google_recaptcha_v2->jsonErrors();
```

### examples
#### getResponse();
>##### Success Example
>        Array
>        (
>            [success] => 1
>            [challenge_ts] => 2024-08-08T14:13:05Z
>            [hostname] => localhost
>        )

>##### Error Example
>       Array
>       (
>           [success] =>
>           [error-codes] => Array
>           (
>               [0] => invalid-input-response
>           )
>       
>       )

>       Array
>       (
>           [success] =>
>           [error-codes] => Array
>           (
>               [0] => missing-input-secret
>           )
>       
>       )

>       Array
>       (
>           [success] =>
>           [error-codes] => Array
>           (
>               [0] => bad-request
>           )
>       
>       )

>       Array
>        (
>            [success] =>
>            [error-codes] => Array
>            (
>                [0] => invalid-hostname
>            )
>
>        )

>       Array
>        (
>            [success] =>
>            [error-codes] => Array
>            (
>                [0] => timeout-or-duplicate
>            )
>        
>        )

>       Array
>        (
>            [success] =>
>            [error-codes] => Array
>            (
>                [0] => invalid-action
>            )
>        
>        )

>       Array
>        (
>            [success] =>
>            [error-codes] => Array
>            (
>                [0] => score-is-low
>            )
>        
>        )


#### isSuccess();
>return true || false


#### jsonErrors();
>##### Error Example
> 
>   Header 400 
> 
>   Body:
> 
> - on validation error
> 
>```json
>   {
>       "success": false,
>       "response": 4000,
>       "var": "h-captcha-response",
>       "description": "g-recaptcha-response",
>       "more_info": "{\"success\":false,\"error-codes\":[\"missing-input-response\",\"missing-input-secret\"]}",
>       "error_details": ""
>   }
>```
> 
> - on missing or empty `$_POST['g-recaptcha-response']`
> 
>```json
>   {
>       "success": false,
>       "response": 1000,
>       "var": "g-recaptcha-response",
>       "description": "MISSING g-recaptcha-response",
>       "more_info": "",
>       "error_details": ""
>   }
>```


### Create From in HTML Code 
#### Visible recaptcha
```html
<form action="process.php" method="POST">
    <form method="POST">
        <!-- Your other form fields -->
        <div class="g-recaptcha" data-sitekey="__YOUR_SITE_KEY__" data-theme="dark" data-hl="ar"></div>
        <input type="submit" value="Submit">
    </form>

    <script src="https://www.google.com/recaptcha/api.js?hl=en" async defer></script>
```

#### Invisible recaptcha
```html
<script src="https://www.google.com/recaptcha/api.js?hl=ar"></script>

<script>
    function onSubmit(token) {
        document.getElementById("demo-form").submit();
    }
</script>


<form method="POST" id="demo-form">
    <input name="test" value="test">
    <button class="g-recaptcha"
            data-sitekey="__YOUR_SITE_KEY__"
            data-callback='onSubmit'
            data-action='__YOUR_ACTION__'>Submit</button>

</form>
```