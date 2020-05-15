<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 08.04.17
 * Time: 20:41
 */

namespace App\Components\B24\Entity;

use App\Components\B24\Crm\Entity\Entity;
use App\Components\B24\Http\Client;
use Illuminate\Support\Collection;

/**
 * Class Deal
 * @package App\Components\B24\Entity
 */
class Deal extends Entity
{
    /**
     * @var string
     */
    protected static $createUrl = 'crm.deal.add.json';

    /**
     * @var string
     */
    protected static $updateUrl = 'crm.deal.update.json';

    /**
     * @var string
     */
    protected static $listUrl = 'crm.deal.list.json';

    /**
     * @var string
     */
    protected static $getUrl = 'crm.deal.get.json';

    /**
     * @var string
     */
    protected static $fieldsUrl = 'crm.deal.fields';

    /**
     * @var string
     */
    protected static $productRowListUrl = 'crm.deal.productrows.get.json';

    /**
     * @var string
     */
    protected static $productRowSetUrl = 'crm.deal.productrows.set.json';

    /**
     * @inheritdoc
     */
    public static function getAdapter()
    {
        return new DealAdapter();
    }

    public function products()
    {
        if($this->id){
            $client = Client::getInstance();
            $result = \GuzzleHttp\json_decode($client -> crmQuery(static::$productRowListUrl, [
                'id' => $this->id
            ]));
            return $result;
        } else {
            throw new \RuntimeException('Deal has not been saved yet');
        }
    }

    public function addProducts(array $productsList)
    {
        if($this->id){
            if(!empty($productsList)){
                $productFields = [];
                foreach ($productsList as $productRow) {
                    /** @var ProductRow $productRow */
                    if(!$productRow instanceof ProductRow){
                        if(is_array($productRow)) {
                            $productRow = new ProductRow($productRow);
                        } elseif($productRow instanceof Collection) {
                            $productRow = new ProductRow($productRow->toArray());
                        } else {
                            throw new \RuntimeException('Product row must be an array, or instance of Collection or ProductRow, '.gettype($productRow).' given');
                        }
                    }
                    $productFields[] = $productRow->getFields();
                }
                if(!empty($productFields)){
                    $client = Client::getInstance();
                    $response = $client -> crmQuery(static::$productRowSetUrl, [
                        'id' => $this->id,
                        'rows' => $productFields
                    ]);
                    if($response){
                        $result = \GuzzleHttp\json_decode($response);
                    } else {
                        $result = false;
                    }
                    return $result;
                } else {
                    throw new \RuntimeException('Empty products fields list');
                }
            } else {
                throw new \RuntimeException('Empty product list');
            }
        } else {
            throw new \RuntimeException('Deal has not been saved yet');
        }
    }
}