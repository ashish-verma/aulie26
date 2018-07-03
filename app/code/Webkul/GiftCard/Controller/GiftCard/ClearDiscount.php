<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_GiftCard
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\GiftCard\Controller\GiftCard;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;

/**
 * Webkul GiftCard ClearDiscount Controller.
 */
class ClearDiscount extends Action
{
    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    protected $_salesRule;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_quote;
    
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_backendSession;
    
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    
    /**
     *
     * @param Context                                            $context
     * @param \Magento\SalesRule\Model\Rule                      $salesRule
     * @param \Magento\Checkout\Model\Cart                       $quote
     * @param \Magento\Framework\Session\SessionManagerInterface $backendSession
     * @param \Magento\Checkout\Model\Session                    $checkoutSession
     */
    public function __construct(
        Context $context,
        \Magento\SalesRule\Model\Rule $salesRule,
        \Magento\Checkout\Model\Cart $quote,
        \Magento\Framework\Session\SessionManagerInterface $backendSession,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_salesRule = $salesRule;
        $this->_quote = $quote;
        $this->_backendSession = $backendSession;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    public function execute()
    {
        $collection = $this->_salesRule->getCollection()->load();
        foreach ($collection as $mo) {
            // Delete coupon
            if ($mo->getName() == $this->_backendSession->getCoupancode()) {
                $mo->delete();
                $this->_backendSession->setCoupancode(null);
                $this->_backendSession->setReducedprice(null);
                $this->_quote
                ->getQuote()
                ->setCouponCode(null)
                ->collectTotals()
                ->save();
                $this->_checkoutSession->setCartWasUpdated(true);
            }
        }
        $this->messageManager->addError(__("Gift Card Discount Removed"));
    }
}
