<?php

declare(strict_types=1);

namespace TestPrj;

use stdClass, Exception, mysqli;

class IPGeo 
{
    public static function connection(
        string $host, 
        string $login, 
        string $password,
        string $dbName
    ) {

    }

    /* 
    * Выполняет запрос к API www.geoplugin.net
    * Принимает на вход: 
    *     string $ip - IP-адресс информацию о местоположении, которого необходимо получить,
    * Возвращает массив состоящий из:
    *     'val' => NULL или stdClass - объект с информацией об IP-адрессе
    *     'error' => string - ошибка
    */
    public static function ipGeoReq(string $ip): array
    {
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
        } else {
            $res['error'] = 'Введите корректный IP-адресс';
        }

        return $res;
    }

    /* 
    * Проверяет значение указанного свойства объекта на то является ли оно не пустой строкой
    * Принимает на вход: 
    *     stdClass $obj - объект свойство которого будет проверено,
    *     string $property_name - наименование свойства объекта
    * Возвращает: значение булевого типа (bool)
    */
    public static function checkStrProperty(stdClass $obj, string $propertyName): bool
    {
        if (is_object($obj)) {
            if (property_exists($obj, $propertyName)) {
                $val = $obj->$propertyName;
                if (!empty($val) && is_string($val)) {
                    return true;
                }
            }
        }
        return false;
    }

    /* 
    * Создает и возвращает объект содержащий информацию об IP адрессе
    * Принимает на вход: 
    *     stdClass $ip_geo_info_response - объект с информаций полученной при обращении к API "www.geoplugin.net"
    *     array $req_props - массив наименованний свойств объекта, значения которых необходимо получить из $ip_geo_info_response
    *     string $default_str - значение по умолчанию, которое будет присвоено свойству нового объекта в случае если значение
    *         свойства объекта $ip_geo_info_response пустое или отсутствует.                    
    * Возвращает: возвращает объект содержащий информацию об IP адрессе
    */
    public static function createIPGeoObj(stdClass $ipGeoInfoResponse): stdClass
    {
        $ip_geo_info = new stdClass();

        foreach (IpGeoConst::REQ_PROPS as $property_name) {
            $ip_geo_info->$property_name = IpGeoConst::DEFAULT_STR;
            if (self::checkStrProperty($ipGeoInfoResponse, $property_name)) {
                $ip_geo_info->$property_name = $ipGeoInfoResponse->$property_name;
            }
        }

        return $ip_geo_info;
    }

    /* 
    * Добавляет IP-адресс и информацию о его местоположении в базу данных
    * Принимает на вход: 
    *     array $form_data - массив с данными из формы добавления IP-адресса
    * Возвращает массив состоящий из: 
    *     array $errors - массива с ошибками,
    *     string $message - сообщение об выполненном действии
    */
    public static function addIP(array $form_data): array
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $db = new mysqli('localhost', 'root', '', 'testprj');

        $errors = [];
        $message = '';
        $table = IpGeoConst::DB_TABLE;

        if (key_exists('ip', $form_data)) {
            if (!empty($form_data['ip'])) {
                $res = self::createIPGeoObj($form_data['ip']);

                if (empty($res['error'])) {
                    $ip_geo_info = self::createIPGeoObj($res['val']);

                    $cols = implode(',', IpGeoConst::DB_COLS);
                    $values = '"' . $form_data['ip'] . '"';
                    foreach (IpGeoConst::DB_COLS_REF as $property) {
                        if ($ip_geo_info->$property !== IpGeoConst::DEFAULT_STR) {
                            $values .= ',"' . $ip_geo_info->$property . '"';
                        } else {
                            $values .= ',NULL';
                        }
                    }

                    try {
                        $query = "INSERT INTO $table ($cols) VALUES ($values)";
                        if ($db->query($query)) $message = IpGeoConst::IP_ADD_MSG;
                    } catch (Exception $e) {
                        array_push($errors, IpGeoConst::IP_EXIST_ERR); //$e->getMessage()
                    }
                } else {
                    array_push($errors, $res['error']);
                }
            } else {
                array_push($errors, IpGeoConst::INPUT_IP_ERR);
            }
        } else {
            array_push($errors, IpGeoConst::INPUT_IP_ERR);
        }

        $db->close();

        return [$errors, $message];
    }

    /* 
    * Возвращает данный по IP-адрессам с постраничным разделением
    * Принимает на вход: 
    *     int $page - номер текущей страницы
    *     array $errors - массив ошибок            
    * Возвращает массив состоящий из: 
    *     array $ip_data - массив с данными об IP-адрессах, 
    *     int $page - номер текушей страницы,
    *     int $total_pages - общее кол-во страниц
    *     array $errors - массива с ошибками
    */
    public static function getPaginationIpData(int $page, array $errors): array
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $db = new mysqli('localhost', 'root', '', 'testprj');

        $result = NULL;

        if ($page <= 0) $page = 1;
        $per_page = IpGeoConst::PER_PAGE;

        $total_items = 0;

        $table = IpGeoConst::DB_TABLE;
        try {
            $query = "SELECT * FROM $table";
            $total_items = count($db->query($query)->fetch_all());
        } catch (\Exception $e) {
            array_push($errors, $e->getMessage());
        }

        $total_pages = ceil($total_items / $per_page);
        if ($page > $total_pages) $page = $total_pages;

        $initial_page = ($page - 1) * $per_page;

        try {
            $query = "SELECT * FROM $table LIMIT $initial_page, $per_page";
            $result = $db->query($query);
        } catch (\Exception $e) {
            array_push($errors, $e->getMessage());
        }

        $ip_data = $result->fetch_all();

        $db->close();

        return [$ip_data, $page, $total_pages, $errors];
    }
}

    
