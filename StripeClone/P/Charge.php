<?php
namespace Df\StripeClone\P;
use Df\StripeClone\Method as M;
use Df\StripeClone\Payer;
use Df\StripeClone\Settings as S;
/**
 * 2016-12-28
 * @see \Dfe\Moip\P\Charge
 * @see \Dfe\Omise\P\Charge
 * @see \Dfe\Paymill\P\Charge
 * @see \Dfe\Spryng\P\Charge
 * @see \Dfe\Square\P\Charge
 * @see \Dfe\Stripe\P\Charge
 * @method M m()
 * @method S s()
 */
abstract class Charge extends \Df\Payment\Charge {
	/**
	 * 2017-02-11
	 * 2017-02-18 Ключ, значением которого является токен банковской карты.
	 * 2017-10-09 The key name of a bank card token or of a saved bank card ID.
	 * @used-by request()
	 * @used-by \Df\StripeClone\P\Reg::k_CardId()
	 * @see \Dfe\Moip\P\Charge::k_CardId()
	 * @see \Dfe\Omise\P\Charge::k_CardId()
	 * @see \Dfe\Paymill\P\Charge::k_CardId()
	 * @see \Dfe\Spryng\P\Charge::k_CardId()
	 * @see \Dfe\Square\P\Charge::k_CardId()
	 * @see \Dfe\Stripe\P\Charge::k_CardId()
	 * @return string
	 */
	abstract function k_CardId();

	/**
	 * 2017-02-18
	 * Dynamic statement descripor
	 * https://mage2.pro/tags/dynamic-statement-descriptor
	 * https://stripe.com/blog/dynamic-descriptors
	 * @used-by request()
	 * @see \Dfe\Moip\P\Charge::k_DSD()
	 * @see \Dfe\Omise\P\Charge::k_DSD()
	 * @see \Dfe\Paymill\P\Charge::k_DSD()
	 * @see \Dfe\Spryng\P\Charge::k_DSD()
	 * @see \Dfe\Square\P\Charge::k_DSD()
	 * @see \Dfe\Stripe\P\Charge::k_DSD()
	 * @return string|null
	 */
	abstract protected function k_DSD();

	/**
	 * 2017-10-09
	 * @used-by request()
	 * @see \Dfe\Square\P\Charge::amountAndCurrency()
	 * @return array(string => string|int)
	 */
	protected function amountAndCurrency() {return [
		self::K_AMOUNT => $this->amountF(), self::K_CURRENCY => $this->currencyC()
	];}

	/**
	 * 2017-06-12
	 * @used-by request()
	 * @see \Dfe\Moip\P\Charge::inverseCapture()
	 * @see \Dfe\Square\P\Charge::inverseCapture()
	 * @return bool
	 */
	protected function inverseCapture() {return false;}

	/**
	 * 2017-02-11
	 * @used-by request()
	 * @see \Dfe\Moip\P\Charge::p()
	 * @see \Dfe\Omise\P\Charge::p()
	 * @see \Dfe\Square\P\Charge::p()
	 * @see \Dfe\Stripe\P\Charge::p()
	 * @return array(string => mixed)
	 */
	protected function p() {return [];}

	/**
	 * 2017-02-18
	 * @used-by request()
	 * @see \Dfe\Moip\P\Charge::k_Capture()
	 * @see \Dfe\Spryng\P\Charge::k_Capture()
	 * @see \Dfe\Square\P\Charge::k_Capture()
	 * @return string
	 */
	protected function k_Capture() {return self::K_CAPTURE;}

	/**
	 * 2017-10-09
	 * @used-by request()
	 * @see \Dfe\Square\P\Charge::k_CustomerId()
	 * @return string
	 */
	protected function k_CustomerId() {return self::K_CUSTOMER_ID;}

	/**
	 * 2017-02-18
	 * @used-by request()
	 * @see \Dfe\Moip\P\Charge::k_Excluded()
	 * @see \Dfe\Spryng\P\Charge::k_Excluded()
	 * @return string[]
	 */
	protected function k_Excluded() {return [];}

