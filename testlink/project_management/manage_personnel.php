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
} elseif ($_REQUEST["method"]=="get_all_personnel") {
    if ($obj->get_all_personnel()) {
        echo 1;
    } else {
        $result = $obj->json_data;
        echo "json_data-->$result<--";
    }
} elseif ($_REQUEST["method"]=="add_personnel") {
    if ($obj->write_personnel()) {
        echo "write_personnel_fail";
    } else {
        echo "write_personnel_succ";
    }
} elseif ($_REQUEST["method"]=="modify_personnel") {
    if ($obj->modify_personnel()) {
        echo "modify_personnel_fail";
    } else {
        echo "modify_personnel_succ";
    }
} elseif ($_REQUEST["method"]=="del_personnel") {
    if ($obj->delete_personnel()) {
        echo "del_personnel_fail";
    } else {
        echo "del_personnel_succ";
    }
} elseif ($_REQUEST["method"]=="replace_id2name") {
    $temp = explode(",", $_POST['id2name_string']);
    echo var_dump($temp);
    $result = $obj->get_name($temp, $config['groups']);
    if ($result==1) {
        echo "replace_id2name fail";
    } else {
        echo "replace_id2name succ";
        echo "id2namestr-->$result<--";
    }
}
?>
