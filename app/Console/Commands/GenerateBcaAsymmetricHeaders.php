<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\BcaHelper;

class GenerateBcaAsymmetricHeaders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bca:generate-asymmetric-headers';

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
        
        $bcaHelper = BcaHelper::getAsymmetricHeaders();

        $this->line("----------");
        $this->info("X-CLIENT-KEY");
        $this->info($bcaHelper["X-CLIENT-KEY"]);
        $this->newLine();
        $this->info("X-TIMESTAMP");
        $this->info($bcaHelper["X-TIMESTAMP"]);
        $this->newLine();
        $this->info("X-SIGNATURE");
        $this->info($bcaHelper["X-SIGNATURE"]);
        $this->line("----------");
    }
}