	/**
	 * 2017-06-11
	 * @used-by request()
	 * @see \Dfe\Moip\P\Charge::v_CardId()
	 * @param string $id
	 * @param bool $isPrevious [optional]
	 * @return string|array(string => mixed)
	 */
	protected function v_CardId($id, $isPrevious = false) {return $id;}

	/**
	 * 2016-12-28
	 * 2016-03-07 Stripe: https://stripe.com/docs/api/php#create_charge
	 * 2016-11-13 Omise: https://www.omise.co/charges-api#charges-create
	 * 2017-02-11 Paymill https://developers.paymill.com/API/index#-transaction-object
	 * @used-by \Dfe\Stripe\Method::chargeNew()
	 * @param M $m
	 * @param bool $capture [optional]
	 * @return array(string => mixed)
	 */
	final static function request(M $m, $capture = true) {
		$i = self::sn($m); /** @var self $i */
		$payer = Payer::s($m); /** @var Payer $payer */
		$r = df_clean_keys([
			$i->k_CustomerId() => $payer->customerId()
			// 2016-03-08 Для Stripe текст может иметь произвольную длину: https://mage2.pro/t/903
			,self::K_DESCRIPTION => $i->description()
			,$i->k_Capture() => $i->inverseCapture() ? !$capture : $capture
			// 2017-02-18
			// «Dynamic statement descripor»
			// https://mage2.pro/tags/dynamic-statement-descriptor
			// https://stripe.com/blog/dynamic-descriptors
			// https://support.stripe.com/questions/does-stripe-support-dynamic-descriptors
			,$i->k_DSD() => $i->s()->dsd()
		] + $i->amountAndCurrency(), $i->k_Excluded());
		/**
		 * 2017-07-30
		 * I placed it here in a separate condition branch
		 * because some payment modules (Moip) implement non-card payment options.
		 * A similar code block is here: @see \Df\StripeClone\P\Reg::request()
		 */
		if ($k = $i->k_CardId() /** @var string $k|null */) {
			$r[$k] = $i->v_CardId($payer->cardId(), $payer->usePreviousCard());
		}
		return $r + $i->p();
	}

	/**
	 * 2017-06-11
	 * @used-by request()
	 * @used-by \Df\StripeClone\P\Reg::charge()
	 * @param M $m
	 * @return self
	 */
	final static function sn(M $m) {return dfcf(function(M $m) {return df_new(
		df_con_heir($m, __CLASS__), $m
	);}, [$m]);}

	/**
	 * 2017-02-11
	 * @used-by amountAndCurrency()
	 * @used-by \Dfe\Moip\P\Charge::k_Excluded()
	 * @used-by \Dfe\Paymill\Facade\Charge::create()
	 */
	const K_AMOUNT = 'amount';

	/**
	 * 2017-02-11
	 * @used-by k_Capture()
	 * @used-by \Dfe\Paymill\Facade\Charge::create()
	 */
	const K_CAPTURE = 'capture';

	/**
	 * 2017-02-11
	 * @used-by amountAndCurrency()
	 * @used-by \Dfe\Moip\P\Charge::k_Excluded()
	 * @used-by \Dfe\Paymill\Facade\Charge::create()
	 * @used-by \Dfe\Spryng\P\Charge::k_Excluded()
	 */
	const K_CURRENCY = 'currency';

	/**
	 * 2017-02-11
	 * @used-by k_CustomerId()
	 * @used-by \Dfe\Moip\P\Charge::k_Excluded()
	 * @used-by \Dfe\Paymill\Facade\Charge::create()
	 */
	const K_CUSTOMER_ID = 'customer';

	/**
	 * 2017-02-11
	 * @used-by request()
	 * @used-by \Dfe\Moip\P\Charge::k_Excluded()
	 * @used-by \Dfe\Paymill\Facade\Customer::create()
	 * @used-by \Dfe\Spryng\P\Charge::k_Excluded()
	 */
	const K_DESCRIPTION = 'description';
}