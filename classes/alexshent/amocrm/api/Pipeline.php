<?php

namespace alexshent\amocrm\api;

# https://www.amocrm.ru/developers/content/api/pipelines

class Pipeline extends Api {
    
    public function get() {
        $response = $this->http_get(null, '/api/v2/pipelines/');
        return $response['_embedded']['items'];
    }
}
