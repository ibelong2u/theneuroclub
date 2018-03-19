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
namespace Quinoid\HomepageBanner\Model;

/**
 * @method Video setTitle($title)
 * @method Video setVideofile($videofile)
 * @method Video setVideothumbnail($videothumbnail)
 * @method Video setStatus($status)
 * @method mixed getTitle()
 * @method mixed getVideofile()
 * @method mixed getVideothumbnail()
 * @method mixed getStatus()
 * @method Video setCreatedAt(\string $createdAt)
 * @method string getCreatedAt()
 * @method Video setUpdatedAt(\string $updatedAt)
 * @method string getUpdatedAt()
 */
class Video extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'quinoid_homepagebanner_video';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'quinoid_homepagebanner_video';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'quinoid_homepagebanner_video';


    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Quinoid\HomepageBanner\Model\ResourceModel\Video');
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * get entity default values
     *
     * @return array
     */
    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }
}
