<?php

namespace App\Console\Commands;

use App\Jobs\Parsing;
use App\Modules\ParserLogic;
use Illuminate\Console\Command;

class StartParseQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'StartParseQueue:startparsing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command start queue and after start process of parsing.';

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
     * @return mixed
     */
    public function handle()
    {
        DB::table('failed_jobs')->delete();
       // $parser = new ParserLogic();
       // $proxy = $parser->parseProxy();
        dispatch(new Parsing())
            ->onQueue('parser')
            ->delay(15);
    }
}
