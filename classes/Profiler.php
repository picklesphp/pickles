<?php

/**
 * Profiler 
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
 * Profiler Class
 *
 * The Profiler class is statically interfaced with and allows for in depth
 * profiling of a site. By default profiling is off, but can be enabled in the
 * config.ini for a site. Out of the box the profiler will report on every
 * class object in the system that extends the code Object class.
 *
 * Note: I really wanted to use PHP Quick Profiler by Ryan Campbell of
 * Particletree but it kept barking out errors when I tried to use it. Here's
 * a link anyway: http://particletree.com/features/php-quick-profiler/
 *
 * @usage <code>Profiler::log('started stuff');</code>
 */
class Profiler
{
	/**
	 * Profile
	 *
	 * Array of logged events
	 *
	 * @static
	 * @access private
	 * @var    array
	 */
	private static $profile = array();

	/**
	 * Queries
	 *
	 * Number of queries that have been logged
	 *
	 * @static
	 * @access private
	 * @var    integer
	 */
	private static $queries = 0;

	/**
	 * Constructor
	 *
	 * Private constructor since this class is interfaced wtih statically.
	 *
	 * @access private
	 */
	private function __construct()
	{

	}

	/**
	 * Log
	 *
	 * Logs the event to be displayed later on. Due to the nature of how much
	 * of a pain it is to determine which class method called this method I
	 * opted to make the method a passable argument for ease of use. Perhaps
	 * I'll revisit in the future. Handles all elapsed time calculations and
	 * memory usage.
	 *
	 * @static
	 * @param  mixed $data data to log
	 * @param  string $method name of the class method being logged
	 */
	public static function log($data, $method = false, $type = false)
	{
		$time      = microtime(true);
		$data_type = gettype($data);

		// Tidys the data by type
		switch ($data_type)
		{
			case 'array':
				$log = '<pre>' . print_r($data, true) . '</pre>';
				break;
	
			case 'object':
				$log = '<span style="color:#666">[</span><span style="color:#777">' . get_parent_class($data) . '</span><span style="color:#666">]</span> '
					 . '<span style="color:#69c">' . get_class($data) . '</span>'
					 . ($method != '' ? '<span style="color:#666">-></span><span style="color:#4eed9e">' . $method . '</span><span style="color:#666">()</span>' : '');

				$data_type = '<span style="color:Peru">' . $data_type . '</span>';
				break;

			case 'string':
			default:
				if ($type != false)
				{
					$data_type = $type;
				}

				$log = $data;
				break;
		}

		self::$profile[] = array(
			'log'     => $log,
			'type'    => $data_type,
			'time'    => $time,
			'elapsed' => $time - PICKLES_START_TIME,
			'memory'  => memory_get_usage(),
		);
	}

	/**
	 * Log Query
	 *
	 * Serves as a wrapper to get query data to the log function
	 *
	 * @static
	 * @param  string $query the query being executed
	 * @param  array $input_parameters optional prepared statement data
	 * @param  array $explain EXPLAIN data for the query
	 * @param  float $duration the speed of the query
	 */
	public static function logQuery($query, $input_parameters = false, $explain = false, $duration = false)
	{
		self::$queries++;

		$log = '';

		if ($input_parameters != 'false' && is_array($input_parameters))
		{
			$log .= '<br />';

			foreach ($input_parameters as $key => $value)
			{
				$log .= '<br /><span style="color:#a82222">' . $key . '</span> <span style="color:#666">=></span> <span style="color:#ffff7f">' . $value . '</span>';

				$query = str_replace($key, '<span style="color:#a82222">' . $key . '</span>', $query);
			}
		}
		
		$log = '<span style="color:#009600">' . $query . '</span>' . $log;

		if (is_array($explain))
		{
			$log .= '<br />';

			foreach ($explain as $table)
			{
				$log .= '<br /><span style="color:RoyalBlue">Possible Keys</span> <span style="color:#666">=></span> <span style="color:DarkGoldenRod">' . ($table['possible_keys'] == '' ? '<em style="color:red">NONE</em>' : $table['possible_keys']) . '</span>'
					 . '<br /><span style="color:RoyalBlue">Key</span> <span style="color:#666">=></span> <span style="color:DarkGoldenRod">'  . ($table['key'] == '' ? '<em style="color:red">NONE</em>' : $table['key']) . '</span>'
					 . '<br /><span style="color:RoyalBlue">Type</span> <span style="color:#666">=></span> <span style="color:DarkGoldenRod">' . $table['type'] . '</span>'
					 . '<br /><span style="color:RoyalBlue">Rows</span> <span style="color:#666">=></span> <span style="color:DarkGoldenRod">'  . $table['rows'] . '</span>'
					 . ($table['Extra'] != '' ? '<br /><span style="color:RoyalBlue">Extra</span> <span style="color:#666">=></span> <span style="color:DarkGoldenRod">' . $table['Extra'] . '</span>' : '');
			}
		}
		
		$log .= '<br /><br /><span style="color:DarkKhaki">Speed:</span> ' . number_format($duration * 100, 3) . ' ms';

		self::log($log, false, '<span style="color:DarkCyan">database</span>');
	}

