<?php

declare(strict_types=1);

namespace TestPrj;

use stdClass, Exception, mysqli, PDO, PDOException;

class IPGeo 
{
    /* 
     * Возвращает объект подключения к БД
     * Принимает на вход: 
     *     string $filename - имя файла конфигурации
     * Возвращает:
     *     array $conf - массив с параметрами определёнными в файле конфигурации
     */
    public static function parseConfigIni(string $filename) 
    {
        $conf = parse_ini_file($filename);
        return $conf;
    }

    /* 
     * Возвращает объект подключения к БД
     * Принимает на вход: 
     *     string $host - хост либо IP-адресс БД,
     *     string $username - имя пользователя БД,
     *     string $password - пароль БД,
     *     string $dbName - наименование БД,
     *     string $errors - массив с ошибок
     * Возвращает:
     *     mysqli $db - объект подключения к БД
     */
    public static function connection(
        string $host, 
        string $username, 
        string $password,
        string $dbName,
        array $errors = [] 
    ) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        
        $db = NULL;

        // MySQL Connection
        // try {
        //     $db = @new mysqli($host, $username, $password, $dbName);
        // } catch (Exception $e) {
        //     $errorMsg = '<b>' . IPGeoConst::DB_CONNECT_ERR . '</b>: ' . $e->getMessage();
        //     array_push($errors, $errorMsg);
        // }

        // PDO Connection (MS MySQL Server Azure)
        try {
            $db = new PDO("sqlsrv:server = $host; Database = $dbName", $username, $password);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // print("Error connecting to SQL Server.");
            // die(print_r($e));
            $errorMsg = '<b>' . IPGeoConst::DB_CONNECT_ERR . '</b>: ' . $e;
            array_push($errors, $errorMsg);
        }

