<?php

namespace App\Console\Commands;

use App\Jobs\AsteriskEventJob;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PAMI\Client\Impl\ClientImpl as PamiClient;
use PAMI\Message\Event\AGIExecStartEvent;
use PAMI\Message\Event\BridgeCreateEvent;
use PAMI\Message\Event\BridgeDestroyEvent;
use PAMI\Message\Event\BridgeEnterEvent;
use PAMI\Message\Event\BridgeLeaveEvent;
use PAMI\Message\Event\ConfbridgeJoinEvent;
use PAMI\Message\Event\ConfbridgeLeaveEvent;
use PAMI\Message\Event\ConfbridgeStartEvent;
use PAMI\Message\Event\DialBeginEvent;
use PAMI\Message\Event\DialStateEvent;
use PAMI\Message\Event\EventMessage;
use PAMI\Message\Event\HangupEvent;
use PAMI\Message\Event\NewstateEvent;
use PAMI\Message\Event\VarSetEvent;


class AsteriskEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asterisk:events';

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
     * @return mixed
     */
    public function handle()
    {
        try {
            $options = array(
                'host' =>  config('services.ami.AMI_HOST'),
                'scheme' => 'tcp://',
                'port' => 5038,
                'username' => config('services.ami.AMI_USER'),
                'secret' => config('services.ami.AMI_PASS'),
                'connect_timeout' => 10000,
                'read_timeout' => 10000
            );
            $pamiClient = new PamiClient($options);
            $pamiClient->open();

            $pamiClient->registerEventListener(
                function (EventMessage $event) use ($pamiClient) {
                    Log::critical(get_class($event));
                    if (
                        $event instanceof DialBeginEvent
                        || $event instanceof DialStateEvent
                        || $event instanceof NewstateEvent
                        || $event instanceof HangupEvent
                        || $event instanceof BridgeEnterEvent
                        || $event instanceof ConfbridgeJoinEvent
                        || $event instanceof ConfbridgeLeaveEvent
                        || $event instanceof BridgeLeaveEvent
                        || $event instanceof BridgeDestroyEvent
                        || $event instanceof ConfbridgeStartEvent
                        || $event instanceof BridgeCreateEvent
                        || $event instanceof VarSetEvent
                        || $event instanceof AGIExecStartEvent
                    ){
                       // AsteriskEventJob::dispatch($event->getRawContent());
                    }
                }
            );

            $running = true;
// Main loop
            while ($running) {
                $pamiClient->process();
                usleep(1000);
            }
// Close the connection
            $pamiClient->close();
        }catch (\Exception $exception){
           Log::critical($exception->getMessage());
        }
    }
}
