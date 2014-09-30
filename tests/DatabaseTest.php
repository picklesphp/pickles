<?php

class DatabaseTest extends PHPUnit_Framework_TestCase
{
    public function testGetInstanceFalse()
    {
        $this->assertFalse(Pickles\Database::getInstance());
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage The specified datasource is not defined in the config.
     */
    public function testGetInstanceDatasourceNotDefined()
    {
        $config = Pickles\Config::getInstance();
        $config->data['pickles']['datasource'] = 'bad';
        Pickles\Database::getInstance();
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage The specified datasource lacks a driver.
     */
    public function testGetInstanceDatasourceLacksDriver()
    {
        $config = Pickles\Config::getInstance();
        $config->data['datasources'] = [
            'bad' => [
                'type' => 'mysql',
            ],
        ];
        $this->assertInstanceOf('Pickles\\Database', Pickles\Database::getInstance());
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage There was an error loading the database configuration.
     */
    public function testOpenConfigError()
    {
        $config = Pickles\Config::getInstance();
        $config->data['datasources'] = [
            'bad' => [
                'type'     => 'mysql',
                'driver'   => 'pdo_mysql',
                'database' => 'test',
            ],
        ];
        $db = Pickles\Database::getInstance();
        $db->open();
    }

    public function testGetInstanceDatasourcesArray()
    {
        $config = Pickles\Config::getInstance();
        $config->data['datasources'] = [
            'mysql' => [
                'type'     => 'mysql',
                'driver'   => 'pdo_mysql',
                'hostname' => 'localhost',
                'username' => 'root',
                'password' => '',
                'database' => 'test',
            ],
        ];
        $this->assertInstanceOf('Pickles\\Database', Pickles\Database::getInstance());
    }

    // Also tests the datasource being missing and selecting the first one
    public function testGetInstanceMySQL()
    {
        $config = Pickles\Config::getInstance();
        unset($config->data['pickles']['datasource']);
        $this->assertInstanceOf('Pickles\\Database', Pickles\Database::getInstance());
    }

    public function testOpenMySQL()
    {
        $config = Pickles\Config::getInstance();
        $config->data['pickles']['datasource'] = 'mysql';
        $db = Pickles\Database::getInstance();
        $db->open();
    }

    public function testExecute()
    {
        $db = Pickles\Database::getInstance();
        $this->assertEquals('0', $db->execute('SHOW TABLES'));
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage No query to execute.
     */
    public function testExecuteNoQuery()
    {
        $db = Pickles\Database::getInstance();
        $db->execute(' ');
    }

    public function testFetch()
    {
        $config = Pickles\Config::getInstance();
        $config->data['pickles']['logging'] = true;
        $config->data['pickles']['profiler'] = true;
        $db = Pickles\Database::getInstance();
        $this->assertEquals([], $db->fetch('SELECT * FROM pickles WHERE id != ?', ['0']));
    }

    public function testExplainNoInput()
    {
        $config = Pickles\Config::getInstance();
        $db = Pickles\Database::getInstance();
        $this->assertEquals([], $db->fetch('SELECT * FROM pickles WHERE id != 0'));
    }

    public function testSlowQuery()
    {
        $db = Pickles\Database::getInstance();
        $this->assertEquals('0', $db->execute('SHOW DATABASES', null, true));
    }

    public function testCloseMySQL()
    {
        $db = Pickles\Database::getInstance();
        $db->open();

        $this->assertTrue($db->close());
    }

    public function testGetInstancePostgreSQL()
    {
        $config = Pickles\Config::getInstance();
        $config->data['pickles']['datasource'] = 'pgsql';
        $config->data['datasources']['pgsql'] = [
            'type'     => 'pgsql',
            'driver'   => 'pdo_pgsql',
            'hostname' => 'localhost',
            'username' => '',
            'password' => '',
            'database' => 'test',
        ];
        $this->assertInstanceOf('Pickles\\Database', Pickles\Database::getInstance());
    }

    /**
     * @expectedException     PDOException
     * @expectedExceptionCode 7
     */
    public function testOpenPostgreSQL()
    {
        // Also throws an exception since I don't have PostgreSQL set up
        $config = Pickles\Config::getInstance();
        $db = Pickles\Database::getInstance();
        $db->open();
    }

    public function testGetInstanceSQLite()
    {
        $config = Pickles\Config::getInstance();
        $config->data['pickles']['datasource'] = 'sqlite';
        $config->data['datasources']['sqlite'] = [
            'type'     => 'sqlite',
            'driver'   => 'pdo_sqlite',
            'hostname' => 'localhost',
            'username' => '',
            'password' => '',
            'database' => 'test',
        ];
        $this->assertInstanceOf('Pickles\\Database', Pickles\Database::getInstance());
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Datasource driver "pdo_invalid" is invalid
     */
    public function testGetInstanceInvalidDriver()
    {
        $config = Pickles\Config::getInstance();
        $config->data['pickles']['datasource'] = 'invalid';
        $config->data['datasources']['invalid'] = [
            'type'     => 'invalid',
            'driver'   => 'pdo_invalid',
            'hostname' => 'localhost',
            'username' => '',
            'password' => '',
            'database' => 'test',
        ];
        Pickles\Database::getInstance();
    }
}
