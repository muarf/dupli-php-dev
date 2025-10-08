# 🖨️ Duplicator - Gestion d'Impression PHP

Application web en PHP pour l'auto-gestion d'impression et de calculs de prix d'impression.  
**Version 0.3a** - Projet depuis 2011

## 📋 Fonctionnalités

### 👤 Utilisateurs
- ✅ Calcul de prix coûtant
- ✅ Statistiques publiques
- ✅ Génération de devis
- ✅ Calcul pour impressions multicolores

### 🔧 Administration
- ✅ Gestion des actualités
- ✅ Gestion des prix d'achats
- ✅ Gestion des prix de vente
- ✅ Date moyenne de changement de fournitures
- ✅ Inscription à la mailing-list (manuel)

## 🚀 Fonctionnalités récentes

- [x] **Désimposition de PDF (unimpose)** - Transforme un livret imposé en pages normales
- [x] **Intégration PHP native** - Remplace le script Python
- [x] **Nettoyage Ghostscript automatique** - Améliore la compatibilité PDF
- [x] **Interface moderne avec drag & drop** - Upload de fichiers simplifié
- [x] **Migration vers SQLite** - Base de données légère et portable

## ✅ Outils de conversion et séparation

- [x] **Conversion PNG/JPG → PDF** - Formats A3/A4, orientation Portrait/Paysage
- [x] **Conversion PDF → PNG** - Extraction pages, choix DPI (72/150/300), export ZIP
- [x] **Séparateur de couleur Riso** - RGB/CMYK/2 tambours, pipette, postérisation, halftone
- [x] **Interface drag & drop** - Sur toutes les pages de conversion

## 🛠️ Technologies utilisées

- **PHP** - Backend
- **SQLite** - Base de données
- **Bootstrap** - Framework CSS & JS
- **TinyMCE** - Éditeur WYSIWYG
- **Ghostscript** - Manipulation de PDFs
- **TCPDF** - Génération PDF
- **Canvas API** - Manipulation d'images (JavaScript)
- **JSZip** - Création d'archives ZIP

## 📦 Installation

```bash
# Cloner le repository
git clone https://github.com/VOTRE_USERNAME/dupli-php-dev.git
cd dupli-php-dev

# Installer les dépendances
composer install

# Accéder à l'interface d'installation
# Ouvrir dans le navigateur : http://votre-serveur/?setup
```

## 📚 Documentation

- **[README_SCRIPTS.md](README_SCRIPTS.md)** - Scripts de test et de gestion de la base de données
- **[README_SQLITE.md](README_SQLITE.md)** - Documentation sur la migration MySQL → SQLite
- **[README.rst](README.rst)** - Documentation originale du projet

## 🔗 Compatibilité

Ce projet peut être intégré avec [dupli-electron-caddy](https://github.com/VOTRE_USERNAME/dupli-electron-caddy) pour une version desktop.

## 📄 Licence

Libre de droit pour projets non commerciaux (hors partis et syndicats).

## 🤝 Contribution

Les contributions sont les bienvenues ! N'hésitez pas à ouvrir une issue ou une pull request.

---

*Projet maintenu avec ❤️ depuis 2011*
