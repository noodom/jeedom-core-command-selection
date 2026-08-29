<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

if (!isConnect()) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
?>

<style>
.miller-picker-container {
    --miller-bg: #1a1a1a;
    --miller-bg-secondary: #222;
    --miller-bg-input: #2b2b2b;
    --miller-bg-hover: rgba(255, 255, 255, 0.06);
    --miller-border: #3a3a3a;
    --miller-border-light: #2d2d2d;
    --miller-text: #ddd;
    --miller-text-muted: #888;
    --miller-primary: #007acc;
    --miller-info: #7ca4d3;
    --miller-warning: #f0ad4e;
    --miller-danger: #d66;
    --miller-badge: rgba(0, 0, 0, 0.3);
    --miller-summary-bg: rgba(0, 122, 204, 0.12);

    display: flex;
    flex-direction: column;
    gap: 12px;
    height: 500px;
    max-height: 70vh;
    margin: 0;
    padding: 5px;
    color: var(--miller-text);
}

[data-theme="core2019_Light"] .miller-picker-container {
    --miller-bg: #fff;
    --miller-bg-secondary: #f5f5f5;
    --miller-bg-input: #fff;
    --miller-bg-hover: rgba(0, 0, 0, 0.06);
    --miller-border: #d5d5d5;
    --miller-border-light: #e2e2e2;
    --miller-text: #333;
    --miller-text-muted: #777;
    --miller-primary: #007acc;
    --miller-info: #3973a8;
    --miller-warning: #c77c00;
    --miller-danger: #c44;
    --miller-badge: rgba(0, 0, 0, 0.05);
    --miller-summary-bg: rgba(0, 122, 204, 0.08);
}
.miller-search-bar, .miller-filter-wrapper { 
    position: relative;
}

.miller-search-icon {
    position: absolute;
    top: 50%;
    left: 10px;
    z-index: 2;
    transform: translateY(-50%);
    color: var(--miller-text-muted);
    font-size: 13px;
    pointer-events: none;
}

.miller-search-bar input {
    width: 100%;
    box-sizing: border-box;
    padding: 8px 55px 8px 32px;
    border: 1px solid var(--miller-border);
    border-radius: 4px;
    outline: none;
    background: var(--miller-bg-input);
    color: var(--miller-text);
    font-size: 14px;
}

.miller-search-bar input::placeholder, .miller-col-filter::placeholder {
    color: var(--miller-text-muted);
    opacity: 1;
}

.miller-search-bar input:focus, .miller-col-filter:focus { 
    border-color: var(--miller-primary); 
}

.miller-search-loading {
    position: absolute;
    top: 50%;
    right: 34px;
    display: none;
    transform: translateY(-50%);
    color: var(--miller-text-muted);
    font-size: 13px;
    opacity: 0.7;
}

.miller-clear-input {
    position: absolute;
    top: 50%;
    right: 8px;
    z-index: 2;
    display: none;
    width: 20px;
    height: 20px;
    padding: 0;
    border: 0;
    border-radius: 50%;
    transform: translateY(-50%);
    background: transparent;
    color: var(--miller-text-muted);
    font-size: 11px;
    line-height: 20px;
    cursor: pointer;
}

.miller-clear-input:hover {
    background: var(--miller-bg-hover);
    color: var(--miller-text);
}

.miller-clear-input.visible { 
    display: block; 
}

.miller-selection-summary {
    padding: 6px 10px;
    overflow: hidden;
    border: 1px solid var(--miller-primary);
    border-radius: 4px;
    background: var(--miller-summary-bg);
    color: var(--miller-text);
    font-size: 12px;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.miller-selection-summary.cmd-info { 
    border-color: var(--miller-info); 
}

.miller-selection-summary.cmd-action { 
    border-color: var(--miller-warning); 
}

.miller-selection-summary .miller-selection-empty { 
    opacity: 0.6; 
    font-style: italic; 
}

.miller-selection-summary .miller-selection-sep { 
    margin: 0 4px; 
    opacity: 0.5; 
}

.miller-columns-wrapper {
    display: flex;
    flex: 1;
    overflow: hidden;
    border: 1px solid var(--miller-border);
    border-radius: 6px;
    background: var(--miller-bg);
}

.miller-col {
    display: flex;
    flex: 1;
    flex-direction: column;
    min-width: 0;
    border-right: 1px solid var(--miller-border-light);
}

.miller-col:last-child { 
    border-right: none; 
}

.miller-col-header {
    display: flex;
    flex-direction: column;
    gap: 5px;
    padding: 8px 12px;
    border-bottom: 1px solid var(--miller-border-light);
    background: var(--miller-bg-secondary);
}

.miller-col-title {
    color: var(--miller-text-muted);
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
}

.miller-col-filter {
    width: 100%;
    box-sizing: border-box;
    padding: 4px 32px 4px 8px;
    border: 1px solid var(--miller-border);
    border-radius: 3px;
    outline: none;
    background: var(--miller-bg-input);
    color: var(--miller-text);
    font-size: 12px;
}

.miller-col-content { 
    flex: 1; 
    overflow-y: auto; 
    padding: 4px 0;
}

.miller-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    overflow: hidden;
    padding: 8px 12px;
    color: var(--miller-text);
    font-size: 13px;
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.1s;
}

