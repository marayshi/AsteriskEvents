<?php

namespace App\Console\Commands;

use App\Jobs\AsteriskEventJob;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PAMI\Client\Impl\ClientImpl as PamiClient;
use PAMI\Exception\PAMIException;
use PAMI\Message\Action\HangupAction;
use PAMI\Message\Action\OriginateAction;
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
use PAMI\Message\Event\NewextenEvent;
use PAMI\Message\Event\NewstateEvent;
use PAMI\Message\Event\OriginateResponseEvent;
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
                   // var_dump(get_class($event));
                    if (
//                        $event instanceof DialBeginEvent
//                        || $event instanceof DialStateEvent
//                        || $event instanceof NewstateEvent
//                        || $event instanceof HangupEvent
//                        || $event instanceof BridgeEnterEvent
//                        || $event instanceof ConfbridgeJoinEvent
//                        || $event instanceof ConfbridgeLeaveEvent
//                        || $event instanceof BridgeLeaveEvent
//                        || $event instanceof BridgeDestroyEvent
//                        || $event instanceof ConfbridgeStartEvent
//                        || $event instanceof BridgeCreateEvent
//                      //  || $event instanceof VarSetEvent
//                        || $event instanceof AGIExecStartEvent
//                    $event instanceof BridgeCreateEvent ||
//                    $event instanceof OriginateResponseEvent ||
                    $event instanceof DialStateEvent
                    ){
                       // AsteriskEventJob::dispatch($event->getRawContent());

                        if ($event->getDialStatus() === "PROGRESS"){


                           // var_dump($event->getCallerIDNum());

                            $cond = true;
                            if ((string)$event->getCallerIDNum() === "3133555555" AND $cond){
                                try {
                                    var_dump($event->getRawContent());

                                    $originateMsg = new OriginateAction("Local/970599588188@ownagev88");
                                    $originateMsg->setAccount(1);
                                    $originateMsg->setCallerId("Ringless " . "<3133444444>");
                                    $originateMsg->setApplication("PlayBack");
                                    $originateMsg->setData("tt-monkeys");
                                    // $originateMsg->setTimeout(2);

                                    $originateMsg->setAsync(false);
                                    $orgresp = $pamiClient->send($originateMsg);

                                    $pamiClient->process();

                                    $pamiClient->send(new HangupAction($event->getDestChannel()));

                                    $cond = false;

                                }catch (PAMIException $PAMIException) {
                                    Log::critical($PAMIException->getMessage());
                                }catch (\Exception $exception){
                                    Log::critical($exception->getMessage());
                                }

                                $cond = false;

                            }



                        }
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
