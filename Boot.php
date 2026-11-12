<?php
declare(strict_types=1);

namespace Plugins\MagixDailymotion;

/*
 * This file is part of MAGIX CMS.
 * Copyright (C) 2008 - 2026 Gerits Aurelien (Magix CMS).
 * License: GPLv3
 */

use App\Component\Hook\HookManager;
use App\Component\Db\PluginDb;
use Magepattern\Component\Tool\SmartyTool;
use Plugins\MagixDailymotion\src\FrontendController;

class Boot
{
    private array $targetModules = [
        'product'  => 'id_product',
        'pages'    => 'id_pages',
        'category' => 'id_cat',
        'news'     => 'id_news',
        'about'    => 'id_about'
    ];

    public function register(): void
    {
        // 1. Coupe-circuit global via la base de données
        $pluginDb = new PluginDb();
        $activeTargets = $pluginDb->getPluginTargets('MagixDailymotion');

        if (empty($activeTargets)) {
            return;
        }

        // ==========================================
        // 1. HOOKS BACKEND (Administration conditionnelle)
        // ==========================================
        foreach ($this->targetModules as $module => $idKey) {
            if (isset($activeTargets[$module]) && $activeTargets[$module] == 1) {

                HookManager::register("{$module}_edit_tab", 'MagixDailymotion', function(array $params) use ($module) {
                    $smarty = SmartyTool::getInstance('admin');
                    $smarty->assign('dm_module', $module);
                    $file = ROOT_DIR . 'plugins' . DS . 'MagixDailymotion' . DS . 'views' . DS . 'admin' . DS . 'hooks' . DS . 'tab_button.tpl';
                    return $smarty->templateExists($file) ? $smarty->fetch($file) : '';
                });

                HookManager::register("{$module}_edit_content", 'MagixDailymotion', function(array $params) use ($module, $idKey) {
                    $smarty = SmartyTool::getInstance('admin');
                    $idModule = (int)($params[$idKey] ?? 0);

                    if ($idModule <= 0) return '';

                    $smarty->assign([
                        'dm_module'    => $module,
                        'dm_id_module' => $idModule
                    ]);

                    $file = ROOT_DIR . 'plugins' . DS . 'MagixDailymotion' . DS . 'views' . DS . 'admin' . DS . 'hooks' . DS . 'tab_content.tpl';
                    return $smarty->templateExists($file) ? $smarty->fetch($file) : '';
                });
            }
        }

        // ==========================================
        // 2. HOOK FRONTEND PRIVÉ (Pour le bloc {if $user_owns_product})
        // ==========================================
        if (!empty($activeTargets['product'])) {
            HookManager::register('displayProductPrivateVideos', 'MagixDailymotion', function(array $params) {
                return FrontendController::renderPrivateVideos($params);
            });
        }
    }
}