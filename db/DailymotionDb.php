<?php
declare(strict_types=1);

namespace Plugins\MagixDailymotion\db;

/*
 * This file is part of MAGIX CMS.
 * Copyright (C) 2008 - 2026 Gerits Aurelien (Magix CMS).
 * License: GPLv3
 */

use App\Backend\Db\BaseDb;
use Magepattern\Component\Database\QueryBuilder;

class DailymotionDb extends BaseDb
{
    /**
     * Récupère la configuration de l'API Dailymotion.
     */
    public function getAuthentication(): array|false
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_dailymotion_config')->limit(1);
        return $this->executeRow($qb);
    }

    /**
     * Sauvegarde ou met à jour la configuration de l'API Dailymotion.
     */
    public function saveAuthentication(array $data): bool
    {
        $existing = $this->getAuthentication();
        $qb = new QueryBuilder();

        if ($existing && !empty($existing['id_dm'])) {
            // Mise à jour de la configuration existante
            $qb->update('mc_dailymotion_config', $data)
                ->where('id_dm = :id', ['id' => (int)$existing['id_dm']]);
            return $this->executeUpdate($qb);
        } else {
            // Première insertion
            $qb->insert('mc_dailymotion_config', $data);
            return $this->executeInsert($qb);
        }
    }

    /**
     * Récupère toutes les vidéos liées à un module spécifique.
     */
    public function fetchVideosByItem(string $itemType, int $itemId): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')
            ->from('mc_dailymotion_item')
            ->where('item_type = :type AND item_id = :id', [
                'type' => $itemType,
                'id'   => $itemId
            ])
            ->orderBy('order_pdn', 'ASC');

        return $this->executeAll($qb) ?: [];
    }

    /**
     * Récupère les détails d'une vidéo spécifique.
     */
    public function getVideoById(int $idVideo): array|false
    {
        $qb = new QueryBuilder();
        $qb->select('*')
            ->from('mc_dailymotion_item')
            ->where('id_dm_item = :id', ['id' => $idVideo]);

        return $this->executeRow($qb);
    }

    /**
     * Insère une nouvelle vidéo et gère l'ordre automatiquement.
     */
    public function insertVideo(array $data): int|false
    {
        $qbCount = new QueryBuilder();
        $qbCount->select(['COUNT(id_dm_item) as total'])
            ->from('mc_dailymotion_item')
            ->where('item_type = :type AND item_id = :id', [
                'type' => $data['item_type'],
                'id'   => $data['item_id']
            ]);

        $countResult = $this->executeRow($qbCount);
        $data['order_pdn'] = (int)($countResult['total'] ?? 0);

        $qb = new QueryBuilder();
        $qb->insert('mc_dailymotion_item', $data);

        return $this->executeInsert($qb) ? (int)$this->getLastInsertId() : false;
    }

    /**
     * Met à jour l'ordre des vidéos.
     */
    public function reorderVideo(array $videoIds): bool
    {
        $success = true;
        foreach ($videoIds as $index => $id) {
            $qb = new QueryBuilder();
            $qb->update('mc_dailymotion_item', ['order_pdn' => $index])
                ->where('id_dm_item = :id', ['id' => (int)$id]);

            if (!$this->executeUpdate($qb)) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * Supprime une vidéo de la BDD.
     */
    public function deleteVideo(int $idVideo): bool
    {
        $qb = new QueryBuilder();
        $qb->delete('mc_dailymotion_item')->where('id_dm_item = :id', ['id' => $idVideo]);
        return $this->executeDelete($qb);
    }
}