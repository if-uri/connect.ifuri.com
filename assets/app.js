// Author: Tom Sapletta · https://tom.sapletta.com
// Part of the ifURI solution.

const checks = Array.from(document.querySelectorAll('.connector-check'));
const command = document.getElementById('installCommand');
const copyInstall = document.getElementById('copyInstall');
const selectAvailable = document.getElementById('selectAvailable');
const hubBase = window.CONNECT_HUB_BASE || 'https://connect.ifuri.com';

function selectedIds() {
  return checks.filter((item) => item.checked && !item.disabled).map((item) => item.value);
}

function installCommand(ids = selectedIds()) {
  const suffix = ids.length ? `?connectors=${encodeURIComponent(ids.join(','))}` : '';
  return `curl -fsSL '${hubBase}/install${suffix}' | bash`;
}

function refreshCommand() {
  if (!command) return;
  command.textContent = installCommand();
}

async function copyText(value, button) {
  if (navigator.clipboard?.writeText) {
    await navigator.clipboard.writeText(value);
  } else {
    const input = document.createElement('textarea');
    input.value = value;
    input.setAttribute('readonly', '');
    input.style.position = 'fixed';
    input.style.left = '-9999px';
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    input.remove();
  }
  const previous = button.textContent;
  button.textContent = (window.CONNECT_I18N && window.CONNECT_I18N.copied) || 'Copied';
  window.setTimeout(() => {
    button.textContent = previous;
  }, 1400);
}

checks.forEach((item) => item.addEventListener('change', refreshCommand));

selectAvailable?.addEventListener('click', () => {
  checks.forEach((item) => {
    if (!item.disabled) item.checked = true;
  });
  refreshCommand();
});

copyInstall?.addEventListener('click', () => {
  copyText(command.textContent, copyInstall).catch(() => {});
});

document.addEventListener('click', (event) => {
  const button = event.target.closest('[data-copy]');
  if (!button || button.disabled) return;
  copyText(button.dataset.copy, button).catch(() => {});
});

document.addEventListener('click', (event) => {
  const button = event.target.closest('[data-copy-target]');
  if (!button || button.disabled) return;
  const target = document.getElementById(button.dataset.copyTarget);
  if (!target) return;
  copyText(target.value || target.textContent || '', button).catch(() => {});
});

const search = document.getElementById('connectorSearch');
const cards = Array.from(document.querySelectorAll('.connector'));
const count = document.getElementById('connectorCount');
const noResults = document.getElementById('noResults');

function filterConnectors() {
  const term = (search?.value || '').trim().toLowerCase();
  let visible = 0;
  cards.forEach((card) => {
    const match = !term || (card.dataset.search || '').includes(term);
    card.classList.toggle('hidden', !match);
    if (match) visible += 1;
  });
  const ofWord = (window.CONNECT_I18N && window.CONNECT_I18N.of) || 'of';
  if (count) count.textContent = term ? `${visible} ${ofWord} ${cards.length}` : '';
  if (noResults) noResults.hidden = visible !== 0;
}

search?.addEventListener('input', filterConnectors);

document.querySelectorAll('[data-tabs]').forEach((tabs) => {
  const buttons = Array.from(tabs.querySelectorAll('[data-tab-target]'));
  const panels = Array.from(tabs.querySelectorAll('[data-tab-panel]'));
  buttons.forEach((button) => {
    button.addEventListener('click', () => {
      const target = button.dataset.tabTarget;
      buttons.forEach((item) => {
        const active = item === button;
        item.classList.toggle('is-active', active);
        item.setAttribute('aria-selected', active ? 'true' : 'false');
      });
      panels.forEach((panel) => {
        const active = panel.dataset.tabPanel === target;
        panel.classList.toggle('is-active', active);
        panel.hidden = !active;
      });
    });
  });
});

refreshCommand();

