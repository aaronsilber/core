<?php
namespace Df\Payment\W;
use Df\Framework\Controller\Result\Text;
use Df\Payment\W\Exception\Ignored;
/**
 * 2016-08-27
 * @see \Df\GingerPaymentsBase\Controller\Confirm
 * @see \Dfe\AllPay\Controller\Confirm\Index
 * @see \Dfe\Iyzico\Controller\Index\Index
 * @see \Dfe\Omise\Controller\Index\Index
 * @see \Dfe\Paymill\Controller\Index\Index
 *
 * 2017-03-19
 * The class is not abstract anymore: you can use it as a base for a virtual type:
 * 1) Stripe: https://github.com/mage2pro/stripe/blob/1.9.1/etc/di.xml#L6
 */
class Action extends \Df\Payment\Action {
	/**
	 * 2016-08-27
	 * @final Unable to use the PHP «final» keyword here because of the M2 code generation.
	 * @override
	 * @see \Magento\Framework\App\Action\Action::execute()
	 * @return Text
	 */
	function execute() {
		/** @var string $m */
		$m = $this->m();
		/** @var Text $result */
		try {
			$result = F::s($m)->handler()->handle();
		}
		catch (Ignored $e) {
			$result = $this->ignored($e);
		}
		catch (\Exception $e) {
			df_log_l($e);
			df_sentry($m, $e);
			if ($e instanceof IEvent && $e->r()) {
				dfp_log_l($m, $e->r());
			}
			$result = $this->error($e);
		}
		/**
		 * 2017-01-07
		 * Иначе мы можем получить сложнодиагностируемый сбой «Invalid return type».
		 * @see \Magento\Framework\App\Http::launch()
		 * https://github.com/magento/magento2/blob/2.1.3/lib/internal/Magento/Framework/App/Http.php#L137-L145
		 */
		return df_ar(df_response_sign($result), Text::class);
	}

	/**
	 * 2017-01-02
	 * @used-by execute()
	 * @see \Dfe\AllPay\Controller\Confirm\Index::error()
	 * @param \Exception $e
	 * @return $this
	 */
	protected function error(\Exception $e) {return Handler::resultError($e);}

	/**
	 * 2017-01-17
	 * @used-by execute()
	 * @param Ignored $e
	 * @return Text
	 */
	private function ignored(Ignored $e) {
		/**
		 * 2017-02-01 
		 * Отныне игнорируемые операции логирую только на своих серверах.
		 * Аналогично поступаю и с @see \Df\Payment\Method::action():
		 * @see \Df\StripeClone\Method::needLogActions()
		 */
		if (df_my()) {
			/** @var string $m */
			dfp_sentry_tags($m = $e->m());
			/** @var array(string => mixed) $req */
			$req = $e->event()->r();
			/** @var Event $ev */
			$ev = $e->event();			
			/** @var string $label */
			$label = $ev->tl();
			df_sentry($this, "[{$e->mTitle()}] {$label}: ignored", [
				'extra' => ['Payment Data' => df_json_encode_pretty($req)]
			]);
			dfp_log_l($m, $req, $ev->t());
		}
		return Text::i($e->message());
	}
}