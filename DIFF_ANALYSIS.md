# Analyse des différences entre dupli-php-dev et dupli-electron-caddy/app

## Date d'analyse
1er novembre 2024 - Comparaison avec commit a375cc4 de dupli-electron-caddy

## Statistiques globales

### Fichiers PHP
- **dupli-electron-caddy/app** : 111 fichiers PHP (hors vendor)
- **dupli-php-dev** : 130 fichiers PHP (hors vendor)

### Différences principales

#### 1. Fichiers présents uniquement dans dupli-php-dev
- Fichiers de test : `test_*.php` (19 fichiers de test)
- Fichiers publics supplémentaires :
  - `public/test_pricing.php`
  - `public/test_settings.php`
  - `public/css/inline-translation.css`
  - `public/js/inline-translation.js`

#### 2. Dossiers/fichiers différents

**view/** : Tous les fichiers HTML diffèrent (15 fichiers)
- `accueil.html.php`
- `admin.html.php`
- `admin_translations.html.php`
- `base.html.php`
- `changement.html.php`
- `footer.html.php`
- `header.html.php`
- `imposition.html.php`
- `imposition_tracts.html.php`
- `pdf_to_png.html.php`
- `png_to_pdf.html.php`
- `riso_separator.html.php`
- `stats.html.php`
- `tirage_multimachines.html.php`
- `unimpose.html.php`

**models/** : 2 fichiers différents
- `models/admin/DatabaseManager.php`
- `models/stats.php`

**controler/** : 1 fichier différent
- `controler/functions/i18n.php`

**public/** : Différences dans les uploads et fichiers temporaires
- Fichiers d'upload PDF supplémentaires dans `dupli-php-dev`
- Dossiers temporaires et sauvegardes présents dans `dupli-php-dev`

## Historique Git

### dupli-electron-caddy (commit a375cc4)
- Commit : `a375cc4` - fix: Add permissions to release workflow for GitHub Actions
- Dernière mise à jour de `app/` : Ce commit ne modifie pas le code de `app/`

### dupli-php-dev
Derniers commits (les plus récents) :
- `0e1a557` - Ajout de la fonctionnalité d'édition inline des traductions pour les admins
- `562a69c` - fix: Restauration des clés de traduction manquantes et correction du système d'édition inline
- `0260141` - feat: Amélioration système de traductions et ajout aide_machines
- `51d9f21` - fix: Chemins base de données et permissions mkdir dans AppImage

## Conclusion

**dupli-php-dev est EN AVANCE** par rapport à `dupli-electron-caddy/app` au commit `a375cc4` :

1. **Fonctionnalités ajoutées** :
   - Édition inline des traductions (fichiers CSS/JS dans public/)
   - Système d'aide machines amélioré
   - Fichiers de test supplémentaires

2. **Modifications dans les fichiers existants** :
   - Tous les fichiers `view/*.html.php` ont été modifiés (probablement pour les traductions)
   - `i18n.php` a été modifié
   - `DatabaseManager.php` a été modifié

3. **Synchronisation nécessaire** :
   - Les modifications dans `dupli-php-dev` doivent être synchronisées vers `dupli-electron-caddy/app/`
   - Les fichiers de test peuvent être exclus de la synchronisation

## Recommandation

Mettre en place une automatisation GitHub Actions pour synchroniser `dupli-php-dev` → `dupli-electron-caddy/app/` à chaque push sur `main`.

