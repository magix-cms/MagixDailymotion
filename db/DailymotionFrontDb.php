<?php
declare(strict_types=1);

namespace Plugins\MagixDailymotion\db;

/*
 * This file is part of MAGIX CMS.
 * Copyright (C) 2008 - 2026 Gerits Aurelien (Magix CMS).
 * License: GPLv3
 */

use App\Frontend\Db\BaseDb;
use Magepattern\Component\Database\QueryBuilder;

class DailymotionFrontDb extends BaseDb
{
    /**
     * Récupère les vidéos strictement "publiques" pour un élément précis (Layout Dynamique).
     */
    public function getPublicVideos(string $itemType, int $itemId): array
    {
        $cache = $this->getSqlCache();
        $qb = new QueryBuilder();

        $qb->select([
            'id_dm_item',
            'name_pdn',
            'video_id_pdn',
            'thumbnail_720_url',
            'visibility_pdm'
        ])
            ->from('mc_dailymotion_item')
            ->where('item_type = :item_type', ['item_type' => $itemType])
            ->where('item_id = :item_id', ['item_id' => $itemId])
            ->where("visibility_pdm = 'public'")
            ->orderBy('order_pdn', 'ASC');

        $cacheKey = $cache->generateKey($qb->getSql(), $qb->getParams(), 'magixdailymotion');

        if (($data = $cache->get($cacheKey)) !== null) {
            return $data;
        }

        $results = $this->executeAll($qb) ?: [];
        $cache->set($cacheKey, $results, 86400);

        return $results;
    }

    /**
     * Récupère les vidéos strictement "privées" (avec leur private_id) pour les produits achetés.
     */
    public function getPrivateVideos(string $itemType, int $itemId): array
    {
        $cache = $this->getSqlCache();
        $qb = new QueryBuilder();

        $qb->select([
            'id_dm_item',
            'name_pdn',
            'video_id_pdn',
            'private_id',
            'thumbnail_720_url',
            'visibility_pdm'
        ])
            ->from('mc_dailymotion_item')
            ->where('item_type = :item_type', ['item_type' => $itemType])
            ->where('item_id = :item_id', ['item_id' => $itemId])
            ->where("visibility_pdm = 'private'")
            ->orderBy('order_pdn', 'ASC');

        $cacheKey = $cache->generateKey($qb->getSql(), $qb->getParams(), 'magixdailymotion');

        if (($data = $cache->get($cacheKey)) !== null) {
            return $data;
        }

        $results = $this->executeAll($qb) ?: [];
        $cache->set($cacheKey, $results, 86400);

        return $results;
    }
}