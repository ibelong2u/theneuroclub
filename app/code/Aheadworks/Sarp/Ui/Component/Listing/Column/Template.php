<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Ui\Component\Listing\Column;

use Aheadworks\Sarp\Model\Logger\Data\Template\FilterProvider;
use Aheadworks\Sarp\Model\Logger\Data\Template\Translator;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class Template
 * @package Aheadworks\Sarp\Ui\Component\Listing\Column
 */
class Template extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var FilterProvider
     */
    private $filterProvider;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param FilterProvider $filterProvider
     * @param Translator $translator
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        FilterProvider $filterProvider,
        Translator $translator,
        array $components = [],
        array $data = []
    ) {
        $this->filterProvider = $filterProvider;
        $this->translator = $translator;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $index = $this->getName();
            $filter = $this->filterProvider->getFilter();
            foreach ($dataSource['data']['items'] as & $item) {
                if ($item[$index]) {
                    $value = $this->translator->translate($item[$index]);
                    $item[$index] = $filter->filter($value);
                }
            }
        }
        return $dataSource;
    }
}
