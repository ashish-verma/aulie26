<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_GiftCard
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\GiftCard\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class ExpiryDate extends Column
{
    /**
     * @var \Webkul\GiftCard\Helper\Data
     */
    protected $_helper;

    /**
     * Constructor.
     *
     * @param ContextInterface             $context
     * @param UiComponentFactory           $uiComponentFactory
     * @param array                        $components
     * @param array                        $data
     * @param \Webkul\GiftCard\Helper\Data $helperData
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = [],
        \Webkul\GiftCard\Helper\Data $helperData
    ) {
        $this->_helper = $helperData;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                    $item['expiry'] = $this->_helper->createExpirationDateOfGiftCard($item['duration'], $item['alloted']);
            }
        }
        return $dataSource;
    }
}
