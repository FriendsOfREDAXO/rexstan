var Rexstan = {

    /**
     * Click-Actions auf Buttons in pages/analysis.php zum Bedienen der Datei-Sections mit Bootstrap-Collapse
     * - alle einblenden
     * - alle ausblenden
     * selector: Selektor zum Auffinden der Sections und darin der collapse-Header(buttons)
     */
    showAll: function(selector) {
        let sections = document.querySelectorAll(selector);
        sections.forEach( node => $(node).collapse('show') );
    },

    hideAll: function(selector) {
        let sections = document.querySelectorAll(selector);
        sections.forEach( node => $(node).collapse('hide') );
    },

    /**
     * Filter/Suche initialisieren und durchführen.
     * 
     * Die übergebenen IDs (aray fileSectionIds) der Messagelisten je Datei werden in nodes aufgelöst
     * für schnelleren Zugriff (array targetSections)
     * 
     * Der Reset-Button im Suchfeld löst nach Click-Event zwei Aktionen aus:
     * - Inhalt des Suchfelds leeren
     * - auf dem Suchfeld einen "input"-Event auslösen, um die Darstellung der Messages zu aktualisieren
     * 
     * Der input-Tag im Suchfeld löst nach einen Input-Event die Anpassung der Darstellung aus:
     * - Schleife über die Datei-Sektionen (targetSections)
     *   - Markiert die Liste/Section als gefiltert (.rexstan-search-applied)
     *   - Alle dem Filter entsprechenden Einträge der Liste mit .rexstan-search-hit markieren
     *   - Im Header der Section den Treffer-Zähler aktualisieren
     *     - kein Suchbegriff vorhanden: leer
     *     - 0 Treffer: .label-warning + Wert=0
     *     - n Treffer: .label-success + Wert=n
     */
    filterResults: function (searchId, targetSectionIds ) {

        let searchField = document.getElementById(searchId);
        if( !searchField ) {
            console.error('rexstan: Suchfeld "#'+searchId+'" nicht gefunden');
            return;
        }
    
        let targetSections = [];
        for (let targetId of targetSectionIds) {
            let targetNode = document.getElementById(targetId);
            if( !targetNode ) {
                console.error('rexstan: Zielblock "#'+targetId+'" nicht gefunden');
                continue;
            }
            targetSections.push(targetNode);
    
            targetNode.classList.remove('rexstan-search-applied');
            targetNode._rexstanBadge = document.getElementById(targetId+'-badge');
            targetNode.childNodes.forEach( node => {
                node._rexstanHaystack = node.querySelector(':scope .rexstan-search-item');
                node._rexstanHaystack = node._rexstanHaystack ? node._rexstanHaystack.textContent.toLowerCase() : '';
            });
        };

        if( 0 === targetSections.length ) {
            searchField.classList.add('hidden');
            return;
        }

        let searchInput = searchField.querySelector('input');
        let searchReset = searchField.querySelector('.form-control-clear');
    
        searchReset.addEventListener('click', e => {
            searchInput.value = '';
            let event = new CustomEvent('input',{bubbles: true, cancelable: true});
            searchInput.dispatchEvent(event);
        });
    
        searchInput.addEventListener('input', e => {
            let needle = e.target.value.toLowerCase();
            searchReset.classList.toggle('hidden',0 === needle.length);
            let amount = 0;
            let hasFilter = needle.length > 0;
            for (let target of targetSections) {
                target.classList.toggle('rexstan-search-applied',hasFilter);
                target.childNodes.forEach( node => {
                    node.classList.toggle('rexstan-search-hit',(hasFilter && node._rexstanHaystack.indexOf(needle)>=0));
                });
                amount = target.querySelectorAll(':scope > .rexstan-search-hit').length;
                if( hasFilter ) {
                    target._rexstanBadge.innerHTML = '<i class="rex-icon rex-icon-search"></i> = '+amount;
                    target._rexstanBadge.classList.toggle('rexstan-badge-success',(0<amount));
                } else {
                    target._rexstanBadge.innerHTML = '';
                }
            }
        },false);
        searchInput.value = '';
    },

    /**
     * Sticky-Headers: Der Stoppunkt der Dateisektionen muss immer unter dem
     * Suchleisten-Header sein.      
     * 
     * Auf den Suchleisten-Header (#headerSectionId) wird ein ResizeObserver aktiviert.
     * Initial und bei Änderungen der Höhe (z.B. wenn die Fensterbreite veränderet wird)
     * überträgt der ResizeObserver die aktuelle Höhe des Suchleisten-Header als Stoppunkt
     * in die Dateisektionen (node.style.top = `${height}px`;)
     */
     stickyHeader: function (headerSectionId) {

        let headerNode = document.getElementById(headerSectionId);
        if( !headerNode ) {
            console.error('rexstan: Kopfzeile "#'+headerNode+'" nicht gefunden');
            return;
        }
    
        let fileSectionHeader = headerNode.parentNode.querySelectorAll('#'+headerSectionId+' ~ section.rexstan > .panel > .panel-heading')

        let height;
        let myObserver = new ResizeObserver(entries => {
            entries.forEach(entry => {
                if( height != entry.contentRect.height ) {
                    height = entry.contentRect.height;
                    for (let node of fileSectionHeader) {
                        node.style.top = `${height}px`;
                    }
                }
            });
        });
        myObserver.observe(headerNode);
    }

}