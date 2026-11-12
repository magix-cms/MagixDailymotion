{* 1. On interroge notre plugin silencieusement *}
{has_purchased id_product=$product.id assign="user_owns_product"}

{* 2. On adapte l'affichage en fonction du résultat *}
{if $user_owns_product}

    <div class="alert alert-success">
        <h5><i class="bi bi-unlock-fill"></i> Contenu débloqué</h5>
        <p class="mb-0">Merci pour votre achat ! Voici vos accès :</p>
    </div>

    {* --- LES VIDÉOS PRIVÉES (MagixDailymotion) --- *}
    {hook name="displayProductPrivateVideos" id_product=$product.id}

    {* --- LES FICHIERS PRIVÉS JOINTS : PDF, Word, Excel, MP3 (MagixFiles) --- *}
    {hook name="displayProductPrivateFiles" id_product=$product.id}

{else}

    {* --- L'UTILISATEUR N'A PAS ACHETÉ LE PRODUIT (Ou n'est pas connecté) --- *}
    <div class="card bg-light border-0 p-4 text-center">
        <i class="bi bi-lock fs-1 text-muted mb-3"></i>
        <h5>Contenu Premium</h5>
        <p class="text-muted">Achetez ce produit pour débloquer la vidéo explicative et les fichiers de travail.</p>

        {* On affiche le bouton d'ajout au panier *}
        {hook name="displayProductAddToCart" id_product=$product.id}
    </div>

{/if}