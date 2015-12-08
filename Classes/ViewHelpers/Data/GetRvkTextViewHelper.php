<?php
namespace Slub\SlubFindExtend\ViewHelpers\Data;

	/***************************************************************
	 *
	 *  Copyright notice
	 *
	 *  This script is part of the TYPO3 project. The TYPO3 project is
	 *  free software; you can redistribute it and/or modify
	 *  it under the terms of the GNU General Public License as published by
	 *  the Free Software Foundation; either version 3 of the License, or
	 *  (at your option) any later version.
	 *
	 *  The GNU General Public License can be found at
	 *  http://www.gnu.org/copyleft/gpl.html.
	 *
	 *  This script is distributed in the hope that it will be useful,
	 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
	 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 *  GNU General Public License for more details.
	 *
	 *  This copyright notice MUST APPEAR in all copies of the script!
	 ***************************************************************/

/**
 * GetRvkTextViewHelper
 */
class GetRvkTextViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Replaces RVK with result from RVK API
	 *
	 * @param string $rvk RVK
	 * @return string
	 */
	public function render($rvk = NULL) {

		$rvk_string = '';

		if ($rvk === NULL) {
			$rvk = $this->renderChildren();
		}

		$rvk_obj = json_decode(file_get_contents('http://rvk.uni-regensburg.de/api/json/node/'.urlencode(trim($rvk))));

		if($rvk_obj->{'error-code'} > 0) { return $rvk; }
		else {

			if($rvk_obj->{'node'}->{'register'}) {

				foreach ($rvk_obj->{'node'}->{'register'} as $register) {

					if (strlen($rvk_string) > 0) {
						$rvk_string .= ' - ';
					}

					$rvk_string .= utf8_encode($register);
				}

				return trim($rvk) . ' : ' . $rvk_string . (strlen($rvk_string) > 0 ? ' - ' : '') . $rvk_obj->{'node'}->{'benennung'};
			} else {
				return $rvk;
			}
		}
	}

}