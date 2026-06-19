/**
 * ifuri-ecobar — the shared ecosystem top bar.
 *
 * One source of truth for the standard ifURI header so a visitor can move
 * between domains (ifuri.com, connect.ifuri.com, get.ifuri.com, docs) from any
 * page. Self-contained: injects its own scoped styles and markup, marks the
 * active item by hostname, and is language-aware via the ?lang= query param.
 *
 * Canonical file: ifuri-com/httpdocs/assets/ifuri-ecobar.js
 * Vendored copies on the other sites are kept in sync at deploy time.
 *
 * Include once, near the end of <body>:
 *   <script src="/assets/ifuri-ecobar.js" defer></script>
 */
(function () {
  "use strict";
  if (document.querySelector(".ifuri-ecobar")) return;

  var params = new URLSearchParams(location.search);
  var lang = params.get("lang") === "en" ? "en" : "pl";
  var IFURI = "https://ifuri.com";
  var CONNECT = "https://connect.ifuri.com";
  var GET = "https://get.ifuri.com";
  var DOCS = "https://ifuri.com/urirun/";

  function ifuriView(view) {
    return IFURI + "/?view=" + view + "&lang=" + lang;
  }

  // [key, pl, en, href, matches(host,view)]
  var items = [
    ["product", "Produkt", "Product", ifuriView("product"), function (h, v) { return h === "ifuri.com" && v === "product"; }],
    ["runtime", "Runtime", "Runtime", ifuriView("runtime"), function (h, v) { return h === "ifuri.com" && v === "runtime"; }],
    ["docs", "Dokumentacja", "Docs", DOCS, function (h) { return /docs|urirun/.test(location.pathname) && h === "ifuri.com"; }],
    ["connect", "Connectory", "Connectors", CONNECT, function (h) { return h === "connect.ifuri.com"; }],
    ["flows", "Flows", "Flows", ifuriView("flows"), function (h, v) { return h === "ifuri.com" && v === "flows"; }],
    ["network", "Sieć", "Network", ifuriView("network"), function (h, v) { return h === "ifuri.com" && v === "network"; }],
    ["download", "Pobieranie", "Download", GET, function (h) { return h === "get.ifuri.com"; }]
  ];

  var host = location.hostname.replace(/^www\./, "");
  var view = params.get("view") || "";

  var css =
    ".ifuri-ecobar{position:sticky;top:0;z-index:9999;display:flex;align-items:center;gap:18px;" +
    "padding:0 18px;min-height:48px;background:#1E1B4B;color:#fff;font:14px/1.4 system-ui,-apple-system,'Segoe UI',sans-serif;" +
    "border-bottom:1px solid #312E81;flex-wrap:wrap}" +
    ".ifuri-ecobar a{color:#C7D2FE;text-decoration:none}" +
    ".ifuri-ecobar a:hover{color:#fff}" +
    ".ifuri-ecobar .ifuri-ecobar-brand{display:flex;align-items:center;gap:8px;font-weight:800;color:#fff;letter-spacing:-.01em}" +
    ".ifuri-ecobar .ifuri-ecobar-brand b{background:linear-gradient(90deg,#818CF8,#34D399);-webkit-background-clip:text;background-clip:text;color:transparent}" +
    ".ifuri-ecobar nav{display:flex;gap:14px;flex-wrap:wrap;flex:1}" +
    ".ifuri-ecobar nav a.is-active{color:#fff;font-weight:700;border-bottom:2px solid #34D399;padding-bottom:2px}" +
    ".ifuri-ecobar select{background:#312E81;color:#fff;border:1px solid #4338CA;border-radius:6px;padding:3px 6px;font:inherit}" +
    "@media(max-width:640px){.ifuri-ecobar{gap:10px}.ifuri-ecobar nav{gap:10px;font-size:13px}}";

  var style = document.createElement("style");
  style.setAttribute("data-ifuri-ecobar", "");
  style.textContent = css;
  document.head.appendChild(style);

  var bar = document.createElement("header");
  bar.className = "ifuri-ecobar";

  var brand = document.createElement("a");
  brand.className = "ifuri-ecobar-brand";
  brand.href = IFURI + "/?lang=" + lang;
  brand.setAttribute("aria-label", "ifURI");
  brand.innerHTML = "<span>if<b>URI</b></span>";
  bar.appendChild(brand);

  var nav = document.createElement("nav");
  nav.setAttribute("aria-label", "ifURI ecosystem");
  items.forEach(function (it) {
    var a = document.createElement("a");
    a.href = it[3];
    a.textContent = lang === "en" ? it[2] : it[1];
    if (it[3].indexOf(location.origin) !== 0) a.rel = "noopener";
    try { if (it[4](host, view)) a.className = "is-active"; } catch (e) {}
    nav.appendChild(a);
  });
  bar.appendChild(nav);

  var sel = document.createElement("select");
  sel.setAttribute("aria-label", "Language");
  ["pl", "en"].forEach(function (l) {
    var o = document.createElement("option");
    o.value = l; o.textContent = l.toUpperCase();
    if (l === lang) o.selected = true;
    sel.appendChild(o);
  });
  sel.addEventListener("change", function () {
    var p = new URLSearchParams(location.search);
    p.set("lang", sel.value);
    location.search = p.toString();
  });
  bar.appendChild(sel);

  document.body.insertBefore(bar, document.body.firstChild);
})();
