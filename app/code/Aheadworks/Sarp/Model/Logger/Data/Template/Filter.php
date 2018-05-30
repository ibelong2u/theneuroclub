<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Logger\Data\Template;

use Aheadworks\Sarp\Model\Logger\Data\Template\Html\RendererPool as HtmlRendererPool;
use Magento\Framework\Filter\Template as TemplateFilter;
use Magento\Framework\Stdlib\StringUtils;

/**
 * Class Filter
 * @package Aheadworks\Sarp\Model\Logger\Data\Template
 */
class Filter extends TemplateFilter
{
    /**
     * Entity link construction regular expression
     */
    const CONSTRUCTION_ENTITY_LINK_HTML_PATTERN = '/{{([a-z]{0,15})\s(.*?)\s(.*?)}}/si';

    /**
     * @var HtmlRendererPool
     */
    private $htmlRendererPool;

    /**
     * @var FormatterPool
     */
    private $formatterPool;

    /**
     * @param StringUtils $string
     * @param HtmlRendererPool $htmlRendererPool
     * @param FormatterPool $formatterPool
     * @param array $variables
     */
    public function __construct(
        StringUtils $string,
        HtmlRendererPool $htmlRendererPool,
        FormatterPool $formatterPool,
        array $variables = []
    ) {
        parent::__construct($string, $variables);
        $this->htmlRendererPool = $htmlRendererPool;
        $this->formatterPool = $formatterPool;
    }

    /**
     * {@inheritdoc}
     */
    public function filter($value)
    {
        try {
            $directives = [self::CONSTRUCTION_ENTITY_LINK_HTML_PATTERN => 'entityLinkHtmlDirective'];
            foreach ($directives as $pattern => $directive) {
                if (preg_match_all($pattern, $value, $constructions, PREG_SET_ORDER)) {
                    foreach ($constructions as $construction) {
                        $callback = [$this, $directive];
                        if (is_callable($callback)) {
                            try {
                                $replacedValue = call_user_func($callback, $construction);
                            } catch (\Exception $e) {
                                throw $e;
                            }
                            $value = str_replace($construction[0], $replacedValue, $value);
                        }
                    }
                }
            }
            return parent::filter($value);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Entity link html directive
     *
     * @param string[] $construction
     * @return string
     */
    public function entityLinkHtmlDirective($construction)
    {
        $linkType = $construction[1];
        if (in_array($linkType, ['orderLink', 'invoiceLink'])) {
            $renderer = $this->htmlRendererPool->createRenderer($linkType);
            return $renderer->render(
                [
                    'id' => $construction[2],
                    'title' => $construction[3]
                ]
            );
        }
        return '';
    }

    /**
     * Format date directive
     *
     * @param string[] $construction
     * @return string
     */
    public function formatDateDirective($construction)
    {
        $formatter = $this->formatterPool->getFormatter('date');
        return $formatter->format($construction[2]);
    }
}
