<?php
/**
 * Quinoid_HomepageBanner extension
 *                     NOTICE OF LICENSE
 *
 *                     This source file is subject to the MIT License
 *                     that is bundled with this package in the file LICENSE.txt.
 *                     It is also available through the world-wide-web at this URL:
 *                     http://opensource.org/licenses/mit-license.php
 *
 *                     @category  Quinoid
 *                     @package   Quinoid_HomepageBanner
 *                     @copyright Copyright (c) 2017
 *                     @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Quinoid\HomepageBanner\Model\ResourceModel;

class Video extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * constructor
     *
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    )
    {
        $this->date = $date;
        parent::__construct($context);
    }


    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('quinoid_homepagebanner_video', 'video_id');
    }

    /**
     * Retrieves Video Title from DB by passed id.
     *
     * @param string $id
     * @return string|bool
     */
    public function getVideoTitleById($id)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), 'title')
            ->where('video_id = :video_id');
        $binds = ['video_id' => (int)$id];
        return $adapter->fetchOne($select, $binds);
    }

    /**
     * Retrieves Video thumbnail path and file path from DB by passed id whose status is enabled.
     *
     * @param string $id
     * @return string|bool
     */
    public function getVideoDataById($id)
    {
        $adapter = $this->getConnection();
        $fields = array('videofile','videothumbnail','redirect_url','video_url');
        $select = $adapter->select()
            ->from($this->getMainTable(),$fields)
            ->where('video_id = :video_id');
        $binds = ['video_id' => (int)$id];
        return $adapter->fetchAll($select, $binds);
    }

    /**
     * Retrieves Video Options i.e. video_id & title from DB by passed id whose status is enabled.
     *
     * @return string|bool
     */
    public function getVideoOptions()
    {
        $adapter = $this->getConnection();
        $fields = array('video_id', 'title');
        $select = $adapter->select()
            ->from($this->getMainTable(),$fields)
            ->where('status = true');
        return $adapter->fetchAll($select);
    }

    /**
     * Retrieves Video Options i.e. video_id & title from DB by passed id whose status is enabled.
     *
     * @return string|bool
     */
    public function getAllVideos()
    {
        $adapter = $this->getConnection();
        $fields = array('video_id','title', 'redirect_url','show_in_frontend','upload_type','videofile','videothumbnail','video_url');
        $select = $adapter->select()
            ->from($this->getMainTable(),$fields)
            ->where('status = true');
        return $adapter->fetchAll($select);
    }

    /**
     * before save callback
     *
     * @param \Magento\Framework\Model\AbstractModel|\Quinoid\HomepageBanner\Model\Video $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setUpdatedAt($this->date->date());
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->date->date());
        }
        return parent::_beforeSave($object);
    }
}
