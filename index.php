<?php
    require_once('const.php');
    require_once('lib.php');

    if (count($_POST)) {
        list($errors, $message) = add_ip($_POST);
    } else {
        $errors = [];
        $message = '';
    }

    $page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? $_GET['page'] : 1;

    list($ip_data, $page, $total_pages, $errors) = get_pagination_ip_data($page, $errors);
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= TITLE ?></title>

    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/main.css" />
</head>

<body>
    <div class="container">
        <h3 class="page_header"><?= IP_LIST_STR ?></h3>
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
                <div class="col-auto">
                    <?php
                    if (count($errors)) {
                        echo '<div class="form__errors">*' . implode(', ', $errors) . '</div>';
                    }
                    if (!empty($message)) {
                        echo '<div class="form__message">*' . $message . '</div>';
                    }
                    ?>
                </div>
            </div>
        </form>
        <div class="ip_list">
            <ul class="list-group">
                <?php
                foreach ($ip_data as $i => $ip_info) {
                    echo '<li class="list-group-item">';

                    $col_str = DB_COLS_STR[DB_COLS[0]];
                    $val = ($ip_info[0]) ? $ip_info[0] : DEFAULT_STR;
                    
                    echo '<button class="btn btn-primary ip_btn_collapse" type="button" data-bs-toggle="collapse" data-bs-target="#collapse'.$i.'">' .
                        $col_str . ': ' . $val
                    . '</button>';

                    //echo '<button class="btn btn-danger ip_btn_del" type="button">Удалить</button>';

                    echo '<div class="collapse ip_info_collapse" id="collapse'.$i.'"><div class="card card-body">';
                    for ($j = 1; $j < count($ip_info); $j++) {
                        $col_str = DB_COLS_STR[DB_COLS[$j]];
                        $val = ($ip_info[$j]) ? $ip_info[$j] : DEFAULT_STR;
                        echo '<p><span class="col_name">'.$col_str.'</span>: <span class="col_val">'.$val.'</span></p>';
                    }
                    echo '</div></div>';
                    echo '</li>';
                }
                ?>
            </ul>
            <nav>
                <ul class="pagination">
                    <?php
                    if ($page >= 2) {
                        echo '<li class="page-item">
                                  <a class="page-link" href="?page=' . ($page - 1) . '"><span>&laquo;</span></a>
                              </li>';
                    }
                    ?>
                    <li class="page-item">
                        <a class="page-link" href="#"><?= "$page/$total_pages" ?></a>
                    </li>
                    <?php
                    if ($page < $total_pages) {
                        echo '<li class="page-item">
                                  <a class="page-link" href="?page=' . ($page + 1) . '"><span>&raquo;</span></a>
                              </li>';
                    }
                    ?>
                </ul>
            </nav>
        </div>
    </div>
    <script src="js/bootstrap.min.js"></script>
</body>

</html>