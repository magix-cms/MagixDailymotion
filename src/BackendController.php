<?php

declare(strict_types=1);

namespace Plugins\MagixDailymotion\src;

/*
 * This file is part of MAGIX CMS.
 * Copyright (C) 2008 - 2026 Gerits Aurelien (Magix CMS).
 * License: GPLv3
 */

use App\Backend\Controller\BaseController;
use Plugins\MagixDailymotion\db\DailymotionDb;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\SmartyTool;
use Magepattern\Component\Tool\FormTool;
use App\Component\Cache\CacheManager;
use App\Component\File\VideoUploadTool;

class BackendController extends BaseController
{
    public function run(): void
    {
        SmartyTool::addTemplateDir('dailymotion', ROOT_DIR . 'plugins' . DS . 'MagixDailymotion' . DS . 'views' . DS . 'admin');

        $action = $_GET['action'] ?? 'index';

        // 1. Interception de la sauvegarde de configuration (comme le plugin Contact)
        if ($action === 'saveConfig' && Request::isMethod('POST')) {
            $this->processSaveConfig();
            return;
        }

        // 2. Routage dynamique pour les actions du widget
        if (method_exists($this, $action)) {
            $this->$action();
        } else {
            $this->index();
        }
    }

    /**
     * Affiche la page de configuration principale du plugin.
     */
    private function index(): void
    {
        $db = new DailymotionDb();
        $config = $db->getAuthentication(); // Récupère la config actuelle

        $this->view->assign([
            'dm_config' => $config,
            'hashtoken' => $this->session->getToken()
        ]);

        $this->view->display('index.tpl');
    }

    /**
     * Traite la sauvegarde des identifiants Dailymotion.
     */
    private function processSaveConfig(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $db = new DailymotionDb();

        $data = [
            'apikey_dm'    => FormTool::simpleClean($_POST['apikey_dm'] ?? ''),
            'apisecret_dm' => ($_POST['apisecret_dm'] ?? ''),
            'username_dm'  => FormTool::simpleClean($_POST['username_dm'] ?? ''),
            'password_dm'  => FormTool::simpleClean($_POST['password_dm'] ?? '')
        ];

        // Méthode à ajouter dans DailymotionDb pour insérer ou mettre à jour la ligne unique (id_dm = 1)
        if ($db->saveAuthentication($data)) {
            $this->jsonResponse(true, 'Configuration Dailymotion enregistrée avec succès.', ['type' => 'update']);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la sauvegarde de la configuration.');
        }
    }

    // ==========================================
    // MÉTHODES DU WIDGET (AJAX)
    // ==========================================

    public function loadList(): void
    {
        if (ob_get_length()) ob_clean();

        $itemType = $_GET['module'] ?? '';
        $itemId = (int)($_GET['id_module'] ?? 0);

        if (empty($itemType) || $itemId === 0) {
            echo '<div class="alert alert-warning">Paramètres manquants.</div>';
            return;
        }

        $db = new DailymotionDb();
        $videos = $db->fetchVideosByItem($itemType, $itemId);

        $columns = [
            'thumbnail_360_url' => ['title' => 'Aperçu', 'type' => 'image', 'class' => 'img-thumbnail', 'width' => '120px'],
            'name_pdn'          => ['title' => 'Nom', 'type' => 'text', 'class' => 'fw-bold text-dark'],
            'visibility_pdm'    => ['title' => 'Visibilité', 'type' => 'status', 'class' => 'text-center']
        ];

        $this->view->assign([
            'videos'       => $videos,
            'ajax_columns' => $columns,
            'item_type'    => $itemType,
            'item_id'      => $itemId,
            'hashtoken'    => $this->session->getToken()
        ]);

        $this->view->display('ajax/manager.tpl');
    }

