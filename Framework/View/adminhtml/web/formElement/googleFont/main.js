define(['jquery', 'Df_Core/Select2', 'domReady!'], function($) {return (
	/**
	 * 2015-11-28
	 * @param {Object} config
	 * @param {String} config.id
	 * @param {String} config.dataSource
	 */
	function(config) {
		/** @type {jQuery} HTMLSelectElement */
		var $element = $(document.getElementById(config.id));
		// 2015-11-28
		// https://select2.github.io/examples.html#responsive
		$element.css('width', '100%');
		/**
		 * 2015-11-28
		 * Чтобы можно было делать такие асинхронные запросы к другому домену,
		 * я добавил в настройках Nginx:
		 * add_header 'Access-Control-Allow-Origin' '*';
		 * http://enable-cors.org/server_nginx.html
		 * https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS
		 */
		$.getJSON(config.dataSource, function(data) {$element.select2({
			data: $.map(data,
				/**
				 * 2015-11-28
				 * @param {Object} item
				 * @param {String} item.family
				 * @param {String[]} item.variants
				 * @returns {Object}
				 */
				function(item) {return {
					id: item.family
					,text: item.family
					// 2015-11-28
					// http://stackoverflow.com/a/17621060
					,children: $.map(item.variants,
					   /**
					 	* 2015-11-28
						* @param {String} variant
					 	* @returns {Object}
					 	*/
						function(variant) {return {
							// https://developers.google.com/fonts/docs/getting_started#Syntax
							// http://fonts.googleapis.com/css?family=Tangerine:bold
							id: [item.family, variant].join(':')
						   ,text: variant
						}}
					)
				};}
			),
			/**
			 * 2015-11-28
			 * http://stackoverflow.com/a/19701390
			 * @name Select2Item
			 * @property {?Select2Item[]} children
			 * https://github.com/select2/select2/blob/4.0.1/dist/js/select2.full.js#L4734-L4750
			 * @property {Boolean} disabled
			 * @property {HTMLOptionElement} element
			 * @property {String} id		Например: "ABeeZee:regular"
			 * @property {Boolean} selected
			 * @property {String} text	Например: "regular"
			 */
			/**
			 * 2015-11-28
			 * https://select2.github.io/announcements-4.0.html#new-matcher
			 *
			 * 1) https://github.com/select2/select2/blob/4.0.1/dist/js/select2.full.js#L3272-L3276
					SelectAdapter.prototype.matches = function (params, data) {
						var matcher = this.options.get('matcher');
						return matcher(params, data);
					};
			 * 2) https://github.com/select2/select2/blob/4.0.1/dist/js/select2.full.js#L4728-L4771
			 *
			 * @param {Object} params
			 * @param {?String} params.term
			 * https://github.com/select2/select2/blob/4.0.1/dist/js/select2.full.js#L4762
			 * @param {Select2Item} item
			 * @param {?Select2Item} parent [optional]
			 * @returns {?Select2Item}
			 * https://github.com/select2/select2/blob/4.0.1/dist/js/select2.full.js#L4770
			 */
			matcher: function matcher(params, item, parent) {
				//return $.fn.select2.defaults.defaults.matcher(params, item);
				// Always return the object if there is nothing to compare
				/** @type {?Select2Item} */
				var result;
				if ('' === $.trim(params.term)) {
					result = item;
				}
				else if (!item.children || !item.children.length) {
					var dfContains = function(haystack, needle) {
						return -1 < haystack.toUpperCase().indexOf(needle.toUpperCase());
					};
					result =
						dfContains(item.text, params.term)
						|| (parent && dfContains(parent.text, params.term))
						? item
						: null
					;
				}
				// Do a recursive check for options with children
				else {
					// Clone the data object if there are children
					// This is required as we modify the object to remove any non-matches
					var match = $.extend(true, {}, item);
					// Check each child of the option
					for (var c = item.children.length - 1; 0 <= c; c--) {
						var child = item.children[c];
						var matches = matcher(params, child, item);
						// If there wasn't a match, remove the object in the array
						if (!matches) {
							match.children.splice(c, 1);
						}
					}
					result = match.children.length ? match : matcher(params, match, parent);
				}
				return result;
			},
			/**
			 * 2015-11-28
			 * http://stackoverflow.com/a/33971933
			 * @param {Select2Item} item
			 * @returns {String}
			 */
			templateSelection: function(item) {
				/** @type {jQuery} HTMLOptionElement */
				var $option = $(item.element);
				var $optGroup = $option.parent();
				return $optGroup.attr('label') + ' (' + item.text + ')';
			}
		});});
	}
);});