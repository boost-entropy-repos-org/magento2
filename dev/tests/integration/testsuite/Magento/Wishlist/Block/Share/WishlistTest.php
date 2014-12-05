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

namespace Magento\Wishlist\Block\Share;

class WishlistTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Wishlist\Block\Share\Wishlist
     */
    protected $_block;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    public function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
        $this->_block = $this->_objectManager->create('Magento\Wishlist\Block\Share\Wishlist');
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Wishlist/_files/wishlist.php
     */
    public function testGetWishlistCustomer()
    {
        $this->_customerSession->loginById(1);
        $expectedCustomer = $this->_customerSession->getCustomerDataObject();
        $actualCustomer = $this->_block->getWishlistCustomer();
        $this->assertInstanceOf('Magento\Customer\Api\Data\CustomerInterface', $actualCustomer);
        $this->assertEquals((int)$expectedCustomer->getId(), (int)$actualCustomer->getId());
        $this->assertEquals((int)$expectedCustomer->getWebsiteId(), (int)$actualCustomer->getWebsiteId());
        $this->assertEquals((int)$expectedCustomer->getStoreId(), (int)$actualCustomer->getStoreId());
        $this->assertEquals((int)$expectedCustomer->getGroupId(), (int)$actualCustomer->getGroupId());
        $this->assertEquals($expectedCustomer->getCustomAttributes(), $actualCustomer->getCustomAttributes());
        $this->assertEquals($expectedCustomer->getFirstname(), $actualCustomer->getFirstname());
        $this->assertEquals($expectedCustomer->getLastname(), $actualCustomer->getLastname());
        $this->assertEquals($expectedCustomer->getEmail(), $actualCustomer->getEmail());
        $this->assertEquals($expectedCustomer->getEmail(), $actualCustomer->getEmail());
        $this->assertEquals((int)$expectedCustomer->getDefaultBilling(), (int)$actualCustomer->getDefaultBilling());
        $this->assertEquals((int)$expectedCustomer->getDefaultShipping(), (int)$actualCustomer->getDefaultShipping());
    }
}
 