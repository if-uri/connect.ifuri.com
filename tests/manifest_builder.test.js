'use strict';

const test = require('node:test');
const assert = require('node:assert/strict');

const {
  buildManifest,
  installFrom,
  lineList,
  templateFields,
} = require('../assets/manifest-builder.js');

test('lineList normalizes comma and newline separated values', () => {
  assert.deepEqual(lineList(' dns, http\n\nfile '), ['dns', 'http', 'file']);
  assert.deepEqual(lineList(''), []);
});

test('planned installs use only the first package as pipSpec', () => {
  assert.deepEqual(installFrom({
    installMode: 'planned',
    pipPackages: 'urirun-connector-demo\nignored-package',
  }), {
    mode: 'planned',
    pipSpec: 'urirun-connector-demo',
  });
});

test('non-planned installs retain the complete package list', () => {
  assert.deepEqual(installFrom({
    installMode: 'pip',
    pipPackages: 'one, two',
  }), {
    mode: 'pip',
    pipPackages: ['one', 'two'],
  });
});

test('buildManifest preserves template-owned fields and normalizes form fields', () => {
  const manifest = buildManifest({
    id: ' demo ',
    name: ' Demo Connector ',
    category: ' Test ',
    summary: ' Summary ',
    description: ' Description ',
    uriSchemes: 'demo, https',
    routes: 'demo://host/one\ndemo://host/two,demo://host/three\ndemo://host/four',
    adapterKinds: 'http-service',
    docsUrl: ' https://example.test/docs ',
    keywords: 'demo, test',
    publisherName: ' Example ',
    publisherUrl: ' https://example.test ',
    publisherGithub: ' example/demo ',
    pipPackages: 'urirun-connector-demo',
  }, {
    useCases: ['Demo use case'],
    examples: [{ uri: 'demo://host/one' }],
    requires: ['python>=3.12'],
  });

  assert.equal(manifest.id, 'demo');
  assert.equal(manifest.status, 'planned');
  assert.equal(manifest.provenance, 'community');
  assert.deepEqual(manifest.uriSchemes, ['demo', 'https']);
  assert.deepEqual(manifest.flowExample, manifest.routes.slice(0, 3));
  assert.deepEqual(manifest.useCases, ['Demo use case']);
  assert.deepEqual(manifest.requires, ['python>=3.12']);
  assert.deepEqual(manifest.publisher, {
    name: 'Example',
    url: 'https://example.test',
    github: 'example/demo',
  });
});

test('templateFields gives pipSpec precedence and applies empty nested defaults', () => {
  assert.deepEqual(templateFields({ install: { pipSpec: 'preferred', pipPackages: ['fallback'] } }).pipPackages, 'preferred');
  const empty = templateFields();
  assert.deepEqual(empty.routes, []);
  assert.deepEqual(empty.adapterKinds, []);
  assert.equal(empty.publisherName, undefined);
});
