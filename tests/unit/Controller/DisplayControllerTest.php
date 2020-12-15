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

use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IURLGenerator;
use Test\TestCase;

class DisplayControllerTest extends TestCase {
	/** @var string */
	private $appName;
	/** @var IRequest */
	private $request;
	/** @var IURLGenerator */
	private $urlGenerator;
	/** @var DisplayController */
	private $controller;

	public function setUp(): void {
		$this->appName = 'files_pdfviewer';

		$this->request = $this->getMockBuilder(
			'\OCP\IRequest')
			->disableOriginalConstructor()
			->getMock();
		$this->urlGenerator = $this->getMockBuilder(
			'\OCP\IUrlGenerator')
			->disableOriginalConstructor()
			->getMock();

		$this->controller = $this->getMockBuilder('\OCA\Files_PdfViewer\Controller\DisplayController')->setConstructorArgs(
			[
				$this->appName,
				$this->request,
				$this->urlGenerator
			]
		)->onlyMethods(['getStorage'])->getMock();

		parent::setUp();
	}

	public function testShowPdfViewer() {
		$params = [
			'urlGenerator' => $this->urlGenerator
		];
		$expectedResponse = new TemplateResponse($this->appName, 'viewer', $params, 'blank');
		$policy = new ContentSecurityPolicy();
		$policy->addAllowedChildSrcDomain('\'self\'');
		$policy->addAllowedFontDomain('data:');
		$policy->addAllowedImageDomain('*');
		$policy->addAllowedConnectDomain('blob:');
		$policy->allowEvalScript(false);
		$expectedResponse->setContentSecurityPolicy($policy);

		$this->assertEquals($expectedResponse, $this->controller->showPdfViewer());
	}

	public function testCanDownload() {
		$this->request->expects($this->once())->method('getParam')->willReturn('test.txt');
		$storage = $this->createMock('\OCP\Files\Storage');
		$this->controller->expects($this->once())->method('getStorage')->willReturn($storage);

		$expectedResponse = new JSONResponse(['canDownload' => true]);
		$this->assertEquals($expectedResponse, $this->controller->canDownload());
	}
}
