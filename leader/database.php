<?php
/**
 * This file is part of the Sphoof framework.
 * Copyright (c) 2010-2011 Sphoof
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. You can also view the
 * LICENSE file online at http://www.sphoof.nl/new-bsd.txt
 *
 * @category    Sphoof
 * @copyright   Copyright (c) 2010-2011 Sphoof (http://sphoof.nl)
 * @license             http://sphoof.nl/new-bsd.txt    New BSD License
 * @package             Database
 */

class SpException extends Exception {}

/**
 * Represents an error raised by the SpDatabase package.
 * You should not throw this exception from your own code.
 */
class SpDatabaseException extends SpException {

        /**
         * The ANSI error code.
         * @var string
         */
        protected $ansi;

        /**
         * Constructs the exception.
         *
         * @param string $message
         * @param int $code
         * @param string $ansi
         */
        public function __construct( $message, $code, $ansi ) {
                $this->ansi = $ansi;
                parent::__construct( $message, $code );
        }

        /**
         * Returns the SQLSTATE error code (a five characters alphanumeric identifier
         * defined in the ANSI SQL standard)
         *
         * @return String
         */
        public function getAnsiCode( ) {
                return $this->ansi;
        }
}

/**
 * Represents an error raised by the SpDatabase package upon receiving invalid credentials.
 * You should not throw this exception from your own code.
 */
class SpDatabaseLoginException extends SpDatabaseException {
        public function __construct( $message ) {
                parent::__construct( $message, 7, "08006" );
        }
}

/**
 * A database connection. These classes provide a cleaner layer over PDO, so
 * you'll have to have PDO installed.
 *
 * @package             Database
 */
class SpDatabaseConnection {
        /**
         * An instance of PDO.
         * @var PDO
         */
        protected $pdo;

        /**
         * An array of default options to pass to PDO.
         *
         * @var Array
         */
        protected $attributes = array(
                'attr_errmode' => 'errmode_exception',
                'attr_default_fetch_mode' => 'fetch_assoc'
        );

        /**
         * Construct the DatabaseConnection and set default values.
         *
         * @param Pdo $pdo
         */
        public function __construct( $dsn, $username, $password ) {
                $this->pdo = $this->build( $dsn, $username, $password );
                foreach( $this->attributes as $constant => $value ) {
                        $this->pdo->setAttribute( $this->constant( $constant ), $this->constant( $value ) );
                }
        }

        /**
         * Begin a new transaction.
         *
         * @return boolean
         */
        public function begin( ) {
                return $this->pdo->beginTransaction( );
        }

        /**
         * Commit the current transaction.
         *
         * @return boolean
         */
        public function commit( ) {
                return $this->pdo->commit( );
        }

        /**
         * Returns the identifier of the latest inserted record.
         *
         * @param string $name
         * @return integer
         */
        public function lastInsertId( $name = null ) {
                return $this->pdo->lastInsertId( $name );
        }

        /**
         * Executes a query, by creating a statement and executing it with the parameters.
         *
         * @param string $statement
         * @param array $parameters
         * @return SpDatabaseResultset
         */
        public function query( $statement, Array $parameters = array( ) ) {
                return $this->prepare( $statement )->execute( $parameters );
        }

        /**
         * Rollback the current transaction.
         *
         * @return boolean
         */
        public function rollback( ) {
                return $this->pdo->rollback( );
        }

        /**
         * Prepares a new Statement.
         *
         * @param string $statement
         * @return SpDatabaseStatement
         */
        public function prepare( $statement ) {
                return new SpDatabaseStatement( $this->pdo->prepare( $statement ) );
        }

        /**
         * Returns whether or not the PDO installation supports the driver.
         *
         * @param string $driver
         * @return boolean
         */
        public function supportsDriver( $driver ) {
                return ( in_array( $driver, $this->pdo->getAvailableDrivers( ) ) );
        }

        /**
         * Returns whether or not PDO is using the driver.
         *
         * @param string $driver
         * @return boolean
         */
        public function usingDriver( $driver ) {
                return ( $this->pdo->getAttribute( constant( 'PDO::ATTR_DRIVER_NAME' ) ) === $driver );
        }

        /**
         * Convinience method for writing constants in a more readble way.
         *
         * @param string $constant
         * @return mixed
         */
        protected function constant( $constant ) {
                return constant( strtoupper( 'pdo::' . $constant ) );
        }

        protected function build( $dsn, $username, $password ) {
                try {
                        $pdo = new PDO( $dsn, $username, $password );
                        return $pdo;
                }
                catch( PDOException $e ) {
                        throw new SpDatabaseLoginException( $e->getMessage( ) );
                }
        }
}

