<?php

/**
* 	Author: Rapheal Prince
*	Name: Simple CRUD Library.
*
**/
	class Scrud{

		protected $type;
		protected $Mode;
		protected $tablename;
		protected $fields=[];
		protected $wherefields;
		protected $value;
		protected $id;
		protected $field=[];


		/** 
		* @param Empty.
		* Instantiates the connection type.
		***/

		public function __construct()
		{
			require_once("../src/config.php");
			if($connectDetails["connect_type"]=="MYSQLI" )
			{
				//Connect using MySQLi.
				try
				{
					//$connectDetails['hostname'],$connectDetails['username'],$connectDetails['password'],$connectDetails['DB_NAME']
					$connect= new mysqli($connectDetails['hostname'],$connectDetails['username'],$connectDetails['password'],$connectDetails['DB_NAME']);

					if(isset($connect))
					{
						$this->type=$connect;
						$this->Mode="MYSQLI";
					}
					else{
						throw new Exception("Error Estalishing DB Connection", 1);
						
					}
				}
				catch(Exception $e)
				{
					echo $e->getMessage();
				}
			}
			elseif($connectDetails["connect_type"]=="PDO")
			{
				//Connect using PDO

				try {
					//".$connectDetails['hostname'].";dbname=".$connectDetails['DB_NAME'].", ".$connectDetails['username'].", ".$connectDetails['password']."
					$connect= new PDO("mysql:host=".$connectDetails['hostname'].";dbname=".$connectDetails['DB_NAME']."", $connectDetails['username'], $connectDetails['password']);
					$connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

					if(isset($connect))
					{
						$this->type=$connect;
						$this->Mode="PDO";
					}
					
				} catch (PDOException $e) {

					die($e->getMessage());
					
				}
			}
		}


		/**
		* 	@param $tablename - Name of the table to be worked with and $fields.
		*
		**/

		public function create($tablename,$fields)

		{
			//Verify which host is used.
			$this->tablename=$tablename;
			$this->fields=$fields;

			if($this->Mode=="PDO")
			{
				//Mode in PDO.
				//Use PDO Query.
				//loop through the array.
				//$test=":".implode(",:",array_keys($this->fields));
				$query=sprintf("INSERT into $tablename (%s) VALUES (%s)",implode(",", array_keys($this->fields)),'"'.implode('","', array_values($this->fields)).'"');
				

				try {
					$create_db=$this->type->query($query);
					if($create_db->execute())
					{
						return true;

					}
					else{
						throw new PDOExcception("Error Adding Data to DB",1);
					}
				} catch (PDOException $e) {
					echo $e->getMessage();
					
				}


			}
			else{
				//Mode in Mysqli.
				//USe MqSqli Query.
				$query=sprintf("INSERT into $tablename (%s) VALUES (%s)",implode(",", array_keys($this->fields)),'"'.implode('","', array_values($this->fields)).'"');

				try{
					$connect_db=$this->type->query($query);

					if(isset($connect_db))
					{
						return true;
					}
					else{
						throw new Exception("Error!");
					}

				}catch (PDOException $e)
				{
					echo $e->getMessage();
				}

			}


		}

		/**
		* @param $tablename : Gets all the data from the table.
		**/

		public function get($tablename)
		{
			$this->tablename=$tablename;
			//This will apparently get everything from the DB.
			if($this->Mode=="MYSQLI")
			{
				$get_db="SELECT * FROM $this->tablename";
						//Run the Query.
						$get_db=$this->type->query($get_db);
						if($get_db->num_rows>1)
						{
							foreach($get_db as $data)
							{
								$row[]=$data;
							}
							return $row;
						}
						else{
							return false;
						}
			}
			else{
				//prepare("SELECT id, firstname, lastname FROM MyGuests")
				$get_db=$this->type->prepare("SELECT * FROM $this->tablename");
				$get_db->execute();
				$get_db=$get_db->fetchAll();

				//iterative.
				if(count($get_db)>0)
				{
					//There is something in DB.
					foreach($get_db as $data)
					{
						$row[]=$data;
					}
					return $row;
				}
				else{
					return false;
				}
				
			}

		}

		/**
		* @param $tablename,$id and then the value.
		* So it can have this form, Name of get_where("users","username","chima");
		**/

		public function get_where($tablename,$id,$value)
		{
			$this->tablename=$tablename;
			$this->value=$value;
			$this->id=$id;

			//Check which connection is used.
			if($this->Mode=="MYSQLI")
			{
				//Check what the user is currently using to crawl.
				$query=sprintf("SELECT * FROM $this->tablename WHERE %s = %s",$this->id,$this->value);
				$query=$this->type->query($query);
				if($query->num_rows==1)
				{
					$get=$query->fetch_assoc();
					$row=$get;
					return $row;
					
				}
				else{
					return false;
				}
			}
			else{
				//This is for PDO.
				$query=sprintf("SELECT * FROM $this->tablename WHERE %s = %s",$this->id,$this->value);
				$query=$this->type->prepare($query);
				$query->execute();

				if(count($query)==1)
				{
					$get=$query->fetch(PDO::FETCH_ASSOC);
					$row=$get;
					return $row;
				}
			}


		}

		/**
		*	@param $tablename,$field,$id and value.
		*   Basically this can be update("users",array("username"=>"string","password"=>"anotherstring"),"id",1)
		*/
		public function update($tablename,$field,$id,$value)
		{
			$this->tablename=$tablename;
			$this->id=$id;
			$this->value=$value;
			$this->field=$field;

			if($this->Mode=="MYSQLI")
			{
				//$query="UPDATE $this->tablename SET $this->field=$this->value WHERE $this->id=$this->id";
				//die(implode(",",array_values($this->field)));
				$getKey=key($this->field);
				$getVal=current($this->field);
				//Walk through the array

				array_walk($this->field, function(&$value,$key)
				{
					$value="{$key}='{$value}'";
				});

				$update_vals=implode(",", $this->field);
				
				$query=sprintf("UPDATE %s SET %s WHERE %s=%s",$this->tablename,$update_vals,$this->id,$this->value);
				//$query=key($this->field)."<br>";
				//die($arr['username']);
				var_dump($query);
				$update_query=$this->type->query($query);
				if(isset($update_query))
				{
					return true;
				}
				
			}
			else{
				//This is to Update using PDO.
				$getKey=key($this->field);
				$getVal=current($this->field);
				//Walk through the array

				array_walk($this->field, function(&$value,$key)
				{
					$value="{$key}='{$value}'";
				});

				$update_vals=implode(",", $this->field);
				$query=sprintf("UPDATE %s SET %s WHERE %s=%s",$this->tablename,$update_vals,$this->id,$this->value);
				//Run the query
				$query=$this->type->prepare($query);
				$update_query=$query->execute();

				if(isset($update_query))
				{
					return true;
				}
				else{
					return false;
				}

			}
		}

		/**
		*	@param $tablename, id and then value.
		*   This can take the form, delete("users","username","Rapheal");
		*/
		public function delete($tablename,$id,$value)
		{
			//Delete from $tablename, val
			$this->tablename=$tablename;
			$this->id=$id;
			$this->value=$value;
			if($this->Mode=="MYSQLI")
			{
				$query=sprintf("DELETE FROM %s WHERE %s=%s",$this->tablename,$this->id,$this->value);
				$delete_db=$this->type->query($query);

				if(isset($delete_db))
				{
					return true;
				}
				else{
					return false;
				}
			}
			else{
				//PDO.
				//By the Way, The Config.php file should NEVER be empty.
				$query=sprintf("DELETE FROM %s WHERE %s=%s",$this->tablename,$this->id,$this->value);
				$query=$this->type->prepare($query);
				$delete_db=$query->execute();
				if(isset($delete_db))
				{
					return true;
				}
				else{
					return false;
				}

			}
		}


	}