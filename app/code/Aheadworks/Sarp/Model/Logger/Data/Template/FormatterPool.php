<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Logger\Data\Template;

use Aheadworks\Sarp\Model\Logger\Data\Template\Formatter\Date;

/**
 * Class FormatterPool
 * @package Aheadworks\Sarp\Model\Logger\Data\Template
 */
class FormatterPool
{
    /**
     * @var array
     */
    private $pool = ['date' => Date::class];

    /**
     * @var FormatterFactory
     */
    private $factory;

    /**
     * @var array
     */
    private $formatterInstances = [];

    /**
     * @param FormatterFactory $factory
     */
    public function __construct(FormatterFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Retrieve formatter instance
     *
     * @param string $formatterCode
     * @return FormatterInterface
     * @throws \LogicException
     */
    public function getFormatter($formatterCode)
    {
        if (!isset($this->formatterInstances[$formatterCode])) {
            if (!isset($this->pool[$formatterCode])) {
                throw new \LogicException(sprintf('Unknown formatter: %s requested', $formatterCode));
            }
            $formatterInstance = $this->factory->create($this->pool[$formatterCode]);
            if (!$formatterInstance instanceof FormatterInterface) {
                throw new \LogicException(
                    sprintf('Formatter %s does not implement required interface.', $formatterCode)
                );
            }
            $this->formatterInstances[$formatterCode] = $formatterInstance;
        }
        return $this->formatterInstances[$formatterCode];
    }
}
