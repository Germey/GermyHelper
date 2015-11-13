<?php 

	class Mysql {

		private $mysql_server_name = "localhost";
		private $mysql_username = "cqc";
		private $mysql_password = "CQCcqc123";
		private $mysql_database = "spider"; 
		private $conn;

		public function __construct() {
			$this->conn=mysql_connect($this->mysql_server_name, $this->mysql_username,
                        $this->mysql_password);
			if (!$this->conn) {
				die("数据库连接失败");
			} else {
				mysql_select_db($this->mysql_database, $this->conn);
			}
			mysql_query("SET NAMES UTF8");
		}


		public function insertData($from_user, $date, $content, $reply, $type) {
			$sql = 'insert into weixin_data(from_user, date, content, reply, type) values("%s", "%s", "%s", "%s", "%s")';
			$sql = sprintf($sql, $from_user, $date, $content, $reply, $type);
			$result = mysql_query($sql, $this->conn);
			if ($result) {
				echo "add one data";
			} else {
				echo "add failed";
			}
		}

	}


	//$mysql = new Mysql();
	//$mysql->insertData("1",'dd','2012-12-09','dd','dsds','text');