        return [$db, $errors];
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
            $res['error'] = IPGeoConst::INPUT_CORRECT_IP_ERR;
        }

        return $res;
    }

    /* 
    * Проверяет значение указанного свойства объекта на то является ли оно не пустой строкой
    * Принимает на вход: 
    *     stdClass $obj - объект свойство которого будет проверено,
    *     string $propertyName - наименование свойства объекта
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
    *     stdClass $ipGeoInfoResponse - объект с информаций полученной при обращении к API "www.geoplugin.net"              
    * Возвращает: 
    *     stdClass $ipInfo - объект содержащий информацию об IP адрессе
    */
    public static function createIPInfoObj(stdClass $ipGeoInfoResponse): stdClass
    {
        $ipInfo = new stdClass();

        foreach (IpGeoConst::REQ_PROPS as $propertyName) {
            $ipInfo->$propertyName = IpGeoConst::DEFAULT_STR;
            if (self::checkStrProperty($ipGeoInfoResponse, $propertyName)) {
                $ipInfo->$propertyName = $ipGeoInfoResponse->$propertyName;
            }
        }

        return $ipInfo;
    }

    /* 
    * Добавляет IP-адресс и информацию о его местоположении в базу данных
    * Принимает на вход: 
    *     array $formData - массив с данными из формы добавления IP-адреса ($_POST)
    * Возвращает массив состоящий из: 
    *     array $errors - массива с ошибками,
    *     string $message - сообщение об выполненном действии
    */
    public static function addIP(array $formData): array
    {
        $errors = [];
        $message = '';

        $conf = IPGeo::parseConfigIni(IPGeoConst::CONF_FILE_NAME);

        list($db, $errors) = self::connection(
            $conf['db_host'],
            $conf['db_username'],
            $conf['db_password'],
            $conf['db_name'],
            $errors
        );

        if (!$db) {
            return [$errors, $message];
        }

        $table = IpGeoConst::DB_TABLE;

        if (key_exists('ip', $formData)) {
            if (!empty($formData['ip'])) {
                $res = self::ipGeoReq($formData['ip']);

                if (empty($res['error'])) {
                    $ipInfo = self::createIPInfoObj($res['val']);

                    $cols = implode(',', IpGeoConst::DB_COLS);
                    $values = "'{$formData['ip']}'";
                    foreach (IpGeoConst::DB_COLS_REF as $propertyName) {
                        if ($ipInfo->$propertyName !== IpGeoConst::DEFAULT_STR) {
                            $values .= ",'{$ipInfo->$propertyName}'";
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

        //$db->close(); // MySQL
        $db = null; // PDO

        return [$errors, $message];
    }

    /* 
     * Удаляет указанный IP-адресс из БД
     * Принимает на вход: 
     *     array $urlData - массив с URL-параметрами ($_GET)
     * Возвращает массив состоящий из: 
     *     array $errors - массива с ошибками,
     *     string $message - сообщение об выполненном действии
     */
    public static function deleteIP(array $urlData): array
    {
        $errors = [];
        $message = '';

        if (key_exists('ip', $urlData)) {
            if (filter_var($urlData['ip'], FILTER_VALIDATE_IP)) {
                $conf = IPGeo::parseConfigIni(IPGeoConst::CONF_FILE_NAME);

                list($db, $errors) = self::connection(
                    $conf['db_host'],
                    $conf['db_username'],
                    $conf['db_password'],
                    $conf['db_name'],
                    $errors
                );

                $ip = $urlData['ip'];
                $ip_col = IPGeoConst::IP_COL;
                $table = IPGeoConst::DB_TABLE;

                try {
                    $query = "SELECT $ip_col FROM $table WHERE $ip_col = '$ip'";
                    $temp = $db->query($query);
                    if ($temp) {
                        //if (count($temp->fetch_all())) { // MySQL
                        if (count($temp->fetchAll())) { // PDO
                            $query = "DELETE FROM $table WHERE $ip_col = '$ip'";
                            if ($db->query($query)) {
                                $message = IPGeoConst::IP_DELETE_MSG . ": $ip";
                            } 
                        } else {
                            array_push($errors, IPGeoConst::UNKNOWN_IP_ERR . ": $ip");
                        }
                    }
                    $temp = null; // PDO
                } catch (Exception $e) {
                    array_push($errors, IPGeoConst::IP_DELETE_ERR);
                }

                //$db->close(); // MySQL
                $db = null; // PDO
            } else {
                array_push($errors, IPGeoConst::INPUT_CORRECT_IP_ERR);
            }
        }

        return [$errors, $message];
    }

    /* 
    * Возвращает данный по IP-адрессам с постраничным разделением
    * Принимает на вход: 
    *     int $page - номер текущей страницы
    *     array $errors - массив ошибок            
    * Возвращает массив состоящий из: 
    *     array $ipData - массив с данными об IP-адрессах, 
    *     int $page - номер текушей страницы,
    *     int $totalPages - общее кол-во страниц
    *     array $errors - массив с ошибок
    */
    public static function getPaginationIpData(int $page, array $errors): array
    {
        $conf = IPGeo::parseConfigIni(IPGeoConst::CONF_FILE_NAME);

        list($db, $errors) = self::connection(
            $conf['db_host'],
            $conf['db_username'],
            $conf['db_password'],
            $conf['db_name'],
            $errors
        );

        $result = null;
        $ipData = [];
        $totalPages = 0;

        if (!$db) {
            return [$ipData, $page, 0, $errors];
        }

        if ($page <= 0) $page = 1;
        $per_page = IpGeoConst::PER_PAGE;

        $total_items = 0;

        $table = IpGeoConst::DB_TABLE;
        try {
            $query = "SELECT * FROM $table";
            $temp = $db->query($query);
            if ($temp) {
                //$total_items = count($temp->fetch_all()); // MySQL
                $total_items = count($temp->fetchAll()); // PDO
            }
            $temp = null; // PDO
        } catch (Exception $e) {
            $errorMsg = '<b>' . IPGeoConst::DB_ERR . '</b>: ' . $e->getMessage();
            array_push($errors, $errorMsg);
        }

        if ($total_items) {
            $totalPages = ceil($total_items / $per_page);
            if ($page > $totalPages) $page = $totalPages;

            $initial_page = ($page - 1) * $per_page;

            try {
                //$query = "SELECT * FROM $table LIMIT $initial_page, $per_page"; // MySQL
                
                $from = $initial_page + 1; // PDO MS SQL Server
                $to = $per_page * $page; // PDO MS SQL Server

                $cols = implode(', ', IPGeoConst::DB_COLS);

                $query = "SELECT $cols FROM
                    (
                        SELECT ROW_NUMBER() OVER (ORDER BY (SELECT 0)) as [Count], * FROM $table
                    ) as a
                    WHERE [Count] BETWEEN $from and $to"; // PDO MS SQL Server
                $result = $db->query($query);
            } catch (Exception $e) {
                $errorMsg = '<b>' . IPGeoConst::DB_ERR . '</b>: ' . $e->getMessage();
                array_push($errors, $errorMsg);
            }
            
            
            if ($result) {
                //$ipData = $result->fetch_all(); // MySQL
                $ipData = $result->fetchAll(PDO::FETCH_NUM);
                $result = null; // PDO
            }
        }
        
        //$db->close(); // MySQL
        $db = null; // PDO

        return [$ipData, $page, (int) $totalPages, $errors];
    }
}
