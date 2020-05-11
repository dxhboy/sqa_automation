<?php
class db_control {
    /*
     * include all database control
     */
    public function __construct($host, $user, $passwd, $db) {  
        /*
         * init var 
         */ 
        $this->host = $host;
        $this->user = $user;
        $this->passwd = $passwd;
        $this->db = $db;
    }
    
    public function define_array($table) {
        /*
         * define defalut array
         * support all testlink table
         */ 
        global $config;
        $db = $config['db'];  
        $create_time = date('Y-m-d'); 
        $sql = "select COLUMN_NAME from information_schema.COLUMNS where table_name = '$table' and table_schema = '$db'";
        $info = [];
        if (!$this->select('', '', $sql)) {
            $array_ = json_decode($this->json_data, true); 
            $columns = array_values($array_);
            foreach ($columns as $i) {
                if (array_values($i)[0]=='create_time') {
                    $info[array_values($i)[0]] = $create_time;
                } else {
                    $info[array_values($i)[0]] = "";
                }
            }
        }
        return $info; 
    }  
    
    public function parse_array($array_, $combine="=") {
        /*
         * parse_array
         * $combine default is "=", mean combine key,value as key=value,
         * if $combine is not "=", return array $keys #$values;
         */
        $keys = (array_keys($array_));
        $values = (array_values($array_));
        $length = count($keys);
        $new_array = [];
        for ($i=0; $i<$length; ++$i) {
            $val = stripcslashes($values[$i]);
            $val = "'".str_ireplace("'","\'",$val,$count)."'"; 
            if ($val==="''") {
                $val = "null";
            }
            if ($combine === "=") {
                $new_array[$i] = "$keys[$i]=$val";
            } else {
                $values[$i] = $val;
                
            }
        }
        if ($combine !== "=") {
            $new_array[0] = $keys;
            $new_array[1] = $values;
        }
        return $new_array; 
    }
    
    public function connect() {
        /*
         * connect mysql , return 1 if fail else 0
         */
        if (!empty($this->con)) {
            echo "success, mysql connect had exists\n";
            return 0;
        }
        $con = new mysqli($this->host, $this->user, $this->passwd, $this->db);
        if ($con->connect_errno) {
            echo  "failed to connect mysql :(" . $con->connect_errno . ")" .$con-connect_error;
            return 1; 
        } else {
            mysqli_query($con, "SET NAMES utf8"); # set encoding
            echo "success to connect mysql\n";
            $this->con = $con;
            return 0;
        }
    }
    
    public function get_next_id($table) {
        /*
         *get table last record id,  next_id = id + 1,
        */
        $con = $this->con; 
        $id = 0;
        $sql = "select * from $table order by id desc limit 1";
        $result = mysqli_query($con, $sql);
        if (!$result) {
            echo "exec sql fail, " . mysqli_error($con);
            return 1;
        } 
        while ($row = mysqli_fetch_array($result)) {
            $id = $row['id'];
        }
        $this->next_id = $id + 1;
        echo "get last_id:" .$id. " create next_id:" .$this->next_id. " success\n";
        return 0;
    }

    public function create_sql($array_, $table, $type='insert') {
        /*
         *design this function for create sql (delete and insert)
         */
        if ($type == 'insert') {
            $new_array = $this->parse_array($array_, $combine='');
            $key_str = implode(',', $new_array[0]);
            $value_str = implode(',', $new_array[1]);
            $value_str = str_ireplace("''","null",$value_str,$count);
            $sql = "insert into $table ($key_str) values ($value_str)";
        } elseif ($type == 'delete') {
            $new_array = $this->parse_array($array_);
            $temp_str = implode(' and ', $new_array);
            $sql = "delete from $table where $temp_str";
        } elseif ($type == 'update') {
            # $array_ = [$set_, $condition_]; 
            $new_array0 = $this->parse_array($array_[0]);
            $new_array1 = $this->parse_array($array_[1]);
            $set_str = implode(',', $new_array0);
            $condition_str = implode(' and ', $new_array1);
            $sql = "update $table set $set_str where $condition_str";
        } 
        return $sql;
    }
    
    public function exec_sql($sql) {
        /*
         * exec sql
         */
        if ($this->connect()) {
            return 1;
        } 
        $con = $this->con;
        $result = mysqli_query($con, $sql);
        if (!$result) {
            echo "exec_sql fail " . mysqli_error($con);
            return 1;
        }
        echo "exec ($sql) success\n";
        return 0;
    }

    public function select($array_='', $table='', $sql='') {
        /*
         * select * from * where *=* and ,
         * if $array_ is empty, sql is: select * from table_name
         * return json data
         */ 
        $this->connect();
        $con = $this->con;
        if (empty($sql)) {
            if (empty($array_)) {
                $sql = "select * from $table";
            } else {
                $sql = $this->create_sql($array_, $table, $type='delete');
                $sql = str_ireplace("delete", "select *", $sql);
            }
        }
        $result = mysqli_query($con, $sql);
        if (!$result) {
            echo "select fail " . mysqli_error($con);
            return 1;
        }
        $json_data = [];
        $num = 0; 
        while ($row = mysqli_fetch_array($result)) {
            $count = count($row)/2;
            $index = [];
            for ($i=0;$i<$count;$i++) {
                $index[$i] = ' ';
            }
            $row = array_diff_key($row, $index) ;
            $json_data[$num] = $row;
            $num += 1;
        }
        # JSON_UNESCAPED_UNICODE
        $this->json_data = json_encode($json_data, JSON_UNESCAPED_UNICODE);
        echo "select success, result save as \$this->json_data\n";
        return 0;
    }

    public function update($set_, $condition_, $table) {
        /*
         * update table_name set xxx=xxx,xxx=xxx where xxx=xxx and xxx=xxx;
         * $set_ is a array, just like $set = [$name='test'];
         * $condition_ is a array, just like $condition_ = [$id='0'];
         */
        $con = $this->con;
        $array_ = [$set_, $condition_];
        $sql = $this->create_sql($array_, $table, $type='update');
        if ($this->exec_sql($sql) == 1) {
            return 1;
        }
        return 0;
    }
  
  public function delete($condition_, $table) {
      /*
       * delete table_name set xxx=xxx,xxx=xxx where xxx=xxx and xxx=xxx;
       * $condition_ is a array, just like $condition_ = [$id='0'];
       */
      $con = $this->con;
      
      $sql = $this->create_sql($condition_, $table, $type='delete');
      if ($this->exec_sql($sql) == 1) {
          return 1;
      }
      return 0;
  }
}
?>
