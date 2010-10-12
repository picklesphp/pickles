<?php

/**
 * Object Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under the GNU General Public License Version 3
 * Redistribution of these files must retain the above copyright notice.
 *
 * @package   PICKLES
 * @author    Josh Sherman <josh@phpwithpickles.org>
 * @copyright Copyright 2007-2010, Gravity Boulevard, LLC
 * @license   http://www.gnu.org/licenses/gpl.html GPL v3
 * @link      http://phpwithpickles.org
 */

/**
 * Object Class
 *
 * Every instantiated class in PICKLES should be extending this class. By doing
 * so the class is automatically hooked into the profiler, and the object will
 * have access to some common components as well.
 */
class Object
{
	/**
	 * Object Instances
	 *
	 * @static
	 * @access private
	 * @var    mixed
	 */
	protected static $instances = array();

	/**
	 * Instance of the Config object
	 *
	 * @access protected
	 * @var    object
	 */
	protected $config = null;

	/**
	 * Constructor
	 *
	 * Establishes a Config instance for all children to enjoy
	 */
	public function __construct()
	{
		if (get_class($this) == 'Config')
		{
			$this->config = true;
		}
		else
		{
			$this->config = Config::getInstance();
		}

		// Optionally logs the constructor to the profiler
		if ($this->config == true || (isset($this->config->pickles['profiler']) && $this->config->pickles['profiler'] == true))
		{
			Profiler::log($this, '__construct');
		}
	}

	/**
	 * Get Instance
	 *
	 * Gets an instance of the passed class. Allows for easy sharing of certain
	 * classes within the system to avoid the extra overhead of creating new
	 * objects each time. Also avoids the hassle of passing around variables.
	 *
	 * @static
	 * @param  string $class name of the class
	 * @return object instance of the class
	 */
	public static function getInstance($class = false)
	{
		// In < 5.3 arguments must match in child, hence defaulting $class
		if ($class == false)
		{
			return false;
		}
		else
		{
			if (!isset(self::$instances[$class]))
			{
				self::$instances[$class] = new $class();
			}

			return self::$instances[$class];
		}
	}

	/**
	 * Destructor
	 */
	public function __destruct()
	{
		// Optionally logs the destructor to the profiler
		if ($this->config == true || (isset($this->config->pickles['profiler']) && $this->config->pickles['profiler'] == true))
		{
			Profiler::log($this, '__destruct');
		}
	}
}

?>
