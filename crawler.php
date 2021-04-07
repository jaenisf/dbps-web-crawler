<?php
	// the web crawler class
	class Crawler {
		protected $markup = '';
		public $base = '';

		public function __construct($uri) {
			$this->base = $uri;
			$this->markup = $this->getMarkup($uri);
		}
		
		public function getMarkup($uri) {
			return file_get_contents($uri);
		}
		
		public function get($type) {
			$method = "_get_{$type}";
			
			if (method_exists($this, $method)) {
				return call_user_func(array($this, $method));
			}
		}
		
		protected function _get_images() {
			if (!empty($this->markup)) {
				preg_match_all('/<img([^>]+)\/>/i', $this->markup, $images);
				return !empty($images[1]) ? $images[1] : FALSE;
			}
		}
		
		protected function _get_links() {
			if (!empty($this->markup)){
				//preg_match_all('/<a([^>]+)\>(.*?)\<\/a\>/i', $this->markup, $links);
				preg_match_all('/href=\"(.*?)\"/i', $this->markup, $links);
				return !empty($links[1]) ? $links[1] : FALSE;
			}
		}
		
		protected function _get_titles() {
			if (!empty($this->markup)) {
				preg_match_all('/<title>([^<]*)<\/title>/im', $this->markup, $titles);
				return !empty($titles[1]) ? $titles[1] : FALSE;
			}
		}
		
		protected function _get_words() {
			if (!empty($this->markup)) {
				$words = text_to_words_array_with_count($this->markup);
				return !empty($words) ? $words : FALSE;
			}
		}
	}
?>