    public function upload(): void
    {
        if (ob_get_length()) ob_clean();

        $token = Request::isPost('hashtoken') ? $_POST['hashtoken'] : '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $itemType   = FormTool::simpleClean($_POST['module_dm'] ?? '');
        $itemId     = (int)($_POST['id_module'] ?? 0);
        $visibility = FormTool::simpleClean($_POST['video_visibility'] ?? 'private');

        // AJOUT : Récupération du titre personnalisé
        $customTitle = FormTool::simpleClean($_POST['video_title'] ?? '');

        if (empty($itemType) || $itemId === 0) {
            $this->jsonResponse(false, 'Les références du module sont obligatoires.');
        }

        $uploadTool = new VideoUploadTool();
        $uploadResult = $uploadTool->singleVideoUpload('upload', ['video', $itemType, (string)$itemId]);

        if (!$uploadResult['status']) {
            $this->jsonResponse(false, $uploadResult['msg']);
        }

        // AJOUT : Logique de définition du titre final
        // Si le champ est vide, on prend le nom du fichier sans l'extension
        $finalTitle = !empty($customTitle) ? $customTitle : pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
        // On remplace les tirets et underscores par des espaces pour faire plus propre
        if (empty($customTitle)) {
            $finalTitle = ucwords(str_replace(['_', '-'], ' ', $finalTitle));
        }

        try {
            // On passe le $finalTitle calculé à l'API au lieu de $uploadResult['file']
            $apiData = $this->pushToDailymotionAPI($uploadResult['full_path'], $finalTitle, $visibility);

            $uploadTool->removeLocalVideo($uploadResult['full_path']);

            $db = new DailymotionDb();
            $db->insertVideo([
                'item_type'         => $itemType,
                'item_id'           => $itemId,
                'name_pdn'          => $finalTitle, // On sauvegarde aussi ce titre propre en BDD
                'video_id_pdn'      => $apiData['id'],
                'private_id'        => $apiData['private_id'] ?? null,
                'visibility_pdm'    => $visibility,
                'thumbnail_360_url' => $apiData['thumbnail_360_url'] ?? null,
                'thumbnail_720_url' => $apiData['thumbnail_720_url'] ?? null,
            ]);

            CacheManager::clearFrontend('magixdailymotion');
            $this->jsonResponse(true, 'Vidéo transférée et liée avec succès.');
        } catch (\Exception $e) {
            $uploadTool->removeLocalVideo($uploadResult['full_path']);
            $this->jsonResponse(false, 'Erreur API : ' . $e->getMessage());
        }
    }

    private function pushToDailymotionAPI(string $filePath, string $title, string $visibility): array
    {
        $db = new DailymotionDb();
        $auth = $db->getAuthentication();

        if (!$auth || empty($auth['apikey_dm'])) {
            throw new \Exception('Clés d\'authentification Dailymotion manquantes dans la configuration du plugin.');
        }

        $api = new \Dailymotion();
        $api->setGrantType(
            \Dailymotion::GRANT_TYPE_PASSWORD,
            $auth['apikey_dm'],
            $auth['apisecret_dm'],
            ['write', 'manage_videos'],
            ['username' => $auth['username_dm'], 'password' => $auth['password_dm']]
        );

        $urlUpload = $api->uploadFile($filePath);

        $videoData = [
            'url'       => $urlUpload,
            'title'     => $title,
            'published' => ($visibility !== 'draft'),
            'private'   => ($visibility === 'private')
        ];

        if ($visibility === 'private') {
            $cleanDomain = preg_replace('/^www\.|:[0-9]+$/', '', $_SERVER['HTTP_HOST']);
            $videoData['whitelisted_referers'] = "www." . $cleanDomain . "," . $cleanDomain;
        }

        $result = $api->post('/me/videos', $videoData);
        $details = $api->get('/video/' . $result['id'], ['fields' => ['private_id', 'thumbnail_360_url', 'thumbnail_720_url']]);

        return array_merge($result, $details);
    }

    public function delete(): void
    {
        if (ob_get_length()) ob_clean();

        $token = Request::isPost('hashtoken') ? $_POST['hashtoken'] : '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $idVideo = (int)($_POST['id_dm_item'] ?? 0);

        if ($idVideo > 0) {
            $db = new DailymotionDb();
            $video = $db->getVideoById($idVideo);

            if ($video && !empty($video['video_id_pdn'])) {
                try {
                    $auth = $db->getAuthentication();
                    $api = new \Dailymotion();
                    $api->setGrantType(
                        \Dailymotion::GRANT_TYPE_PASSWORD,
                        $auth['apikey_dm'],
                        $auth['apisecret_dm'],
                        ['delete', 'manage_videos'],
                        ['username' => $auth['username_dm'], 'password' => $auth['password_dm']]
                    );
                    $api->delete('/video/' . $video['video_id_pdn']);
                } catch (\Exception $e) {}
            }

            if ($db->deleteVideo($idVideo)) {
                CacheManager::clearFrontend('magixdailymotion');
                $this->jsonResponse(true, 'Vidéo supprimée avec succès.');
            }
        }
        $this->jsonResponse(false, 'Impossible de supprimer cette vidéo.');
    }

    public function reorder(): void
    {
        if (ob_get_length()) ob_clean();

        $token = Request::isPost('hashtoken') ? $_POST['hashtoken'] : '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $orderedIds = $_POST['ids'] ?? [];

        if (!empty($orderedIds) && is_array($orderedIds)) {
            $db = new DailymotionDb();
            if ($db->reorderVideo($orderedIds)) {
                CacheManager::clearFrontend('magixdailymotion');
                $this->jsonResponse(true, 'Ordre mis à jour.');
            }
        }
        $this->jsonResponse(false, 'Erreur lors du tri.');
    }
}