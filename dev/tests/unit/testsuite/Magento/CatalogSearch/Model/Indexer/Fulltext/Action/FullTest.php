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

namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Action;

class FullTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Search\Request\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $searchRequestConfig;
    /** @var \Magento\Framework\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManager;
    /** @var \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full */
    protected $object;

    public function setUp()
    {
        $resource = $this->getMockBuilder('Magento\Framework\App\Resource')
            ->disableOriginalConstructor()
            ->getMock();
        $catalogProductType = $this->getMockBuilder('Magento\Catalog\Model\Product\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $eavConfig = $this->getMockBuilder('Magento\Eav\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchRequestConfig = $this->getMockBuilder('Magento\Framework\Search\Request\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $catalogProductStatus =
            $this->getMockBuilder('Magento\Catalog\Model\Product\Attribute\Source\Status')
                ->disableOriginalConstructor()
                ->getMock();
        $engineProvider = $this->getMockBuilder('Magento\CatalogSearch\Model\Resource\EngineProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $eventManager = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $catalogSearchData = $this->getMockBuilder('Magento\CatalogSearch\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $scopeConfig = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder('Magento\Framework\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $dateTime = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime')
            ->disableOriginalConstructor()
            ->getMock();
        $localeResolver = $this->getMockBuilder('Magento\Framework\Locale\ResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $localeDate = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\TimezoneInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $fulltextResource = $this->getMockBuilder('Magento\CatalogSearch\Model\Resource\Fulltext')
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->object = $objectManagerHelper->getObject(
            'Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full',
            array(
                'resource' => $resource,
                'catalogProductType' => $catalogProductType,
                'eavConfig' => $eavConfig,
                'searchRequestConfig' => $this->searchRequestConfig,
                'catalogProductStatus' => $catalogProductStatus,
                'engineProvider' => $engineProvider,
                'eventManager' => $eventManager,
                'catalogSearchData' => $catalogSearchData,
                'scopeConfig' => $scopeConfig,
                'storeManager' => $this->storeManager,
                'dateTime' => $dateTime,
                'localeResolver' => $localeResolver,
                'localeDate' => $localeDate,
                'fulltextResource' => $fulltextResource
            )
        );
    }

    public function testReindexAll()
    {
        $this->storeManager->expects($this->once())->method('getStores')->willReturn([]);
        $this->searchRequestConfig->expects($this->once())->method('reset');
        $this->object->reindexAll();
    }
}
