<?php

declare(strict_types=1);

namespace TestPrj;

require_once 'autoloader.php';

$errors = [];
$message = '';

if (count($_GET)) {
    if (key_exists('action', $_GET)) {
        if ($_GET['action'] === 'delete') {
            list($errors, $message) = IpGeo::deleteIP($_GET);
        } elseif ($_GET['action'] === 'add') {
            if (count($_POST)) {
                list($errors, $message) = IpGeo::addIP($_POST);
            }
        }
    }
}

$page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? (int) $_GET['page'] : 1;

list($ipData, $page, $totalPages, $errors) = IPGeo::getPaginationIpData($page, $errors);

?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= IpGeoConst::TITLE ?></title>

    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/main.css" />
</head>

<body>
    <div class="container">
        <h3 class="page_header"><?= IpGeoConst::IP_LIST_STR ?></h3>
        <form class="form ip_form" action="?action=add" method="POST">
            <div class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="ip" class="col-form-label"><b><?= IpGeoConst::IP_STR ?></b></label>
                </div>
                <div class="col-auto">
                    <input type="text" id="ip" class="form-control" name="ip">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary"><?= IpGeoConst::SUBMIT_BTN_STR ?></button>
                </div>
                <div class="col-auto">
                    <?= HtmlWriter::getErrorsHTML($errors); ?>
                    <?= HtmlWriter::getMessageHTML($message); ?>
                </div>
            </div>
        </form>
        <div class="ip_list">
            <ul class="list-group">
                <?= HtmlWriter::getIPListHTML($ipData); ?>
            </ul>
            <?= HtmlWriter::getPaginationHTML($page, $totalPages); ?>
        </div>
    </div>
    <script src="js/bootstrap.min.js"></script>
</body>

</html>