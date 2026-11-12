{*
 * This file is part of MAGIX CMS.
 * Copyright (C) 2008 - 2026 Gerits Aurelien (Magix CMS).
 * License: GPLv3
 *}
{extends file="layout.tpl"}

{block name='head:title'}Configuration Dailymotion{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-play-btn me-2 text-danger"></i> Plugin Dailymotion
        </h1>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="m-0 fw-bold text-primary">Paramètres de l'API Dailymotion</h6>
        </div>

        <div class="card-body p-4">
            {* Formulaire intercepté par MagixFormTools grâce à la classe "validate_form" *}
            <form id="dm_config_form" action="index.php?controller=MagixDailymotion&action=saveConfig" method="post" class="validate_form">
                <input type="hidden" name="hashtoken" value="{$hashtoken|default:''}">

                <div class="alert alert-info mb-4">
                    <i class="bi bi-info-circle me-2"></i> Ces identifiants sont nécessaires pour uploader vos vidéos directement sur votre compte Dailymotion via le widget.
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="apikey_dm" class="form-label fw-medium">Clé API (API Key) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="apikey_dm" name="apikey_dm" value="{$dm_config.apikey_dm|default:''}" required>
                    </div>
                    <div class="col-md-6 mt-3 mt-md-0">
                        <label for="apisecret_dm" class="form-label fw-medium">Secret API (API Secret) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="apisecret_dm" name="apisecret_dm" value="{$dm_config.apisecret_dm|default:''}" required>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="username_dm" class="form-label fw-medium">Nom d'utilisateur <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username_dm" name="username_dm" value="{$dm_config.username_dm|default:''}" required>
                    </div>
                    <div class="col-md-6 mt-3 mt-md-0">
                        <label for="password_dm" class="form-label fw-medium">Mot de passe <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_dm" name="password_dm" value="{$dm_config.password_dm|default:''}" required>
                    </div>
                </div>

                <hr class="my-4">
                <div class="d-flex justify-content-end">
                    <button class="btn btn-danger px-4" type="submit">
                        <i class="bi bi-save me-1"></i> Sauvegarder la configuration
                    </button>
                </div>
            </form>
        </div>
    </div>
{/block}