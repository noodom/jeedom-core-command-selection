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

try {
    require_once __DIR__ . '/../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');

    if (!isConnect()) {
        throw new Exception(__('401 - Accès non autorisé', __FILE__), -1234);
    }

    ajax::init();
    $action = init('action');

    if ($action == 'search') {
        $query = trim(init('query'));
        $type = trim(init('type'));
        $subType = trim(init('subType'));
        $limit = intval(init('limit'));
        $limit = $limit <= 0 ? 100 : min($limit, 500);

        $result = [];
        $queryLower = mb_strtolower($query, 'UTF-8');

        foreach (cmd::all() as $cmd) {
            if (!is_object($cmd)) {
                continue;
            }

            if ($type !== '' && $cmd->getType() !== $type) {
                continue;
            }

            if ($subType !== '' && $cmd->getSubType() !== $subType) {
                continue;
            }

            $id = (string) $cmd->getId();
            $name = (string) $cmd->getName();
            $eqLogic = $cmd->getEqLogic();

            if (!is_object($eqLogic)) {
                continue;
            }

            $object = $eqLogic->getObject();
            $objectName = is_object($object) ? (string) $object->getName() : '{{Sans Objet}}';
            $eqLogicName = (string) $eqLogic->getName();
            $humanName = '[' . $objectName . '][' . $eqLogicName . '][' . $name . ']';

            if ($query !== '') {
                $idMatch = $id === $query;
                $nameMatch = mb_strpos(mb_strtolower($name, 'UTF-8'), $queryLower) !== false;
                $humanMatch = mb_strpos(mb_strtolower($humanName, 'UTF-8'), $queryLower) !== false;

                if (!$idMatch && !$nameMatch && !$humanMatch) {
                    continue;
                }
            }

            $result[] = [
                'id' => $id,
                'name' => $name,
                'humanName' => $humanName,
                'type' => (string) $cmd->getType(),
                'subType' => (string) $cmd->getSubType(),
                'object_id' => is_object($object) ? (string) $object->getId() : '',
                'object_name' => $objectName,
                'eqLogic_id' => (string) $eqLogic->getId(),
                'eqLogic_name' => $eqLogicName
            ];

            if (count($result) >= $limit) {
                break;
            }
        }

        ajax::success($result);
    }

    if ($action == 'getEqLogicsWithoutObject') {
        if (!isConnect()) {
            throw new Exception('{{401 - Accès non autorisé}}');
        }

        $result = [];

        foreach (eqLogic::all() as $eqLogic) {
            if ($eqLogic->getObject_id() === null) {
                $result[] = [
                    'id' => $eqLogic->getId(),
                    'name' => $eqLogic->getName(),
                    'logicalId' => $eqLogic->getLogicalId(),
                    'eqType_name' => $eqLogic->getEqType_name()
                ];
            }
        }

        usort($result, function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        ajax::success($result);
    }

    throw new Exception(__('Aucune méthode correspondante à :', __FILE__) . ' ' . $action);
} catch (Exception $e) {
    ajax::error(displayException($e), $e->getCode());
}