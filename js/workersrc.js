/**
 * Returns true if the viewer was opened as the standalone attachment popup,
 * i.e. window.open('?file=blob:...') from the pdf.js viewer. The genuine
 * indicator is the "file" *query* parameter holding a blob: URL. We parse the
 * actual query parameter instead of doing a loose substring match on the whole
 * URL (the old `indexOf('?file=blob')` check), because that match could be
 * satisfied from an unrelated part of the URL - notably the hash fragment,
 * which an attacker fully controls (e.g. "...#?file=blob") to force the viewer
 * out of its iframe.
 **/
function isBlobDownloadPopup () {
	var query = location.search.replace(/^\?/, '');
	var params = query.split('&');
	for (var i = 0; i < params.length; i++) {
		var pair = params[i].split('=');
		if (pair[0] === 'file') {
			var value;
			try {
				value = decodeURIComponent(pair.slice(1).join('='));
			} catch (e) {
				// A malformed %-sequence is never a genuine blob: popup URL.
				return false;
			}
			return value.indexOf('blob:') === 0;
		}
	}
	return false;
}

/**
 * Checks if the page is displayed in an iframe or as the blob download popup.
 * If neither, redirect to /.
 **/
function redirectIfNotDisplayedInFrame () {
	try {
		if (window.frameElement || isBlobDownloadPopup()) {
			return;
		}
	} catch (e) {}

	window.location.href = '/';
}
redirectIfNotDisplayedInFrame();

function deferredViewerConfig() {
	// Security hardening (CVE-2024-4367): disable eval-based rendering and
	// scripting FIRST and unconditionally, before any statement that might
	// throw. Previously a throwing locale lookup could abort this function
	// before isEvalSupported was set, silently leaving eval enabled and the
	// FontMatrix code-injection sink reachable.
	PDFViewerApplicationOptions.set('isEvalSupported', false);
	PDFViewerApplicationOptions.set('disablePreferences', true);

	var enableScripting = false;
	try {
		var head = document.getElementsByTagName('head')[0];
		enableScripting = head.getAttribute('data-enableScripting') === 'true';
	} catch (e) {
		// best-effort; keep the safe default (scripting disabled)
	}
	PDFViewerApplicationOptions.set('enableScripting', enableScripting);

	if (isBlobDownloadPopup()) {
		var closeButton = document.getElementById('secondaryToolbarClose');
		if (closeButton) {
			closeButton.addEventListener('click', function() {
				window.close();
			});
		}
	}

	try {
		var headEl = document.getElementsByTagName('head')[0];
		PDFViewerApplicationOptions.set('workerSrc', headEl.getAttribute('data-workersrc'));
		PDFViewerApplicationOptions.set('cMapUrl', headEl.getAttribute('data-cmapurl'));
		PDFViewerApplicationOptions.set('sandboxBundleSrc', headEl.getAttribute('data-sandbox'));
		PDFViewerApplicationOptions.set('printResolution', 300);
		PDFViewerApplicationOptions.set('externalLinkTarget', pdfjsLib.LinkTarget.BLANK);
	} catch (e) {
		// best-effort; the security-relevant options above are already set
	}

	// The locale lookup dereferences parent.OC and may throw when the viewer
	// runs top-level (e.g. the blob popup); isolate it so it can never abort
	// the security-relevant option above.
	try {
		PDFViewerApplicationOptions.set('locale', getSanitizedCurrentLocale());
	} catch (e) {
		// best-effort; locale is cosmetic and must not abort config
	}
}

function getSanitizedCurrentLocale(){
	return parent.OC.getLocale().replace('_', '-');
}

// Wait until viewer is ready and patch it on the fly
parent.document.addEventListener('webviewerloaded', deferredViewerConfig, true);
try {
	parent.document.documentElement.lang = getSanitizedCurrentLocale();
} catch (e) {
	// best-effort; lang attribute is cosmetic
}
