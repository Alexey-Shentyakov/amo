<?php

namespace alexshent\amocrm\api;

# https://www.amocrm.ru/developers/content/api/contacts

class Contact extends Api {
    
    public function get_by_email($email) {
        $response = $this->http_get(
            [
                'query' => $email
            ],
            '/api/v2/contacts/'
        );
        
        return $response['_embedded']['items'];
    }
    
    public function get_by_telnum($telnum) {
        $response = $this->http_get(
            [
                'query' => $telnum
            ],
            '/api/v2/contacts/'
        );
        
        return $response['_embedded']['items'];
    }
    
    public function add($data) {
        $response = $this->http_post(
            ['add' => $data],
            null,
            '/api/v2/contacts/'
        );
        
        return $response['_embedded']['items'];
    }
}
