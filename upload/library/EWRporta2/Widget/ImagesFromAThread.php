<?php
/**
 * Created by Esteban @ www.attitude.cl
 * esteban@attitude.cl
 *
 * Licensed under GPLv2
 * http://www.gnu.org/licenses/gpl-2.0.html
 */

class EWRporta2_Widget_ImagesFromAThread extends XenForo_Model
{
	public function getCachedData($widget, &$options)
	{
		$sti_images          = array();
		$sti_tid             = $options['imagesfromathread_tid'];
		$sti_scanLimit       = $options['imagesfromathread_scanlimit'];
		$sti_imagesLimit     = $options['imagesfromathread_imageslimit'];
		$sti_excludedDomains = $options['imagesfromathread_excludeddomains'];
		$sti_excludeGif      = $options['imagesfromathread_excludegif'];
		$twoDaysAgo          = time()-(60*60*24);
		$xenForoModelPost    = XenForo_Model::create('XenForo_Model_Post');

		$posts = $xenForoModelPost->getNewestPostsInThreadAfterDate($sti_tid, $twoDaysAgo, array('limit' => $sti_scanLimit));

		foreach ($posts as $p) {
			//Quotes
			if (stripos($p['message'], '[quote') !== false) {
				$cleanedPost = preg_replace("/\[quote(.+?)\[\/quote\]/is", '', $p['message']);
			} else {
				$cleanedPost = $p['message'];
			}

			//Search images
			preg_match_all('/\[img\](.*?)\[\/img\]/i', $cleanedPost, $matches, PREG_SET_ORDER);

			foreach ($matches as $i) {
				//Gif?
				if($sti_excludeGif == '1') {
					if (strlen(stristr($i[1],'.gif')) > 0) {
						continue;
					}
				}

				//Excluded domains
				if(!empty($sti_excludedDomains)) {
					$nopedomains = explode(PHP_EOL, trim($sti_excludedDomains));

					if($this->stripos_array($i[1], $nopedomains) !== NULL) {
						continue;
					}
				}

				$sti_images[] = $i[1];
			}

			//Max Limit reached?
			if(!empty($sti_imagesLimit) AND $sti_imagesLimit >= 1) {
				if(count($sti_images) >= $sti_imagesLimit) {
					break;
				}
			}
		}

		$images = array_unique($sti_images);

		return $images;
	}

	public function getUncachedData($widget, $options)
	{
		$wUncached = $this->getCachedData($widget, $options);

		return $wUncached;
	}

	/**
	 * http://php.net/manual/es/function.strpos.php#102773
	 */
	public function stripos_array($haystack, $needles)
	{
		if ( is_array($needles) ) {
			foreach ($needles as $str) {
				if ( is_array($str) ) {
					$pos = $this->stripos_array($haystack, $str);
				} else {
					$pos = stripos($haystack, $str);
				}
				if ($pos !== FALSE) {
					return $pos;
				}
			}
		} else {
			return stripos($haystack, $needles);
		}
	}
}
