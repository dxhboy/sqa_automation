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
}
$b = new db_control('172.0.10.81', 'root', 'casa', 'testlink');
if ($_POST['selected']=='---') {
  $sql = "update tcsteps set `ignore`=0,`enforce`=0 where id=".$_POST['id'];
  $b->connect();
  $b->exec_sql($sql);
} elseif ($_POST['selected']=='enforce') {
  $sql = "update tcsteps set `ignore`=0,`enforce`=1 where id=".$_POST['id'];
  $b->connect();
  $b->exec_sql($sql);
} elseif ($_POST['selected']=='ignore') {
  $sql = "update tcsteps set `ignore`=1,`enforce`=0 where id=".$_POST['id'];
  $b->connect();
  $b->exec_sql($sql);
} else {
  return;
}
?>
