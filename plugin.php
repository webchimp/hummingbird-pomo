<?php
	/**
	 * PoMo plugin
	 * Provides internationalization services to Hummingbird.
	 * Version:		1.0
	 * Author(s):	webchimp <github.com/webchimp>
	 * ToDo:
	 */

	class Pomo {
		protected $locales;
		protected $locale;
		protected $locale_directory;

		/**
		 * Constructor
		 */
		function __construct() {
			global $site;
			# Initialize variables
			$this->locales = array();
			$this->locale = '';
			$this->locale_directory = $site->baseDir('/plugins/pomo/lang/');

			# Register router
			$site->addRoute('/:lang', 'Pomo::getPage', true);
			$site->addRoute('/:lang/*params', 'Pomo::getPage', true);
		}

		/**
		 * Handle a (possibly) localized route
		 * @param  mixed $params          Route or array
		 * @param  string $templates_dir  Templates dir
		 * @return boolean                TRUE if routing was successful, FALSE otherwise
		 */
		static function getPage($params, $templates_dir = '') {
			global $site;
			global $pomo;
			# We must check whether this is a localized route or not
			$lang = $params[1];
			if ( array_key_exists( $lang, $pomo->getLocales() ) ) {
				# A registered locale, strip the locale identifier and rebuild the route
				$page = '';
				for ($i = 2; $i < count($params); $i++) {
					$page .= sprintf('/%s', $params[$i]);
				}
				if ( empty($page) ) {
					$page = '/home';
				}
				# Override the current locale
				$pomo->setLocale($lang);
				$site->addBodyClass( sprintf('lang-%s', $lang) );
				# Call the base router again with the new route
				$site->matchRoute($page);
				return true;
			} else {
				# Otherwise just set the default locale slug
				$site->addBodyClass( sprintf('lang-%s', $pomo->getLocale() ) );
			}
			return false;
		}

		/**
		 * Register a new locale
		 * @param string $key         	Locale identifier (es, en, it, etc)
		 * @param string $translation 	The php locale code
		 */
		function addLocale($key, $translation) {
			$this->locales[$key] = $translation;
		}

		/**
		 * Set the current locale
		 * @param string $key 			Locale identifier
		 */
		function setLocale($key) {
			global $site;

			$this->locale = $key;
			$locale = strtolower($this->locales[$this->locale]);

			putenv("LANG=" . $locale);
			setlocale('LC_ALL', $locale);
			bindtextdomain("hummingbird", $this->locale_directory);
			textdomain("hummingbird");
		}

		/**
		 * Get the current locale
		 * @return string  				The current locale identifier
		 */
		function getLocale() {
			$ret = $this->locale;
			return $ret;
		}

		/**
		 * Get the list of registered locales
		 * @return array 				List of registered locales
		 */
		function getLocales() {
			return $this->locales;
		}

		/**
		 * Get a localized URL
		 * @param  string  $path   		URL path
		 * @param  boolean $echo   		Whether to print the result or not
		 * @param  string  $locale 		Locale identifier to override the current locale
		 * @return string          		The well-formed URL
		 */
		function urlTo($path, $echo = false, $locale = '') {
			global $site;
			if ( empty($locale) ) {
				$locale = $this->getLocale();
			}
			$ret = $site->baseUrl( sprintf('/%s%s', $locale, $path) );
			if ($echo) {
				echo $ret;
			}
			return $ret;
		}

		/**
		 * Get specified translation
		 * @param  string $key Translation key
		 * @return string      			The specified translation or the key if it wasn't found
		 */
		function translate($key, $echo = true) {
			$ret = $key;
			if (! empty($this->locale) && isset( $this->locales[$this->locale][$key] ) ) {
				$ret = $this->locales[$this->locale][$key];
			}
			if ($echo) {
				echo $ret;
			}
			return $ret;
		}
	}

	# Instantiate the plugin object
	$pomo = new pomo();
?>