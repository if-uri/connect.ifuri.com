const checks = Array.from(document.querySelectorAll('.connector-check'));
const command = document.getElementById('installCommand');
const copyInstall = document.getElementById('copyInstall');
const selectAvailable = document.getElementById('selectAvailable');

function selectedIds() {
  return checks.filter((item) => item.checked && !item.disabled).map((item) => item.value);
}

function installCommand(ids = selectedIds()) {
  const suffix = ids.length ? `?connectors=${encodeURIComponent(ids.join(','))}` : '';
  return `curl -fsSL 'https://connect.ifuri.com/install${suffix}' | bash`;
}

function refreshCommand() {
  command.textContent = installCommand();
}

async function copyText(value, button) {
  await navigator.clipboard.writeText(value);
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

refreshCommand();
