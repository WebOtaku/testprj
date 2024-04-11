<?php
    require('db.php');
    require_once('geoloc_req.php');
    require_once('const.php');
    require_once('lib.php');

    if (count($_POST)) {
        list($errors, $message) = add_ip($_POST);
    } else {
        $errors = [INPUT_IP_ERR];
        $message = '';
    }

    $result = NULL;

    try {
        $query = 'SELECT * FROM ip_geo';
        $result = $db->query($query);
    } catch (Exception $e) {
        array_push($errors, IP_EXIST_ERR); //$e->getMessage()
    }

    $result = $result->fetch_all();

    $ip_list = '';
    foreach ($result as $ip_geo_info) {
        $ip_list .= '<li class="list-group-item">';
        for ($i = 0; $i < count($ip_geo_info); $i++) {
            $col_str = DB_COLS_STR[DB_COLS[$i]];
            $val = ($ip_geo_info[$i]) ? $ip_geo_info[$i] : DEFAULT_STR;
            $ip_list .= '<p>'. $col_str . ': ' . $val .'</p>';
        }
        $ip_list .= '</li>';
    }
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= TITLE ?></title>

    <link rel="stylesheet" href="css/bootstrap.min.css" />
</head>

<body>
    <div class="container">
        <form class="form ip_form" method="POST">
            <div class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="ip" class="col-form-label"><?= IP_STR ?></label>
                </div>
                <div class="col-auto">
                    <input type="text" id="ip" class="form-control" name="ip">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary"><?= SUBMIT_BTN_STR ?></button>
                </div>
            </div>
                <?php
                    if (count($errors)) {
                        echo '<div class="form__errors">*' . implode(', ', $errors) . '</div>';
                    }
                    if (!empty($message)) {
                        echo '<div class="form__message">*' . $message . '</div>';
                    }
                ?>
        </form>
        <div class="ip_lisst">
            <h3><?= IP_LIST_STR ?></h3>
            <ul class="list-group">
                <?= $ip_list ?>
            </ul>
        </div>
    </div>
    <script src="js/bootstrap.min.js"></script>
</body>

</html>