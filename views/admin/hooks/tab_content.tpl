<div class="tab-pane fade" id="magix-dailymotion-pane" role="tabpanel" aria-labelledby="magix-dailymotion-tab" tabindex="0">
    <div class="card shadow-sm border-0 mt-3">
        <div class="card-body">

            {* VUE 1 : LA ZONE AJAX (Liste) *}
            <div id="magix-dailymotion-app" data-module="{$dm_module}" data-id="{$dm_id_module}">
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border text-danger mb-3" role="status"></div>
                    <p>Chargement des vidéos...</p>
                </div>
            </div>

            {* VUE 2 : LE FORMULAIRE D'UPLOAD *}
            <div id="dm_view_form" style="display: none;">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                    <h5 class="mb-0 text-danger" id="dm_form_title">
                        <i class="bi bi-cloud-arrow-up me-2"></i>Uploader une vidéo
                    </h5>
                    <div>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="dmApp.showList()">
                            <i class="bi bi-arrow-left"></i> Retour à la liste
                        </button>
                    </div>
                </div>

                <form id="dm_form_element" enctype="multipart/form-data">
                    <input type="hidden" id="dm_id_dm_item" name="id_dm_item" value="0">

                    {* NOUVEAU : Champ Titre Optionnel *}
                    <div class="mb-4">
                        <label for="dm_video_title" class="form-label fw-bold"><i class="bi bi-type-h1 me-2"></i>Titre de la vidéo sur Dailymotion</label>
                        <input type="text" class="form-control" name="video_title" id="dm_video_title" placeholder="Ex: Présentation de notre nouveau service">
                        <div class="form-text text-muted">
                            Laissez vide pour utiliser automatiquement le nom du fichier envoyé.
                        </div>
                    </div>

                    {* Fichier Vidéo *}
                    <div class="bg-light p-4 rounded border mb-4">
                        <label class="form-label fw-bold"><i class="bi bi-file-earmark-play me-2"></i>Fichier source</label>
                        <input class="form-control form-control-lg" type="file" name="file" id="dm_file_input" accept="video/mp4,video/x-m4v,video/avi,video/quicktime,video/mpeg">
                        <div class="form-text text-muted mt-2">
                            Formats : MP4, AVI, MOV, MPEG. La vidéo sera directement transférée sur Dailymotion.
                        </div>
                    </div>

                    {* Visibilité *}
                    <div class="mb-4">
                        <label class="form-label fw-bold"><i class="bi bi-shield-lock me-2"></i>Niveau de visibilité</label>
                        <div class="d-flex gap-4 mt-2 p-3 border rounded bg-white">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="video_visibility" id="vis_private" value="private" checked>
                                <label class="form-check-label fw-medium text-success" for="vis_private">Privée (Tunnel de vente)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="video_visibility" id="vis_public" value="public">
                                <label class="form-check-label text-dark" for="vis_public">Publique</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="video_visibility" id="vis_draft" value="draft">
                                <label class="form-check-label text-muted" for="vis_draft">Brouillon</label>
                            </div>
                        </div>
                    </div>
                </form>

                <hr class="my-4">
                <div class="d-flex justify-content-end align-items-center">
                    <div id="dm_loading_indicator" class="text-primary me-4 fw-bold" style="display: none;">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Upload et synchronisation Dailymotion en cours...
                    </div>

                    <button type="button" class="btn btn-secondary me-2 px-4" id="dm_btn_cancel" onclick="dmApp.showList()">Annuler</button>
                    <button type="button" class="btn btn-danger px-5" id="dm_btn_save" onclick="dmApp.save()">
                        <i class="bi bi-cloud-upload me-2"></i> Transférer
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

{block name="javascripts" append}
    <script>
        {literal}
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof MagixAjaxManager !== 'undefined') {

                // Initialisation avec préfixe "dm" et suffixe "dm_item"
                window.dmApp = new MagixAjaxManager(
                    'magix-dailymotion-app',
                    'magix-dailymotion-tab',
                    'MagixDailymotion',
                    'dm',
                    'dm_item'
                );

                // Surcharge de addItem pour vider le champ file
                const originalAddItem = window.dmApp.addItem.bind(window.dmApp);
                window.dmApp.addItem = function() {
                    originalAddItem();
                    document.getElementById('dm_file_input').value = '';
                };

                // Surcharge de save() pour gérer l'upload lourd et cibler l'action "upload"
                window.dmApp.save = function() {
                    const formElement = document.getElementById('dm_form_element');
                    const fileInput = document.getElementById('dm_file_input');

                    if (!fileInput.files.length) {
                        MagixToast.error("Veuillez sélectionner un fichier vidéo.");
                        return;
                    }

                    // AJOUT : Vérification stricte du poids du fichier côté client (800 Mo)
                    const maxFileSize = 800 * 1024 * 1024;
                    if (fileInput.files[0].size > maxFileSize) {
                        MagixToast.error("La vidéo est trop volumineuse. La limite est de 800 Mo.");
                        fileInput.value = ''; // Réinitialise le champ
                        return;
                    }

                    // UI : Activation du loader
                    const btnSave = document.getElementById('dm_btn_save');
                    const btnCancel = document.getElementById('dm_btn_cancel');
                    const loader = document.getElementById('dm_loading_indicator');

                    btnSave.disabled = true;
                    btnCancel.disabled = true;
                    loader.style.display = 'block';

                    const formData = new FormData(formElement);
                    const tokenInput = document.getElementById('dm_hashtoken');

                    formData.append('hashtoken', tokenInput.value);
                    formData.append('module_dm', this.container.dataset.module);
                    formData.append('id_module', this.container.dataset.id);

                    // Appel ciblé vers l'action 'upload' au lieu de 'save'
                    fetch(`index.php?controller=${this.controllerName}&action=upload`, {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status || data.success) {
                            MagixToast.success(data.message || 'Vidéo uploadée avec succès.');
                            this.showList();
                            this.loadList();
                        } else {
                            MagixToast.error(data.message || 'Erreur lors de la sauvegarde.');
                        }
                    })
                    .catch(error => {
                        MagixToast.error("Une erreur réseau est survenue lors de l'upload.");
                    })
                    .finally(() => {
                        // UI : Désactivation du loader
                        btnSave.disabled = false;
                        btnCancel.disabled = false;
                        loader.style.display = 'none';
                        fileInput.value = '';
                    });
                };
            }
        });
        {/literal}
    </script>
{/block}