function escapeHtml(value) {
  return String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function connectorBuilderContext() {
  const root = document.getElementById('connectorBuilder');
  if (!root || !window.ConnectorManifestBuilder) return null;
  return {
    root,
    form: document.getElementById('connectorBuilderForm'),
    output: document.getElementById('manifestOutput'),
    folder: document.getElementById('manifestFolder'),
    result: document.getElementById('validationResult'),
    template: window.CONNECTOR_TEMPLATE || {},
    tools: window.ConnectorManifestBuilder,
  };
}

function setBuilderField(context, name, value) {
  const field = context.form?.elements.namedItem(name);
  if (field) field.value = Array.isArray(value) ? value.join('\n') : (value || '');
}

function clearBuilderValidation(context) {
  context.result.textContent = '';
  context.result.className = 'validation-result';
}

function readBuilderManifest(context) {
  const values = Object.fromEntries(new FormData(context.form).entries());
  return context.tools.buildManifest(values, context.template);
}

function renderBuilderManifest(context) {
  const manifest = readBuilderManifest(context);
  context.output.value = JSON.stringify(manifest, null, 2);
  if (context.folder) {
    context.folder.textContent = `data/connectors/${manifest.id || '<id>'}/manifest.json`;
  }
  return manifest;
}

function renderBuilderValidation(context, payload) {
  context.result.classList.toggle('ok', Boolean(payload.ok));
  context.result.classList.toggle('error', !payload.ok);
  const i18n = window.CONNECT_I18N || {};
  if (payload.ok) {
    const validText = i18n.validManifest || 'Valid manifest.';
    context.result.innerHTML = `<strong>${escapeHtml(validText)}</strong><span>${escapeHtml(payload.folder || '')}</span>`;
    return;
  }
  const errors = payload.errors || [{ field: 'unknown', message: 'validation failed' }];
  const fixTemplate = i18n.fixIssues || 'Fix {n} issue(s).';
  const fixText = fixTemplate.replace('{n}', String(errors.length));
  context.result.innerHTML = `<strong>${escapeHtml(fixText)}</strong><ul>${errors.map((item) => `<li><code>${escapeHtml(item.field)}</code> ${escapeHtml(item.message)}</li>`).join('')}</ul>`;
}

function loadBuilderTemplate(context) {
  Object.entries(context.tools.templateFields(context.template))
    .forEach(([name, value]) => setBuilderField(context, name, value));
  clearBuilderValidation(context);
  renderBuilderManifest(context);
}

async function validateBuilderManifest(context) {
  const manifest = renderBuilderManifest(context);
  try {
    const response = await fetch('/validate-connector', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(manifest),
    });
    renderBuilderValidation(context, await response.json());
  } catch (error) {
    renderBuilderValidation(context, {
      ok: false,
      errors: [{ field: 'network', message: String(error) }],
    });
  }
}

function initializeConnectorBuilder(context) {
  document.getElementById('loadTemplate')?.addEventListener('click', () => loadBuilderTemplate(context));
  document.getElementById('buildManifest')?.addEventListener('click', () => renderBuilderManifest(context));
  document.getElementById('validateManifest')?.addEventListener('click', () => validateBuilderManifest(context));
  context.form?.addEventListener('input', () => {
    clearBuilderValidation(context);
    renderBuilderManifest(context);
  });
  loadBuilderTemplate(context);
}

const manifestBuilder = connectorBuilderContext();
if (manifestBuilder) initializeConnectorBuilder(manifestBuilder);


// Theme toggle: cycles light/dark and persists; defaults to OS preference.
(function () {
  const KEY = 'ifuri-theme';
  const root = document.documentElement;
  const saved = localStorage.getItem(KEY);
  if (saved === 'dark' || saved === 'light') root.setAttribute('data-theme', saved);
  const nav = document.querySelector('.site-header nav');
  if (!nav) return;
  const isDark = () => root.getAttribute('data-theme')
    ? root.getAttribute('data-theme') === 'dark'
    : window.matchMedia('(prefers-color-scheme: dark)').matches;
  const btn = document.createElement('button');
  btn.type = 'button';
  btn.className = 'theme-toggle';
  btn.setAttribute('aria-label', 'Toggle dark mode');
  const render = () => { btn.textContent = isDark() ? '\u2600' : '\u263e'; };
  btn.addEventListener('click', () => {
    const next = isDark() ? 'light' : 'dark';
    root.setAttribute('data-theme', next);
    localStorage.setItem(KEY, next);
    render();
  });
  nav.prepend(btn);
  render();
})();
