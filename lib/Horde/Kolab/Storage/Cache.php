<?php
/**
 * A cache for Kolab storage.
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
 * The Kolab_Cache class provides a cache for Kolab groupware objects.
 *
 * The Horde_Kolab_Storage_Cache singleton instance provides caching for all
 * storage folders. So before operating on the cache data it is necessary to
 * load the desired folder data. Before switching the folder the cache data
 * should be saved.
 *
 * This class does not offer a lot of safeties and is primarily intended to be
 * used within the Horde_Kolab_Storage_Data class.
 *
 * Copyright 2007-2011 The Horde Project (http://www.horde.org/)
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
class Horde_Kolab_Storage_Cache
{
    /**
     * The link to the horde cache.
     *
     * @var Horde_Cache
     */
    protected $horde_cache;

    /**
     * List cache instances.
     *
     * @var array
     */
    private $_list_caches;

    /**
     * Data cache instances.
     *
     * @var array
     */
    private $_data_caches;

    /**
     * Constructor.
     *
     * @param Horde_Cache $cache The global cache for temporary data storage.
     */
    public function __construct($cache)
    {
        $this->horde_cache = $cache;
    }

    /**
     * Return a data cache.
     *
     * @param array $data_params Return the data cache for a data set
     *                           with these parameters.
     *
     * @return Horde_Kolab_Storage_Cache_Data The data cache.
     */
    public function getDataCache($data_params)
    {
        $data_id = $this->_getDataId($data_params);
        if (!isset($this->_data_caches[$data_id])) {
            $this->_data_caches[$data_id] = new Horde_Kolab_Storage_Cache_Data($this);
        }
        return $this->_data_caches[$data_id];
    }

    /**
     * Retrieve data set.
     *
     * @param string $data_id ID of the data set.
     *
     * @return string The cached data set.
     */
    public function loadData($data_id)
    {
        return $this->horde_cache->get($data_id, 0);
    }

    /**
     * Cache data set.
     *
     * @param string $data_id ID of the data set.
     * @param string $data    The data to be cached.
     *
     * @return NULL
     */
    public function storeData($data_id, $data)
    {
        $this->horde_cache->set($data_id, $data);
    }

    /**
     * Retrieve an attachment.
     *
     * @param string $data_id       ID of the data set.
     * @param string $obid          Object backend id.
     * @param string $attachment_id Attachment ID.
     *
     * @return resource A stream opened to the attachement data.
     */
    public function loadAttachment($data_id, $obid, $attachment_id)
    {
        return $this->horde_cache->get(
            $this->_getAttachmentId($data_id, $obid, $attachment_id),
            0
        );
    }

    /**
     * Store an attachment.
     *
     * @param string   $data_id       ID of the data set.
     * @param string   $obid          Object backend id.
     * @param string   $attachment_id Attachment ID.
     * @param resource $data          A stream opened to the attachement data.
     *
     * @return NULL
     */
    public function storeAttachment($data_id, $obid, $attachment_id, $data)
    {
        $this->horde_cache->set(
            $this->_getAttachmentId($data_id, $obid, $attachment_id),
            $data
        );
    }

    /**
     * Return a list cache.
     *
     * @param array $connection_params Return the list cache for a connection
     *                                 with these parameters.
     *
     * @return Horde_Kolab_Storage_Cache_List The list cache.
     */
    public function getListCache($connection_params)
    {
        $list_id = $this->_getListId($connection_params);
        if (!isset($this->_list_caches[$list_id])) {
            $this->_list_caches[$list_id] = new Horde_Kolab_Storage_Cache_List(
                $this
            );
            $this->_list_caches[$list_id]->setListId($list_id);
        }
        return $this->_list_caches[$list_id];
    }

    /**
     * Retrieve list data.
     *
     * @param string $list_id ID of the connection matching the list.
     *
     * @return string The data of the object.
     */
    public function loadList($list_id)
    {
        return $this->horde_cache->get($list_id, 0);
    }

    /**
     * Cache list data.
     *
     * @param string $list_id ID of the connection matching the list.
     * @param string $data          The data to be cached.
     *
     * @return NULL
     */
    public function storeList($list_id, $data)
    {
        $this->horde_cache->set($list_id, $data);
    }

    /**
     * Compose the list key.
     *
     * @param array $connection_params Return the list ID for a connection with
     *                                 these parameters.
     *
     * @return string The list cache ID.
     */
    private function _getListId($connection_params)
    {
        foreach (array('host', 'port', 'user') as $key) {
            $this->_requireParameter($connection_params, 'list', $key);
        }
        ksort($connection_params);
        return md5(serialize($connection_params));
    }

    /**
     * Compose the data key.
     *
     * @param array $data_params Return the data ID for a data set with these
     *                           parameters.
     *
     * @return string The data cache ID.
     */
    private function _getDataId($data_params)
    {
        foreach (array('host', 'port', 'folder', 'type', 'owner') as $key) {
            $this->_requireParameter($data_params, 'data', $key);
        }
        ksort($data_params);
        return md5(serialize($data_params));
    }

    /**
     * Compose the attachment key.
     *
     * @param string $data_id       ID of the data set.
     * @param string $obid          Object backend id.
     * @param string $attachment_id Attachment ID.
     *
     * @return string The attachment cache ID.
     */
    private function _getAttachmentId($data_id, $obid, $attachment_id)
    {
        return md5(
            serialize(array('d' => $data_id, 'o' => $obid, 'p' => $attachment_id))
        );
    }

    /**
     * Determine if a necessary parameter is set.
     *
     * @return NULL
     *
     * @throws Horde_Kolab_Storage_Exception In case the parameter is missing.
     */
    private function _requireParameter($parameters, $type, $key)
    {
        if (!isset($parameters[$key])) {
            throw new Horde_Kolab_Storage_Exception(
                sprintf(
                    'Unable to determine the %s cache key: The "%s" parameter is missing!',
                    $type,
                    $key
                )
            );
        }

    }
}
