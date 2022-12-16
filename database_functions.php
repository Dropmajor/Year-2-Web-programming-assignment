<?php
//this file is reused from my web programming assignment, it was created with consideration of both this and the
//web programming assignments
class database
{
    private $mysqli;
    private $connectionError;
    public function __construct()
    {
        $this->mysqli = new mysqli();
        $this->mysqli->init();
        if (!$this->mysqli) { // If initalising MySQLi failed (i.e. it didn't return true, hence the ! for checking not true)
            $this->connectionError = "<p>Initalising MySQLi failed</p>";
        } else {
            // Establish secure connection using SSL for use with MySQLi
            $this->mysqli->ssl_set(NULL, NULL, NULL, '/public_html/sys_tests', NULL);

            // Connect the MySQL connection
            $this->mysqli->real_connect("db.bucomputing.uk", "s5406054", "AzmRsokJXbgcNe4MEeMsak4dqNFLPvrR",
                "s5406054", 6612, NULL, MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT);
            if ($this->mysqli->connect_errno) { // If connection error assign error message to the variable
                $this->connectionError = "<p>Failed to connect to MySQL. " .
                    "Error (" . $this->mysqli->connect_errno . "): " . $this->mysqli->connect_error . "</p>";
            }
        }
    }

    public function __destruct()
    {
        //if a connection error never triggered then close the connection
        if(empty($this->connectionError))
        {
            $this->mysqli->close();
        }
    }

    public function getError()
    {
        return $this->connectionError;
    }

    //this function should be run on queries that dont need sanitisation, such as select alls
    function runQuery($query)
    {
        if(empty($this->getError()))
        {
            return $this->mysqli->query($query);
        }
    }

    function prepareQuery($query, $data)
    {
        if(!empty($this->getError()))
        {
            return $this->getError();
        }
        $data = (array) $data;
        $bind_args[] = & $format;
        for($i = 0; $i < count($data); $i++)
        {
            //store the data types of each piece of data for binding the params
            switch (gettype($data[$i]))
            {
                case "integer":
                    $format .= "i";
                    break;
                case "string":
                    $format .= "s";
                    break;
                case "double":
                    $format .= "d";
            }
            //add the data as references
            $bind_args[] = & $data[$i];
        }
        $statement = $this->mysqli->prepare($query);
        //
        call_user_func_array(array($statement, 'bind_param'), $bind_args);
        $statement->execute();
        return $statement->get_result();
    }

    //insert given data into table of the given name, this function sanitises the data when inserted.
    //the function is scalable and works regardless of the table size, only requirements is to enter the correct type and amount of data
    function insertData($table, $data)
    {
        if(empty($table) || empty($data))
        {
            return false;
        }
        if(!empty($this->getError()))
        {
            return $this->getError();
        }
        $format = "";
        $data = (array) $data;
        //call user func only works with references, add the data formats as a reference
        $bind_args[] = & $format;
        for($i = 0; $i < count($data); $i++)
        {
            //store the data types of each piece of data for binding the params
            switch (gettype($data[$i]))
            {
                case "integer":
                    $format .= "i";
                    break;
                case "string":
                    $format .= "s";
                    break;
                case "double":
                    $format .= "d";
            }
            //add the data as references
            $bind_args[] = & $data[$i];
        }
        //get the information about the table we want to insert into
        $tableFormat = mysqli_query($this->mysqli,"DESCRIBE ". $table);
        $query = "INSERT INTO ". $table ." (";
        //this variable stores the appropriate number of question marks for binding the variables
        $queryValues = "";
        while ($row = mysqli_fetch_array($tableFormat))
        {
            $query .= $row['Field'] .",";
            //if the extra field isnt empty it means the row has auto increment sent, which means it is a primary key,
            //so dont assign a value
            if(!empty($row['Extra']))
                $queryValues .= "NULL";
            else if(!empty($queryValues))
                $queryValues .= ", ?";
            else
                $queryValues .= "?";
        }
        $query = substr($query, 0, -1);
        $query .= ") VALUES (". $queryValues .")";
        $statement = $this->mysqli->prepare($query);
        call_user_func_array(array($statement, 'bind_param'), $bind_args);
        $statement->execute();
    }

    //works the same as the insert data function except it is used to update a value in a table based off its primary key
    function updateData($table, $data, $key)
    {
        if(empty($table) || empty($data) || empty($key))
        {
            return false;
        }
        if(!empty($this->getError()))
        {
            return $this->getError();
        }
        $format = "";
        $data = (array) $data;
        //call user func only works with references, add the data formats as a reference
        $bind_args[] = & $format;
        for($i = 0; $i < count($data); $i++)
        {
            //store the data types of each piece of data for binding the params
            switch (gettype($data[$i]))
            {
                case "integer":
                    $format .= "i";
                    break;
                case "string":
                    $format .= "s";
                    break;
                case "double":
                    $format .= "d";
            }
            //add the data as references
            $bind_args[] = & $data[$i];
        }
        //get the information about the table we want to insert into
        $tableFormat = mysqli_query($this->mysqli,"DESCRIBE ". $table);
        $query = "UPDATE ". $table ." SET";
        $keyField = "";
        while ($row = mysqli_fetch_array($tableFormat))
        {
            if(!empty($row['Extra']))
                $keyField = $row['Field'];
            else
                $query .= " ". $row['Field'] ."=?,";
        }
        $query = substr($query, 0, -1);
        $query .= " WHERE ". $keyField ."=". $key;
        $statement = $this->mysqli->prepare($query);
        call_user_func_array(array($statement, 'bind_param'), $bind_args);
        $statement->execute();
    }
}
