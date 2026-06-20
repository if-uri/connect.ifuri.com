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

const builder = document.getElementById('connectorBuilder');
if (builder) {
  const form = document.getElementById('connectorBuilderForm');
  const output = document.getElementById('manifestOutput');
  const folder = document.getElementById('manifestFolder');
  const result = document.getElementById('validationResult');
  const template = window.CONNECTOR_TEMPLATE || {};

  const lineList = (value) => (value || '')
    .split(/[\n,]+/)
    .map((item) => item.trim())
    .filter(Boolean);

  const escapeHtml = (value) => String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

  const setField = (name, value) => {
    const field = form?.elements.namedItem(name);
    if (field) field.value = Array.isArray(value) ? value.join('\n') : (value || '');
  };

  const readManifest = () => {
    const values = Object.fromEntries(new FormData(form).entries());
    const pipValues = lineList(values.pipPackages);
    const install = { mode: values.installMode || 'planned' };
    if (install.mode === 'planned' && pipValues[0]) {
      install.pipSpec = pipValues[0];
    } else if (pipValues.length) {
      install.pipPackages = pipValues;
    }
    return {
      id: (values.id || '').trim(),
      name: (values.name || '').trim(),
      status: values.status || 'planned',
      category: (values.category || '').trim(),
      summary: (values.summary || '').trim(),
      description: (values.description || '').trim(),
      uriSchemes: lineList(values.uriSchemes),
      routes: lineList(values.routes),
      useCases: template.useCases || [],
      examples: template.examples || [],
      flowExample: lineList(values.routes).slice(0, 3),
      requires: template.requires || ['python>=3.10'],
      install,
      adapterKinds: lineList(values.adapterKinds),
      docsUrl: (values.docsUrl || '').trim(),
      keywords: lineList(values.keywords),
      provenance: values.provenance || 'community',
      publisher: {
        name: (values.publisherName || '').trim(),
        url: (values.publisherUrl || '').trim(),
        github: (values.publisherGithub || '').trim(),
      },
    };
  };

  const renderManifest = () => {
    const manifest = readManifest();
    output.value = JSON.stringify(manifest, null, 2);
    if (folder) folder.textContent = `data/connectors/${manifest.id || '<id>'}/manifest.json`;
    return manifest;
  };

  const renderValidation = (payload) => {
    result.classList.toggle('ok', Boolean(payload.ok));
    result.classList.toggle('error', !payload.ok);
    const i18n = window.CONNECT_I18N || {};
    if (payload.ok) {
      const validText = i18n.validManifest || 'Valid manifest.';
      result.innerHTML = `<strong>${escapeHtml(validText)}</strong><span>${escapeHtml(payload.folder || '')}</span>`;
      return;
    }
    const errors = payload.errors || [{ field: 'unknown', message: 'validation failed' }];
    const fixTemplate = i18n.fixIssues || 'Fix {n} issue(s).';
    const fixText = fixTemplate.replace('{n}', String(errors.length));
    result.innerHTML = `<strong>${escapeHtml(fixText)}</strong><ul>${errors.map((item) => `<li><code>${escapeHtml(item.field)}</code> ${escapeHtml(item.message)}</li>`).join('')}</ul>`;
  };

  const loadTemplate = () => {
    setField('id', template.id);
    setField('name', template.name);
    setField('status', template.status);
    setField('provenance', template.provenance);
    setField('category', template.category);
    setField('installMode', template.install?.mode);
    setField('summary', template.summary);
    setField('description', template.description);
    setField('uriSchemes', template.uriSchemes || []);
    setField('routes', template.routes || []);
    setField('adapterKinds', template.adapterKinds || []);
    setField('pipPackages', template.install?.pipSpec || (template.install?.pipPackages || []).join('\n'));
    setField('publisherName', template.publisher?.name);
    setField('publisherUrl', template.publisher?.url);
    setField('publisherGithub', template.publisher?.github);
    setField('docsUrl', template.docsUrl);
    setField('keywords', template.keywords || []);
    result.textContent = '';
    result.className = 'validation-result';
    renderManifest();
  };

  document.getElementById('loadTemplate')?.addEventListener('click', loadTemplate);
  document.getElementById('buildManifest')?.addEventListener('click', renderManifest);
  document.getElementById('validateManifest')?.addEventListener('click', async () => {
    const manifest = renderManifest();
    try {
      const response = await fetch('/validate-connector', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(manifest),
      });
      const payload = await response.json();
      renderValidation(payload);
    } catch (error) {
      renderValidation({ ok: false, errors: [{ field: 'network', message: String(error) }] });
    }
  });
  form?.addEventListener('input', () => {
    result.textContent = '';
    result.className = 'validation-result';
    renderManifest();
  });
  loadTemplate();
}


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
