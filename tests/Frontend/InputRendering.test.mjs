import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { test } from 'node:test';
import { fileURLToPath } from 'node:url';
import { compileScript, parse } from 'vue/compiler-sfc';
import * as Vue from 'vue';
import { renderToString } from 'vue/server-renderer';

const inputPath = fileURLToPath(new URL('../../resources/js/Components/UI/Input.vue', import.meta.url));
const source = readFileSync(inputPath, 'utf8');
const compiled = compileScript(parse(source, { filename: inputPath }).descriptor, {
  id: 'input-rendering-test',
  inlineTemplate: true,
}).content;

const vueImports = [...compiled.matchAll(/^import \{([\s\S]*?)\} from ["']vue["'];?$/gm)];
const bindings = [];
let componentSource = compiled;

for (const vueImport of vueImports) {
  for (const binding of vueImport[1].split(',').map((item) => item.trim()).filter(Boolean)) {
    const [exportName, localName] = binding.split(/\s+as\s+/);
    bindings.push([exportName, localName || exportName]);
  }

  componentSource = componentSource.replace(vueImport[0], '');
}

componentSource = componentSource.replace('export default', 'return');

const Input = new Function(
  ...bindings.map(([, localName]) => localName),
  componentSource,
)(...bindings.map(([exportName]) => Vue[exportName]));

test('Input renders supported types and optional label, error, prefix, and suffix combinations', async () => {
  const types = {
    text: 'Texte',
    time: '09:30',
    email: 'paroisse@example.com',
    password: 'mot-de-passe',
    color: '#1E40AF',
  };
  let renderedCombinations = 0;

  for (const [type, value] of Object.entries(types)) {
    for (const withLabel of [false, true]) {
      for (const withError of [false, true]) {
        for (const withPrefix of [false, true]) {
          for (const withSuffix of [false, true]) {
            const props = { type, modelValue: value, id: 'test-input' };
            const slots = {};

            if (withLabel) {
              props.label = 'Champ';
            }

            if (withError) {
              props.error = 'Valeur invalide';
            }

            if (withPrefix) {
              slots.prefix = () => Vue.h('span', 'Préfixe');
            }

            if (withSuffix) {
              slots.suffix = () => Vue.h('span', 'Suffixe');
            }

            const warnings = [];
            const app = Vue.createSSRApp({
              render: () => Vue.h(Input, props, slots),
            });

            app.config.warnHandler = (message) => warnings.push(message);
            app.config.errorHandler = (error) => {
              throw error;
            };

            const html = await renderToString(app);

            assert.match(html, new RegExp(`type="${type}"`));
            assert.equal(html.includes('Champ'), withLabel);
            assert.equal(html.includes('Valeur invalide'), withError);
            assert.equal(html.includes('Préfixe'), withPrefix);
            assert.equal(html.includes('Suffixe'), withSuffix);
            assert.equal(html.includes('pl-10'), withPrefix);
            assert.equal(html.includes('pr-10'), withSuffix);
            assert.deepEqual(warnings, []);
            renderedCombinations++;
          }
        }
      }
    }
  }

  assert.equal(renderedCombinations, 80);
});

test('Phase 1 and Phase 2 pages compile as Vue single-file components', () => {
  const pages = [
    '../../resources/js/Pages/Admin/Annonces/Index.vue',
    '../../resources/js/Pages/Admin/Annonces/Form.vue',
    '../../resources/js/Pages/Admin/Horaires/Index.vue',
    '../../resources/js/Pages/Admin/Liturgie/Index.vue',
    '../../resources/js/Pages/Admin/Intentions/Index.vue',
    '../../resources/js/Pages/Admin/Intentions/Show.vue',
    '../../resources/js/Pages/Admin/Intentions/Feuille.vue',
    '../../resources/js/Pages/Public/Paroisse.vue',
    '../../resources/js/Pages/Public/Annonces/Index.vue',
    '../../resources/js/Pages/Public/Annonces/Show.vue',
    '../../resources/js/Pages/Public/Intentions/Nouvelle.vue',
    '../../resources/js/Pages/Public/Intentions/Confirmation.vue',
    '../../resources/js/Pages/Public/Intentions/Suivi.vue',
  ];

  for (const relativePath of pages) {
    const path = fileURLToPath(new URL(relativePath, import.meta.url));
    const source = readFileSync(path, 'utf8');
    const parsed = parse(source, { filename: path });
    assert.deepEqual(parsed.errors, []);

    const compiled = compileScript(parsed.descriptor, {
      id: `phase1-${relativePath}`,
      inlineTemplate: true,
    });
    assert.equal(typeof compiled.content, 'string');
  }
});

test('Phase 2 public intention forms keep the anti-spam protections and plain text', () => {
  const formPages = [
    '../../resources/js/Pages/Public/Intentions/Nouvelle.vue',
    '../../resources/js/Pages/Public/Intentions/Suivi.vue',
  ];

  for (const relativePath of formPages) {
    const path = fileURLToPath(new URL(relativePath, import.meta.url));
    const source = readFileSync(path, 'utf8');

    assert.ok(source.includes('reference_colis'), `${relativePath} doit contenir le honeypot reference_colis`);
    assert.ok(source.includes('tabindex="-1"'), `${relativePath} doit retirer le honeypot du parcours clavier`);
    assert.ok(source.includes('autocomplete="off"'), `${relativePath} doit désactiver l'autocomplétion du honeypot`);
    assert.ok(source.includes('aria-hidden="true"'), `${relativePath} doit masquer le honeypot aux lecteurs d'écran`);
    assert.ok(source.includes('form_token'), `${relativePath} doit transporter le jeton signé form_token`);
    assert.ok(source.includes('{{'), `${relativePath} doit rendre les données en texte brut via {{ }}`);
    assert.ok(!source.includes('v-html'), `${relativePath} ne doit jamais utiliser v-html`);
  }

  const confirmationPath = fileURLToPath(new URL('../../resources/js/Pages/Public/Intentions/Confirmation.vue', import.meta.url));
  const confirmationSource = readFileSync(confirmationPath, 'utf8');

  assert.ok(!confirmationSource.includes('v-html'), 'La confirmation ne doit jamais utiliser v-html');
  assert.ok(confirmationSource.includes('{{ code }}'), 'La confirmation doit afficher le code en texte brut');
  assert.equal(
    (confirmationSource.match(/\{\{\s*code\s*\}\}/g) || []).length,
    1,
    'La confirmation ne doit afficher le code qu’une seule fois',
  );
});
