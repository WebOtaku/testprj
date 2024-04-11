<?php
    require_once('const.php');

    /* 
     * Проверяет значение указанного свойства объекта на то является ли оно не пустой строкой
     * Принимает на вход: 
     *     stdClass $obj - объект свойство которого будет проверено,
     *     string $property_name - наименование свойства объекта
     * Возвращает: значение булевого типа (bool)
     */
    function check_str_property(stdClass $obj, string $property_name): bool {
        if (is_object($obj)) {
            if (property_exists($obj, $property_name)) {
                $val = $obj->$property_name;
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
    function create_ip_geo_obj(stdClass $ip_geo_info_response, array $req_props, string $default_str): stdClass {
        $ip_geo_info = new stdClass();

        foreach ($req_props as $property_name) {
            $ip_geo_info->$property_name = $default_str;
            if (check_str_property($ip_geo_info_response, $property_name)) {
                $ip_geo_info->$property_name = $ip_geo_info_response->$property_name;
            }
        }

        return $ip_geo_info;
    }

    function add_ip(array $form_data): array {
        require('db.php');

        $errors = [];
        $message = '';

        if (key_exists('ip', $form_data)) {
            if (!empty($form_data['ip'])) {
                $res = ip_geo_req($form_data['ip']);

                if (empty($res['error'])) {
                    $ip_geo_info = create_ip_geo_obj($res['val'], REQ_PROPS, DEFAULT_STR);

                    $cols = implode(',', DB_COLS);
                    $values = '"' . $form_data['ip'] . '"';
                    foreach (DB_COLS_REF as $property) {
                        if ($ip_geo_info->$property !== DEFAULT_STR) {
                            $values .= ',"' . $ip_geo_info->$property . '"';
                        } else {
                            $values .= ',NULL';
                        }
                    }

                    try {
                        $query = 'INSERT INTO ip_geo (' . $cols . ') VALUES (' . $values . ')';
                        if ($db->query($query)) $message = IP_ADD_MSG;
                    } catch (Exception $e) {
                        array_push($errors, IP_EXIST_ERR); //$e->getMessage()
                    }
                } else {
                    array_push($errors, $res['error']);
                }
            } else {
                array_push($errors, INPUT_IP_ERR);
            }
        } else {
            array_push($errors, INPUT_IP_ERR);
        }

        $db->close();

        return [$errors, $message];
    }
?>