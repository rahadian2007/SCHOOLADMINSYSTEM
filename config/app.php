<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'Payment System'),
    'description' => env('APP_DESCRIPTION', 'Payment System'),

    /*
    |--------------------------------------------------------------------------
    | BCA Configuration
    |--------------------------------------------------------------------------
    |
    | Keys related to BCA connections.
    |
    */

    'bca_private_key' => env('APP_BCA_PRIV_KEY', ''),
    'bca_public_key' => env('APP_BCA_PUB_KEY', ''),
    'bca_client_id' => env('APP_BCA_CLIENT_ID', 'ebfcf5ce-6d38-49c6-9bed-4ac0c0322783'),
    'bca_client_secret' => env('APP_BCA_CLIENT_SECRET', '106f89cd-f5a8-459b-a2b7-a06ca3f5044a'),
    'bca_api_base_url' => env('APP_BCA_API_BASE_URL', ''),
    'bca_partner_id' => env('APP_BCA_PARTNER_ID', ''),
    'bca_company_id' => env('APP_BCA_COMPANY_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'Asia/Jakarta',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'id',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /*
        * Package Service Providers...
        */
        Collective\Html\HtmlServiceProvider::class,

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        
        Spatie\Permission\PermissionServiceProvider::class,
        Tymon\JWTAuth\Providers\LaravelServiceProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App' => Illuminate\Support\Facades\App::class,
        'Arr' => Illuminate\Support\Arr::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Form' => Collective\Html\FormFacade::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Html' => Collective\Html\HtmlFacade::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'Str' => Illuminate\Support\Str::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,

    ],

    // BCA Response map
    // TYPE: AUTH
    "AUTH_INVALID_FIELD_FORMAT" => [
        "HTTP_CODE" => 400,
        "CODE" => "4007300",
        "MSG" => "Invalid field format",
        "PAYMENT_FLAG_STATUS" => null,
    ],
    "AUTH_UNAUTHORIZED_CONNECTION" => [
        "HTTP_CODE" => 400,
        "CODE" => "4007300",
        "MSG" => "Unauthorized. [Connection not allowed]",
        "PAYMENT_FLAG_STATUS" => null,
    ],
    "AUTH_INVALID_TIMESTAMP_FORMAT" => [
        "HTTP_CODE" => 400,
        "CODE" => "4007301",
        "MSG" => "invalid timestamp format [X-TIMESTAMP]",
        "PAYMENT_FLAG_STATUS" => null,
    ],
    "AUTH_INVALID_MANDATORY_FIELD" => [
        "HTTP_CODE" => 400,
        "CODE" => "4007302",
        "MSG" => "Invalid mandatory field",
        "PAYMENT_FLAG_STATUS" => null,
    ],
    "AUTH_UNAUTHORIZED_SIGNATURE" => [
        "HTTP_CODE" => 401,
        "CODE" => "4017300",
        "MSG" => "Unauthorized. [Signature]",
        "PAYMENT_FLAG_STATUS" => null,
    ],
    "AUTH_UNAUTHORIZED_UNKNOWN_CLIENT" => [
        "HTTP_CODE" => 401,
        "CODE" => "4017300",
        "MSG" => "Unauthorized. [Unknown client]",
        "PAYMENT_FLAG_STATUS" => null,
    ],
    "AUTH_SERVER_ERROR_TIMEOUT" => [
        "HTTP_CODE" => 504,
        "CODE" => "5047300",
        "MSG" => "Timeout",
        "PAYMENT_FLAG_STATUS" => null,
    ],

    // TYPE: INQUIRY
    "INQUIRY_ACCESS_TOKEN_INVALID" => [
        "HTTP_CODE" => 401,
        "CODE" => "4012401",
        "MSG" => "Invalid Token (B2B)",
        "PAYMENT_FLAG_STATUS" => null,
    ],
    "INQUIRY_UNAUTHORIZED_SIGNATURE" => [
        "HTTP_CODE" => 401,
        "CODE" => "4012400",
        "MSG" => "Unauthorized. [Signature]",
        "PAYMENT_FLAG_STATUS" => null,
    ],
    "INQUIRY_UNAUTHORIZED_STRING_TO_SIGN" => [
        "HTTP_CODE" => 401,
        "CODE" => "4012400",
        "MSG" => "Unauthorized. [Signature]",
        "PAYMENT_FLAG_STATUS" => null,
    ],
    "INQUIRY_UNAUTHORIZED_UNKNOWN_CLIENT" => [
        "HTTP_CODE" => 401,
        "CODE" => "4012400",
        "MSG" => "Unauthorized. [Unknown client]",
        "PAYMENT_FLAG_STATUS" => null,
    ],
    "INQUIRY_MISSING_MANDATORY_FIELD" => [
        "HTTP_CODE" => 400,
        "CODE" => "4002402",
        "MSG" => "Invalid Mandatory Field",
        "PAYMENT_FLAG_STATUS" => '01',
    ],
    "INQUIRY_INVALID_FIELD_FORMAT" => [
        "HTTP_CODE" => 400,
        "CODE" => "4002401",
        "MSG" => "Invalid Field Format",
        "PAYMENT_FLAG_STATUS" => '01',
    ],
    "INQUIRY_CONFLICTED_EXTERNAL_ID" => [
        "HTTP_CODE" => 409,
        "CODE" => "4092400",
        "MSG" => "Conflict",
        "PAYMENT_FLAG_STATUS" => "01",
    ],
    "INQUIRY_VALID_VA" => [
        "HTTP_CODE" => 200,
        "CODE" => "2002400",
        "MSG" => "Success",
        "PAYMENT_FLAG_STATUS" => "00",
    ],
    "INQUIRY_VALID_VA_SETTLED" => [
        "HTTP_CODE" => 404,
        "CODE" => "4042414",
        "MSG" => "Paid bill",
        "PAYMENT_FLAG_STATUS" => "01",
    ],
    "INQUIRY_VALID_VA_EXPIRED" => [
        "HTTP_CODE" => 404,
        "CODE" => "4042419",
        "MSG" => "Invalid Bill",
        "PAYMENT_FLAG_STATUS" => "01",
    ],
    "INQUIRY_UNREGISTERED_VA" => [
        "HTTP_CODE" => 404,
        "CODE" => "4042412",
        "MSG" => "Invalid Bill",
        "PAYMENT_FLAG_STATUS" => "01",
    ],
    "INQUIRY_REQUEST_PARSING_ERROR" => [
        "HTTP_CODE" => 400,
        "CODE" => "4002400",
        "MSG" => "Bad Request",
        "PAYMENT_FLAG_STATUS" => "01",
    ],
    "INQUIRY_RESPONSE_PARSING_ERROR" => [
        "HTTP_CODE" => 400,
        "CODE" => "4002400",
        "MSG" => "Bad Request",
        "PAYMENT_FLAG_STATUS" => "01",
    ],

    // TYPE: PAYMENT
    "PAYMENT_ACCESS_TOKEN_INVALID" => [
        "HTTP_CODE" => 401,
        "CODE" => "4012501",
        "MSG" => "Invalid Token (B2B)",
        "PAYMENT_FLAG_STATUS" => null,
    ],
    "PAYMENT_UNAUTHORIZED_SIGNATURE" => [
        "HTTP_CODE" => 401,
        "CODE" => "4012500",
        "MSG" => "Unauthorized. [Signature]",
        "PAYMENT_FLAG_STATUS" => null,
    ],
    "PAYMENT_UNAUTHORIZED_STRING_TO_SIGN" => [
        "HTTP_CODE" => 401,
        "CODE" => "4012500",
        "MSG" => "Unauthorized. [Signature]",
        "PAYMENT_FLAG_STATUS" => null,
    ],
    "PAYMENT_UNAUTHORIZED_UNKNOWN_CLIENT" => [
        "HTTP_CODE" => 401,
        "CODE" => "4012500",
        "MSG" => "Unauthorized. [Unknown client]",
        "PAYMENT_FLAG_STATUS" => null,
    ],
    "PAYMENT_MISSING_MANDATORY_FIELD" => [
        "HTTP_CODE" => 400,
        "CODE" => "4002502",
        "MSG" => "Invalid mandatory field",
        "PAYMENT_FLAG_STATUS" => '01',
    ],
    "PAYMENT_INVALID_MANDATORY_FIELD" => [
        "HTTP_CODE" => 400,
        "CODE" => "4002501",
        "MSG" => "Invalid mandatory field",
        "PAYMENT_FLAG_STATUS" => '01',
    ],
    "PAYMENT_CONFLICTED_EXTERNAL_ID" => [
        "HTTP_CODE" => 409,
        "CODE" => "4092500",
        "MSG" => "Conflict",
        "PAYMENT_FLAG_STATUS" => "01",
    ],
    "PAYMENT_INVALID_FIELD_FORMAT" => [
        "HTTP_CODE" => 400,
        "CODE" => "4007300",
        "MSG" => "Invalid field format",
        "PAYMENT_FLAG_STATUS" => null,
    ],
    "PAYMENT_VALID_VA" => [
        "HTTP_CODE" => 200,
        "CODE" => "2002500",
        "MSG" => "Success",
        "PAYMENT_FLAG_STATUS" => "00",
    ],
    "PAYMENT_VALID_VA_SETTLED" => [
        "HTTP_CODE" => 404,
        "CODE" => "4042514",
        "MSG" => "Paid bill",
        "PAYMENT_FLAG_STATUS" => "01",
    ],
    "PAYMENT_INCONSISTENT_REQUEST" => [
        "HTTP_CODE" => 404,
        "CODE" => "4042518",
        "MSG" => "Inconsistent Request",
        "PAYMENT_FLAG_STATUS" => "01",
    ],
    "PAYMENT_INVALID_AMOUNT" => [
        "HTTP_CODE" => 404,
        "CODE" => "4042513",
        "MSG" => "Invalid Amount",
        "PAYMENT_FLAG_STATUS" => "01",
    ],
    "PAYMENT_VALID_VA_EXPIRED" => [
        "HTTP_CODE" => 404,
        "CODE" => "4042519",
        "MSG" => "Invalid Bill",
        "PAYMENT_FLAG_STATUS" => "01",
    ],
    "PAYMENT_UNREGISTERED_VA" => [
        "HTTP_CODE" => 404,
        "CODE" => "4042512",
        "MSG" => "Invalid Bill",
        "PAYMENT_FLAG_STATUS" => "01",
    ],
    "PAYMENT_REQUEST_PARSING_ERROR" => [
        "HTTP_CODE" => 400,
        "CODE" => "4002500",
        "MSG" => "Bad Request",
        "PAYMENT_FLAG_STATUS" => "01",
    ],
    "PAYMENT_RESPONSE_PARSING_ERROR" => [
        "HTTP_CODE" => 400,
        "CODE" => "4002500",
        "MSG" => "Bad Request",
        "PAYMENT_FLAG_STATUS" => "01",
    ],

    "SERVER_INTERNAL_ERROR" => [
        "HTTP_CODE" => 500,
        "CODE" => "5002600",
        "MSG" => "Internal Server error",
        "PAYMENT_FLAG_STATUS" => "01",
    ],

    "STATUS_PAYMENT_NOT_FOUND" => [
        "HTTP_CODE" => 404,
        "CODE" => "4042601",
        "MSG" => "Transaction not found",
        "PAYMENT_FLAG_STATUS" => "01",
    ],

];
