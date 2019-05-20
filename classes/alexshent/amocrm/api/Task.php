<?php

# https://www.amocrm.ru/developers/content/api/tasks

namespace alexshent\amocrm\api;

class Task extends Api {
    public function add($data) {
        $response = $this->http_post(
        ['add' => $data],
        null,
        '/api/v2/tasks'
        );
        
        return $response['_embedded']['items'];
    }
}
