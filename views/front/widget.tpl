{*
 * This file is part of MAGIX CMS.
 * Copyright (C) 2008 - 2026 Gerits Aurelien (Magix CMS).
 * License: GPLv3
 *}

{if !empty($dm_widget_data.items)}
    <div class="magix-dailymotion-widget {if $dm_widget_data.visibility == 'private'}my-4{else}my-5{/if}">
        <div class="d-flex align-items-center mb-4">
            {if $dm_widget_data.visibility == 'private'}
                <i class="bi bi-play-btn-fill fs-3 text-success me-2"></i>
                <h4 class="h5 mb-0 fw-bold">{#private_videos_title#|default:'Vos vidéos de formation'}</h4>
            {else}
                <i class="bi bi-play-circle fs-2 text-danger me-3"></i>
                <h3 class="h4 mb-0 fw-bold">{#videos_title#|default:'Nos Vidéos'}</h3>
            {/if}
        </div>

        {* Grille responsive : 1 colonne sur mobile, 2 sur tablette, 3 sur desktop (ou 2 en mode privé) *}
        <div class="row row-cols-1 row-cols-md-2 {if $dm_widget_data.visibility != 'private'}row-cols-lg-3{/if} g-4">
            {foreach $dm_widget_data.items as $video}
                {* Sélection automatique du private_id pour les vidéos privées *}
                {if $dm_widget_data.visibility == 'private' && !empty($video.private_id)}
                    {$embed_id = $video.private_id}
                {else}
                    {$embed_id = $video.video_id_pdn}
                {/if}

                <div class="col">
                    <div class="card shadow-sm h-100 border-0 bg-body-tertiary">

                        {* Iframe Dailymotion encapsulée avec un ratio Bootstrap 16:9 *}
                        <div class="ratio ratio-16x9 rounded-top overflow-hidden">
                            <iframe
                                    src="https://www.dailymotion.com/embed/video/{$embed_id}?ui-logo=0&ui-start-screen-info=0"
                                    allow="autoplay; fullscreen; picture-in-picture"
                                    allowfullscreen
                                    title="{$video.name_pdn|escape:'html'}"
                                    class="border-0">
                            </iframe>
                        </div>

                        <div class="card-body">
                            <h5 class="card-title h6 fw-medium text-truncate mb-0" title="{$video.name_pdn|escape:'html'}">
                                {$video.name_pdn|escape:'html'}
                            </h5>
                        </div>

                    </div>
                </div>
            {/foreach}
        </div>
    </div>
{/if}