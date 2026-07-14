function isBlobDownloadPopup(location) {
	var query = location.search.replace(/^\?/, '');
	var params = query.split('&');
	for (var i = 0; i < params.length; i++) {
		var pair = params[i].split('=');
		if (pair[0] === 'file') {
			var value = decodeURIComponent(pair.slice(1).join('='));
			return value.indexOf('blob:') === 0;
		}
	}
	return false;
}
const cases = [
  // legit attachment popup: window.open("?file=" + encodeURIComponent("blob:...#name"))
  [{search: "?file=" + encodeURIComponent("blob:https://host/uuid#a.pdf")}, true, "legit blob popup"],
  // attacker trick: file=blob only in the hash to fool the old substring guard
  [{search: "?file=/s/TOKEN/download", hash: "#?file=blob"}, false, "attacker hash #?file=blob"],
  // normal iframe render: query has a download URL (also framed, but test the parser)
  [{search: "?file=" + encodeURIComponent("/s/TOKEN/download")}, false, "normal download url"],
  // no params
  [{search: ""}, false, "empty"],
];
let ok = true;
for (const [loc, expected, name] of cases) {
  const got = isBlobDownloadPopup({search: loc.search || "", hash: loc.hash || ""});
  const pass = got === expected;
  ok = ok && pass;
  console.log((pass?"PASS":"FAIL"), name, "->", got);
}
process.exit(ok?0:1);
