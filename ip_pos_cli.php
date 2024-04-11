<?php
    // var_dump($argc); // количество переданных параметров
    // var_dump($argv); // переданные параметры
    if (array_key_exists(1, $argv)) {
        if($argv[1] === "cli") {
            echo "This is cli section";
        }
    } else {
        echo "Enter correct parametr to perform php-cli script";
    }
?>