<?php
require './task_manage_system.php';
$obj = new task_manage_system($config['host'], $config['user'], $config['passwd'], $config['db']);

if ($_REQUEST["method"]=="get_all_group") {
    if ($obj->get_all_group()) {
        echo 1;
    } else {
        $result = $obj->json_data;
        echo "json_data-->$result<--";
    }
} elseif ($_REQUEST["method"]=="add_group") {
    if ($obj->write_group()) {
        echo "write_group_fail";
    } else {
        echo "write_group_succ";
    }
} elseif ($_REQUEST["method"]=="del_group") {
    if ($obj->delete_group()) {
        echo "del_group_fail";
    } else {
        echo "del_group_succ";
    }
}
?>
