<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 04.12.17
 * Time: 17:41
 */

namespace App\Model\Booking;

use App\Components\Validator\PhoneInputValidator;

/**
 * Trait OrderAddClient
 * @package App\Model\Booking
 */
trait OrderAddClient
{
    /**
     * @var array
     */
    protected $credentials = [];

    /**
     * @param $credentials
     * @throws \RuntimeException
     */
    protected function validateClient(array $credentials)
    {
        if(!empty($credentials)){
            if(!empty($credentials['firstName']) && !empty($credentials['lastName'])) {
                if (!empty($credentials['phone'])) {
                    if(PhoneInputValidator::validate($credentials['phone'])){
                        return true;
                    } else {
                        throw new \RuntimeException('Phone are required');
                    }
                } else {
                    throw new \RuntimeException('Phone are required');
                }
            } elseif(!empty($credentials['name'])) {
                if(!empty($credentials['phone'])){
                    if(PhoneInputValidator::validate($credentials['phone'])){
                        return true;
                    } else {
                        throw new \RuntimeException('Phone is invalid');
                    }
                } else {
                    throw new \RuntimeException('Phone are required');
                }
            } else {
                throw new \RuntimeException('First and last names are required');
            }
        } else {
            throw new \RuntimeException('Client credentials is empty');
        }
    }

    /**
     * @param array $credentials
     */
    public function setClientCredentials(array $credentials)
    {
        $this->validateClient($credentials);

        if(!empty($credentials['firstName']) && !empty($credentials['lastName'])){

            $this->credentials['firstName'] = $credentials['firstName'];
            $this->credentials['lastName'] = $credentials['lastName'];
            $this->credentials['surName'] = !empty($credentials['surName']) ? $credentials['surName'] : null;
            $this->credentials['email'] = !empty($credentials['email']) ? $credentials['email'] : null;
            $this->credentials['phone'] = PhoneInputValidator::cleanup($credentials['phone']);
            $this->credentials['name'] = $this->credentials['lastName'] . ' ' . $this->credentials['firstName'] . ($this->credentials['surName'] ? ' ' . $this->credentials['surName'] : '');

        } elseif(!empty($credentials['name'])) {

            $this->credentials['email'] = !empty($credentials['email']) ? $credentials['email'] : null;
            $this->credentials['phone'] = PhoneInputValidator::cleanup($credentials['phone']);
            $this->credentials['name'] = $credentials['name'];

            $nameParts = preg_split('/ +/',$credentials['name'],3,PREG_SPLIT_NO_EMPTY);
            if(!empty($nameParts)){
                $this->credentials['firstName'] = !empty($nameParts[0]) ? $nameParts[0] : null;
                $this->credentials['lastName'] = !empty($nameParts[1]) ? $nameParts[1] : null;
                $this->credentials['surName'] = !empty($nameParts[2]) ? $nameParts[2] : null;
            }
        }
        $this->clientId = null;
    }

    /**
     * @return array
     */
    public function getClientCredentials()
    {
        return collect($this->credentials)->merge([
            'payType' => $this->payType
        ])->toArray();
    }
}