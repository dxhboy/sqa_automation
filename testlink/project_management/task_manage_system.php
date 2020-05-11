<?php
# get global config
$config = require('db_config.php');
require './db_base.php';

class task_manage_system extends db_control {
    /*
     * include all task_manage_system control
     */
    public function write_table($temp , $table) {
        /*
         * write table record
         */
        global $config;
        if ($this->connect()) {
            return 1;
        }
        $temp_revert = array_flip($temp);
        if ($this->get_next_id($table)) {
            return 1;
        }
        $info = $this->define_array($table);
        if ($info === 1) {
            return 1;
        } else {
            $info['id'] = $this->next_id;
            foreach ($temp as $i) {
                if (!empty(array_key_exists($i, $_POST))) {
                    $info[$temp_revert[$i]] = $_POST[$i];
                }
            }
        }
        $sql = $this->create_sql($info, $table);
        if ($this->exec_sql($sql)) {
            return 1;
        }
        return 0;
    }

    public function get_name($temp, $table) {
        /*
         * use id get name
         * $temp is a array ,like ['1', '2', '3'] , id list
         */
        $result = []; 
        $fail_list = [];
        foreach ($temp as $i) {
            $array_ = [];
            $array_['id'] = $i;
            if ($this->select($array_, $table)) {
                $log = "get id:$i name fail\n";
                array_push($fail_list, $log);
            } else {
                $array_ = json_decode($this->json_data, true);
                $name = $array_[0]['name'];
                $result[$i] = $name;
            }
        }
        if ($fail_list!= []) {
            return 1; 
        }
        return json_encode($result, true);
    }

    public function delete_tablel($temp, $table) {
        /*
         * delete_tablel record
         */
        global $config;
        if ($this->connect()) {
            return 1;
        }
        $temp_revert = array_flip($temp);
        if ($this->get_next_id($table)) {
            return 1;
        }
        $info = $this->define_array($table);
        if ($info === 1) {
            return 1;
        } else {
            $info['id'] = $this->next_id;
            foreach ($temp as $i) {
                if (!empty(array_key_exists($i, $_POST))) {
                    $info[$temp_revert[$i]] = $_POST[$i];
                }
            }
        }
        $sql = $this->create_sql($info, $table, $type='delete');
        if ($this->exec_sql($sql)) {
            return 1;
        }
        return 0;
    }

    public function write_task() {
        /*
         * write_task 
         */
        $temp = [
            "name"         => "task_name",
            "notes"        => "task_notes",
			"parent_id"    => "task_parent_id",
			"project_id"   => "project",
            "status"       => "task_status",
            "group_id"     => "task_group",
            "staff_id"     => "task_staff",
            "start_time"   => "task_starttime",
            "est_com_time" => "task_endtime",
			"pc" => "task_pc",
			"taskpicpath" => "taskpicpath",
			"taskfilepath" => "taskfilepath",
			
        ];
        global $config;
        if ($this->write_table($temp, $config['task'])) {
            return 1;
        }
        return 0;
    }
    
    public function write_project() {
        /*
         * write_project 
         */
        $temp = [
            "name"         => "project_name",
            "notes"        => "project_notes",
            "status"       => "project_status",
            "start_time"   => "project_starttime",
            "est_com_time" => "project_endtime",
			"longtime" => "longtime",
			"picpath" => "picpath",
			"filepath" => "filepath",
        ];
        global $config;
        if ($this->write_table($temp, $config['project'])) {
            return 1;
        }
        return 0;
    }
    
    public function write_personnel() {
        /*
         * write_personnel 
         */
        $temp = [
            "name"     => "personnel_name",
            "group_id" => "personnel_group_id",
        ];
        global $config;
        if ($this->write_table($temp, $config['personnel'])) {
            return 1;
        }
        return 0;
    }
    
    public function write_group() {
        /*
         * write_group 
         */
        $temp = [
            "name"     => "group_name",
            "group_id" => "group_group_id",
        ];
        global $config;
        if ($this->write_table($temp, $config['groups'])) {
            return 1;
        }
        return 0;
    }

    public function modify_personnel() {
        /*
         * modify_personnel 
         */
        global $config;
        $temp = [
            "name"     => "personnel_name",
            "group_id" => "personnel_group_id",
        ];
        $set_ = [];
        $condition_ = [];
        $condition_['name'] = $_POST['personnel_name'];
        $set_['group_id'] = $_POST['personnel_group_id'];
        if ($this->update($set_, $condition_, $config["personnel"])) {
            return 1;
        }
        return 0;
    }

    public function get_all_personnel() {
        /*
         * get_all_personnel 
         */ 
        global $config;
        if ($this->select('', $config['personnel'], '')) {
            return 1;
        }
        return 0;
    }
    
    public function get_all_group() {
        /*
         * get_all_group 
         */ 
        global $config;
        if ($this->select('', $config['groups'], '')) {
            return 1;
        }
        return 0;
    }
    
    public function delete_personnel() {
        /*
         * delete_personnel
         */
        global $config;
        $table = $config["personnel"];
        $temp = explode(",", $_POST['del_personnel_id']);
        $fail_list = []; 
        foreach ($temp as $i) {
            $array_ = [];
            $array_["id"] = $i;
            $sql = $this->create_sql($array_, $table, $type="delete");
            if ($this->exec_sql($sql)) {
                $log = "delete id:$i fail";
                $fail_list[$i] = $log;
            } 
        }
        if ($fail_list != []) {
            return 1;
        }
        return 0;
    }  
    
    public function delete_group() {
        /*
         * delete_group
         */
        global $config;
        $table = $config["groups"];
        $temp = explode(",", $_POST['del_group_id']);
        $fail_list = []; 
        foreach ($temp as $i) {
            $array_ = [];
            $array_["id"] = $i;
            $sql = $this->create_sql($array_, $table, $type="delete");
            if ($this->exec_sql($sql)) {
                $log = "delete id:$i fail";
                $fail_list[$i] = $log;
            } 
        }
        if ($fail_list != []) {
            return 1;
        }
        return 0;
    }
}
?>
