<?php

namespace Api\Library\Service;

use Api\Library\Contracts\Service\AuthorizeServiceInterface;
use Api\Modules\Users\DomaiModel\Model\User;

class ExternalAuthorizationService implements AuthorizeServiceInterface
{
    public function authorize(User $Payer, $value): bool
    {
        $url = 'https://run.mocky.io/v3/8fafdd68-a090-496f-8c9a-3442cf30dae6';
        $result = $this->sendCurl($url);
        if ($result instanceof \stdClass) {
            return $result->message == 'Autorizado';
        } else {
            return false;
        }
    }
    
    private function sendCurl(string $url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);
        
        // Submit the request
        $result = curl_exec($curl);
        curl_close($curl);
        
        return json_decode($result);
    }
}
