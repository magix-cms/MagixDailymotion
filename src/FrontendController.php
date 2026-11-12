<?php
declare(strict_types=1);

namespace Plugins\MagixDailymotion\src;

/*
 * This file is part of MAGIX CMS.
 * Copyright (C) 2008 - 2026 Gerits Aurelien (Magix CMS).
 * License: GPLv3
 */

use Plugins\MagixDailymotion\db\DailymotionFrontDb;
use Magepattern\Component\Tool\SmartyTool;
use App\Component\Db\PluginDb;

class FrontendController
{
    private static array $targetsCache = [];

    // ==========================================
    // 1. LAYOUT DYNAMIQUE (VIDÉOS PUBLIQUES)
    // ==========================================

    public static function renderWidget(array $params = []): string
    {
        if (empty(self::$targetsCache)) {
            $pluginDb = new PluginDb();
            self::$targetsCache = $pluginDb->getPluginTargets('MagixDailymotion');
        }

        if (empty(self::$targetsCache)) return '';

        $hookName = $params['name'] ?? '';

        if ($hookName === 'displayPageBottom') {
            if (empty(self::$targetsCache['pages'])) return '';
            return self::processRender($params, 'pages', 'id_pages', 'public');
        }
        if ($hookName === 'displayAboutBottom') {
            if (empty(self::$targetsCache['about'])) return '';
            return self::processRender($params, 'about', 'id_about', 'public');
        }
        if ($hookName === 'displayProductExtraContent') {
            if (empty(self::$targetsCache['product'])) return '';
            return self::processRender($params, 'product', 'id_product', 'public');
        }
        if ($hookName === 'displayCategoryBottom') {
            if (empty(self::$targetsCache['category'])) return '';
            return self::processRender($params, 'category', 'id_cat', 'public');
        }
        if ($hookName === 'displayNewsBottom') {
            if (empty(self::$targetsCache['news'])) return '';
            return self::processRender($params, 'news', 'id_news', 'public');
        }

        return '';
    }

    // ==========================================
    // 2. HOOK MANUEL (VIDÉOS PRIVÉES DÉBLOQUÉES)
    // ==========================================

    public static function renderPrivateVideos(array $params = []): string
    {
        if (empty(self::$targetsCache)) {
            $pluginDb = new PluginDb();
            self::$targetsCache = $pluginDb->getPluginTargets('MagixDailymotion');
        }

        if (empty(self::$targetsCache['product'])) return '';

        return self::processRender($params, 'product', 'id_product', 'private');
    }

    /**
     * Méthode métier de rendu (Public et Privé)
     */
    private static function processRender(array $params, string $itemType, string $idKey, string $visibility = 'public'): string
    {
        try {
            $view = SmartyTool::getInstance('front');

            $langData = $view->getTemplateVars('current_lang') ?: $view->getTemplateVars('lang') ?: ['id_lang' => 1];
            $idLang = (int)($langData['id_lang'] ?? 1);

            $itemId = (int)($params[$idKey] ?? 0);
            if ($itemId === 0) return '';

            $db = new DailymotionFrontDb();
            $videos = ($visibility === 'private')
                ? $db->getPrivateVideos($itemType, $itemId)
                : $db->getPublicVideos($itemType, $itemId);

            if (empty($videos)) return '';

            $template = ROOT_DIR . 'plugins' . DS . 'MagixDailymotion' . DS . 'views' . DS . 'front' . DS . 'widget.tpl';
            if (!file_exists($template)) return '';

            // 1. Assignation des variables à Smarty
            $view->assign('dm_widget_data', [
                'module'     => $itemType,
                'visibility' => $visibility,
                'items'      => $videos
            ]);

            // 2. Cache ID distinct entre public et privé
            $cacheId = 'dailymotion_' . $visibility . '_' . $itemType . '_' . $itemId . '_' . $idLang;

            // 3. Rendu avec l'identifiant de cache
            return $view->fetch($template, $cacheId);

        } catch (\Throwable $e) {
            return "";
        }
    }
}