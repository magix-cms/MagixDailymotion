<input type="hidden" id="dm_hashtoken" value="{$hashtoken}">
<input type="hidden" id="dm_module" value="{$item_type}">
<input type="hidden" id="dm_id_module" value="{$item_id}">

<div id="dm_view_list">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0 text-gray-800"><i class="bi bi-camera-video me-2 text-danger"></i>Vidéos Dailymotion</h5>
        <button type="button" class="btn btn-primary btn-sm" onclick="dmApp.addItem()">
            <i class="bi bi-upload me-1"></i> Ajouter une vidéo
        </button>
    </div>

    {include file="components/ajax-table.tpl"
    data=$videos
    id_key="id_dm_item"
    columns=$ajax_columns
    sortable=true
    delete_action="dmApp.deleteItem"
    empty_msg="Aucune vidéo n'a été liée à cet élément."}
</div>