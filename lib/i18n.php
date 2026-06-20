<?php
declare(strict_types=1);

/**
 * UI string catalog for connect.ifuri.com, keyed by string id then language.
 *
 * Language follows the same ?lang= signal as the shared ifuri ecobar, which
 * defaults to Polish across the ecosystem (ifuri.com, get.ifuri.com, docs).
 * English is the fallback when a key is missing a translation. Connector
 * catalog content (names, summaries) stays data-driven in data/connectors.json
 * and is intentionally not translated here.
 */
return [
    // --- shared chrome / footer ---
    'footerCatalog'  => ['en' => 'Catalog version', 'pl' => 'Wersja katalogu'],
    'footerSite'     => ['en' => 'Site v', 'pl' => 'Strona v'],
    'footerUpdated'  => ['en' => 'Updated', 'pl' => 'Zaktualizowano'],
    'copy'           => ['en' => 'Copy', 'pl' => 'Kopiuj'],
    'copyInstall'    => ['en' => 'Copy install', 'pl' => 'Kopiuj instalację'],
    'openInstaller'  => ['en' => 'Open installer script', 'pl' => 'Otwórz skrypt instalatora'],
    'docs'           => ['en' => 'Docs', 'pl' => 'Dokumentacja'],
    'verified'       => ['en' => '✓ verified', 'pl' => '✓ zweryfikowany'],
    'community'      => ['en' => 'community', 'pl' => 'społeczność'],
    'verifiedTitle'  => ['en' => 'Maintained and audited by if-uri', 'pl' => 'Utrzymywany i audytowany przez if-uri'],
    'communityTitle' => ['en' => 'Third-party community connector', 'pl' => 'Zewnętrzny connector społecznościowy'],
    'categoryFallback' => ['en' => 'Connector', 'pl' => 'Connector'],
    'schemesAria'    => ['en' => 'URI schemes', 'pl' => 'Schematy URI'],
    'statusAvailable'=> ['en' => 'available', 'pl' => 'dostępny'],
    'statusPlanned'  => ['en' => 'planned', 'pl' => 'planowany'],

    // --- shared site-header navigation (json/llms.txt filenames stay literal) ---
    'navConnectors'  => ['en' => 'Connectors', 'pl' => 'Connectory'],
    'navCategories'  => ['en' => 'Categories', 'pl' => 'Kategorie'],
    'navSubmit'      => ['en' => 'Submit connector', 'pl' => 'Zgłoś connector'],

    // --- index.php ---
    'eyebrow'        => ['en' => 'Connector hub for ifuri + urirun', 'pl' => 'Hub connectorów dla ifuri + urirun'],
    'h1'             => ['en' => 'Install URI connectors with one command.', 'pl' => 'Instaluj connectory URI jedną komendą.'],
    'lead'           => ['en' => 'Choose connectors, copy one command and let ifuri/urirun expose their URI bindings, registry entries, requirements and flow examples.', 'pl' => 'Wybierz connectory, skopiuj jedną komendę, a ifuri/urirun udostępni ich bindings URI, wpisy rejestru, wymagania i przykłady flow.'],
    'copyDefault'    => ['en' => 'Copy default install', 'pl' => 'Kopiuj domyślną instalację'],
    'buildTitle'     => ['en' => 'Build install command', 'pl' => 'Zbuduj komendę instalacji'],
    'buildLead'      => ['en' => 'Select multiple connectors. Planned connectors stay visible but are not included in the installer yet.', 'pl' => 'Wybierz wiele connectorów. Planowane connectory pozostają widoczne, ale nie są jeszcze dołączane do instalatora.'],
    'selectAvailable'=> ['en' => 'Select available', 'pl' => 'Zaznacz dostępne'],
    'filterPlaceholder' => ['en' => 'Filter connectors by name, summary or URI scheme…', 'pl' => 'Filtruj connectory po nazwie, opisie lub schemacie URI…'],
    'filterAria'     => ['en' => 'Filter connectors', 'pl' => 'Filtruj connectory'],
    'connectorsAria' => ['en' => 'Connectors', 'pl' => 'Connectory'],
    'details'        => ['en' => 'Details', 'pl' => 'Szczegóły'],
    'noResults'      => ['en' => 'No connectors match your filter.', 'pl' => 'Żaden connector nie pasuje do filtra.'],
    'machineTitle'   => ['en' => 'Machine endpoints', 'pl' => 'Endpointy maszynowe'],
    'machineLead'    => ['en' => 'ifuri app and future registry tooling can read the same catalog as users.', 'pl' => 'Aplikacja ifuri i przyszłe narzędzia rejestru czytają ten sam katalog co użytkownicy.'],
    'ecoTitle'       => ['en' => 'ifuri ecosystem', 'pl' => 'Ekosystem ifuri'],
    'ecoLead'        => ['en' => 'Use these public entry points together: website, one-line installer and connector hub.', 'pl' => 'Korzystaj z tych publicznych punktów wejścia razem: strona, jednoliniowy instalator i hub connectorów.'],

    // --- connector.php (detail + 404) ---
    'nfTitle'        => ['en' => 'Connector not found - ifuri Connect', 'pl' => 'Nie znaleziono connectora - ifuri Connect'],
    'nfH1'           => ['en' => 'Connector not found.', 'pl' => 'Nie znaleziono connectora.'],
    'nfLead'         => ['en' => 'The requested connector is not published in the ifuri connector catalog.', 'pl' => 'Żądany connector nie jest opublikowany w katalogu connectorów ifuri.'],
    'nfBack'         => ['en' => 'Back to connector hub', 'pl' => 'Wróć do huba connectorów'],
    'connectorWord'  => ['en' => 'connector', 'pl' => 'connector'],
    'tablistAria'    => ['en' => 'Connector sections', 'pl' => 'Sekcje connectora'],
    'tabOverview'    => ['en' => 'Overview', 'pl' => 'Przegląd'],
    'tabRoutes'      => ['en' => 'URI routes', 'pl' => 'Trasy URI'],
    'tabInstall'     => ['en' => 'Install', 'pl' => 'Instalacja'],
    'tabRegistry'    => ['en' => 'Registry', 'pl' => 'Rejestr'],
    'whatItDoes'     => ['en' => 'What it does', 'pl' => 'Co robi'],
    'requirements'   => ['en' => 'Requirements', 'pl' => 'Wymagania'],
    'routesTitle'    => ['en' => 'Routes', 'pl' => 'Trasy'],
    'examplesTitle'  => ['en' => 'Examples', 'pl' => 'Przykłady'],
    'exampleFallback'=> ['en' => 'Example', 'pl' => 'Przykład'],
    'installCommandTitle' => ['en' => 'Install command', 'pl' => 'Komenda instalacji'],
    'plannedNotice'  => ['en' => 'This connector is planned. The installer is visible for contract design, but execution is disabled until the connector package is available.', 'pl' => 'Ten connector jest planowany. Instalator jest widoczny na potrzeby projektowania kontraktu, ale wykonanie jest wyłączone, dopóki pakiet connectora nie będzie dostępny.'],
    'registryEntryTitle' => ['en' => 'Registry entry', 'pl' => 'Wpis rejestru'],
    'fullRegistry'   => ['en' => 'Full registry', 'pl' => 'Pełny rejestr'],
    'connectorCatalog' => ['en' => 'Connector catalog', 'pl' => 'Katalog connectorów'],
    'connectorJson'  => ['en' => 'Connector JSON', 'pl' => 'JSON connectora'],
    'searchIndex'    => ['en' => 'Search index', 'pl' => 'Indeks wyszukiwania'],
    'llmIndex'       => ['en' => 'LLM index', 'pl' => 'Indeks LLM'],

    // --- categories.php ---
    'catTitle'       => ['en' => 'Connector categories · connect.ifuri.com', 'pl' => 'Kategorie connectorów · connect.ifuri.com'],
    'catDescription' => ['en' => 'Browse ifURI / urirun connectors by category: automation, operations, DNS, transport, planning, data and more.', 'pl' => 'Przeglądaj connectory ifURI / urirun według kategorii: automatyzacja, operacje, DNS, transport, planowanie, dane i więcej.'],
    'catEyebrow'     => ['en' => 'Catalog', 'pl' => 'Katalog'],
    'catH1'          => ['en' => 'Connectors by category', 'pl' => 'Connectory według kategorii'],
    'lanTitle'       => ['en' => 'Use connectors in a LAN demo', 'pl' => 'Użyj connectorów w demie LAN'],
    'lanLead'        => ['en' => 'Install connectors from this hub, then drive them across machines with a URI flow.', 'pl' => 'Zainstaluj connectory z tego huba, a potem steruj nimi na wielu maszynach za pomocą flow URI.'],
    'lanDemo1'       => ['en' => 'URI commands drive real browsers inside noVNC desktops across four computers.', 'pl' => 'Komendy URI sterują prawdziwymi przeglądarkami w pulpitach noVNC na czterech komputerach.'],
    'lanDemo2'       => ['en' => 'host plus nodes install hub connectors and execute URI routes end-to-end.', 'pl' => 'host i węzły instalują connectory z huba i wykonują trasy URI end-to-end.'],
    'allConnectors'  => ['en' => 'All connectors', 'pl' => 'Wszystkie connectory'],
    'connectorDocs'  => ['en' => 'Connector docs', 'pl' => 'Dokumentacja connectorów'],
    'novncGuide'     => ['en' => 'noVNC guide', 'pl' => 'Przewodnik noVNC'],

    // --- submit.php ---
    'submitTitle'    => ['en' => 'Submit a URI connector - ifuri Connect', 'pl' => 'Zgłoś connector URI - ifuri Connect'],
    'submitEyebrow'  => ['en' => 'Connector submission', 'pl' => 'Zgłoszenie connectora'],
    'submitH1'       => ['en' => 'Build a connector manifest.', 'pl' => 'Zbuduj manifest connectora.'],
    'manifestFields' => ['en' => 'Manifest fields', 'pl' => 'Pola manifestu'],
    'manifestFieldsLead' => ['en' => 'Community connectors must declare publisher and adapter kinds.', 'pl' => 'Connectory społecznościowe muszą zadeklarować wydawcę i rodzaje adapterów.'],
    'resetSample'    => ['en' => 'Reset sample', 'pl' => 'Resetuj przykład'],
    'fName'          => ['en' => 'Name', 'pl' => 'Nazwa'],
    'fStatus'        => ['en' => 'Status', 'pl' => 'Status'],
    'fProvenance'    => ['en' => 'Provenance', 'pl' => 'Pochodzenie'],
    'fCategory'      => ['en' => 'Category', 'pl' => 'Kategoria'],
    'fInstallMode'   => ['en' => 'Install mode', 'pl' => 'Tryb instalacji'],
    'fSummary'       => ['en' => 'Summary', 'pl' => 'Podsumowanie'],
    'fDescription'   => ['en' => 'Description', 'pl' => 'Opis'],
    'fAdapterKinds'  => ['en' => 'Adapter kinds', 'pl' => 'Rodzaje adapterów'],
    'fPip'           => ['en' => 'pip packages or pip spec', 'pl' => 'pakiety pip lub pip spec'],
    'fPublisher'     => ['en' => 'Publisher', 'pl' => 'Wydawca'],
    'fPublisherUrl'  => ['en' => 'Publisher URL', 'pl' => 'URL wydawcy'],
    'fPublisherGithub' => ['en' => 'Publisher GitHub', 'pl' => 'GitHub wydawcy'],
    'fDocsUrl'       => ['en' => 'Docs URL', 'pl' => 'URL dokumentacji'],
    'fKeywords'      => ['en' => 'Keywords', 'pl' => 'Słowa kluczowe'],
    'buildJson'      => ['en' => 'Build JSON', 'pl' => 'Zbuduj JSON'],
    'validate'       => ['en' => 'Validate', 'pl' => 'Waliduj'],
    'generatedManifest' => ['en' => 'Generated manifest', 'pl' => 'Wygenerowany manifest'],
    'copyJson'       => ['en' => 'Copy JSON', 'pl' => 'Kopiuj JSON'],
    'submitDocs'     => ['en' => 'Submit docs', 'pl' => 'Dokumentacja zgłaszania'],

    // --- strings consumed by assets/app.js (exposed via window.CONNECT_I18N) ---
    'jsCopied'       => ['en' => 'Copied', 'pl' => 'Skopiowano'],
    'jsOf'           => ['en' => 'of', 'pl' => 'z'],
    'jsValidManifest'=> ['en' => 'Valid manifest.', 'pl' => 'Poprawny manifest.'],
    'jsFixIssues'    => ['en' => 'Fix {n} issue(s).', 'pl' => 'Problemy do poprawienia: {n}.'],
];
