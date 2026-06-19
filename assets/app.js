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
  button.textContent = 'Copied';
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
  if (count) count.textContent = term ? `${visible} of ${cards.length}` : '';
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
