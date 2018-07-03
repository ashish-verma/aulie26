<?php

namespace One97\Paytm\Controller\Standard;

class Response extends \One97\Paytm\Controller\Paytm
{

    public function execute()
    {	
		//$_POST=   ['ORDERID' => '000000006','MID' => 'Pearl209382576527767','TXNID' => '20180414111212800110168826700015448','TXNAMOUNT' => '10.00','PAYMENTMODE' => 'PPI','CURRENCY' => 'INR','TXNDATE' => '2018-04-14 08:48:40.0','STATUS' => 'TXN_SUCCESS','RESPCODE' => '01','RESPMSG' => 'Txn Success','GATEWAYNAME' => 'WALLET','BANKTXNID' => '','BANKNAME' => 'WALLET','CHECKSUMHASH' => '5BJqIKb3ILRBqpRX4HtwBWHsFd7g6Bt4dnIR/L8d0Usy8afYVm9kTJYJKj4/+xeNnuStlRdNebony2WD64WsLpZIiYZZvzkDzv/d2lnvGlE='];
		//$this->getRequest()->setParams($_POST);
		$comment = "";
        $request = $_POST;
		if(!empty($_POST)){
			foreach($_POST as $key => $val){
				if($key != "CHECKSUMHASH"){
					$comment .= $key  . "=" . $val . ", \n <br />";
				}
			}
		}

		$this->_logger->debug(var_export($this->getRequest()->getParams(), true)); 
	
		$errorMsg = '';
		$successFlag = false;
		$resMessage = $_POST['RESPMSG'];
        $orderId = $this->getRequest()->getParam('ORDERID');
        $order = $this->getOrderById($orderId);
        $orderTotal = round($order->getGrandTotal(), 2);
        $orderStatus = $this->getRequest()->getParam('STATUS');
		$resCode = $this->getRequest()->getParam('RESPCODE');
        $orderTxnAmount = $this->getRequest()->getParam('TXNAMOUNT');
        //print_r($request);
        if($this->getPaytmModel()->validateResponse($request, $orderId))
        {
			if($orderStatus == "TXN_SUCCESS" && $orderTotal == $orderTxnAmount){				
				// Create an array having all required parameters for status query.				
				$requestParamList = array("MID" => $_POST['MID'] , "ORDERID" => $orderId);
				$StatusCheckSum  =  $this->getPaytmModel()->generateStatusChecksum($requestParamList);
				$requestParamList['CHECKSUMHASH'] = $StatusCheckSum;
				
				// Call the PG's getTxnStatus() function for verifying the transaction status.
				$check_status_url = $this->getPaytmModel()->getNewStatusQueryUrl(); 				
				$responseParamList = $this->getPaytmHelper()->callNewAPI($check_status_url, $requestParamList);
				$this->_logger->debug(var_export($responseParamList, true)); 
				if($responseParamList['STATUS']=='TXN_SUCCESS' && $responseParamList['TXNAMOUNT']==$_POST['TXNAMOUNT'])
				{
					$successFlag = true;
					$comment .=  "Success ";
					$order->setStatus($order::STATE_PROCESSING);
					$order->setExtOrderId($orderId);
					$returnUrl = $this->getPaytmHelper()->getUrl('checkout/onepage/success');
				}
				else{
					$errorMsg = 'It seems some issue in server to server communication. Kindly connect with administrator.';
					$comment .=  "Fraud Detucted";
					$order->setStatus($order::STATUS_FRAUD);
					$returnUrl = $this->getPaytmHelper()->getUrl('checkout/onepage/failure');
				}
			}else{
				if($resCode == "141" || $resCode == "8102" || $resCode == "8103" || $resCode == "14112"){
					$errorMsg = 'Paytm Transaction Failed ! Transaction was cancelled.';
					$comment .=  "Payment cancelled by user";
					$order->setStatus($order::STATE_CANCELED);
					$this->_cancelPayment("Payment cancelled by user");
					//$order->save();
					$returnUrl = $this->getPaytmHelper()->getUrl('checkout/cart');
				}else{
					$errorMsg = 'Paytm Transaction Failed ! '.$resMessage;
					$comment .=  "Failed";
					
					$order->setStatus($order::STATE_PAYMENT_REVIEW);
					$returnUrl = $this->getPaytmHelper()->getUrl('checkout/onepage/failure');
				}
			}            
        }
        else
        {
			$errorMsg = 'Paytm Transaction Failed ! Fraud has been detected';
			$comment .=  "Fraud Detucted";
            $order->setStatus($order::STATUS_FRAUD);
            $returnUrl = $this->getPaytmHelper()->getUrl('checkout/onepage/failure');
        }
		$this->addOrderHistory($order,$comment);
        $order->save();
		if($successFlag){
			$this->messageManager->addSuccess( __('Paytm transaction has been successful.') );
		}else{
			$this->messageManager->addError( __($errorMsg) );
		}
        $this->getResponse()->setRedirect($returnUrl);
    }

}
