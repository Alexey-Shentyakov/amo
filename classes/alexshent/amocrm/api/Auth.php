<?php

namespace alexshent\amocrm\api;

# https://www.amocrm.ru/developers/content/api/auth

class Auth extends Api {
    
    public function auth() {
        $response = $this->http_post(
            [
                'USER_LOGIN' => \alexshent\amocrm\Config::USER_LOGIN,
                'USER_HASH' => \alexshent\amocrm\Config::USER_HASH
            ],
            [
                'type' => 'json'
            ],
            '/private/api/auth.php',
            false
        );
        
        $result = $response['response'];
        
        if (isset($result['auth'])) {
            return true;
        }
        else {
            return false;
        }
    }
}
