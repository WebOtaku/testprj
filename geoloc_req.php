<?php
    function ip_geo_req(string $ip): array {
        $res = [
            'val' => NULL,
            'error' => ''
        ];

        if ($ip = filter_var($ip, FILTER_VALIDATE_IP)) {
            $ch = curl_init('http://www.geoplugin.net/json.gp?ip=' . $ip);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            $res['val'] = json_decode($response);
        }
        else {
            $res['error'] = 'Введите корректный IP-адресс';
        }

        return $res;
    }
?>