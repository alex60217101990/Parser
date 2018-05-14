<?php

namespace App\Jobs;

use App\Modules\ParserLogic;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class Parsing implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $region;
    protected $page_size;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($reg, $size)
    {
        if(!empty($reg)&&!empty($size)){
            $this->page_size = $size;
            $this->region = $reg;
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $parser = new ParserLogic();
        $parser->proxy_two();
        if(!empty($this->region)&&!empty($this->page_size)) {
            $links = $parser->GetAllLinks($this->region, $this->page_size);
        }else{
            $links = $parser->GetAllLinks();
        }
       // $result = $parser->startParse($links);
        $result1 = $parser->StartParsePage($links);
    }
}
