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
namespace Webkul\GiftCard\Plugin;

/**
 * Class CreditmemoService.
 */
class CreditmemoService
{
    public function afterRefund(\Magento\Sales\Model\Service\CreditmemoService $subject, $result)
    {
            $haveGiftCardItems = 0;
            $haveProductForRefund = 0;
        foreach ($result->getOrder()->getAllItems() as $items) {
            if ($haveGiftCardItems == 0) {
                if ($items->getProductType() == "giftcard") {
                    $haveGiftCardItems = 1;
                    continue;
                }
            }
            if (!($items->getQtyOrdered() == $items->getQtyRefunded())) {
                $haveProductForRefund = 1;
            }
        }
        if ($haveGiftCardItems==1 && $haveProductForRefund == 0) {
            $result->getOrder()->setStatus('closed');
            $result->getOrder()->setState('closed');
            $result->getOrder()->save();
        }
            return $result ;
    }
}
