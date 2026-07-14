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

use OC\Files\Filesystem;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\Share\IManager;

class DisplayController extends Controller {
	/** @var IURLGenerator */
	private $urlGenerator;

	/** @var IManager */
	private $shareManager;

	/**
	 * @param string $AppName
	 * @param IRequest $request
	 * @param IURLGenerator $urlGenerator
	 * @param IManager $shareManager
	 */
	public function __construct(
		$AppName,
		IRequest $request,
		IURLGenerator $urlGenerator,
		IManager $shareManager
	) {
		parent::__construct($AppName, $request);
		$this->urlGenerator = $urlGenerator;
		$this->shareManager = $shareManager;
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 *
	 * @return TemplateResponse
	 */
	public function showPdfViewer() {
		$params = [
			'urlGenerator' => $this->urlGenerator,
		];

		$response = new TemplateResponse($this->appName, 'viewer', $params, 'blank');

		$policy = new ContentSecurityPolicy();
		$policy->addAllowedChildSrcDomain('\'self\'');
		$policy->addAllowedFontDomain('data:');
		$policy->addAllowedImageDomain('*');
		$policy->addAllowedConnectDomain('blob: data:');
		// CVE-2024-4367: the patched pdf.js no longer needs eval for rendering.
		// ContentSecurityPolicy allows 'unsafe-eval' by default, so we must
		// explicitly disable it (not merely omit allowEvalScript(true)) to make
		// the new Function() font-rendering sink unreachable in the browser.
		$policy->allowEvalScript(false);
		$response->setContentSecurityPolicy($policy);

		return $response;
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 * @return JSONResponse
	 */

	public function canDownload() {
		$canDownload = true;

		$storage = $this->getStorage($this->request->getParams());

		if (!$storage->instanceOfStorage('OCA\Files_Sharing\SharedStorage')) {
			return new JSONResponse(['canDownload' => $canDownload]);
		}

		/** @var \OCA\Files_Sharing\SharedStorage $storage */
		'@phan-var \OCA\Files_Sharing\SharedStorage $storage';  /* @phpstan-ignore-line */
		$share = $storage->getShare();
		$downloadPermission = $share->getAttributes()->getAttribute('permissions', 'download');

		if ($downloadPermission !== null && !$downloadPermission) {
			$canDownload = false;
		}

		return new JSONResponse(['canDownload' => $canDownload]);
	}

	protected function getStorage($params) {
		if (isset($params['sharingToken'])) {
			$share = $this->shareManager->getShareByToken($params['sharingToken']);
			return $share->getNode()->getStorage();
		}

		$fileInfo =  Filesystem::getFileInfo($params['path']);
		return $fileInfo->getStorage();
	}
}
