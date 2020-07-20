/**
 * Checks if the page is displayed in an iframe. If not redirect to /.
 **/
function redirectIfNotDisplayedInFrame () {
	try {
		if (window.frameElement || location.href.indexOf('?file=blob') !== -1) {
			return;
		}
	} catch (e) {}

	window.location.href = '/';
}
redirectIfNotDisplayedInFrame();

function deferredViewerConfig() {
	if (location.href.indexOf('?file=blob') !== -1) {
		document.getElementById('secondaryToolbarClose').addEventListener(
			'click',
			function() {
				window.close();
			}
		);
	}
	try {
		PDFViewerApplicationOptions.set('workerSrc', document.getElementsByTagName('head')[0].getAttribute('data-workersrc'));
		PDFViewerApplicationOptions.set('locale', parent.OC.getLocale());
		PDFViewerApplicationOptions.set('cMapUrl', document.getElementsByTagName('head')[0].getAttribute('data-cmapurl'));
	} catch (e) {}
	pdfjsLib.externalLinkTarget = pdfjsLib.LinkTarget.BLANK;
	pdfjsLib.isEvalSupported = false;
}

// Wait until viewer is ready and patch it on the fly
document.addEventListener('webviewerloaded', deferredViewerConfig, true);
