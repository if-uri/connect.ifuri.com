// Pure connector-manifest transformations shared by the browser UI and tests.
(function exposeManifestBuilder(root, factory) {
  const api = factory();
  if (typeof module === 'object' && module.exports) module.exports = api;
  if (root) root.ConnectorManifestBuilder = api;
}(typeof window !== 'undefined' ? window : globalThis, () => {
  'use strict';

  function lineList(value) {
    return String(value || '')
      .split(/[\n,]+/)
      .map((item) => item.trim())
      .filter(Boolean);
  }

  function trimmed(value) {
    return String(value || '').trim();
  }

  function installFrom(values) {
    const pipPackages = lineList(values.pipPackages);
    const install = { mode: values.installMode || 'planned' };
    if (install.mode === 'planned' && pipPackages[0]) {
      install.pipSpec = pipPackages[0];
    } else if (pipPackages.length) {
      install.pipPackages = pipPackages;
    }
    return install;
  }

  function buildManifest(values = {}, template = {}) {
    const routes = lineList(values.routes);
    return {
      id: trimmed(values.id),
      name: trimmed(values.name),
      status: values.status || 'planned',
      category: trimmed(values.category),
      summary: trimmed(values.summary),
      description: trimmed(values.description),
      uriSchemes: lineList(values.uriSchemes),
      routes,
      useCases: template.useCases || [],
      examples: template.examples || [],
      flowExample: routes.slice(0, 3),
      requires: template.requires || ['python>=3.10'],
      install: installFrom(values),
      adapterKinds: lineList(values.adapterKinds),
      docsUrl: trimmed(values.docsUrl),
      keywords: lineList(values.keywords),
      provenance: values.provenance || 'community',
      publisher: {
        name: trimmed(values.publisherName),
        url: trimmed(values.publisherUrl),
        github: trimmed(values.publisherGithub),
      },
    };
  }

  function templateFields(template = {}) {
    const install = template.install || {};
    const publisher = template.publisher || {};
    return {
      id: template.id,
      name: template.name,
      status: template.status,
      provenance: template.provenance,
      category: template.category,
      installMode: install.mode,
      summary: template.summary,
      description: template.description,
      uriSchemes: template.uriSchemes || [],
      routes: template.routes || [],
      adapterKinds: template.adapterKinds || [],
      pipPackages: install.pipSpec || (install.pipPackages || []).join('\n'),
      publisherName: publisher.name,
      publisherUrl: publisher.url,
      publisherGithub: publisher.github,
      docsUrl: template.docsUrl,
      keywords: template.keywords || [],
    };
  }

  return Object.freeze({ buildManifest, installFrom, lineList, templateFields });
}));
