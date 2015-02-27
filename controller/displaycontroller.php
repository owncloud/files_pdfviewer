<?php
/**
 * @author Lukas Reschke
 * @copyright 2014 Lukas Reschke lukas@owncloud.com
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Files_PdfViewer\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\IURLGenerator;

class DisplayController extends Controller {

	/** @var IURLGenerator */
	private $urlGenerator;

	/**
	 * @param string $AppName
	 * @param IRequest $request
	 * @param IURLGenerator $urlGenerator
	 */
	public function __construct($AppName,
								IRequest $request,
								IURLGenerator $urlGenerator) {
		parent::__construct($AppName, $request);
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 *
	 * @param string $file
	 * @return TemplateResponse
	 */
	public function showPdfViewer($file='') {
		$params = array();
		$this->extractFilename($file, $params);

		$params['urlGenerator'] = $this->urlGenerator;

		return new TemplateResponse($this->appName, 'viewer', $params, 'blank');
	}

	/**
	 * Extract filename from url
	 *
	 * @param string $file
	 * @param array $params
	 */
	public function extractFilename($file, &$params) {
		if (empty($file) or !is_array($params)) {
			return;
		}

		$urlParts = parse_url($file);
		if (isset($urlParts['query']) && strpos($urlParts['query'], '&') !== false) {
			$query = explode('&', ($urlParts['query']));
			foreach ($query as $value) {
				if (strpos($value, '=') !== false) {
					list($k, $v) = explode('=', $value);
					if ($k == 'files') {
						$params['filename'] = $v;
						return;
					}
				}
			}
		}

	}

}
