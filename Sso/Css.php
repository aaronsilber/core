<?php
// 2016-12-04
namespace Df\Sso;
use Magento\Framework\View\Element\AbstractBlock as _P;
class Css extends _P {
	/**
	 * 2016-12-04
	 * @override
	 * @see _P::_toHtml()
	 * @return string
	 */
	final protected function _toHtml() {
		/** @var string $hhl */
		$hhl = df_style_inline_hide('.header.links', '#switcher-currency');
		return self::isAccConfirmation() ? $hhl . df_style_inline_hide('.login-container') :
			(self::isRegCompletion() ? $hhl . df_x_magento_init(__CLASS__, 'reg-completion') : '')
	;}

	/**
	 * 2016-12-04
	 * Кэшировать результат нужно обязательно, потому что в данном случае
	 * значение getDfNeedConfirm() уничтожается при извлечении из сессии.
	 * @return bool
	 */
	public static function isAccConfirmation() {return dfcf(function() {return
		df_is_login() && df_customer_session()->getDfNeedConfirm(true)
	;});}

	/**
	 * 2016-12-02
	 * Случай, когда покупатель авторизовался в провайдере SSO,
	 * но информации провайдера SSO недостаточно для автоматической регистрации
	 * покупателя в Magento.
	 * В этом случае метод @see \Df\Sso\CustomerReturn::execute()
	 * перенаправляет покупателя на страницу регистрации.
	 * В этом случае мы не показываем наши кнопки SSO,
	 * а также скрываем из шапки стандартные ссылки
	 * «Sign In», «Create an Account» и блок выбора валюты.
	 *
	 * 2016-12-04
	 * Кэшировать результат нужно обязательно, потому что в данном случае
	 * значение getDfNeedConfirm() уничтожается при извлечении из сессии.
	 * @return bool
	 */
	public static function isRegCompletion() {return dfcf(function() {return
		df_is_reg() && df_customer_session()->getDfSsoProvider()
	;});}
}