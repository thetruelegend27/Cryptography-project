<?php

# by JiangCat
# MySQL数据库控制器

class db
{
	private $config = array(
		'dbservername'		=> '',
		'dbname'			=> '',
		'dbusername'		=> '',
		'dbpassword'		=> '',
		'technicalemail'	=> ''
	);

	private $_CONNECTOR = '';
	private $current_query_result = false;
	
	private $shutdown_queries = array();
	private $query_count = 0;

	public function connect($config)
	{
		$this->_CONNECTOR = new mysqli($config['dbservername'], $config['dbusername'], $config['dbpassword'], $config['dbname']);
		if ( $this->_CONNECTOR->connect_errno ) {
			die('Unable to connect database: ') . $this->_CONNECTOR->connect_error;
		}
		$this->config = array(
			'dbservername'		=> $config['dbservername'],
			'dbname'			=> $config['dbname'],
			'dbusername'		=> $config['dbusername'],
			'dbpassword'		=> $config['dbpassword'],
			'technicalemail'	=> $config['technicalemail']
		);
		$this->_CONNECTOR->query("SET character set 'utf8'");
		$this->_CONNECTOR->query("SET NAMES 'utf8'");
		return true;
	}

	public function query_count() {
		return $this->query_count;
	}

	public function query($sql, $query_mode=MYSQLI_STORE_RESULT) {
		$this->current_query_result = $this->_CONNECTOR->query($sql, $query_mode);
		if ( false === $this->current_query_result ) {
			trigger_error("Query Errors:\n$sql".'<br />'.$this->_CONNECTOR->error, E_USER_ERROR);
		}
		$this->query_count ++;
		return $this->current_query_result;
	}

	public function query_unbuffered($sql) {
		return $this->query($sql, MYSQLI_USE_RESULT);
	}

	public function query_first($sql='') {
		$result = $this->query($sql);
		$row = $this->fetch_array($result);
		$this->free_result($result);
		return $row;
	}
	
	public function shutdown_query($sql) {
		$this->shutdown_queries[] = $sql;
	}
	
	public function num_rows($query_result=false) {
		return !$query_result ? $this->current_query_result->num_rows : $query_result->num_rows;
	}
	
	public function affected_rows() {
		return $this->_CONNECTOR->affected_rows;
	}

	public function insert_id() {
		return $this->_CONNECTOR->insert_id;
	}
	
	public function fetch_array($query_result=false) {
		return !$query_result ? $this->current_query_result->fetch_assoc() : $query_result->fetch_assoc();
	}
	
	public function free_result($query_result=false) {
		!$query_result ? $this->current_query_result->free() : $query_result->free();
	}
	
	function __destruct() {
		if ( !count($this->shutdown_queries) )
			return;
		foreach ( $this->shutdown_queries AS $sql )
			$this->query_unbuffered($sql);
	}

	/*
	// 关闭数据库连接
	function close_db() {
		if ( $this->_CONNECTOR ) {
			if ( !empty($this->open_queries) )
				foreach ( $this->open_queries as $query_id )
					$this->free_result($query_id);

			return @mysql_close($this->_CONNECTOR);
		}
		return false;
	}

	// 获取表名
	function get_table_names() {
		$result = mysql_query('SHOW TABLES FROM ' . $this->database, $this->_CONNECTOR);
		$tables = array();
		while ( $row = mysql_fetch_array($result, MYSQL_NUM) )
			$tables[] = $row[0];
		mysql_free_result($result);
		return $tables;
	}

	// 获取列名
	function get_result_fields($query_id='') {
		if ( $query_id == '' )
			$query_id = $this->query_id;

		$fields = array();
		while ( $field = mysql_fetch_field($query_id) )
			$fields[] = $field;

		return $fields;
	}
	*/
}
?>