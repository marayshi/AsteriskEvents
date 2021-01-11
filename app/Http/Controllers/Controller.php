<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use PAMI\Client\Impl\ClientImpl as PamiClient;
use PAMI\Exception\PAMIException;
use PAMI\Message\Action\OriginateAction;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function ringlessVoicemail(){
        $options = array(
            'host' =>  config('services.ami.AMI_HOST'),
            'scheme' => 'tcp://',
            'port' => 5038,
            'username' => config('services.ami.AMI_USER'),
            'secret' => config('services.ami.AMI_PASS'),
            'connect_timeout' => 30000,
            'read_timeout' => 30000
        );
        $number = \request()->number;


       // Cache::put("number",$number);
        cache()->store('redis')->put('bar', 'baz', 600); // 10 Minutes


        try {
            $pamiClient = new PamiClient($options);
            $pamiClient->open();


            $originateMsg = new OriginateAction("Local/970599588188@ownagev88");
            $originateMsg->setAccount(1);
//            $originateMsg->setActionID("123456");
            $originateMsg->setCallerId("Ringless " . "<3133555555>");
            $originateMsg->setApplication("PlayBack");
            $originateMsg->setData("tt-monkeys");
            // $originateMsg->setTimeout(2);

            $originateMsg->setAsync(true);
            $orgresp = $pamiClient->send($originateMsg);

            $pamiClient->process();



        }catch (PAMIException $PAMIException) {
            Log::critical($PAMIException->getMessage());
        }catch (\Exception $exception){
            Log::critical($exception->getMessage());
        }


//        try {
//            $pamiClient = new PamiClient($options);
//            $pamiClient->open();
//
//
//            $originateMsg = new OriginateAction("Local/".$number."@ownagev88");
//            $originateMsg->setAccount(1);
//            //$originateMsg->setActionID($actionID);
//            $originateMsg->setCallerId("Rongless " . "<3133555555>");
//            $originateMsg->setApplication("PlayBack");
//            $originateMsg->setData("tt-monkeys");
//
//            $originateMsg->setAsync(true);
//            $orgresp = $pamiClient->send($originateMsg);
//
//            $pamiClient->process();
//
//          //  return response();
//
//        }catch (PAMIException $PAMIException) {
//            Log::critical($PAMIException->getMessage());
//        }catch (\Exception $exception){
//            Log::critical($exception->getMessage());
//        }
    }
}
