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
namespace Quinoid\HomepageBanner\Block\Adminhtml\Video\Edit\Tab;

class Video extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Country options
     *
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $booleanOptions;

    /**
     * constructor
     *
     * @param \Magento\Config\Model\Config\Source\Yesno $booleanOptions
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Config\Model\Config\Source\Yesno $booleanOptions,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    )
    {
        $this->booleanOptions = $booleanOptions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Quinoid\HomepageBanner\Model\Video $video */
        $video = $this->_coreRegistry->registry('quinoid_homepagebanner_video');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('video_');
        $form->setFieldNameSuffix('video');
        $htmlIdPrefix = $form->getHtmlIdPrefix();
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('General Information'),
                'class'  => 'fieldset-wide'
            ]
        );
        $fieldset->addType('image', 'Quinoid\HomepageBanner\Block\Adminhtml\Video\Helper\Image');
        $fieldset->addType('file', 'Quinoid\HomepageBanner\Block\Adminhtml\Video\Helper\File');
        if ($video->getId()) {
            $fieldset->addField(
                'video_id',
                'hidden',
                ['name' => 'video_id']
            );
        }
        $fieldset->addField(
            'title',
            'text',
            [
                'name'  => 'title',
                'label' => __('Title'),
                'title' => __('Title'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'redirect_url',
            'text',
            [
                'name'  => 'redirect_url',
                'label' => __('Redirect URL'),
                'title' => __('Redirect URL'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'show_in_frontend',
            'select',
            [
                'name'  => 'show_in_frontend',
                'label' => __('Show in Frontend'),
                'title' => __('Show in Frontend'),
                'required' => true,
                'values' => $this->booleanOptions->toOptionArray(),
            ]
        );
        $fieldset->addField(
            'status',
            'select',
            [
                'name'  => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'required' => true,
                'values' => $this->booleanOptions->toOptionArray(),
            ]
        );
        $fieldset->addField(
            'upload_type',
            'select',
            [
                'name'  => 'upload_type',
                'label' => __('Upload Type'),
                'title' => __('Upload Type'),
                'required' => true,
                'values' => [
                    ['label' => __('Video/Image File'), 'value' => 0],
                    ['label' => __('Video URL'), 'value' => 1],
                ],
            ]
        );
        $fieldset->addField(
            'videofile',
            'file',
            [
                'name'  => 'videofile',
                'label' => __('Attachment File (Video/Image)'),
                'title' => __('Video File'),
            ]
        );
        $fieldset->addField(
            'videothumbnail',
            'file',
            [
                'name'  => 'videothumbnail',
                'label' => __('Responsive Image File'),
                'title' => __('Responsive Image File'),
            ]
        );
        $fieldset->addField(
            'video_url',
            'text',
            [
                'name'  => 'video_url',
                'label' => __('Video URL'),
                'title' => __('Video URL'),
            ]
        );
        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )->addFieldMap(
                "{$htmlIdPrefix}upload_type",
                'upload_type'
            )
            ->addFieldMap(
                "{$htmlIdPrefix}videofile",
                'videofile'
            )
            ->addFieldDependence(
                'videofile',
                'upload_type',
                '0'
            )
            ->addFieldMap(
                "{$htmlIdPrefix}videothumbnail",
                'videothumbnail'
            )
            ->addFieldDependence(
                'videothumbnail',
                'upload_type',
                '0'
            )
            ->addFieldMap(
                "{$htmlIdPrefix}video_url",
                'video_url'
            )
            ->addFieldDependence(
                'video_url',
                'upload_type',
                '1'
            )
        );
        $videoData = $this->_session->getData('Quinoid_homepagebanner_video_data', true);
        if ($videoData) {
            $video->addData($videoData);
        } else {
            if (!$video->getId()) {
                $video->addData($video->getDefaultValues());
            }
        }
        $form->addValues($video->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Banner');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
