# MagixDailymotion - Gestionnaire de Vidéos Dailymotion pour Magix CMS 4

[![Release](https://img.shields.io/github/release/magix-cms/MagixDailymotion.svg)](https://github.com/magix-cms/MagixDailymotion/releases/latest)
[![License](https://img.shields.io/github/license/magix-cms/MagixDailymotion.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D%208.2-blue.svg)](https://php.net/)
[![Magix CMS](https://img.shields.io/badge/Magix%20CMS-4.x-success.svg)](https://www.magix-cms.com/)

MagixDailymotion est un gestionnaire de vidéos avancé de type **hybride** pour Magix CMS 4. Il permet d'uploader directement vos vidéos vers votre compte Dailymotion via leur API officielle et de les associer à n'importe quel module natif du cœur (Produits, Pages, Actualités, Catégories, Accueil, Catalogue, À propos).

Il intègre une gestion fine de la visibilité et des restrictions de domaine permettant de vendre des contenus vidéo exclusifs (formations, tutoriels, replays) via `Magixcartpay` et `Magixaccount`.

## 🚀 Fonctionnalités principales

* **Synchronisation Directe API Dailymotion** : Téléversement depuis le back-office avec titre personnalisé (ou reprise automatique du nom de fichier), suppression automatique du fichier vidéo temporaire sur votre serveur après transfert, et récupération automatique des miniatures (360p / 720p) ainsi que de l'identifiant privé (`private_id`).
* **Visibilité Conditionnelle (Public / Privé / Brouillon)** : Diffusez des vidéos publiques en libre accès dans vos fiches produits ou pages, conservez des vidéos en brouillon, ou réservez vos vidéos privées à un tunnel de vente (`Magixcartpay`).
* **Protection Anti-Partage (`whitelisted_referers`)** : Les vidéos configurées en mode « Privé » sont automatiquement restreintes via l'API Dailymotion pour n'être lisibles (embed) que depuis votre propre nom de domaine.
* **Architecture Hybride & Multimodule** : Dispose d'une page d'administration dédiée pour configurer vos identifiants API Dailymotion et d'un widget AJAX injectable dans les onglets d'édition des modules du cœur (réorganisation par glisser-déposer et lecteur responsive Bootstrap 5.3).
* **Sécurité d'Upload Renforcée (`VideoUploadTool`)** : Validation stricte combinant liste blanche d'extensions, contrôle réel du type MIME via `finfo` et blocage des doubles extensions suspectes.

## 📦 Installation

1. Placez le dossier `MagixDailymotion` dans le répertoire `plugins/`.
2. Installez le plugin depuis l'administration de Magix CMS 4.
3. Rendez-vous dans **Plugins > MagixDailymotion** pour renseigner vos identifiants API (nécessite une **clé API privée** générée depuis votre espace partenaire Dailymotion).
4. *Note : Pour exploiter la fonctionnalité "Vidéos Privées" dans un espace membre ou un tunnel de vente, les plugins `Magixaccount` (comptes clients) et `Magixcartpay` (moteur e-commerce) doivent être installés et actifs.*

## ⚙️ Configuration Serveur (Vidéos volumineuses)

Les vidéos étant réceptionnées temporairement par votre serveur avant leur transfert vers Dailymotion, assurez-vous que votre configuration PHP et serveur web (Apache/Nginx) autorise l'envoi de fichiers lourds (ex. jusqu'à 800 Mo) :

```ini
upload_max_filesize = 1024M
post_max_size = 1050M
memory_limit = 1024M
max_execution_time = 3600
max_input_time = 3600
```

*(Sous Nginx, pensez également à ajuster `client_max_body_size 1050m;` et `fastcgi_read_timeout 3600s;`).*

## 🎨 Intégration sur le site (Frontend)

L'affichage s'adapte automatiquement selon que la vidéo est publique (libre d'accès) ou privée (vendue avec un produit).

### 1. Vidéos Publiques (Layout Dynamique)

Vous n'avez **rien à coder**. Si vous ajoutez des vidéos « Publiques » à un produit, une page ou une actualité, le widget s'affichera automatiquement grâce au système de Layout Dynamique de Magix CMS via les hooks standards :

```smarty
{* S'insère automatiquement via le gestionnaire de Layout *}
{hook name="displayProductExtraContent" id_product=$productData.id|default:0}
```

### 2. Vente de Vidéos (Zone Privée Sécurisée)

Pour les vidéos définies en « Privée » dans l'administration d'un produit, utilisez une zone conditionnelle dans votre thème (ex: `skin/votre_theme/catalog/product.tpl`) afin de débloquer le lecteur uniquement aux clients ayant acheté le produit :

```smarty
{* 1. MagixCartpay vérifie si le client connecté possède ce produit *}
{has_purchased id_product=$productData.id|default:0 assign="user_owns_product"}

{if $user_owns_product}

    <div class="alert alert-success">
        <h5><i class="bi bi-unlock-fill"></i> Vos vidéos débloquées</h5>
        <p>Merci pour votre achat ! Voici vos accès vidéo :</p>
    </div>

    {* 2. Affiche UNIQUEMENT les vidéos MagixDailymotion configurées en "Privée" *}
    {hook name="displayProductPrivateVideos" id_product=$productData.id|default:0}

{else}

    <div class="card bg-light border-0 p-4 text-center">
        <i class="bi bi-lock fs-1 text-muted mb-3"></i>
        <h5>Contenu Vidéo Premium</h5>
        <p class="text-muted">Achetez ce produit pour débloquer l'accès complet aux vidéos.</p>

        {* 3. Affiche le bouton d'ajout au panier Magixcartpay *}
        {hook name="displayProductAddToCart" id_product=$productData.id|default:0}
    </div>

{/if}
```

## 🛠️ Formats Vidéo pris en charge

Le système embarque un validateur strict (vérification combinée de l'extension et du `finfo` MIME-Type) empêchant l'upload de scripts malveillants :

* **Vidéos** : `.mp4`, `.avi`, `.mpeg`, `.mov`, `.qt` (`video/mp4`, `video/x-msvideo`, `video/mpeg`, `video/quicktime`)

## 📄 Licence

Ce projet est sous licence **GPLv3**. Voir le fichier [LICENSE](LICENSE) pour plus de détails.
Copyright (C) 2008 - 2026 Gerits Aurelien (Magix CMS)