<?php
/**
*
* A full management system for MySQL databse control
*
*/

class database
{
    private $connection;
    private static $_instance;
    private $host = "";
    private $username = "";
    private $password = "";
    private $database = "";
    
    /**
    * Get instance for database
    * @return $_instance
    */
    public static function get_instance()
    {
        if(!self::$_instance)
        {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    // construct
    private function __construct()
    {
        $this->_connection = new mysqli($this->host, $this->username, $this->password, $this->database);
        
        // error
        if ($this->_connection->connect_error)
        {
            trigger_error("Connection Error: " . $this->_connection->connect_error(), E_USER_ERROR);
        }
    }
    
    // Magic method clone is empty to prevent duplication of connection
    private function __clone() { }
    
    // close connection
    public function __destroy()
    {
        $this->_connection->close();
    }
    
    // Get the connection
    public function get_connection()
    {
        return $this->_connection;
    }
    
    /**
    * Get content from the database
    *
    * @param $fields an array with what columns to pull
    * @param $table which table to query
    *
    * @return $content with the table content
    * @return $message with either an error or success message
    */
    public function select( $fields, $table, $extra = '' )
    {
        $message = array();
        
        foreach($fields as $value)
        {
            $val[] = $value;
        }
        
        $rows = implode(', ', $val);
        
        $sql = "SELECT " . $rows . " FROM " . $table . " " . $extra;
        
        if ($result = $this->_connection->query( $sql ))
        {
            if ($result->num_rows > 0)
            {
                try
                {
                    while ($row = $result->fetch_assoc())
                    {
                        $content[] = $row;
                    }
                    return $content;
                }
                catch (Exception $e)
                {
                    trigger_error("Error: " , $e->getMessages());
                }
            }
            else
            {
                $message = array(
                    'is_error' => 'warning',
                    'message' => 'There are no records in the Datbase.'
                );
            }
        }
        else
        {
            $message = array(
                'is_error' => 'danger',
                'message' => 'Query Error, please revise the information.'
            );
        }
    }
    
    
    /**
    * Insert a new record in the database
    *
    * @param $content_array an array setup `Column => Value`
    * @param $table which table to query
    *
    * @return $message with either an error or success message
    */
    public function insert( $content_array, $table )
    {
        $message = array();
        $array_num = count($content_array);

        // create value holders
        $value_param = str_repeat( '?, ', $array_num );
        $stmt_values = rtrim($value_param, ', ');

        // create bind params
        $stmt_param = str_repeat('s', $array_num);


        foreach($content_array as $key => $value)
        {
            $key_val[] = $key;
            $val[] = $value;
        }

        $table_rows = implode(', ', $key_val);

        $sql = "INSERT INTO " . $table . " (" . $table_rows . ") VALUES (" . $stmt_values . ")";

        if($stmt = $this->_connection->prepare( $sql ))
        {
            $stmt->bind_param($stmt_param, ...$val);
            $stmt->execute();
            $stmt->close();
            $message = array(
                'is_error' => 'success',
                'message' => 'Record was entered into database.'
            );
        }
        else
        {
            $message = array(
                'is_error' => 'danger',
                'message' => 'Query Error, please revise the information.'
            );
        }
        
        return $message;
    }
    
    
    /**
    * Update a record in the database
    *
    * @param $id will determine the unique record
    * @param $content_array an array setup `Column => Value`
    * @param $table which table to query
    *
    * @return $message with either an error or success message
    */
    public function update( $id, $content_array, $table )
    {
        $message = array();
        $array_num = count($content_array);
        
        // value holders
        $value_param = str_repeat('?, ', $array_num);
        $stmt_values = rtrim($value_param, ', ');
        
        // bind params
        $stmt_param__old = str_repeat('s', $array_num);
        $add_id = 'i';
        $stmt_param = substr($stmt_param__old, 0, -1).$add_id;
        
        foreach($content_array as $key => $value)
        {
            if($key == 'id')
            {
                continue;
            }
            $key_val[] = $key . ' = ?';
        }
        
        foreach($content_array as $key => $value)
        {
            $val[] = $value;
        }
        
        $table_rows = implode(', ', $key_val);
        
        $sql = "UPDATE " . $table . " SET " . $table_rows . " WHERE id = ?";
        
        if ($stmt = $this->_connection->prepare( $sql ))
        {
            $stmt->bind_param($stmt_param, ...$val);
            $stmt->execute();
            
            if ($stmt->errno)
            {
                $message = array(
                    'is_error' => 'danger',
                    'message' => 'Error: ' . $stmt->error
                );
            }
            
            $message = array(
                'is_error' => 'success',
                'message' => 'Success: ' . $stmt->affected_rows . ' were updated.'
            );
            $stmt->close();
        }
        else
        {
            // in case of error prepraring the SQL statement
            $message = array(
                'is_error' => 'danger',
                'message' => 'Query Error, please revise the information.'
            );
        }
        
        return $message;
    }

    /**
    * Delete a record from the database
    *
    * @param $id will be the unique id from our database
    * @param $table which table to query
    *
    * @return $message with either an error or success message
    */
    public function delete($id, $table)
    {
        $messsage = array();

        if(is_numeric($id))
        {

            $sql = "DELETE FROM " . $table . " WHERE id = ? LIMIT 1";

            if ($stmt = $this->_connection->prepare( $sql ))
            {
                $stmt->bind_param('i', $id);
                $stmt->execute();

                $message = array(
                    'is_error' => 'success',
                    'message' => 'Success: ' . $stmt->affected_rows . ' were updated.'
                );

                $stmt->close();
            }
            else
            {
                $message = array(
                    'is_error' => 'danger',
                    'message' => 'Error: There was a problem with your query'
                );
            }

        }
        else
        {
            $message = array(
                'is_error' => 'warning',
                'message' => 'Warning: Could not perform action. The ID is not valid'
            );
        }
        
        return $message;
    }
}