.miller-item:hover { 
    background: var(--miller-bg-hover);
}

.miller-item.selected { 
    background: var(--miller-primary) !important; 
    color: #fff !important; 
}

.miller-item .miller-item-name { 
    min-width: 0; 
    overflow: hidden; 
    text-overflow: ellipsis; 
}

.miller-item .badge-id {
    flex-shrink: 0;
    margin-left: 6px;
    padding: 2px 5px;
    border-radius: 3px;
    background: var(--miller-badge);
    color: var(--miller-text);
    font-size: 10px;
    opacity: 0.8;
}

.miller-item.cmd-action { 
    color: var(--miller-warning); 
}

.miller-item.cmd-info { 
    color: var(--miller-info); 
}

.miller-item.selected.cmd-action, .miller-item.selected.cmd-info { 
    color: #fff; 
}

.miller-search-result { 
    display: flex; 
    flex-direction: column; 
    width: 100%; 
    overflow: hidden; 
}

.miller-search-human, .miller-search-path { 
    overflow: hidden; 
    text-overflow: ellipsis; 
}
.miller-search-path { 
    margin-top: 2px; 
    color: var(--miller-text-muted); 
    font-size: 10px; 
}

.miller-item.miller-item-exact { 
    border-left: 3px solid var(--miller-primary); 
}

.miller-match {
    padding: 0 1px;
    border-radius: 2px;
    background: rgba(240, 173, 78, 0.35);
    color: inherit;
    font-weight: 700;
}

.miller-exact-badge {
    flex-shrink: 0;
    margin-left: 6px;
    padding: 1px 6px;
    border-radius: 3px;
    background: var(--miller-primary);
    color: #fff;
    font-size: 9px;
    font-weight: bold;
    text-transform: uppercase;
    white-space: nowrap;
}

.miller-empty, .miller-error { 
    padding: 12px; 
    font-size: 13px; 
}

.miller-empty { 
    color: var(--miller-text-muted); 
}

.miller-error { 
    color: var(--miller-danger); 
}

.miller-search-truncated {
    flex-shrink: 0;
    padding: 6px 12px;
    border-bottom: 1px solid var(--miller-border-light);
    background: var(--miller-bg-secondary);
    color: var(--miller-text-muted);
    font-size: 11px;
    font-style: italic;
    text-align: center;
}

.miller-item-subtype {
    flex-shrink: 0;
    margin-left: 4px;
    padding: 2px 5px;
    border-radius: 3px;
    background: var(--miller-bg-secondary);
    color: var(--miller-text-muted);
    font-size: 10px;
    opacity: 0.8;
}
</style>

