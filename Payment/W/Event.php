<?php
namespace Df\Payment\W;
use Df\Payment\IMA;
use Df\Payment\Method as M;
use Df\Payment\W\Exception\Critical;
/**
 * 2017-03-09
 * @see \Df\PaypalClone\W\Event
 * @see \Df\StripeClone\W\Event
 */
abstract class Event implements IEvent, IMA {
	/**
	 * 2017-01-16
	 * @used-by pid()
	 * @see \Df\StripeClone\W\Event::k_pid()
	 * @see \Df\GingerPaymentsBase\W\Event::k_pid()
	 * @see \Dfe\AllPay\W\Event::k_pid()
	 * @see \Dfe\Dragonpay\W\Event::k_pid
	 * @see \Dfe\IPay88\W\Event::k_pid()
	 * @see \Dfe\Robokassa\W\Event::k_pid()
	 * @see \Dfe\SecurePay\W\Event::k_pid()
	 * @return string
	 */
	abstract protected function k_pid();

	/**
	 * 2017-01-06
	 * 2017-03-18 The type of the current transaction.
	 * @used-by \Df\StripeClone\W\Nav::id()
	 * @used-by \Df\Payment\W\Strategy\ConfirmPending::_handle()
	 * @see \Df\GingerPaymentsBase\W\Event::ttCurrent()
	 * @see \Df\PaypalClone\W\Event::ttCurrent()
	 * @see \Dfe\Moip\W\Event::ttCurrent()
	 * @see \Dfe\Omise\W\Event\Charge\Capture::ttCurrent()
	 * @see \Dfe\Omise\W\Event\Charge\Complete::ttCurrent()
	 * @see \Dfe\Omise\W\Event\Refund::ttCurrent()
	 * @see \Dfe\Paymill\W\Event\Refund::ttCurrent()
	 * @see \Dfe\Paymill\W\Event\Transaction\Succeeded::ttCurrent()
	 * @see \Dfe\Stripe\W\Event\Charge\Captured::ttCurrent()
	 * @see \Dfe\Stripe\W\Event\Charge\Refunded::ttCurrent()
	 * @return string
	 */
	abstract function ttCurrent();

	/**
	 * 2017-03-10
	 * @used-by \Df\Payment\W\F::event()
	 * @param Reader $r
	 */
	final function __construct(Reader $r) {$this->_r = $r;}

	/**
	 * 2016-08-27
	 * Раньше метод isSuccessful() вызывался из метода @see validate().
	 * Отныне же @see validate() проверяет, корректно ли сообщение от платёжной системы.
	 * Даже если оплата завершилась отказом покупателя, но оповещение об этом корректно,
	 * то @see validate() вернёт true.
	 * isSuccessful() же проверяет, прошла ли оплата успешно.
	 * @used-by \Df\Payment\W\Strategy\ConfirmPending::_handle()
	 * @used-by \Df\PaypalClone\W\Event::ttCurrent()
	 * @see \Df\PaypalClone\W\Event::isSuccessful()
	 * @return bool
	 */
	function isSuccessful() {return true;}

	/**
	 * 2017-01-02
	 * @used-by \Df\Payment\W\Handler::log()
	 * @see \Df\PaypalClone\W\Event::logTitleSuffix()
	 * @return string|null
	 */
	function logTitleSuffix() {return null;}

	/**
	 * 2017-03-17
	 * @final I do not use the PHP «final» keyword here to allow refine the return type using PHPDoc.
	 * $m здесь НЕ СОДЕРЖИТ корректного II.
	 * Для вычисления корректного II нам ещё предстоит провести кучу операций:
	 * 1) Определить, к какой транзакции Magento относится данное событие.
	 * 2) Загрузить эту транзакцию из БД.
	 * 3) По транзакции получить II.
	 * Это всё нам ещё предстоит!
	 * @override
	 * @see \Df\Payment\IMA::m()
	 * @used-by \Df\Payment\W\Nav::mPartial()
	 * @used-by \Df\PaypalClone\Signer::_sign()
	 * @return M
	 */
	function m() {return $this->_r->m();}

