<?php

/**
 * Resource Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2014, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * Resource Class
 *
 * This is a parent class that all PICKLES modules should be extending. Each
 * module can specify it's own meta data and whether or not a user must be
 * properly authenticated to view the page. Currently any pages without a
 * template are treated as pages being requested via AJAX and the return will
 * be JSON encoded. In the future this may need to be changed out for logic
 * that allows the requested module to specify what display type(s) it can use.
 */
class Resource extends Object
{
    /**
     * Secure
     *
     * Whether or not the page should be loaded via SSL.
     *
     * @var boolean defaults to false
     */
    public $secure = false;

    /**
     * Required
     *
     * Variables that are required.
     *
     * @var array
     */
    public $required = [];

    /**
     * Filter
     *
     * Variables to filter.
     *
     * @var array
     */
    public $filter = [];

    /**
     * Validate
     *
     * Variables to validate.
     *
     * @var array
     */
    public $validate = [];

    // @todo
    public $status  = 200;
    public $message = 'OK';
    public $echo    = false;
    public $limit   = false;
    public $offset  = false;
    public $errors  = [];
    public $uids    = [];

    // @todo if $status != 200 && $message == 'OK' ...

    /**
     * Constructor
     *
     * The constructor does nothing by default but can be passed a boolean
     * variable to tell it to automatically run the __default() method. This is
     * typically used when a module is called outside of the scope of the
     * controller (the registration page calls the login page in this manner.
     */
    public function __construct($uids = false)
    {
        $this->uids = $uids;

        parent::__construct(['cache', 'db']);

        $method   = $_SERVER['REQUEST_METHOD'];
        $filter   = isset($this->filter[$method]);
        $validate = isset($this->validate[$method]);

        if ($filter || $validate)
        {
            // Hack together some new globals
            if (in_array($method, ['PUT', 'DELETE']))
            {
                $GLOBALS['_' . $method] = [];

                // @todo Populate it
            }

            $global =& $GLOBALS['_' . $method];

            // Checks that the required parameters are present
            // @todo Add in support for uid:* variables
            if ($validate)
            {
                $variables = [];

                foreach ($this->validate[$method] as $variable => $rules)
                {
                    if (!is_array($rules))
                    {
                        $variable = $rules;
                    }

                    $variables[] = $variable;
                }

                $missing_variables = array_diff($variables, array_keys($global));

                if ($missing_variables !== array())
                {
                    foreach ($missing_variables as $variable)
                    {
                        $this->errors[$variable] = 'The ' . $variable . ' parameter is required.';
                    }
                }
            }

            foreach ($global as $variable => $value)
            {
                // Applies any filters
                if ($filter && isset($this->filter[$method][$variable]))
                {
                    // @todo Definitely could see the need to expand this out
                    //       to allow for more robust filters to be applied
                    //       similar to how the validation logic work.
                    $global[$variable] = $this->filter[$method][$variable]($value);
                }

                if ($validate && isset($this->validate[$method][$variable]))
                {
                    $rules = $this->validate[$method][$variable];

                    if (is_array($rules))
                    {
                        if (isset($global[$variable]) && !String::isEmpty($global[$variable]))
                        {
                            if (is_array($rules))
                            {
                                $rule_errors = Validate::isValid($global[$variable], $rules);

                                if (is_array($rule_errors))
                                {
                                    $this->errors[$variable] = $rule_errors[0];
                                }
                            }
                        }
                    }
                }
            }

            // if PUT or DELETE, need to update the super globals directly as
            // they do not stay in sync. Probably need to make them global in
            // this class method
            //
            // $_PUT = $GLOBALS['_PUT'];
        }
    }
}

