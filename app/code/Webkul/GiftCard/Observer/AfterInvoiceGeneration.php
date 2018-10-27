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

namespace Webkul\GiftCard\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\RequestInterface;

class AfterInvoiceGeneration implements ObserverInterface
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_salesOrder;
    
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_catalpgProduct;
    
    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    protected $_magentoSalesRule;
    
    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $_magentoSalesOrderItem;
    
    /**
     * @var \Webkul\GiftCard\Helper\Data
     */
    protected $_helperData;
    
    /**
     * @var \Webkul\GiftCard\Model\GiftDetailFactory
     */
    protected $_giftDetailFactory;
    
    /**
     * @var \Webkul\GiftCard\Model\GiftUserFactory
     */
    protected $_giftUserFactory;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_timezoneInterface;
    
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     *
     * @param \Magento\Sales\Model\Order                           $salesOrder
     * @param \Magento\Catalog\Model\Product                       $catalpgProduct
     * @param \Magento\SalesRule\Model\Rule                        $magentoSalesRule
     * @param \Magento\Sales\Model\Order\ItemFactory               $magentoSalesOrderItem
     * @param \Webkul\GiftCard\Helper\Data                         $helperData
     * @param \Webkul\GiftCard\Model\GiftDetailFactory             $giftDetailFactory
     * @param \Webkul\GiftCard\Model\GiftUserFactory               $giftUserFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
     * @param \Magento\Framework\Message\ManagerInterface          $messageManager
     */
    public function __construct(
        \Magento\Sales\Model\Order $salesOrder,
        \Magento\Catalog\Model\Product $catalpgProduct,
        \Magento\SalesRule\Model\Rule $magentoSalesRule,
        \Magento\Sales\Model\Order\ItemFactory $magentoSalesOrderItem,
        \Webkul\GiftCard\Helper\Data $helperData,
        \Webkul\GiftCard\Model\GiftDetailFactory $giftDetailFactory,
        \Webkul\GiftCard\Model\GiftUserFactory $giftUserFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_salesOrder = $salesOrder;
        $this->_catalpgProduct = $catalpgProduct;
        $this->_magentoSalesRule = $magentoSalesRule;
        $this->_magentoSalesOrderItem = $magentoSalesOrderItem;
        $this->_helperData = $helperData;
        $this->_giftDetailFactory = $giftDetailFactory;
        $this->_giftUserFactory = $giftUserFactory;
        $this->_timezoneInterface = $timezoneInterface;
        $this->_messageManager = $messageManager;
    }

