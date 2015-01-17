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
	public function showPdfViewer($file) {
		$datas = parse_url($file);
		$results = explode('&', ($datas['query']));

		$datas = array();
		foreach ($results as $key => $value) {
			list($k, $v) = explode('=', $value);
			$datas[$k] = $v;
		}

		$params = array(
			'urlGenerator' => $this->urlGenerator,
			'filename' => $datas['files'],
		);

		return new TemplateResponse($this->appName, 'viewer', $params, 'blank');
	}

}