	/**
	 * 2016-07-20
	 * @used-by ttCurrent()
	 * @used-by \Df\Payment\W\Strategy\ConfirmPending::_handle()
	 * @used-by \Dfe\AllPay\W\Event\Offline::statusExpected()
	 * @used-by \Dfe\AllPay\W\Nav\Offline::id()
	 * @see \Dfe\AllPay\W\Event\Offline::needChangePaymentState()
	 * @return bool
	 */
	function needChangePaymentState() {return true;}

	/**
	 * 2016-07-09
	 * 2017-01-04
	 * 2017-03-16
	 * Возвращает некую основу для вычисления идентификатора родительской транзакции в Magento.
	 * Эта основа в настоящее время бывает 2-х видов:
	 *
	 * 1) Идентификатор платежа в платёжной системе.
	 * Так происходит для Stripe-подобных модулей.
	 * На основе этого идентификатора мы:
	 *     1.1) вычисляем идентификатор родительской транзакции
	 *     (посредством прибавления окончания «-<тип родительской транзакции>»)
	 *     1.2) создаём идентификатор текущей транзакции
	 *     (аналогично, посредством прибавления окончания «-<тип текущей транзакции>»).
	 *
	 * 2) Переданный нами ранее платёжной системе наш внутренний идентификатор родительской транзакции
	 * (т.е., запроса к платёжой системе) в локальном (коротком) формате
	 * (т.е. без приставки «<имя платёжного модуля>-»).
	 *
	 * @used-by \Df\Payment\W\Nav::pid()
	 * @used-by \Df\PaypalClone\W\Event::idE()
	 * @used-by \Df\StripeClone\W\Event::idBase()
	 * @used-by \Dfe\Robokassa\W\Handler::result()
	 * @return string
	 */
	final function pid() {return $this->rr($this->k_pid());}

	/**
	 * 2017-03-10
	 * @override
	 * @see \Df\Payment\W\IEvent::r()
	 * @used-by \Df\Payment\W\Exception::r()
	 * @used-by \Df\Payment\W\Handler::r()
	 * @used-by \Df\PaypalClone\W\Event::validate()
	 * @used-by \Dfe\IPay88\W\Event::option()
	 * @used-by \Dfe\Robokassa\W\Event::optionTitle()
	 * @param string|string[]|null $k [optional]
	 * @param string|null $d [optional]
	 * @return array(string => mixed)|mixed|null
	 */
	final function r($k = null, $d = null) {return $this->_r->r($k, $d);}

	/**
	 * 2017-03-13
	 * @final I do not use the PHP «final» keyword here to allow refine the return type using PHPDoc.
	 * @return Reader
	 */
	function rd() {return $this->_r;}

	/**
	 * 2017-01-12
	 * @used-by \Df\PaypalClone\W\Event::idE()
	 * @used-by \Df\PaypalClone\W\Event::signatureProvided()
	 * @param string|string[]|null $k [optional]
	 * @param mixed|null $d [optional]
	 * @return array(string => mixed)|mixed
	 * @throws Critical
	 */
	final function rr($k = null, $d = null) {return $this->_r->rr($k, $d);}

	/**
	 * 2017-03-10
	 * 2017-03-13
	 * Returns a value in our internal format, not in the PSP format.
	 * @used-by tl()
	 * @used-by \Dfe\AllPay\Method::getInfoBlockType()
	 * @return string|null
	 */
	final function t() {return $this->_r->t();}

	/**
	 * 2017-03-10 Type label.
	 * @override
	 * @see \Df\Payment\W\IEvent::r()
	 * @used-by \Df\Payment\W\Action::ignored()
	 * @used-by \Df\Payment\W\Handler::log() 
	 * @used-by \Dfe\AllPay\Choice::title()
	 * @return string
	 */
	final function tl() {return dfc($this, function() {return $this->tl_(
		$this->useRawTypeForLabel() ? $this->_r->tRaw() : $this->t()
	);});}

