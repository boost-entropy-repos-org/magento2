<?php
/**
 *
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
namespace Magento\Review\Controller\Adminhtml\Product;

class JsonProductInfo extends \Magento\Review\Controller\Adminhtml\Product
{
    /** @var  \Magento\Catalog\Api\ProductRepositoryInterface */
    protected $productRepository;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context, $coreRegistry, $reviewFactory, $ratingFactory);
        $this->productRepository = $productRepository;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $response = new \Magento\Framework\Object();
        $id = $this->getRequest()->getParam('id');
        if (intval($id) > 0) {
            $product = $this->productRepository->getById($id);

            $response->setId($id);
            $response->addData($product->getData());
            $response->setError(0);
        } else {
            $response->setError(1);
            $response->setMessage(__('We can\'t get the product ID.'));
        }
        $this->getResponse()->representJson($response->toJSON());
    }
}
