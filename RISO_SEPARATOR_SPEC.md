# Séparateur de Couleur Riso - Spécifications

## Fonctionnalités à implémenter (en JavaScript pur)

### 1. Upload et Affichage
- Drag & drop d'image
- Support PNG/JPG
- Affichage aperçu original

### 2. Séparation des Canaux
- Extraction canal Rouge (R)
- Extraction canal Vert (G)  
- Extraction canal Bleu (B)
- Conversion en niveaux de gris par canal

### 3. Attribution aux Tambours
- Liste déroulante par canal
- Sélection du tambour (Noir, Rouge, Bleu, Jaune, Vert, Violet)
- Couleurs Riso : 
  - Noir: #000000
  - Rouge: #FF5C5C  
  - Bleu: #0078BF
  - Jaune: #FFD800
  - Vert: #00A95C
  - Violet: #765BA7

### 4. Prévisualisation
- Canvas pour chaque couche séparée
- Canvas de superposition avec blend modes
- Ajustement opacité par couche
- Toggle visibilité par couche

### 5. Export
- Téléchargement PNG de chaque couche
- Nom fichier : original_tambour_couleur.png
- Format prêt pour impression Riso

## Technologies
- Canvas API (manipulation pixels)
- File API (upload)
- Blob API (export)
- 100% JavaScript côté client

## Référence
- https://github.com/axelberggraf/riso-separator
- https://github.com/antiboredom/p5.riso
