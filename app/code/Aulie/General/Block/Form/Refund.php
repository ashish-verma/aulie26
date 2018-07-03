<?php
namespace Aulie\General\Block\Form;
class Refund extends \Magento\Framework\View\Element\Template
{	
	protected $_template = 'form/return.phtml';

	public function __construct(\Magento\Framework\View\Element\Template\Context $context)
	{
		//var_dump( $this->_template );die;
		parent::__construct($context);
	}

	public function sayHello()
	{
		return __('Product Returns');
	}
}