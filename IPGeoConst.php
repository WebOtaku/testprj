<?php

declare(strict_types=1);

namespace TestPrj;

class IPGeoConst
{
    public const CONF_FILE_NAME = 'config.ini';
    public const TITLE = 'IP Геолакация';
    public const IP_STR = 'IP';
    public const COUNTRY_STR = 'Страна';
    public const REGION_STR = 'Регион';
    public const CITY_STR = 'Город';
    public const DEFAULT_STR = 'Неизвестно';
    public const IP_LIST_STR = 'Список IP-адрессов';
    public const INPUT_IP_ERR = 'Введите IP-адресс';
    public const INPUT_CORRECT_IP_ERR = 'Укажите корректный IP-адресс';
    public const IP_EXIST_ERR = 'Указанный IP-адресс уже добавлен';
    public const IP_DELETE_ERR = 'Ошибка удаления IP-адреса';
    public const UNKNOWN_IP_ERR = 'Неизвестный IP-адресс';
    public const DB_CONNECT_ERR = 'Ошибка подключения к БД';
    public const DB_ERR = 'Ошибка при обращении к БД';
    public const IP_ADD_MSG = 'IP-адресс успешно добавлен';
    public const IP_DELETE_MSG = 'Успешно удалён IP-адресс';
    public const SUBMIT_BTN_STR = 'Добавить';
    public const DB_TABLE = 'ip_geo';
    public const IP_COL = 'ip';
    public const DB_COLS = [self::IP_COL, 'country', 'region', 'city'];
    public const REQ_PROPS = [
        'geoplugin_countryName', 
        'geoplugin_regionName', 
        'geoplugin_city'
    ];
    public const DB_COLS_REF = [
        'country' =>  'geoplugin_countryName',
        'region' => 'geoplugin_regionName',
        'city' => 'geoplugin_city'
    ];
    public const DB_COLS_STR = [
        'ip' => self::IP_STR,
        'country' => self::COUNTRY_STR,
        'region' => self::REGION_STR,
        'city' => self::CITY_STR
    ];
    public const PER_PAGE = 4;
}