/**
 * A database statement, which provides a nicer layer over PDO's prepared
 * statement.
 *
 * @package             Database
 */
class SpDatabaseStatement {
        /**
         * Constructs the statement.
         *
         * @param PdoStatement $statement
         */
        public function __construct( PdoStatement $statement ) {
                $this->statement = $statement;
        }

        /**
         * Bind a parameter to the statement.
         *
         * @param string $parameter
         * @param mixed $value
         * @param int $type
         * @return boolean
         */
        public function bind( $parameter, $value, $type = null ) {
                if( $this == null )
        		        throw new SpException("Null statement.");
                if( $type == null )
                        $type = $this->getPDOConstantType($value);
                if( $value == null )
                        $value = '';

                return $this->statement->bindValue( $parameter, $value, $type );
        }

        public function bindParam( $number, $value, $type = null ) {
        	if( $this == null )
        		throw new SpException("Null statement.");
                if( $type == null )
                        $type = $this->getPDOConstantType($value);
                if( $value == null )
                        $value = '';
                return $this->statement->bindParam( $number, $value, $type );
        }

        /**
         * Execute the statement with the passed parameters. This will return an
         * instance of SpResultset on success, or false on failure.
         *
         * @param array $parameters
         * @return mixed
         */
        public function execute( Array $parameters = null ) {
        	if( $this == null )
        		throw new SpException("Null statement.");
                try {
                        $parameters = $this->parameters( $parameters );
                        return ( false !== $this->statement->execute( $parameters ) ? ($this->resultset = new SpDatabaseResultset( $this->statement ) ) : false );
                }
                catch( PDOException $e ) {
                        throw new SpDatabaseException(
                                isset( $e->errorInfo[2] ) ? $e->errorInfo[2] : $e->getMessage( ),
                                $e->errorInfo[1],
                                $e->errorInfo[0]
                        );
                }
        }

        public function rowCount() {
        	if( $this == null )
        		throw new SpException("Null statement.");
                return (isset($this->resultset) ? $this->resultset->rowCount() : 0);
        }

        /**
         * Loops through the parameters, and adds a colon if it is not present.
         *
         * @param array $parameters
         * @return array
         */
        protected function parameters( Array $parameters = null ) {
                foreach( (array) $parameters as $key => $value ) {
                        if( !is_integer( $key ) && substr( $key, 0, 1 ) !== ':' ) {
                                $parameters[':' . $key] = $value;
                        }
                }
                return $parameters;
        }

        protected function getPDOConstantType( $var ) {
                if( is_int( $var ) )
                        return PDO::PARAM_INT;
                if( is_bool( $var ) )
                        return PDO::PARAM_BOOL;
                if( is_null( $var ) )
                        return PDO::PARAM_NULL;
                //Default
                return PDO::PARAM_STR;
        }
}

/**
 * Represents a resultset from the database.
 *
 * @package             Database
 */
class SpDatabaseResultset {
        /**
         * An instance of the executed PDO statement.
         *
         * @var PdoStatement
         */
        protected $statement;

        /**
         * Construct the resultset.
         *
         * @param PdoStatement $statement
         */
        public function __construct( PdoStatement $statement ) {
                $this->statement = $statement;
        }

        /**
         * Returns a single result in an associative array. If $key is passed, it
         * will return the value of the key from the single result, if there is any.
         *
         * @return Array
         */
        public function fetch( $key = null, $default = false ) {
        	if( $this == null )
        		throw new SpException("Null resultset.");
                if( is_array( $values = $this->statement->fetch( ) ) ) {
                        return isset( $key ) ? ( isset( $values[$key] ) ? $values[$key] : $default ) : $values;
                }
                return $default;
        }

        /**
         * Returns a single result in an associative array. If $key is passed, it
         * will return the value of the key from the single result, if there is any.
         *
         * @return Array
         */
        public function fetchIdx( $index = null, $default = false ) {
        	if( $this == null )
        		throw new SpException("Null resultset.");
                if( is_array( $values = $this->statement->fetch( PDO::FETCH_NUM ) ) ) {
                        return isset( $index ) ? ( isset( $values[$index] ) ? $values[$index] : $default ) : $values;
                }
                return $default;
        }

        /**
         * Returns all results in an associative multidimentional array.
         *
         * @return Array
         */
        public function fetchAll( ) {
        	if( $this == null )
        		throw new SpException("Null resultset.");
                return $this->statement->fetchAll( );
        }

        /**
         * Returns the number of rows affected by the last DELETE, INSERT or UPDATE
         *
         * @return int
         */
        public function rowCount() {
        	if( $this == null )
        		throw new SpException("Null resultset.");
                return $this->statement->rowCount( );
        }
}
?>
