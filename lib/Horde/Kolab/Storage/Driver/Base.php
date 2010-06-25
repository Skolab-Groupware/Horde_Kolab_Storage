<?php
/**
 * The base driver definition for accessing Kolab storage drivers.
 *
 * PHP version 5
 *
 * @category Kolab
 * @package  Kolab_Storage
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_Storage
 */

/**
 * The base driver definition for accessing Kolab storage drivers.
 *
 * Copyright 2009-2010 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @category Kolab
 * @package  Kolab_Storage
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_Storage
 */
abstract class Horde_Kolab_Storage_Driver_Base
implements Horde_Kolab_Storage_Driver
{
    /**
     * The group handler for this connection.
     *
     * @var Horde_Group
     */
    private $_groups;

    /**
     * Additional connection parameters.
     *
     * @var array
     */
    private $_params;

    /**
     * Constructor.
     *
     * @param Horde_Group $groups  The groups handler.
     * @param array $params        Connection parameters.
     */
    public function __construct(Horde_Group $groups, $params = array())
    {
        $this->_groups = $groups;
        $this->_params = $params;
    }

    /**
     * Return a parameter setting for this connection.
     *
     * @param string $key     The parameter key.
     * @param mixed  $default An optional default value.
     *
     * @return mixed The parameter value.
     */
    public function getParam($key, $default = null)
    {
        return isset($this->_params[$key]) ? $this->_params[$key] : $default;
    }

    /**
     * Retrieve the namespace information for this connection.
     *
     * @return Horde_Kolab_Storage_Driver_Namespace The initialized namespace handler.
     */
    public function getNamespace()
    {
        if (isset($this->_params['namespaces'])) {
            return new Horde_Kolab_Storage_Driver_Namespace_Config(
                $this->_params['namespaces']
            );
        }
        return new Horde_Kolab_Storage_Driver_Namespace_Fixed();
    }

    /**
     * Get the group handler for this connection.
     *
     * @return Horde_Group The group handler.
     */
    public function getGroupHandler()
    {
        return $this->_groups;
    }

}