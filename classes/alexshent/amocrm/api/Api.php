<?php

namespace alexshent\amocrm\api;

abstract class Api {

    // HTTP GET request
    protected function http_get($params = null, $path) {
        
        $url = \alexshent\amocrm\Config::SCHEME . '://'
        . \alexshent\amocrm\Config::AUTHORITY
        . $path;
        
        if (!empty($params)) {
            $get_params = http_build_query($params);
            $url .= '?' . $get_params;
        }
        
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl,CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
        curl_setopt($curl,CURLOPT_URL, $url);
        curl_setopt($curl,CURLOPT_HEADER, false);
        curl_setopt($curl,CURLOPT_COOKIEFILE, \alexshent\amocrm\Config::COOKIE_FILE);
        curl_setopt($curl,CURLOPT_COOKIEJAR, \alexshent\amocrm\Config::COOKIE_FILE);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
        
        $out = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        $code=(int) $code;

        $errors = [
          301=>'Moved permanently',
          400=>'Bad request',
          401=>'Unauthorized',
          403=>'Forbidden',
          404=>'Not found',
          500=>'Internal server error',
          502=>'Bad gateway',
          503=>'Service unavailable'
        ];
        
        try
        {
          /* Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке */
          if($code!=200 && $code!=204) {
            throw new \Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',$code);
          }
        }
        catch(Exception $E)
        {
          die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
        }
        
        $response = json_decode($out, true);

        return $response;
    }
    
    // --------------------------------------
    
    // HTTP POST request
    protected function http_post($data, $params = null, $path, $use_cookie = true) {
        
        $url = \alexshent\amocrm\Config::SCHEME . '://'
        . \alexshent\amocrm\Config::AUTHORITY
        . $path;
        
        if (!empty($params)) {
            $get_params = http_build_query($params);
            $url .= '?' . $get_params;
        }
        
        $curl=curl_init();
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
        curl_setopt($curl,CURLOPT_URL, $url);
        curl_setopt($curl,CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl,CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curl,CURLOPT_HEADER, false);
        curl_setopt($curl,CURLOPT_COOKIEFILE, \alexshent\amocrm\Config::COOKIE_FILE);
        curl_setopt($curl,CURLOPT_COOKIEJAR, \alexshent\amocrm\Config::COOKIE_FILE);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 0);
        
        $out = curl_exec($curl);
        
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        $code = (int) $code;
        $errors = array(
          301=>'Moved permanently',
          400=>'Bad request',
          401=>'Unauthorized',
          403=>'Forbidden',
          404=>'Not found',
          500=>'Internal server error',
          502=>'Bad gateway',
          503=>'Service unavailable'
        );
        
        try
        {
          #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
         if($code!=200 && $code!=204)
            throw new \Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',$code);
        }
        catch(Exception $E)
        {
          die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
        }

        $response = json_decode($out, true);
        
        return $response;
    }
}