/**
 * This is the method that fires when the event runs.
 *
 * @param Observer $observer
 */
    public function execute(Observer $observer)
    {
        return;
        $invoice = $observer->getEvent()->getInvoice();
        $oids = $invoice->getOrderId();
        $sl = $this->_salesOrder->load($oids);
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $_Symbol = $_objectManager->create('Magento\Directory\Model\CurrencyFactory')->create()->load($this->_helperData->getBaseCurrencyCode());
        $couponCode=$sl->getCouponCode();
        $discountAmt=$sl->getDiscountAmount();
        foreach ($sl->getAllItems() as $item) {
            $productid = $item->getProductId();
            $gcqty=$item->getQtyOrdered();
            for ($i=0; $i < intval($gcqty); $i++) {
                $giftmodel  = $this->_catalpgProduct->load($productid);
                if ($giftmodel->getTypeId() == 'giftcard') {
                    $userEmail = "";
                    $userMessage = "";
                    $options = $item->getProductOptions();
                    $customOptions = $options['options'];
                    if (!empty($customOptions)) {
                        foreach ($customOptions as $option) {
                            if ($option['label'] == 'Email To') {
                                $userEmail = $option['value'];
                            }
                            if ($option['label'] == 'Message') {
                                $userMessage = $option['value'];
                            }
                        }
                    }
                    $customer=$sl->getCustomerEmail();
                    $customer_name=$sl->getCustomerFirstname()." ".$sl->getCustomerLastname();
                    $mailData=[];
                    /* Assign values for your template variables  */
                    $emailTemplateVariables = [];
                    // $price= $giftmodel->getPrice();
                    $price= $item->getPrice();
                    $mailData['price']=$price;
                    $emailTemplateVariables['myvar1'] = $price;
                    // $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    // $_Symbol = $_objectManager->create('Magento\Directory\Model\CurrencyFactory')->create()->load($this->_helperData->getBaseCurrencyCode());
                    $emailTemplateVariables['myvar8'] = $_Symbol->getCurrencySymbol();
                    // $des= $giftmodel->getShortDescription();
                    $des = $giftmodel->getDescription();
                    $mailData['description']=$des;
                    $emailTemplateVariables['myvar2'] = $des;
                    $email = $userEmail;
                    $mailData['reciever']=$email;
                    /* Receiver Detail  */
                    $receiverInfo = [
                        'name' => 'Reciver Name',
                        'email' => $email
                    ];
                    $emailTemplateVariables['myvar6'] = 'Reciver Name';
                    $emailTemplateVariables['myvar7'] = $email;
                    $emailTemplateVariables['myvar9'] = $userMessage;
                    $emailTemplateVariables['myvar11'] = $giftmodel->getSmallImage();
                    $giftcode=$this->_helperData->get_rand_id(12);
                    $mailData['sender']=$customer;
                    $mailData['sender_name']=$customer_name;
                    $emailTemplateVariables['myvar4'] = $customer;
                    $emailTemplateVariables['myvar5'] = $customer_name;
                    /* Sender Detail  */
                    $senderInfo = [
                        'name' => $customer_name,
                        'email' => $customer
                    ];
                    $usageDurationOfGiftCard = $this->_helperData->getGiftCardActiveDuration();
                    if ($email) {
                        $data=["price"=>$price,"description"=>$des,"email"=>$email,"from"=>$customer,"message"=>$userMessage,"duration"=>$usageDurationOfGiftCard,'order_id'=>$oids];
                        $model=$this->_giftDetailFactory->create()->setData($data);
                        $dateTimeAsTimeZone = $this->_timezoneInterface
                                        ->date(new \DateTime(date("Y/m/d h:i:sa")))
                                        ->format('Y/m/d H:i:s');
                        $emailTemplateVariables['myvar10'] = $this->_helperData->createExpirationDateOfGiftCard($usageDurationOfGiftCard, $dateTimeAsTimeZone);
                        try {
                            $id=$model->save()->getGiftId();
                            $model2=$this->_giftUserFactory->create()->setData(["giftcodeid"=>$id,"amount"=>$price,"alloted"=>$dateTimeAsTimeZone,"email"=>$email,"from"=>$customer,"remaining_amt"=>$price,"is_active"=>"yes","is_expire"=>0]);
                            $id2=$model2->save()->getGiftuserid();
                            $this->_giftDetailFactory->create()->load($id)->setGiftCode($id2.$giftcode)->save();
                            $this->_giftUserFactory->create()->load($id2)->setCode($id2.$giftcode)->save();
                            $emailTemplateVariables['myvar3'] = $id2.$giftcode;
                            $mailData['code']=$id2.$giftcode;
                            try {
                                $this->_helperData->customMailSendMethod(
                                    $emailTemplateVariables,
                                    $senderInfo,
                                    $receiverInfo
                                );
                            } catch (\Exception $e) {
                                $this->_messageManager->addError(__($e->getMessage()));
                                return false;
                            }
                        } catch (\Exception $e) {
                                $this->_messageManager->addError(__($e->getMessage()));
                                return false;
                        }
                    }
                }
            }
        }
        $cc=$this->_giftUserFactory->create()->getCollection()->addFieldToFilter('code', $couponCode);
        if ($cc->getSize()) {
            $gift_user_data=[];
            $customerName=$sl->getCustomerFirstname()." ".$sl->getCustomerLastname();
            $customerEmail=$sl->getCustomerEmail();
            $gift_user_data["orderId"]=$sl->getIncrementId();
            $gift_user_data["reciever_email"]=$customerEmail;
            $gift_user_data["reciever_name"]=$customerName;
            $gift_user_data["reduced_ammount"]=$discountAmt;
            $emailTemplateVariablesForLeftAmt["myvar1"]=$sl->getIncrementId();
            $emailTemplateVariablesForLeftAmt["myvar2"]=$customerEmail;
            $emailTemplateVariablesForLeftAmt["myvar3"]=$customerName;
            $emailTemplateVariablesForLeftAmt["myvar4"]=$discountAmt;
            $emailTemplateVariablesForLeftAmt['myvar8'] = $_Symbol->getCurrencySymbol();
            $model3=$this->_giftUserFactory->create()
            ->getCollection()
            ->addFieldToFilter("code", $couponCode);
            foreach ($model3 as $m3) {
                $gift_user_data["previous_ammount"]=$amnt=$m3->getAmount();
                $gift_user_data["gift_code"]=$m3->getCode();
                $emailTemplateVariablesForLeftAmt["myvar5"]=$amnt=$m3->getAmount();
                $emailTemplateVariablesForLeftAmt["myvar6"]=$m3->getCode();
                $m3->setAmount($amnt+$discountAmt)->save();
                $gift_user_data["result_ammount"]=$m3->getAmount();
                $emailTemplateVariablesForLeftAmt["myvar7"]=$m3->getAmount();
                $giftCodeId = $m3->getGiftcodeid();
                $date = $m3->getAlloted();
            }
            $giftDetailModel = $this->_giftDetailFactory->create()->load($giftCodeId);
            $duration = $giftDetailModel->getDuration();
            $emailTemplateVariablesForLeftAmt["myvar9"] = $date;
            $emailTemplateVariablesForLeftAmt["myvar10"] = $this->_helperData->createExpirationDateOfGiftCard($duration, $date);
            $collection = $this->_magentoSalesRule->getCollection()->load();
            foreach ($collection as $m) {
                if ($m->getName() == $couponCode) {
                    $m->delete();
                }
            }
            $receiverInfo = [
                'name' => $customerName,
                'email' => $customerEmail
            ];
            $adminName = $this->_helperData->getAdminNameFromConfig();
            $adminEmail = $this->_helperData->getAdminEmailFromConfig();
            if (!isset($adminName) || $adminName == "") {
                $adminName = $this->_helperData->getStorename();
            }
            if (!isset($adminEmail) || $adminEmail == "") {
                $adminEmail = $this->_helperData->getStoreEmail();
            }
            $senderInfo = [
                'name' => $adminName,
                'email' => $adminEmail
            ];
            $emailTemplateVariablesForLeftAmt['myvar8'] = $this->_helperData->getBaseCurrencyCode();
            $this->_helperData->customMailSendMethodForLeftAmt(
                $emailTemplateVariablesForLeftAmt,
                $senderInfo,
                $receiverInfo
            );
            $coupon_model = $this->_magentoSalesRule->getCollection()->load();
            foreach ($coupon_model as $cpn) {
                if (trim($cpn->getName()) == trim($couponCode)) {
                    $cpn->delete();
                }
            }
        }
    }

    public function sendGiftCard($oids)
    {               
       // $sl = $this->_salesOrder->load($oids);
        $sl = $this->_salesOrder->loadByIncrementId($oids);
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $_Symbol = $_objectManager->create('Magento\Directory\Model\CurrencyFactory')->create()->load($this->_helperData->getBaseCurrencyCode());
        $couponCode=$sl->getCouponCode();
        $discountAmt=$sl->getDiscountAmount();
        foreach ($sl->getAllItems() as $item) {
            $productid = $item->getProductId();
            $gcqty= 1;//$item->getQtyOrdered();
            for ($i=0; $i < intval($gcqty); $i++) {
                $giftmodel  = $this->_catalpgProduct->load($productid);
                if ($giftmodel->getTypeId() == 'giftcard') {
                    $userEmail = "";
                    $userMessage = "";
                    $options = $item->getProductOptions();
                    $customOptions = $options['options'];
                    if (!empty($customOptions)) {
                        foreach ($customOptions as $option) {
                            if ($option['label'] == 'To') {
                                $userEmail = $option['value'];
                            }
                            if ($option['label'] == 'Message') {
                                $userMessage = $option['value'];
                            }
                        }
                    }
                    $customer=$sl->getCustomerEmail();
                    $customer_name=$sl->getCustomerFirstname()." ".$sl->getCustomerLastname();
                    $mailData=[];
                    /* Assign values for your template variables  */
                    $emailTemplateVariables = [];
                    // $price= $giftmodel->getPrice();
                    $price= $item->getPrice();
                    $mailData['price']=$price;
                    $emailTemplateVariables['myvar1'] = $price;
                    // $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    // $_Symbol = $_objectManager->create('Magento\Directory\Model\CurrencyFactory')->create()->load($this->_helperData->getBaseCurrencyCode());
                    $emailTemplateVariables['myvar8'] = $_Symbol->getCurrencySymbol();
                    // $des= $giftmodel->getShortDescription();
                    $des = $giftmodel->getDescription();
                    $mailData['description']=$des;
                    $emailTemplateVariables['myvar2'] = $des;
                    $email = $userEmail;
                    $mailData['reciever']=$email;
                    /* Receiver Detail  */
                    $receiverInfo = [
                        'name' => 'Reciver Name',
                        'email' => $email
                    ];
                    $emailTemplateVariables['myvar6'] = 'Reciver Name';
                    $emailTemplateVariables['myvar7'] = $email;
                    $emailTemplateVariables['myvar9'] = $userMessage;
                    $emailTemplateVariables['myvar11'] = $giftmodel->getSmallImage();
                    $giftcode=$this->_helperData->get_rand_id(12);
                    $mailData['sender']=$customer;
                    $mailData['sender_name']=$customer_name;
                    $emailTemplateVariables['myvar4'] = $customer;
                    $emailTemplateVariables['myvar5'] = $customer_name;
                    /* Sender Detail  */
                    $senderInfo = [
                        'name' => $customer_name,
                        'email' => $customer
                    ];
                    $usageDurationOfGiftCard = $this->_helperData->getGiftCardActiveDuration();
                    if ($email) {
                        $data=["price"=>$price,"description"=>$des,"email"=>$email,"from"=>$customer,"message"=>$userMessage,"duration"=>$usageDurationOfGiftCard,'order_id'=>$oids];
                        $model=$this->_giftDetailFactory->create()->setData($data);
                        $dateTimeAsTimeZone = $this->_timezoneInterface
                                        ->date(new \DateTime(date("d-m-Y h:i:sa")))
                                        ->format('d-m-Y H:i:s');
                        $emailTemplateVariables['myvar10'] = $this->_helperData->createExpirationDateOfGiftCard($usageDurationOfGiftCard, $dateTimeAsTimeZone);
                        try {
                            $id=$model->save()->getGiftId();
                            $model2=$this->_giftUserFactory->create()->setData(["giftcodeid"=>$id,"amount"=>$price,"alloted"=>$dateTimeAsTimeZone,"email"=>$email,"from"=>$customer,"remaining_amt"=>$price,"is_active"=>"yes","is_expire"=>0]);
                            $id2=$model2->save()->getGiftuserid();
                            $this->_giftDetailFactory->create()->load($id)->setGiftCode($id2.$giftcode)->save();
                            $this->_giftUserFactory->create()->load($id2)->setCode($id2.$giftcode)->save();
                            $emailTemplateVariables['myvar3'] = $id2.$giftcode;
                            $mailData['code']=$id2.$giftcode;
                            try {
                                $this->_helperData->customMailSendMethod(
                                    $emailTemplateVariables,
                                    $senderInfo,
                                    $receiverInfo
                                );
                            } catch (\Exception $e) {
                                $this->_messageManager->addError(__($e->getMessage()));
                                return false;
                            }
                        } catch (\Exception $e) {
                                $this->_messageManager->addError(__($e->getMessage()));
                                return false;
                        }
                    }
                }
            }
        }
        $cc=$this->_giftUserFactory->create()->getCollection()->addFieldToFilter('code', $couponCode);
        if ($cc->getSize()) {
            $gift_user_data=[];
            $customerName=$sl->getCustomerFirstname()." ".$sl->getCustomerLastname();
            $customerEmail=$sl->getCustomerEmail();
            $gift_user_data["orderId"]=$sl->getIncrementId();
            $gift_user_data["reciever_email"]=$customerEmail;
            $gift_user_data["reciever_name"]=$customerName;
            $gift_user_data["reduced_ammount"]=$discountAmt;
            $emailTemplateVariablesForLeftAmt["myvar1"]=$sl->getIncrementId();
            $emailTemplateVariablesForLeftAmt["myvar2"]=$customerEmail;
            $emailTemplateVariablesForLeftAmt["myvar3"]=$customerName;
            $emailTemplateVariablesForLeftAmt["myvar4"]=$discountAmt;
            $emailTemplateVariablesForLeftAmt['myvar8'] = $_Symbol->getCurrencySymbol();
            $model3=$this->_giftUserFactory->create()
            ->getCollection()
            ->addFieldToFilter("code", $couponCode);
            foreach ($model3 as $m3) {
                $gift_user_data["previous_ammount"]=$amnt=$m3->getAmount();
                $gift_user_data["gift_code"]=$m3->getCode();
                $emailTemplateVariablesForLeftAmt["myvar5"]=$amnt=$m3->getAmount();
                $emailTemplateVariablesForLeftAmt["myvar6"]=$m3->getCode();
                $m3->setAmount($amnt+$discountAmt)->save();
                $gift_user_data["result_ammount"]=$m3->getAmount();
                $emailTemplateVariablesForLeftAmt["myvar7"]=$m3->getAmount();
                $giftCodeId = $m3->getGiftcodeid();
                $date = $m3->getAlloted();
            }
            $giftDetailModel = $this->_giftDetailFactory->create()->load($giftCodeId);
            $duration = $giftDetailModel->getDuration();
            $emailTemplateVariablesForLeftAmt["myvar9"] = $date;
            $emailTemplateVariablesForLeftAmt["myvar10"] = $this->_helperData->createExpirationDateOfGiftCard($duration, $date);
            $collection = $this->_magentoSalesRule->getCollection()->load();
            foreach ($collection as $m) {
                if ($m->getName() == $couponCode) {
                    $m->delete();
                }
            }
            $receiverInfo = [
                'name' => $customerName,
                'email' => $customerEmail
            ];
            $adminName = $this->_helperData->getAdminNameFromConfig();
            $adminEmail = $this->_helperData->getAdminEmailFromConfig();
            if (!isset($adminName) || $adminName == "") {
                $adminName = $this->_helperData->getStorename();
            }
            if (!isset($adminEmail) || $adminEmail == "") {
                $adminEmail = $this->_helperData->getStoreEmail();
            }
            $senderInfo = [
                'name' => $adminName,
                'email' => $adminEmail
            ];
            $emailTemplateVariablesForLeftAmt['myvar8'] = $this->_helperData->getBaseCurrencyCode();
            $this->_helperData->customMailSendMethodForLeftAmt(
                $emailTemplateVariablesForLeftAmt,
                $senderInfo,
                $receiverInfo
            );
            $coupon_model = $this->_magentoSalesRule->getCollection()->load();
            foreach ($coupon_model as $cpn) {
                if (trim($cpn->getName()) == trim($couponCode)) {
                    $cpn->delete();
                }
            }
        }
    }
}
