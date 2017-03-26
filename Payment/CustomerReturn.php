<?php
// 2016-08-27
namespace Df\Payment;
use Df\Sales\Model\Order as DFO;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order as O;
use Magento\Sales\Model\Order\Payment as OP;
use Magento\Sales\Model\Order\Payment\Transaction as T;
/**
 * 2017-01-07
 * 2017-03-19
 * @see \Dfe\AllPay\Controller\CustomerReturn\Index
 * The class is not abstract anymore: you can use it as a base for a virtual type.
 * 1) Ginger Payments: https://github.com/mage2pro/ginger-payments/blob/0.4.1/etc/di.xml#L7
 * 2) Kassa Compleet: https://github.com/mage2pro/kassa-compleet/blob/0.4.1/etc/di.xml#L7
 * 3) Omise: https://github.com/mage2pro/omise/blob/1.7.1/etc/di.xml#L6
 * 4) SecurePay: https://github.com/mage2pro/securepay/blob/1.4.1/etc/di.xml#L7
 */
class CustomerReturn extends Action {
	/**
	 * 2016-08-27
	 * @override
	 * @see \Magento\Framework\App\Action\Action::execute()
	 * @return Redirect
	 */
	final function execute() {
		if ($this->needLog()) {
			dfp_report($this->m(), $_REQUEST, 'customerReturn');
		}
		/** @var Session $ss */
		$ss = df_checkout_session();
		/** @var O|DFO|null $o */
		/** @var Redirect $result */
		if (($o = $ss->getLastRealOrder()) && !$o->isCanceled()) {
			$result = $this->_redirect('checkout/onepage/success');
		}
		else {
			/** @var O|DFO|null $o */
			if ($o && $o->canCancel()) {
				$o->cancel()->save();
			}
			$ss->restoreQuote();
			// 2016-07-14
			// Show an explanation message to the customer
			// when it returns to the store after an unsuccessful payment attempt.
			df_checkout_error(df_var($this->s()->messageFailure(), ['originalMessage' => $this->message()]));
			// 2016-05-06
			// «How to redirect a customer to the checkout payment step?» https://mage2.pro/t/1523
			$result = $this->_redirect('checkout', ['_fragment' => 'payment']);
		}
		return $result;
	}

	/**
	 * 2016-08-27
	 * @used-by execute()
	 * @see \Dfe\AllPay\Controller\CustomerReturn\Index::message()
	 * @return string
	 */
	protected function message() {return '';}
}