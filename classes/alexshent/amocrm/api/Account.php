<?php

namespace alexshent\amocrm\api;

# https://www.amocrm.ru/developers/content/api/account

class Account extends Api {
    
    public function get_info() {
        $response = $this->http_get(
            null,
            '/api/v2/account'
        );
        
        return $response;
    }
    
    public function get_users_info() {
        $response = $this->http_get(
            [
                'with' => 'users'
            ],
            '/api/v2/account'
        );
        
        return $response['_embedded']['users'];
    }
    
    public function get_custom_fields_info() {
        $response = $this->http_get(
            ['with' => 'custom_fields'],
            '/api/v2/account'
        );
        
        return $response;
    }
}
