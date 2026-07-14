# Security regression fixtures

## CVE-2024-4367 — FontMatrix code injection (OC10-111)

`cve-2024-4367-poc.pdf` embeds a `/FontMatrix` whose sixth element is a
JavaScript string instead of a number. On a vulnerable build (pdf.js with the
eval-based glyph compiler and `isEvalSupported` effectively `true`) opening this
document executes the payload in the viewer origin. The bundled payload is
benign: it only sets `globalThis.__cve_2024_4367__ = 'reached'` and the document
title, so it can be used to confirm exploitation without side effects.

### Automated check

Run all of these with `make test-security`; they also run on every PR via the
`Security regression checks` CI job. Each script exits non-zero on failure.

`verify-cve-2024-4367.mjs` reproduces the two backported guards in isolation and
asserts that:

* a `/FontMatrix` that is not exactly six numbers is replaced by the identity
  matrix (evaluator guard), and
* the glyph-command sink coerces every non-number argument to `0`, so no
  attacker string can reach `new Function(...)`, while the intentional `scale`
  identifiers are preserved.

> **Drift caveat:** guards A and B in this script are *reproductions* of the
> logic, not the shipped code — the real code lives in a minified vendor bundle
> that cannot be imported. To catch the bundle regressing away from these
> reproductions (e.g. on a future pdf.js upgrade that drops the backport), the
> script's "source tripwire" section (checks `S1`–`S4`) reads the actual
> `pdf.js` / `pdf.worker.js` and fails if the guards are no longer present. The
> manual end-to-end check below remains the authoritative verification.

`test-blob-popup-detection.mjs` covers the `workersrc.js` frame-escape guard: it
asserts that the genuine `?file=blob:...` attachment popup is allowed while an
attacker-controlled `#?file=blob` hash fragment (which defeated the old
`indexOf('?file=blob')` substring check) is rejected. Every line must print
`PASS`.

### Manual end-to-end check

1. Serve the app and open `cve-2024-4367-poc.pdf` in the pdf viewer.
2. In the browser console, evaluate `window.__cve_2024_4367__`.
   * Vulnerable: `'reached'` (the payload ran).
   * Fixed: `undefined` (the payload never executed).

The fix is defended at three independent layers: the FontMatrix type check and
the sink coercion in the bundled pdf.js, the fail-safe `isEvalSupported=false`
ordering in `js/workersrc.js`, and the removal of `allowEvalScript(true)` from
the Content-Security-Policy in `DisplayController`.
