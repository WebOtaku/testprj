<?php
    define('TITLE', 'IP Геолакация');
    define('IP_STR', 'IP');
    define('COUNTRY_STR', 'Страна');
    define('REGION_STR', 'Регион');
    define('CITY_STR', 'Город');
    define('DEFAULT_STR', 'Неизвестно');
    define('IP_LIST_STR', 'Список IP-адрессов');

    define('INPUT_IP_ERR', 'Введите IP-адресс');
    define('IP_EXIST_ERR', 'Указанный IP-адресс уже добавлен');
    define('IP_ADD_MSG', 'IP-адресс успешно добавлен');

    define('SUBMIT_BTN_STR', 'Добавить');

    define('DB_TABLE', 'ip_geo');
    define('IP_COL', 'ip');
    define('DB_COLS', [IP_COL, 'country', 'region', 'city']);

    define('REQ_PROPS', ['geoplugin_countryName', 'geoplugin_regionName', 'geoplugin_city']);

    define('DB_COLS_REF', [
        'country' =>  'geoplugin_countryName',
        'region' => 'geoplugin_regionName',
        'city' => 'geoplugin_city'
    ]);

    define('DB_COLS_STR', [
        'ip' => IP_STR,
        'country' => COUNTRY_STR,
        'region' => REGION_STR,
        'city' => CITY_STR
    ]);

    define('PER_PAGE', 3);
?>

