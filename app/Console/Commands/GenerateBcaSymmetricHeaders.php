<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\BcaHelper;

class GenerateBcaSymmetricHeaders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bca:generate-symmetric-headers {httpMethod} {relativeUriPath} {accessToken} {body}';

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
        $httpMethod = strtoupper($this->argument('httpMethod'));
        $relativeUriPath = $this->argument('relativeUriPath');
        $accessToken = $this->argument('accessToken');
        $body = $this->argument('body');
        $bcaHelper = BcaHelper::getSymmetricHeaders(
            $httpMethod,
            $relativeUriPath,
            $accessToken,
            $body
        );
        extract($bcaHelper); // $uri, $authorization, $externalId, $timestamp, $signature

        $this->line("----------");
        $this->info("URI");
        $this->info($uri);
        $this->newLine();
        $this->info("Bearer Authorization");
        $this->info($authorization);
        $this->newLine();
        $this->info("External ID");
        $this->info($externalId);
        $this->newLine();
        $this->info("Timestamp");
        $this->info($timestamp);
        $this->newLine();
        $this->info("Signature");
        $this->info($signature);
        $this->line("----------");
    }
}