	/**
	 * 2016-07-09
	 * 2016-07-14
	 * Раньше метод @see \Df\PaypalClone\W\Event::isSuccessful() вызывался из метода validate().
	 * Отныне же validate() проверяет, корректно ли сообщение от платёжной системы.
	 * Даже если оплата завершилась отказом покупателя, но оповещение об этом корректно,
	 * то validate() не возбудит исключительной ситуации.
	 * isSuccessful() же проверяет, прошла ли оплата успешно.
	 * @used-by \Df\Payment\W\Handler::handle()
	 * @see \Df\PaypalClone\W\Event::validate()
	 * @throws \Exception
	 */
	function validate() {}

	/**
	 * 2017-03-13
	 * @used-by tl()
	 * @see \Dfe\AllPay\W\Event::useRawTypeForLabel()
	 * @return bool
	 */
	protected function useRawTypeForLabel() {return false;}

	/**
	 * 2017-03-13
	 * @used-by tl()
	 * @used-by \Df\Payment\W\Event::tl_()
	 * @see \Dfe\AllPay\W\Event::tl_()
	 * @param string|null $t
	 * @return string
	 */
	protected function tl_($t) {return $this->_r->tl_($t);}

	/**
	 * 2017-03-10
	 * @used-by __construct()
	 * @used-by r()
	 * @used-by rd()
	 * @used-by t()
	 * @var Reader
	 */
	private $_r;

	/**
	 * 2017-01-12
	 * @used-by \Df\StripeClone\Method::chargeNew()
	 * @used-by \Dfe\Omise\W\Event\Charge\Complete::ttParent()
	 */
	const T_3DS = '3ds';
	/**
	 * 2017-01-12
	 * @used-by \Df\StripeClone\Method::chargeNew()
	 * @used-by \Df\Payment\W\Strategy\ConfirmPending::_handle()
	 * @used-by \Dfe\Omise\W\Event\Charge\Capture::ttParent()
	 * @used-by \Dfe\Paymill\W\Event\Transaction\Succeeded::ttParent()
	 * @used-by \Dfe\Stripe\W\Event\Charge\Captured::ttParent()
	 */
	const T_AUTHORIZE = 'authorize';
	/**
	 * 2017-01-12
	 * @used-by \Df\StripeClone\Method::charge()
	 * @used-by \Df\StripeClone\Method::chargeNew()
	 * @used-by \Df\Payment\W\Strategy\ConfirmPending::_handle()
	 * @used-by \Dfe\Omise\W\Event\Charge\Capture::ttCurrent()
	 * @used-by \Dfe\Omise\W\Event\Charge\Complete::ttCurrent()
	 * @used-by \Dfe\Omise\W\Event\Refund::ttParent()
	 * @used-by \Dfe\Paymill\W\Event\Refund::ttParent()
	 * @used-by \Dfe\Stripe\W\Event\Charge\Captured::ttCurrent()
	 * @used-by \Dfe\Stripe\W\Event\Charge\Refunded::ttParent()
	 */
	const T_CAPTURE = 'capture';

	/**
	 * 2017-07-30
	 * 2017-08-02 For now it is never used.
	 */
	const T_OFFLINE = 'offline';

	/**
	 * 2017-01-12
	 * @used-by \Df\StripeClone\Method::_refund()
	 * @used-by \Dfe\Omise\W\Event\Refund::ttCurrent()
	 * @used-by \Dfe\Paymill\W\Event\Refund::ttCurrent()
	 * @used-by \Dfe\Paymill\W\Event\Transaction\Succeeded::ttCurrent()
	 * @used-by \Dfe\Stripe\W\Event\Charge\Refunded::ttCurrent()
	 */
	const T_REFUND = 'refund';
}