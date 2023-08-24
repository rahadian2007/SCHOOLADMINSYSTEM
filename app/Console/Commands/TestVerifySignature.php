<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestVerifySignature extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bca:test-verify-signature';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $signature = 'SI+t+As9dqx8/HNtiW2RX6QffaZtqUw6goSTOnkYM39PbpgG7IY03LiUFJwnouEsKRlIwnYTjldflldXrg9Hjg==';
        $body = [
            "partnerServiceId" => "   65676",
            "customerNo" => "22200001",
            "virtualAccountNo" => "   6567622200001",
            "trxDateInit" => "2023-08-08T08:26:00+07:00",
            "channelCode" => 6011,
            "language" => "",
            "amount" => null,
            "hashedSourceAccountNo" => "",
            "sourceBankCode" => "014",
            "additionalInfo" => [
                "value" => ""
            ],
            "passApp" => "",
            "inquiryRequestId" => "20230804478493784910121261"
        ];
        $hashedMinifiedJsonBody = hash("sha256", json_encode($body, JSON_UNESCAPED_SLASHES));
        $this->info($hashedMinifiedJsonBody);
        $method = 'POST';
        $relativeUrl = '/openapi/v1.0/transfer-va/inquiry';
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoxMSwiZXhwIjoxNjkxNDU4ODg2LCJpc3MiOiJodHRwczpcL1wvcGF5bWVudC5hbGhhcW1hcmdhaGF5dS5zY2guaWQiLCJpYXQiOjE2OTE0NTc5ODZ9.5HoQiDSupcOkNDlQexSls8As8_Ap9B-Fm__JxDN4ZL8';
        $timestamp = '2023-08-08T08:26:12+07:00';
        $stringToSign = "$method:$relativeUrl:$token:$hashedMinifiedJsonBody:$timestamp";
        $this->info($stringToSign);
        // $privateKey = openssl_get_privatekey(config('app.bca_private_key'));
        // $this->line("string to sign: " . $stringToSign);
        // $this->line("private key location: " . config('app.bca_private_key'));
        // openssl_sign($stringToSign, $binarySignature, $privateKey, "SHA256");
        // $signature = base64_encode($binarySignature);
        // $this->line("signature: " . $signature);
        
        // $publicKey = openssl_get_publickey(config('app.bca_public_key'));
        $this->line("public key location: " . config('app.bca_public_key'));
        $bcaSecret = 'XtA6VKJtlcuaZ2F9l3d7ksKcmUCRL7I17pSzLRA5';
        $signatureVerifyTest = base64_encode(
            hash_hmac(
                "sha512",
                $stringToSign,
                $bcaSecret,
                true
            )
        );
        $this->line($signature);
        $this->line($signatureVerifyTest);
        $this->line($signature === $signatureVerifyTest ? 'verified' : 'not verified');
    }
}
