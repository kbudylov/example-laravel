<?php

namespace App\Http\Controllers\Api;

use App\Components\B24\Entity\Deal;
use App\Components\B24\HookRequest;
use App\Model\Booking\Order;
use Illuminate\Http\Request;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class CrmController
 * @package App\Http\Controllers\Api
 */
class CrmController extends Controller
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * CrmController constructor.
     *
     * @param Request $request
     *
     * @throws \Exception
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);

        $this->logger = new Logger('b24-webhook-log');
        $this->logger -> pushHandler(new StreamHandler(config('b24.hookLog'),Logger::INFO));
    }

    /**
     * @param Request $request
     */
    public function crmDealDelete(Request $request)
    {
        try {
            $this->logger -> info('Crm request delete deal at: '.time());
            $this->logger -> info('Start parsing');

            $crmRequest = new HookRequest($request, $this->logger);

            if($crmRequest->validateKey(config('b24.crmSyncDealDeleteAppKey'))){
                $id = $crmRequest->getObjectId();
                if($id){
                    /** @var Order $order */
                    $order = Order::findByDealId($id);
                    if($order){
                        if(!$order->trashed()){
                            $this->logger->info('Deleting order #'.$order->id);
                            if($this->deleteOrder($order)){
                                $this->logger->info('Order deleted success');
                            } else {
                                $this->logger->warning('Error deleting order');
                            }
                        } else {
                            $this->logger->info('Order already deleted',[$order->id]);
                        }
                    } else {
                        throw new HttpException(500,'Order not found for deal #['.$id.']');
                    }
                } else {
                    throw new HttpException(500, 'Object id is undefined');
                }
            } else {
                throw new HttpException(403,'Forbidden');
            }
        } catch(\Exception $e) {
            $this->logger->error('Exception: '.$e->getMessage().'; in file: '.$e->getFile().'; on line:'.$e->getLine());
        }
    }

    /**
     * @param Request $request
     */
    public function crmDealUpdate(Request $request)
    {
        try {
            $crmRequest = new HookRequest($request, $this->logger);

            if($crmRequest->validateKey(config('b24.crmSyncDealUpdateStatusAppKey'))){
                $id = $crmRequest->getObjectId();
                if($id){
                    /** @var Order $order */
                    $order = Order::findByDealId($id);
                    if($order){
                        $this->logger->info("Order found for deal",[$order->id, $id]);
                        $deal = Deal::get($id);
                        if($deal){
                            $this->logger->info('Hook request deal found:'.$deal->id);
                            //$this->logger->info('Deal data: ',$deal->getAttributes());
                            //"stageId":"1" -> booking cancelled
                            //"stageId":"LOSE" -> request cancelled
                            //"stageId":"WON" -> sold
                            //"closed":"Y/N"
                            $this->logger->info("Deal [$deal->id] params [closed,stageId]:",[$deal->closed, $deal->stageId]);
                            try {
                                if($deal->closed === "Y"){
                                    $stageId = $deal->stageId;
                                    if($stageId !== "WON"){
                                        $this->logger->info("Deal is LOSE",[$stageId]);
                                        if(!$order->trashed()){
                                            $order->setCancelled();
                                            $this->logger->info("Deleting order for closed LOSE deal",[$order->id]);
                                            if($this->deleteOrder($order)){
                                                $this->logger->info("Order deleted success");
                                            } else {
                                                $this->logger->error("Order deleting error");
                                            }
                                        } else {
                                            $this->logger->info("Order already deleted",[$order->id]);
                                        }
                                    } else {
                                        $this->logger->info("Deal is WON");
                                        if($order->trashed()){
                                            $this->logger->info("Restoring order for closed WON deal ",[$order->id]);
                                            if($order->restore()){
                                                $this->logger->info("Order restored success");
                                            } else {
                                                $this->logger->error("Order restoring error");
                                            }
                                        }
                                        $order->setPayed();
                                    }
                                } else {
                                    //todo: check if order can be restored
                                    if($order->trashed()){
                                        $this->logger->info("Restoring order for deal that not closed",[$order->id]);
                                        if($order->restore()){
                                            $this->logger->info("Order restored success");
                                        } else {
                                            $this->logger->error("Order restoring error");
                                        }
                                    }
                                }
                            } catch (\Exception $e) {
                                $this->logger->error('Exception (Order class is '.get_class($order).'): '.$e->getMessage().'; in file: '.$e->getFile().'; on line:'.$e->getLine());
                            }
                        } else {
                            $this->logger->warning('Hook request deal not found:'.$id);
                        }
                    } else {
                        throw new HttpException(500,'Order not found for deal #['.$id.']');
                    }
                } else {
                    throw new HttpException(500, 'Object id is undefined');
                }
            } else {
                throw new HttpException(403,'Forbidden');
            }
        } catch(\Exception $e) {
            $this->logger->error('Exception: '.$e->getMessage().'; in file: '.$e->getFile().'; on line:'.$e->getLine());
        }
    }

    /**
     * @param Order $order
     * @return bool|null
     * @throws \Exception
     */
    protected function deleteOrder(Order $order)
    {
        //$order->dealId = NULL;
        //$save = $order->save();
        return $order->delete();
    }
}
