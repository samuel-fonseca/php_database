<?php
/**
*
* Connect, view, and modify to a MySQL database
*
*/


class database
{
    /**
     * @var $_connection
     */
    private $_connection;
    
    /**
     * @var $_instance
     */
    private static $_instance;
    
    
    // Connection config for DB
    
    /**
     * @var $_host
     */
    private $_host = '';
    
    /**
     * @var $_username
     */
    private $_username = '';
    
    /**
     * @var $_password
     */
    private $_password = '';
    
    /**
     * @var $_database
     */
    private $_database = '';
    
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
        $this->_connection = new mysqli($this->_host, $this->_username, $this->_password, $this->_database);
        
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
        
        if( !is_array( $fields ) )
        {
            $message = array(
                'is_error' => 'warning',
                'message' => 'Invalid type given for $fields'
            );
            return $message;
            exit();
        }
        
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
                    exit();
                }
            }
            else
            {
                $message = array(
                    'is_error' => 'warning',
                    'message' => 'There are no records in the `' . $table . '` table.'
                );
                return $message;
                exit();
            }
        }
        else
        {
            $message = array(
                'is_error' => 'danger',
                'message' => 'Query Error, please revise the information.'
            );
            return $message;
            exit();
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
        if ( !is_array($content_array) )
        {
            $message = array(
                'is_error' => 'danger',
                'message' => 'Wrong formatted data.'
            );
            return $message;
            exit;
        }
        
        $array_num = count($content_array);

        // create value holders
        $stmt_values = implode(', ', array_fill(0, sizeof($content_array), '?'));

        // create bind params
        $stmt_param = str_repeat('s', $array_num);

        foreach($content_array as $key => $value)
        {
			// if $value is an array then we skip it because
			// it is not the right format
            if ( is_array($value) )
            {
                continue;
            }
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
            
            return true;
        }
        else
        {
            $message = array(
                'is_error' => 'danger',
                'message' => 'Query Error, please revise the information.'
            );
            
            return $message;
        }
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
        
        // bind params
        $stmt_param = str_repeat('s', $array_num);
        $stmt_param .= 'i'; // assumption is that id is `int` value
        
        // setup sql column names
        foreach($content_array as $key => $value)
        {
            // if `id` is included skip it
            if($key == 'id')
            {
                continue;
            }
            $table_rows[] = $key . ' = ?';
        }
        // set index here
        $i = 0;
        // foreach loop to get the values
        foreach($content_array as $key => $value)
        {
            // use numeric indexes
            $val[$i] = $value;
            // increment index
            $i++;
        }
        // add $id to $val
        $val[$i] = $id;
        
        // separate rows with `,`
        $table_rows = implode(', ', $table_rows);
        
        // create query
        $sql = "UPDATE " . $table . " SET " . $table_rows . " WHERE id = ?";
        
        // prepare database
        if ($stmt = $this->_connection->prepare( $sql ))
        {
            // bind the params
            $stmt->bind_param($stmt_param, ...$val);
            // execute the query
            $stmt->execute();
            
            // check for errors
            if ($stmt->errno)
            {
                $message = array(
                    'is_error' => 'danger',
                    'message' => 'Error: ' . $stmt->error
                );
            }
            
            // make sure at least 1 or more rows were affected
            if ($stmt->affected_rows > 0)
            {
                $message = array(
                    'is_error' => 'success',
                    'message' => 'Success: ' . $stmt->affected_rows . ' rows were updated.'
                );
            }
            else
            {
                // if not, send warning to user
                $message = array(
                    'is_error' => 'warning',
                    'message' => 'Warning: ' . $stmt->affected_rows . ' rows were updated.'
                );
            }
            
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
        //return $stmt_param . "<br>" . $table_rows . "<br>" . $sql . "<br>" . var_dump($val) ;
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
