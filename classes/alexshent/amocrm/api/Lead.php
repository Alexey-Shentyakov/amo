<?php

namespace alexshent\amocrm\api;

# https://www.amocrm.ru/developers/content/api/leads

class Lead extends Api {
    
    public function get() {
        $response = $this->http_get(null, '/api/v2/leads');
        
        return $response['_embedded']['items'];
    }
    
    public function get_filter_creation_date($from, $to) {
        $response = $this->http_get(
            [
                'filter[date_create][from]' => $from,
                'filter[date_create][to]' => $to
            ],
            '/api/v2/leads'
        );
        
        return $response['_embedded']['items'];
    }
    
    public function add($data) {
        $response = $this->http_post(
        ['add' => $data],
        null,
        '/api/v2/leads'
        );
        
        return $response['_embedded']['items'];
    }
}
