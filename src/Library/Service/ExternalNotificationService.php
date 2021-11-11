<?php

namespace Api\Library\Service;

use Api\Library\Contracts\Service\NotificationServiceInterface;
use Api\Modules\Users\DomaiModel\Model\User;

class ExternalNotificationService implements NotificationServiceInterface
{
    public function sendNotification(User $receiver): bool
    {
        $url = 'http://o4d9z.mocklab.io/notify';
        $result = $this->sendCurl($url);
        if ($result instanceof \stdClass) {
            return $result->message == 'Success';
        }
        
        return false;
    }
    
    private function sendCurl(string $url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
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
