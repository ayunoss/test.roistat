<?php
/**
 * Решила положить дополнительные функции в includes/functions.php, т.к не знаю куда лучше
 * (подобным образом хранила доп-ные функции при написании модуля для WordPress)
 */

spl_autoload_register(function ($class) {
    include SERVICES. '/' . $class . '.php';
});

/**
 * Выводит результат работы скрипта в консоль браузера
 *
 * @param $data
 */
function print2Console($data) {
    if (is_array($data)) {
        $output = implode(',', $data);
        echo 'Result: ' . $output;
    } else {
        echo 'Result: ' . $data;
    }
}