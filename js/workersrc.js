/**
 * Checks if the page is displayed in an iframe. If not redirect to /.
 **/
function redirectIfNotDisplayedInFrame () {
	try {
		if (window.frameElement) {
			return;
		}
	} catch (e) {}

	window.location.href = '/';
}
redirectIfNotDisplayedInFrame();
try {
	PDFJS.locale = parent.OC.getLocale();
} catch (e) {}
function deferredViewerConfig() {
	PDFJS.externalLinkTarget = PDFJS.LinkTarget.BLANK;
	PDFJS.isEvalSupported = false;
	PDFJS.workerSrc = document.getElementsByTagName('head')[0].getAttribute('data-workersrc');
}

// Wait until viewer is ready and patch it on the fly
if (document.readyState === 'interactive' || document.readyState === 'complete') {
	deferredViewerConfig();
} else {
	document.addEventListener('DOMContentLoaded', deferredViewerConfig, true);
}