<div id="div_cmdHumanInsert" class="miller-picker-container">
    <div class="miller-search-bar">
        <input type="text" id="in_cmdHumanInsertSearch" placeholder="{{Rechercher un objet, équipement, commande ou ID...}}" autocomplete="off">
        <span id="miller_search_loading" class="miller-search-loading"><i class="fas fa-spinner fa-spin"></i></span>
        <button type="button" class="miller-clear-input" id="clear_miller_search" aria-label="{{Effacer}}"><i class="fas fa-times"></i></button>
    </div>
    <div id="div_miller_selection_summary" class="miller-selection-summary"><span class="miller-selection-empty">{{Aucune sélection}}</span></div>
    <div class="miller-columns-wrapper">
        <div class="miller-col" id="col_miller_objects">
            <div class="miller-col-header">
                <span class="miller-col-title">{{Objets}}</span>
                <div class="miller-filter-wrapper">
                    <input type="text" class="miller-col-filter" id="filter_miller_objects" placeholder="{{Filtrer...}}" autocomplete="off">
                    <button type="button" class="miller-clear-input" aria-label="{{Effacer}}"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="miller-col-content" id="list_miller_objects"></div>
        </div>
        <div class="miller-col" id="col_miller_equipments">
            <div class="miller-col-header">
                <span class="miller-col-title">{{Équipements}}</span>
                <div class="miller-filter-wrapper">
                    <input type="text" class="miller-col-filter" id="filter_miller_equipments" placeholder="{{Filtrer...}}" autocomplete="off">
                    <button type="button" class="miller-clear-input" aria-label="{{Effacer}}"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="miller-col-content" id="list_miller_commands_eq"></div>
        </div>
        <div class="miller-col" id="col_miller_commands">
            <div class="miller-col-header">
                <span class="miller-col-title">{{Commandes}}</span>
                <div class="miller-filter-wrapper">
                    <input type="text" class="miller-col-filter" id="filter_miller_commands" placeholder="{{Filtrer...}}" autocomplete="off">
                    <button type="button" class="miller-clear-input" aria-label="{{Effacer}}"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="miller-search-truncated" id="div_miller_search_truncated" style="display: none;"></div>
            <div class="miller-col-content" id="list_miller_commands"></div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const SEARCH_MIN_LENGTH = 2;
    const SEARCH_DEBOUNCE_MS = 300;
    const FULL_HUMAN_NAME_RE = /^#\[([^[\]#]*)\]\[([^[\]#]*)\]\[([^[\]#]*)\]#$/;

    function alphaCompare(a, b) {
        return String(a || '').localeCompare(String(b || ''), 'fr', { sensitivity: 'base', numeric: true });
    }

    function normalizeText(value) {
        return String(value || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }

    if (window.mod_insertCmd === undefined) window.mod_insertCmd = {};

    mod_insertCmd.options = mod_insertCmd.options || {};
    mod_insertCmd.selectedCmd = null;

    const container = document.getElementById('div_cmdHumanInsert');
    if (!container) return;

    const searchInput = document.getElementById('in_cmdHumanInsertSearch');
    const clearSearchButton = document.getElementById('clear_miller_search');
    const searchLoading = document.getElementById('miller_search_loading');
    const objectColumn = document.getElementById('col_miller_objects');
    const equipmentColumn = document.getElementById('col_miller_equipments');
    const commandColumn = document.getElementById('col_miller_commands');
    const objectList = document.getElementById('list_miller_objects');
    const equipmentList = document.getElementById('list_miller_commands_eq');
    const commandList = document.getElementById('list_miller_commands');
    const objectFilterInput = document.getElementById('filter_miller_objects');
    const equipmentFilterInput = document.getElementById('filter_miller_equipments');
    const commandFilterInput = document.getElementById('filter_miller_commands');
    const searchTruncatedNote = document.getElementById('div_miller_search_truncated');
    const selectionSummary = document.getElementById('div_miller_selection_summary');

    let selectedObjectId = null;
    let selectedEqLogicId = null;
    let searchTimer = null;
    let searchRequestId = 0;
    let currentEqLogics = [];
    let currentCommands = [];
    let currentSearchResults = [];
    let searchHasMore = false;
    let pastedExactHumanName = null;
    let lastSearchQuery = '';
    let isSearchMode = false;
    let isInitialLoad = true;
    let objectFilterText = '';
    let equipmentFilterText = '';
    let commandFilterText = '';

    const objectSelectHtml = <?php echo json_encode(jeeObject::getUISelectList()); ?>;
    const objectOptions = (() => {
        const select = document.createElement('select');
        select.innerHTML = objectSelectHtml;
        return Array.from(select.options).map(option => ({
            id: String(option.value || ''),
            name: option.textContent.trim()
        })).sort((a, b) => alphaCompare(a.name, b.name));
    })();

    const objectsById = new Map(objectOptions.map(o => [o.id, o]));
    const escapeDiv = document.createElement('div');

    const CMD_ICONS = {
        info: {
            numeric: 'fa-tachometer-alt',
            binary: 'fa-toggle-on',
            string: 'fa-font',
            other: 'fa-info-circle'
        },
        action: {
            message: 'fa-comment',
            slider: 'fa-sliders-h',
            color: 'fa-palette',
            select: 'fa-list',
            toggle: 'fa-toggle-on',
            other: 'fa-bolt'
        }
    };

    function updateEllipsisTooltip(item) {
        const name = item.querySelector('.miller-item-name, .miller-search-human');
        if (!name) {
            item.removeAttribute('title');
            return;
        }
        requestAnimationFrame(() => {
            if (name.scrollWidth > name.clientWidth) item.title = name.textContent.trim();
            else item.removeAttribute('title');
        });
    }

    mod_insertCmd.setOptions = function (_options) {
        mod_insertCmd.options = _options || {};
        mod_insertCmd.options.cmd = mod_insertCmd.options.cmd || {};
        mod_insertCmd.options.eqLogic = mod_insertCmd.options.eqLogic || {};
        mod_insertCmd.options.object = mod_insertCmd.options.object || {};

        const type = mod_insertCmd.options.cmd.type || '';
        const headerTitle = container.querySelector('#col_miller_commands .miller-col-title');

        if (headerTitle) {
            headerTitle.textContent = type === 'info' ? '{{Commandes info}}' : type === 'action' ? '{{Commandes action}}' : '{{Commandes}}';
        }

        selectedObjectId = null;
        selectedEqLogicId = null;
        mod_insertCmd.selectedCmd = null;
        isInitialLoad = true;
        objectFilterText = '';
        equipmentFilterText = '';
        commandFilterText = '';

        [objectFilterInput, equipmentFilterInput, commandFilterInput].forEach(input => {
            if (input) {
                input.value = '';
                updateClearButton(input);
            }
        });

        renderObjects();
        updateSelectionSummary();
    };

    mod_insertCmd.getCmdId = function () {
        return mod_insertCmd.selectedCmd ? String(mod_insertCmd.selectedCmd.id || '') : null;
    };

    mod_insertCmd.getType = function () {
        return mod_insertCmd.selectedCmd ? String(mod_insertCmd.selectedCmd.type || '') : null;
    };

    mod_insertCmd.getSubType = function () {
        return mod_insertCmd.selectedCmd ? String(mod_insertCmd.selectedCmd.subType || '') : null;
    };

    mod_insertCmd.getName = function () {
        return mod_insertCmd.selectedCmd ? String(mod_insertCmd.selectedCmd.name || '') : '';
    };

    mod_insertCmd.getCmdHumanName = function () {
        if (!mod_insertCmd.selectedCmd) return '';
        const humanName = String(mod_insertCmd.selectedCmd.humanName || mod_insertCmd.selectedCmd.human || '');
        return humanName ? `#${humanName}#` : '';
    };

    mod_insertCmd.getValue = function () {
        return mod_insertCmd.getCmdHumanName();
    };

    mod_insertCmd.execute = function (cmd) {
        if (!cmd) return;

        mod_insertCmd.selectedCmd = cmd;
        const humanName = String(cmd.humanName || cmd.human || '');
        const formattedCmd = humanName ? `#${humanName}#` : '';

        if (mod_insertCmd.options && typeof mod_insertCmd.options.cmd === 'function') {
            mod_insertCmd.options.cmd(formattedCmd);
        } else if (typeof cmdHumanInsertCallBack === 'function') {
            cmdHumanInsertCallBack(formattedCmd);
        } else {
            const targetInput = container.targetInput || (container.dataset.input ? document.querySelector(container.dataset.input) : null);
            if (targetInput && typeof targetInput.insertAtCursor === 'function') targetInput.insertAtCursor(formattedCmd);
        }

        container.closest('.jeeDialog')?._jeeDialog?.close();
    };

    function selectObject(objectId) {
        const isNone = objectId === '';

        selectedObjectId = objectId;
        selectedEqLogicId = null;
        mod_insertCmd.selectedCmd = null;
        equipmentFilterText = '';
        commandFilterText = '';

        [equipmentFilterInput, commandFilterInput].forEach(input => {
            if (input) {
                input.value = '';
                updateClearButton(input);
            }
        });

        isInitialLoad = false;
        mod_insertCmd.options.object.id = objectId;

        renderObjects();
        updateSelectionSummary();
        clearEquipmentColumn();
        clearCommandColumn();

        if (isNone) {
            loadAllEquipments();
        } else {
            loadEquipments(objectId, false);
        }
    }

    function renderObjects() {
        objectList.innerHTML = '';

        let options = objectOptions;
        if (objectFilterText) {
            const needle = normalizeText(objectFilterText);
            options = options.filter(option => normalizeText(option.name).includes(needle));
        }

        if (!options.length) {
            objectList.innerHTML = objectFilterText ? '<div class="miller-empty">{{Aucun résultat}}</div>' : '<div class="miller-empty">{{Aucun objet}}</div>';
            return;
        }

        if (isInitialLoad && selectedObjectId === null) selectedObjectId = options[0].id;

        const fragment = document.createDocumentFragment();

        options.forEach(option => {
            const isNone = option.id === '';
            const div = document.createElement('div');
            div.className = `miller-item${selectedObjectId === option.id ? ' selected' : ''}`;
            div.dataset.objectId = option.id;
            div.innerHTML = `<span class="miller-item-name">${isNone ? '<i class="fas fa-ban"></i>' : '<i class="far fa-object-group"></i>'} ${highlightMatch(option.name, objectFilterText)}</span>`;
            fragment.appendChild(div);
            updateEllipsisTooltip(div);
        });

        objectList.appendChild(fragment);
        if (isInitialLoad) selectedObjectId === '' ? loadAllEquipments() : loadEquipments(selectedObjectId, false);
    }

    objectList.addEventListener('click', function (event) {
        const item = event.target.closest('.miller-item');
        if (item && objectList.contains(item)) selectObject(item.dataset.objectId);
    });

    function loadAllEquipments() {
        equipmentList.innerHTML = '<div class="miller-empty">{{Chargement...}}</div>';
        commandList.innerHTML = '';
        currentEqLogics = [];
        currentCommands = [];
        selectedEqLogicId = null;
        mod_insertCmd.selectedCmd = null;

        const cmdFilter = mod_insertCmd.options.cmd || {};

        jeedom.object.getEqLogic({
            id: -1,
            orderByName: true,
            onlyHasCmds: cmdFilter,
            error: function (error) {
                isInitialLoad = false;
                equipmentList.innerHTML = `<div class="miller-error">${escapeHtml(error && error.message ? error.message : '{{Erreur de chargement}}')}</div>`;
            },
            success: function (eqLogics) {
                currentEqLogics = Array.isArray(eqLogics) ? eqLogics : [];
                renderEquipments();

                if (isInitialLoad && currentEqLogics.length) {
                    selectedEqLogicId = String(currentEqLogics[0].id);
                    renderEquipments();
                    updateSelectionSummary();
                    loadCommands(selectedEqLogicId, false);
                    return;
                }

                updateSelectionSummary();
            }
        });
    }

    function loadEquipments(objectId, restoreSelection) {
        equipmentList.innerHTML = '<div class="miller-empty">{{Chargement...}}</div>';
        commandList.innerHTML = '';
        if (!restoreSelection) selectedEqLogicId = null;

        const cmdFilter = mod_insertCmd.options.cmd || {};

        jeedom.object.getEqLogic({
            id: objectId || -1,
            orderByName: true,
            onlyHasCmds: cmdFilter,
            error: function (error) {
                isInitialLoad = false;
                equipmentList.innerHTML = `<div class="miller-error">${escapeHtml(error && error.message ? error.message : '{{Erreur de chargement}}')}</div>`;
            },
            success: function (eqLogics) {
                currentEqLogics = Array.isArray(eqLogics) ? eqLogics : [];
                renderEquipments();

                if (isInitialLoad && currentEqLogics.length) {
                    selectedEqLogicId = String(currentEqLogics[0].id);
                    renderEquipments();
                    updateSelectionSummary();
                    loadCommands(selectedEqLogicId, false);
                    return;
                }

                if (selectedEqLogicId) {
                    const exists = currentEqLogics.some(eq => String(eq.id) === String(selectedEqLogicId));
                    if (exists) loadCommands(selectedEqLogicId, true);
                }
            }
        });
    }

    function renderEquipments() {
        equipmentList.innerHTML = '';
        if (!currentEqLogics.length) {
            equipmentList.innerHTML = '<div class="miller-empty">{{Aucun équipement}}</div>';
            return;
        }

        let visibleEqLogics = currentEqLogics;
        if (equipmentFilterText) {
            const needle = normalizeText(equipmentFilterText);
            visibleEqLogics = currentEqLogics.filter(eqLogic => normalizeText(eqLogic.name).includes(needle));
        }

        if (!visibleEqLogics.length) {
            equipmentList.innerHTML = '<div class="miller-empty">{{Aucun résultat}}</div>';
            return;
        }

        const fragment = document.createDocumentFragment();

        visibleEqLogics.forEach(eqLogic => {
            const eqId = String(eqLogic.id);
            const div = document.createElement('div');
            div.className = `miller-item${selectedEqLogicId === eqId ? ' selected' : ''}`;
            div.dataset.eqId = eqId;
            div.innerHTML = `<span class="miller-item-name"><i class="fas fa-puzzle-piece"></i> ${highlightMatch(String(eqLogic.name || ''), equipmentFilterText)}</span>`;
            fragment.appendChild(div);
            updateEllipsisTooltip(div);
        });

        equipmentList.appendChild(fragment);
    }

    function selectEquipment(eqId) {
        selectedEqLogicId = eqId;
        mod_insertCmd.options.eqLogic.id = eqId;
        mod_insertCmd.selectedCmd = null;
        commandFilterText = '';

        if (commandFilterInput) {
            commandFilterInput.value = '';
            updateClearButton(commandFilterInput);
        }

        isInitialLoad = false;
        renderEquipments();
        updateSelectionSummary();
        loadCommands(eqId, false);
    }

    equipmentList.addEventListener('click', function (event) {
        const item = event.target.closest('.miller-item');
        if (item && equipmentList.contains(item)) selectEquipment(item.dataset.eqId);
    });

    function loadCommands(eqLogicId, restoreSelection) {
        commandList.innerHTML = '<div class="miller-empty">{{Chargement...}}</div>';
        const filter = mod_insertCmd.options.cmd || {};
        if (!restoreSelection || !mod_insertCmd.options.cmd) mod_insertCmd.selectedCmd = null;

        jeedom.eqLogic.buildSelectCmd({
            id: eqLogicId,
            filter: filter,
            error: function (error) {
                isInitialLoad = false;
                commandList.innerHTML = `<div class="miller-error">${escapeHtml(error && error.message ? error.message : '{{Erreur de chargement}}')}</div>`;
            },
            success: function (html) {
                const select = document.createElement('select');
                select.innerHTML = html || '';

                currentCommands = Array.from(select.options).map(option => ({
                    id: String(option.value || ''),
                    name: String(option.textContent || '').trim(),
                    type: String(option.getAttribute('data-type') || ''),
                    subType: String(option.getAttribute('data-subType') || ''),
                    humanName: ''
                }));

                const objName = getSelectedObjectName();
                const eqName = getSelectedEqLogicName();
                currentCommands.forEach(cmd => {
                    cmd.humanName = `[${objName}][${eqName}][${cmd.name}]`;
                });

                currentCommands.sort((a, b) => alphaCompare(a.name, b.name));
                renderCommands();

                if (restoreSelection && mod_insertCmd.options.cmd && mod_insertCmd.options.cmd.id) {
                    const wantedId = String(mod_insertCmd.options.cmd.id);
                    const cmd = currentCommands.find(item => String(item.id) === wantedId);

                    if (cmd) {
                        mod_insertCmd.selectedCmd = cmd;
                        renderCommands();
                        updateSelectionSummary();
                        return;
                    }
                }

                if (isInitialLoad && currentCommands.length) {
                    mod_insertCmd.selectedCmd = currentCommands[0];
                    renderCommands();
                    updateSelectionSummary();
                    isInitialLoad = false;
                }
            }
        });
    }

    function getCommandIcon(cmd) {
        const icon = CMD_ICONS[cmd.type]?.[cmd.subType] || CMD_ICONS[cmd.type]?.other || 'fa-info-circle';
        return `<i class="fas ${icon}"></i>`;
    }

    function renderCommands() {
        commandList.innerHTML = '';
        searchTruncatedNote.style.display = 'none';

        if (!currentCommands.length) {
            commandList.innerHTML = '<div class="miller-empty">{{Aucune commande}}</div>';
            return;
        }

        let visibleCommands = currentCommands;
        if (commandFilterText) {
            const needle = normalizeText(commandFilterText);
            visibleCommands = currentCommands.filter(cmd => normalizeText(cmd.name).includes(needle));
        }

        if (!visibleCommands.length) {
            commandList.innerHTML = '<div class="miller-empty">{{Aucun résultat}}</div>';
            return;
        }

        const fragment = document.createDocumentFragment();

        visibleCommands.forEach(cmd => {
            const isSelected = mod_insertCmd.selectedCmd && String(mod_insertCmd.selectedCmd.id) === String(cmd.id);
            const div = document.createElement('div');
            const icon = getCommandIcon(cmd);

            div.className = `miller-item cmd-${cmd.type || 'info'}${isSelected ? ' selected' : ''}`;
            div.dataset.cmdId = String(cmd.id);
            div.innerHTML = `<span class="miller-item-name">${icon} ${highlightMatch(cmd.name, commandFilterText)}</span><span class="badge-id">ID ${escapeHtml(cmd.id)}</span>`;
            fragment.appendChild(div);
            updateEllipsisTooltip(div);
        });

        commandList.appendChild(fragment);
    }

    function searchCommands(query) {
        const requestId = ++searchRequestId;
        lastSearchQuery = query;
        searchLoading.style.display = 'block';
        commandList.innerHTML = '<div class="miller-empty">{{Recherche...}}</div>';

        const cmdFilter = mod_insertCmd.options.cmd || {};

        domUtils.ajax({
            type: 'POST',
            url: 'core/ajax/cmd.human.insert.ajax.php',
            data: {
                action: 'search',
                query: query,
                type: cmdFilter.type || '',
                subType: cmdFilter.subType || '',
                limit: 100
            },
            dataType: 'json',
            global: false,
            error: function (error) {
                if (requestId !== searchRequestId) return;
                searchLoading.style.display = 'none';

                let message = '{{Erreur lors de la recherche}}';
                if (typeof error === 'string') message = error;
                else if (error && error.message) message = error.message;
                else if (error && error.responseJSON && error.responseJSON.message) message = error.responseJSON.message;

                commandList.innerHTML = `<div class="miller-error">${escapeHtml(message)}</div>`;
            },
            success: function (data) {
                if (requestId !== searchRequestId) return;
                searchLoading.style.display = 'none';

                let results = [];
                if (data && Array.isArray(data.result)) results = data.result;
                else if (Array.isArray(data)) results = data;

                searchHasMore = false;
                const lastResult = results[results.length - 1];

                if (lastResult && lastResult.truncated) {
                    searchHasMore = true;
                    results = results.slice(0, -1);
                }

                results.sort((a, b) => {
                    if (pastedExactHumanName) {
                        const aExact = a.humanName === pastedExactHumanName;
                        const bExact = b.humanName === pastedExactHumanName;
                        if (aExact !== bExact) return aExact ? -1 : 1;
                    }
                    return alphaCompare(a.humanName, b.humanName);
                });

                currentSearchResults = results;

                if (pastedExactHumanName) {
                    const exactMatch = results.find(cmd => cmd.humanName === pastedExactHumanName);
                    if (exactMatch) {
                        mod_insertCmd.selectedCmd = exactMatch;
                        updateSelectionSummary();
                    }
                }

                renderSearchResults();
            }
        });
    }

    function updateSearchTruncatedNote() {
        if (!searchHasMore) {
            searchTruncatedNote.style.display = 'none';
            return;
        }

        searchTruncatedNote.textContent = `{{Plus de}} ${currentSearchResults.length} {{résultats — affinez votre recherche pour voir le reste}}`;
        searchTruncatedNote.style.display = 'block';
    }

    function renderSearchResults() {
        commandList.innerHTML = '';
        updateSearchTruncatedNote();

        let results = currentSearchResults;
        if (commandFilterText) {
            const needle = normalizeText(commandFilterText);
            results = results.filter(cmd => normalizeText(cmd.humanName || cmd.name).includes(needle));
        }

        if (!results.length) {
            commandList.innerHTML = currentSearchResults.length
                ? '<div class="miller-empty">{{Aucun résultat pour ce filtre}}</div>'
                : '<div class="miller-empty">{{Aucun résultat}}</div>';
            return;
        }

        const fragment = document.createDocumentFragment();

        results.forEach(cmd => {
            const div = document.createElement('div');
            const isSelected = mod_insertCmd.selectedCmd && String(mod_insertCmd.selectedCmd.id) === String(cmd.id);
            const isExactMatch = Boolean(pastedExactHumanName) && cmd.humanName === pastedExactHumanName;
            const icon = getCommandIcon(cmd);
            const exactBadge = isExactMatch ? '<span class="miller-exact-badge">{{Correspondance exacte}}</span>' : '';

            div.className = `miller-item cmd-${cmd.type || 'info'}${isSelected ? ' selected' : ''}${isExactMatch ? ' miller-item-exact' : ''}`;
            div.dataset.cmdId = String(cmd.id);

            const highlightQuery = commandFilterText || lastSearchQuery;
            div.innerHTML = `<div class="miller-search-result"><div class="miller-search-human">${icon} ${highlightMatch(cmd.humanName || '', highlightQuery)}${exactBadge}</div><div class="miller-search-path">ID ${escapeHtml(String(cmd.id || ''))}</div></div>`;

            fragment.appendChild(div);
            updateEllipsisTooltip(div);
        });

        commandList.appendChild(fragment);
    }

    commandList.addEventListener('click', function (event) {
        const item = event.target.closest('.miller-item');
        if (!item || !commandList.contains(item)) return;

        const cmdId = item.dataset.cmdId;
        const source = isSearchMode ? currentSearchResults : currentCommands;
        const cmd = source.find(c => String(c.id) === cmdId);
        if (!cmd) return;

        mod_insertCmd.selectedCmd = cmd;

        if (isSearchMode) {
            commandList.querySelectorAll('.miller-item.selected').forEach(el => el.classList.remove('selected'));
            item.classList.add('selected');
        } else {
            renderCommands();
        }

        updateSelectionSummary();
    });

    commandList.addEventListener('dblclick', function (event) {
        const item = event.target.closest('.miller-item');
        if (!item || !commandList.contains(item)) return;

        const cmdId = item.dataset.cmdId;
        const source = isSearchMode ? currentSearchResults : currentCommands;
        const cmd = source.find(c => String(c.id) === cmdId);
        if (cmd) mod_insertCmd.execute(cmd);
    });

    function updateClearButton(input) {
        const button = input?.parentElement?.querySelector('.miller-clear-input');
        if (button) button.classList.toggle('visible', Boolean(input.value));
    }

    function clearInput(input) {
        if (!input) return;
        input.value = '';
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.focus();
    }

    if (clearSearchButton) clearSearchButton.addEventListener('click', () => clearInput(searchInput));

    [objectFilterInput, equipmentFilterInput, commandFilterInput].forEach(input => {
        if (!input) return;
        const clearButton = input.parentElement?.querySelector('.miller-clear-input');
        if (clearButton) clearButton.addEventListener('click', () => clearInput(input));
    });

    searchInput.addEventListener('input', function () {
        updateClearButton(searchInput);
        const query = searchInput.value.trim();
        clearTimeout(searchTimer);

        if (query.length < SEARCH_MIN_LENGTH) {
            ++searchRequestId;

            if (isSearchMode) {
                isSearchMode = false;
                currentSearchResults = [];
                searchHasMore = false;
                pastedExactHumanName = null;
                commandFilterText = '';

                if (commandFilterInput) {
                    commandFilterInput.value = '';
                    updateClearButton(commandFilterInput);
                }
            }

            searchLoading.style.display = 'none';
            objectColumn.style.display = 'flex';
            equipmentColumn.style.display = 'flex';
            commandColumn.style.display = 'flex';

            commandList.innerHTML = query ? '<div class="miller-empty">{{Tapez au moins 2 caractères...}}</div>' : '';
            renderObjects();

            if (selectedObjectId !== null) selectedObjectId === '' ? loadAllEquipments() : loadEquipments(selectedObjectId, true);
            return;
        }

        if (!isSearchMode) {
            isSearchMode = true;
            commandFilterText = '';

            if (commandFilterInput) {
                commandFilterInput.value = '';
                updateClearButton(commandFilterInput);
            }
        }

        objectColumn.style.display = 'none';
        equipmentColumn.style.display = 'none';
        commandColumn.style.display = 'flex';

        const fullMatch = FULL_HUMAN_NAME_RE.exec(query);
        if (fullMatch) {
            pastedExactHumanName = `[${fullMatch[1]}][${fullMatch[2]}][${fullMatch[3]}]`;
            searchCommands(pastedExactHumanName);
            return;
        }

        pastedExactHumanName = null;
        searchTimer = setTimeout(() => searchCommands(query), SEARCH_DEBOUNCE_MS);
    });

    if (objectFilterInput) {
        objectFilterInput.addEventListener('input', function () {
            updateClearButton(objectFilterInput);
            objectFilterText = objectFilterInput.value.trim();
            renderObjects();
        });
    }

    if (equipmentFilterInput) {
        equipmentFilterInput.addEventListener('input', function () {
            updateClearButton(equipmentFilterInput);
            equipmentFilterText = equipmentFilterInput.value.trim();
            renderEquipments();
        });
    }

    if (commandFilterInput) {
        commandFilterInput.addEventListener('input', function () {
            updateClearButton(commandFilterInput);
            commandFilterText = commandFilterInput.value.trim();
            isSearchMode ? renderSearchResults() : renderCommands();
        });
    }

    function clearEquipmentColumn() {
        currentEqLogics = [];
        equipmentList.innerHTML = '';
    }

    function clearCommandColumn() {
        currentCommands = [];
        commandList.innerHTML = '';
    }

    function getSelectedObjectName() {
        return getObjectNameById(selectedObjectId);
    }

    function getSelectedEqLogicName() {
        const eq = currentEqLogics.find(item => String(item.id) === String(selectedEqLogicId));
        return eq ? String(eq.name || '') : '';
    }

    function getObjectNameById(id) {
        const option = objectsById.get(String(id));
        return option ? option.name : '';
    }

    function updateSelectionSummary() {
        if (!selectionSummary) return;

        if (mod_insertCmd.selectedCmd) {
            const cmd = mod_insertCmd.selectedCmd;
            const icon = cmd.type === 'action' ? '<i class="fas fa-bolt"></i>' : '<i class="fas fa-info-circle"></i>';

            selectionSummary.classList.remove('cmd-info', 'cmd-action');
            if (cmd.type === 'info' || cmd.type === 'action') selectionSummary.classList.add(`cmd-${cmd.type}`);

            const path = String(cmd.humanName || cmd.name || '')
                .replace(/^\[/, '')
                .replace(/\]$/, '')
                .split('][')
                .map(part => escapeHtml(part))
                .join('<span class="miller-selection-sep">›</span>');

            selectionSummary.innerHTML = `${icon} ${path}`;
            return;
        }

        selectionSummary.classList.remove('cmd-info', 'cmd-action');

        const parts = [];

        if (selectedObjectId !== null) {
            const objName = getObjectNameById(selectedObjectId);
            if (objName) {
                parts.push(`${selectedObjectId === '' ? '<i class="fas fa-ban"></i>' : '<i class="far fa-object-group"></i>'} ${escapeHtml(objName)}`);
            }
        }

        if (selectedEqLogicId) {
            const eqName = getSelectedEqLogicName();
            if (eqName) parts.push(`<i class="fas fa-puzzle-piece"></i> ${escapeHtml(eqName)}`);
        }

        selectionSummary.innerHTML = parts.length
            ? parts.join('<span class="miller-selection-sep">›</span>')
            : '<span class="miller-selection-empty">{{Aucune sélection}}</span>';
    }

    function escapeHtml(value) {
        escapeDiv.textContent = String(value ?? '');
        return escapeDiv.innerHTML;
    }

    function highlightMatch(text, query) {
        const value = String(text ?? '');
        if (!query) return escapeHtml(value);

        const normalizedValue = normalizeText(value);
        const normalizedQuery = normalizeText(query);
        if (!normalizedQuery || normalizedValue.length !== value.length) return escapeHtml(value);

        const startIndex = normalizedValue.indexOf(normalizedQuery);
        if (startIndex === -1) return escapeHtml(value);

        const endIndex = startIndex + normalizedQuery.length;
        const before = value.slice(0, startIndex);
        const match = value.slice(startIndex, endIndex);
        const after = value.slice(endIndex);

        return `${escapeHtml(before)}<strong class="miller-match">${escapeHtml(match)}</strong>${escapeHtml(after)}`;
    }

    renderObjects();
    updateSelectionSummary();
    updateClearButton(searchInput);
    updateClearButton(objectFilterInput);
    updateClearButton(equipmentFilterInput);
    updateClearButton(commandFilterInput);
    
    setTimeout(() => searchInput.focus(), 100);
})();
</script>