	/**
	 * Report
	 *
	 * Generates the Profiler report that is displayed by the Controller.
	 * Contains all the HTML needed to display the data properly inline on the
	 * page. Will generally be displayed after the closing HTML tag.
	 */
	public static function report()
	{
		$start_time = PICKLES_START_TIME;
		$peak_usage = self::formatSize(memory_get_peak_usage());
		$end_time   = self::$profile[count(self::$profile) - 1]['time'];
		$duration   = ($end_time - $start_time);

		$logs  = count(self::$profile);
		$logs .= ' Log' . ($logs == 1 ? '' : 's');

		$files  = count(get_included_files());
		$files .= ' File' . ($files == 1 ? '' : 's');

		$queries = self::$queries . ' Quer'. (self::$queries == 1 ? 'y' : 'ies'); 
		?>
		<style>
			#pickles-profiler
			{
				background: #212121;
				width: 700px;
				margin: 0 auto;
				margin-top: 20px;
				margin-bottom: 20px;
				border-radius: 20px;
				-moz-border-radius: 20px;
				-webkit-border-radius: 20px;
				box-shadow: 0 3px 4px #000;
				-moz-box-shadow: 0 3px 4px #000;
				-webkit-box-shadow: 0 3px 4px #000;
				border: 6px solid #666;
				padding: 10px 20px 20px;
				font-family: monospace;
				font-size: 1em;
			}
			#pickles-profiler table
			{
				width: 100%;
			}
			#pickles-profiler table tr th, #pickles-profiler table tr td
			{
				padding: 10px;
			}
			#pickles-profiler .even
			{
				background-color: #323232;
			}
		</style>
		<div id="pickles-profiler">
			<strong style="font-size:1.5em">PICKLES Profiler</strong><br /><br />
			<table style="border-collapse:separate;border-spacing:1px;border-radius:10px;text-shadow:1px 1px 1px #000">
				<tr>
					<td style="text-align:center;background:#480000">
						<span style="font-weight:bold;">Console</span>
						<div style="color:#ff7f7f;font-size:1.2em;padding-top:10px"><?php echo $logs; ?></div>
					</td>
					<td style="text-align:center;background:#552200">
						<span style="font-weight:bold;">Load Time</span>
						<div style="color:#ffa366;font-size:1.2em;padding-top:10px"><?php echo number_format($duration * 100, 3) . ' ms / ' . ini_get('max_execution_time'); ?></div>
					</td>
					<td style="text-align:center;background:#545500">
						<span style="font-weight:bold;">Memory Usage</span>
						<div style="color:#ffff6d;font-size:1.2em;padding-top:10px"><?php echo $peak_usage . ' / ' . ini_get('memory_limit'); ?></div>
					</td>
					<td style="text-align:center;background:#004200">
						<span style="font-weight:bold;">Database</span>
						<div style="color:#7dff7d;font-size:1.2em;padding-top:10px"><?php echo $queries; ?></div>
					</td>
					<td style="text-align:center;background:#000048">
						<span style="font-weight:bold;">Includes</span>
						<div style="color:#c4c4ff;font-size:1.2em;padding-top:10px"><?php echo $files; ?></div>
					</td>
				</tr>
			</table>
			<table>
				<tr>
					<th style="text-align:left" colspan="2">Console</th>
					<th style="text-align:right">Memory</th>
					<th style="text-align:right">Time</th>
				</tr>
				<?php
				foreach (self::$profile as $key => $entry)
				{
					?>
					<tr>
						<td style="font-weight:bold;color:#999"><?php echo $entry['type']; ?></td>
						<td><?php echo $entry['log']; ?></td>
						<td style="text-align:right" nowrap="nowrap"><?php echo self::formatSize($entry['memory']); ?></td>
						<td style="text-align:right" nowrap="nowrap"><?php echo number_format($entry['elapsed'] * 100, 3); ?> ms</td>
					</tr>
					<?php
				}
				?>
			</table>
		</div>
		<br /><br />
		<?php
	}

	/**
	 * Format Size
	 *
	 * Formats the passed size into a human readable string
	 *
	 * @static
	 * @access private
	 * @param  float $filesize size of the file
	 * @return string formatted number string
	 */
	private static function formatSize($filesize)
	{
		$units = array('bytes', 'kB', 'MB', 'GB');

		return number_format(round($filesize / pow(1024, ($i = floor(log($filesize, 1024)))), 2), 2) . ' ' . $units[$i];
	}
}

?>
