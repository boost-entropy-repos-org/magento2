<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\View\Layout\Generator;

use Magento\Framework\View\Layout;

class Container implements Layout\GeneratorInterface
{
    /**#@+
     * Names of container options in layout
     */
    const CONTAINER_OPT_HTML_TAG = 'htmlTag';
    const CONTAINER_OPT_HTML_CLASS = 'htmlClass';
    const CONTAINER_OPT_HTML_ID = 'htmlId';
    const CONTAINER_OPT_LABEL = 'label';
    /**#@-*/

    const TYPE = 'container';

    /**
     * @var array
     */
    protected $allowedTags = [
        'dd',
        'div',
        'dl',
        'fieldset',
        'main',
        'header',
        'footer',
        'ol',
        'p',
        'section',
        'table',
        'tfoot',
        'ul'
    ];

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * Process container elements
     *
     * @param \Magento\Framework\View\Layout\Reader\Context $readerContext
     * @param Context $generatorContext
     * @return $this
     */
    public function process(Layout\Reader\Context $readerContext, Layout\Generator\Context $generatorContext)
    {
        $structure = $generatorContext->getStructure();
        $scheduledStructure = $readerContext->getScheduledStructure();
        foreach ($scheduledStructure->getElements() as $elementName => $element) {
            list($type, $data) = $element;
            if ($type === self::TYPE) {
                $this->generateContainer($structure, $elementName, $data['attributes']);
                $scheduledStructure->unsetElement($elementName);
            }
        }
        return $this;
    }

    /**
     * Set container-specific data to structure element
     *
     * @param \Magento\Framework\View\Layout\Data\Structure $structure
     * @param string $elementName
     * @param array $options
     * @return void
     */
    public function generateContainer(
        Layout\Data\Structure $structure,
        $elementName,
        $options
    ) {
        $structure->setAttribute(
            $elementName,
            Layout\Element::CONTAINER_OPT_LABEL,
            $options[Layout\Element::CONTAINER_OPT_LABEL]
        );
        unset($options[Layout\Element::CONTAINER_OPT_LABEL]);
        unset($options['type']);

        $this->validateOptions($options);

        foreach ($options as $key => $value) {
            $structure->setAttribute($elementName, $key, $value);
        }
    }

    /**
     * @param array $options
     * @return void
     * @throws \Magento\Framework\Exception
     */
    protected function validateOptions($options)
    {
        if (!empty($options[Layout\Element::CONTAINER_OPT_HTML_TAG])
            && !in_array(
                $options[Layout\Element::CONTAINER_OPT_HTML_TAG],
                $this->allowedTags
            )
        ) {
            throw new \Magento\Framework\Exception(
                sprintf(
                    'Html tag "%s" is forbidden for usage in containers. Consider to use one of the allowed: %s.',
                    $options[Layout\Element::CONTAINER_OPT_HTML_TAG],
                    implode(', ', $this->allowedTags)
                )
            );
        }

        if (empty($options[Layout\Element::CONTAINER_OPT_HTML_TAG])
            && (
                !empty($options[Layout\Element::CONTAINER_OPT_HTML_ID])
                || !empty($options[Layout\Element::CONTAINER_OPT_HTML_CLASS])
            )
        ) {
            throw new \Magento\Framework\Exception(
                'HTML ID or class will not have effect, if HTML tag is not specified.'
            );
        }
    }
}
