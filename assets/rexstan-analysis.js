(function () {
    'use strict';

    var POLL_INTERVAL_MS = 2000;
    var pollTimer = null;

    function el(id) {
        return document.getElementById(id);
    }

    function loadConfig() {
        var cfgEl = el('rexstan-analysis-config');
        if (!cfgEl) {
            return {};
        }

        try {
            return JSON.parse(cfgEl.dataset.config || '{}');
        } catch (e) {
            return {};
        }
    }

    function fetchJson(url) {
        return fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        }).then(function (response) {
            return response.json();
        });
    }

    function renderRunningPlaceholder() {
        return '<div class="rex-view rex-view-info" style="text-align:center;">'
            + '<p><span class="rexstan-analysis-spinner">&#9203;</span> Analyse läuft im Hintergrund … diese Seite aktualisiert sich automatisch, sobald sie fertig ist.</p>'
            + '</div>';
    }

    // The trigger link is always server-rendered already (works without JS,
    // it just reloads the page instead of running this AJAX flow) - this only
    // (re-)binds the click handler and toggles its visibility/label, it never
    // builds the link's markup itself.
    function setToolbar(config, running) {
        var toolbar = el('rexstan-analysis-toolbar');
        if (!toolbar) {
            return;
        }

        if (running) {
            toolbar.innerHTML = '';
            return;
        }

        var btn = el('rexstan-analysis-trigger');
        if (!btn) {
            var label = config.hasResult ? '🔄 Neu analysieren' : '▶️ Analyse starten';
            toolbar.innerHTML = '<p><a href="#" id="rexstan-analysis-trigger" class="btn btn-primary">' + label + '</a></p>';
            btn = el('rexstan-analysis-trigger');
        } else {
            btn.textContent = config.hasResult ? '🔄 Neu analysieren' : '▶️ Analyse starten';
        }

        if (btn && btn.dataset.bound !== '1') {
            btn.dataset.bound = '1';
            btn.addEventListener('click', function (event) {
                event.preventDefault();
                startAnalysis(config);
            });
        }
    }

    function setMeta(text) {
        var meta = el('rexstan-analysis-meta');
        if (meta) {
            meta.textContent = text;
        }
    }

    function stopPolling() {
        if (pollTimer !== null) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    function poll(config) {
        fetchJson(config.apiBase + '&action=status')
            .then(function (data) {
                if (!data || !data.success) {
                    stopPolling();
                    el('rexstan-analysis-result').innerHTML =
                        '<div class="rex-view rex-view-error">Fehler beim Abrufen des Analyse-Status'
                        + (data && data.error ? ': ' + data.error : '')
                        + '</div>';
                    setToolbar(config, false);
                    return;
                }

                if (data.running) {
                    // still running - keep the interval going, nothing to update yet
                    return;
                }

                stopPolling();
                config.hasResult = true;
                el('rexstan-analysis-result').innerHTML = data.html || '';
                setMeta('Ergebnis von gerade eben');
                setToolbar(config, false);
            })
            .catch(function () {
                // a single failed poll (e.g. transient network hiccup) shouldn't
                // give up on the whole run - just try again on the next tick
            });
    }

    function startAnalysis(config) {
        el('rexstan-analysis-result').innerHTML = renderRunningPlaceholder();
        setMeta('');
        setToolbar(config, true);

        fetchJson(config.apiBase + '&action=start')
            .then(function (data) {
                if (!data || !data.success) {
                    el('rexstan-analysis-result').innerHTML =
                        '<div class="rex-view rex-view-error">Fehler beim Starten der Analyse'
                        + (data && data.error ? ': ' + data.error : '')
                        + '</div>';
                    setToolbar(config, false);
                    return;
                }

                stopPolling();
                pollTimer = setInterval(function () {
                    poll(config);
                }, POLL_INTERVAL_MS);
            })
            .catch(function () {
                el('rexstan-analysis-result').innerHTML =
                    '<div class="rex-view rex-view-error">Netzwerkfehler beim Starten der Analyse.</div>';
                setToolbar(config, false);
            });
    }

    function init() {
        var app = el('rexstan-analysis-app');
        if (!app) {
            return;
        }

        var config = loadConfig();
        if (typeof config.apiBase !== 'string' || config.apiBase === '') {
            return;
        }

        setToolbar(config, !!config.running);

        if (config.running) {
            el('rexstan-analysis-result').innerHTML = renderRunningPlaceholder();
            pollTimer = setInterval(function () {
                poll(config);
            }, POLL_INTERVAL_MS);
        }
    }

    document.addEventListener('DOMContentLoaded', init);
}());
