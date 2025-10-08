<!-- JSZip pour export multiple -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<style>
.riso-container {
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
}
.riso-header {
    background: linear-gradient(135deg, #ff6b9d 0%, #c06c84 100%);
    color: white;
    padding: 30px;
    border-radius: 10px;
    text-align: center;
    margin-bottom: 30px;
}
.upload-zone {
    border: 3px dashed #ff6b9d;
    border-radius: 15px;
    padding: 50px;
    text-align: center;
    background: #fff5f8;
    cursor: pointer;
    transition: all 0.3s;
}
.upload-zone:hover {
    border-color: #c06c84;
    background: #ffe8f0;
}
.channel-panel {
    background: white;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 15px;
}
.canvas-container {
    display: inline-block;
    margin: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    overflow: hidden;
}
canvas {
    display: block;
    max-width: 100%;
    height: auto;
}
.layer-controls {
    margin-top: 15px;
}
.preview-canvas {
    border: 2px solid #333;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}
</style>

<div class="riso-container">
    <!-- En-tête -->
    <div class="riso-header">
        <h1><i class="fa fa-palette"></i> Séparateur de Couleur Riso</h1>
        <p>Séparez vos images couleur en couches pour impression multi-tambours</p>
    </div>

    <!-- Zone d'upload -->
    <div id="uploadSection">
        <div class="upload-zone" id="uploadZone">
            <div style="font-size: 64px; color: #ff6b9d; margin-bottom: 20px;">
                <i class="fa fa-cloud-upload"></i>
            </div>
            <h3>Glissez votre image ici</h3>
            <p class="text-muted">ou cliquez pour sélectionner</p>
            <input type="file" id="imageInput" accept="image/png,image/jpeg,image/jpg" style="display: none;">
            <button type="button" class="btn btn-lg" style="background: #ff6b9d; color: white; border: none; padding: 12px 30px; border-radius: 25px; margin-top: 15px;">
                <i class="fa fa-upload"></i> Sélectionner une image
            </button>
        </div>
    </div>

    <!-- Contrôles et Prévisualisation -->
    <div id="separatorSection" style="display: none;">
        <!-- Image originale -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4><i class="fa fa-image"></i> Image originale</h4>
            </div>
            <div class="panel-body text-center">
                <canvas id="originalCanvas"></canvas>
            </div>
        </div>

        <!-- Contrôles de séparation -->
        <div class="panel panel-info">
            <div class="panel-heading">
                <h4><i class="fa fa-sliders"></i> Configuration des couches</h4>
            </div>
            <div class="panel-body">
                <div class="row">
                    <!-- Canal Rouge -->
                    <div class="col-md-4">
                        <div class="channel-panel">
                            <h5><i class="fa fa-circle" style="color: #ff0000;"></i> Canal Rouge</h5>
                            <canvas id="redCanvas" class="img-thumbnail"></canvas>
                            <div class="layer-controls">
                                <label>Tambour:</label>
                                <select class="form-control tambour-select" data-channel="red">
                                    <option value="red">Rouge</option>
                                    <option value="black">Noir</option>
                                    <option value="blue">Bleu</option>
                                    <option value="yellow">Jaune</option>
                                    <option value="green">Vert</option>
                                    <option value="violet">Violet</option>
                                    <option value="none">Aucun</option>
                                </select>
                                <label style="margin-top: 10px;">Opacité: <span id="redOpacity">100</span>%</label>
                                <input type="range" class="form-control" id="redOpacitySlider" min="0" max="100" value="100">
                                <button class="btn btn-sm btn-success btn-block" style="margin-top: 10px;" onclick="exportChannel('red')">
                                    <i class="fa fa-download"></i> Export PNG
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Canal Vert -->
                    <div class="col-md-4">
                        <div class="channel-panel">
                            <h5><i class="fa fa-circle" style="color: #00ff00;"></i> Canal Vert</h5>
                            <canvas id="greenCanvas" class="img-thumbnail"></canvas>
                            <div class="layer-controls">
                                <label>Tambour:</label>
                                <select class="form-control tambour-select" data-channel="green">
                                    <option value="green">Vert</option>
                                    <option value="black">Noir</option>
                                    <option value="blue">Bleu</option>
                                    <option value="yellow">Jaune</option>
                                    <option value="red">Rouge</option>
                                    <option value="violet">Violet</option>
                                    <option value="none">Aucun</option>
                                </select>
                                <label style="margin-top: 10px;">Opacité: <span id="greenOpacity">100</span>%</label>
                                <input type="range" class="form-control" id="greenOpacitySlider" min="0" max="100" value="100">
                                <button class="btn btn-sm btn-success btn-block" style="margin-top: 10px;" onclick="exportChannel('green')">
                                    <i class="fa fa-download"></i> Export PNG
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Canal Bleu -->
                    <div class="col-md-4">
                        <div class="channel-panel">
                            <h5><i class="fa fa-circle" style="color: #0000ff;"></i> Canal Bleu</h5>
                            <canvas id="blueCanvas" class="img-thumbnail"></canvas>
                            <div class="layer-controls">
                                <label>Tambour:</label>
                                <select class="form-control tambour-select" data-channel="blue">
                                    <option value="blue">Bleu</option>
                                    <option value="black">Noir</option>
                                    <option value="red">Rouge</option>
                                    <option value="yellow">Jaune</option>
                                    <option value="green">Vert</option>
                                    <option value="violet">Violet</option>
                                    <option value="none">Aucun</option>
                                </select>
                                <label style="margin-top: 10px;">Opacité: <span id="blueOpacity">100</span>%</label>
                                <input type="range" class="form-control" id="blueOpacitySlider" min="0" max="100" value="100">
                                <button class="btn btn-sm btn-success btn-block" style="margin-top: 10px;" onclick="exportChannel('blue')">
                                    <i class="fa fa-download"></i> Export PNG
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Prévisualisation superposition -->
        <div class="panel panel-success">
            <div class="panel-heading">
                <h4><i class="fa fa-eye"></i> Prévisualisation - Superposition des couches</h4>
            </div>
            <div class="panel-body text-center">
                <canvas id="previewCanvas" class="preview-canvas"></canvas>
                <div style="margin-top: 20px;">
                    <button class="btn btn-primary btn-lg" onclick="updatePreview()">
                        <i class="fa fa-refresh"></i> Actualiser la prévisualisation
                    </button>
                    <button class="btn btn-success btn-lg" onclick="exportAll()">
                        <i class="fa fa-download"></i> Exporter toutes les couches (ZIP)
                    </button>
                </div>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="text-center">
            <button class="btn btn-default btn-lg" onclick="resetSeparator()">
                <i class="fa fa-refresh"></i> Nouvelle image
            </button>
            <a href="?accueil" class="btn btn-default btn-lg">
                <i class="fa fa-home"></i> Retour à l'accueil
            </a>
        </div>
    </div>

    <!-- Panneau d'information -->
    <div class="panel panel-info">
        <div class="panel-heading">
            <h4><i class="fa fa-info-circle"></i> Guide d'utilisation</h4>
        </div>
        <div class="panel-body">
            <ol>
                <li><strong>Uploadez</strong> une image couleur (PNG ou JPG)</li>
                <li><strong>L'image est séparée</strong> automatiquement en 3 canaux RGB</li>
                <li><strong>Assignez un tambour</strong> à chaque canal (Rouge, Bleu, Jaune, Noir, etc.)</li>
                <li><strong>Ajustez l'opacité</strong> de chaque couche avec les sliders</li>
                <li><strong>Prévisualisez</strong> le résultat final de superposition</li>
                <li><strong>Exportez</strong> chaque couche individuellement pour impression</li>
            </ol>
            <div class="alert alert-success">
                <i class="fa fa-lightbulb-o"></i> <strong>Astuce:</strong> 
                Chaque couche exportée est en niveaux de gris. Vous imprimez ensuite chaque couche 
                avec le tambour de couleur correspondant sur votre Risographe.
            </div>
        </div>
    </div>
</div>

<script>
// Couleurs Riso standards (hex)
const RISO_COLORS = {
    'black': '#000000',
    'red': '#FF5C5C',
    'blue': '#0078BF',
    'yellow': '#FFD800',
    'green': '#00A95C',
    'violet': '#765BA7',
    'none': null
};

// Variables globales
let originalImage = null;
let channels = {
    red: null,
    green: null,
    blue: null
};

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    const uploadZone = document.getElementById('uploadZone');
    const imageInput = document.getElementById('imageInput');
    const uploadSection = document.getElementById('uploadSection');
    const separatorSection = document.getElementById('separatorSection');

    // Click sur zone d'upload
    uploadZone.addEventListener('click', () => imageInput.click());
    document.querySelector('.upload-zone button').addEventListener('click', (e) => {
        e.stopPropagation();
        imageInput.click();
    });

    // Sélection fichier
    imageInput.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            loadImage(this.files[0]);
        }
    });

    // Drag & drop
    uploadZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadZone.style.borderColor = '#c06c84';
        uploadZone.style.background = '#ffe8f0';
    });

    uploadZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        uploadZone.style.borderColor = '#ff6b9d';
        uploadZone.style.background = '#fff5f8';
    });

    uploadZone.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadZone.style.borderColor = '#ff6b9d';
        uploadZone.style.background = '#fff5f8';
        
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            const file = e.dataTransfer.files[0];
            if (file.type.startsWith('image/')) {
                loadImage(file);
            } else {
                alert('Veuillez sélectionner une image valide (PNG ou JPG).');
            }
        }
    });

    // Event listeners pour les sliders d'opacité
    document.getElementById('redOpacitySlider').addEventListener('input', function() {
        document.getElementById('redOpacity').textContent = this.value;
        updatePreview();
    });
    document.getElementById('greenOpacitySlider').addEventListener('input', function() {
        document.getElementById('greenOpacity').textContent = this.value;
        updatePreview();
    });
    document.getElementById('blueOpacitySlider').addEventListener('input', function() {
        document.getElementById('blueOpacity').textContent = this.value;
        updatePreview();
    });

    // Event listeners pour les sélecteurs de tambours
    document.querySelectorAll('.tambour-select').forEach(select => {
        select.addEventListener('change', updatePreview);
    });
});

// Charger et afficher l'image
function loadImage(file) {
    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            originalImage = img;
            processImage(img);
            document.getElementById('uploadSection').style.display = 'none';
            document.getElementById('separatorSection').style.display = 'block';
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

// Traiter l'image et séparer les canaux
function processImage(img) {
    // Afficher l'image originale
    const originalCanvas = document.getElementById('originalCanvas');
    const maxWidth = 600;
    const scale = Math.min(1, maxWidth / img.width);
    originalCanvas.width = img.width * scale;
    originalCanvas.height = img.height * scale;
    const ctx = originalCanvas.getContext('2d');
    ctx.drawImage(img, 0, 0, originalCanvas.width, originalCanvas.height);

    // Séparer les canaux RGB
    separateChannels(img);
    
    // Mettre à jour la prévisualisation
    updatePreview();
}

// Séparer l'image en canaux RGB (niveaux de gris)
function separateChannels(img) {
    const canvas = document.createElement('canvas');
    canvas.width = img.width;
    canvas.height = img.height;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(img, 0, 0);
    
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const data = imageData.data;

    // Créer 3 canvas pour les canaux
    const redData = ctx.createImageData(canvas.width, canvas.height);
    const greenData = ctx.createImageData(canvas.width, canvas.height);
    const blueData = ctx.createImageData(canvas.width, canvas.height);

    // Séparer les canaux
    for (let i = 0; i < data.length; i += 4) {
        // Canal rouge -> niveaux de gris basés sur valeur rouge
        redData.data[i] = data[i];     // R
        redData.data[i+1] = data[i];   // G
        redData.data[i+2] = data[i];   // B
        redData.data[i+3] = 255;       // A

        // Canal vert -> niveaux de gris basés sur valeur verte
        greenData.data[i] = data[i+1];
        greenData.data[i+1] = data[i+1];
        greenData.data[i+2] = data[i+1];
        greenData.data[i+3] = 255;

        // Canal bleu -> niveaux de gris basés sur valeur bleue
        blueData.data[i] = data[i+2];
        blueData.data[i+1] = data[i+2];
        blueData.data[i+2] = data[i+2];
        blueData.data[i+3] = 255;
    }

    // Afficher les canaux séparés
    displayChannel('redCanvas', redData, canvas.width, canvas.height);
    displayChannel('greenCanvas', greenData, canvas.width, canvas.height);
    displayChannel('blueCanvas', blueData, canvas.width, canvas.height);

    // Stocker les données des canaux
    channels.red = redData;
    channels.green = greenData;
    channels.blue = blueData;
}

// Afficher un canal sur un canvas
function displayChannel(canvasId, imageData, width, height) {
    const canvas = document.getElementById(canvasId);
    const maxWidth = 250;
    const scale = Math.min(1, maxWidth / width);
    canvas.width = width * scale;
    canvas.height = height * scale;
    
    const tempCanvas = document.createElement('canvas');
    tempCanvas.width = width;
    tempCanvas.height = height;
    const tempCtx = tempCanvas.getContext('2d');
    tempCtx.putImageData(imageData, 0, 0);
    
    const ctx = canvas.getContext('2d');
    ctx.drawImage(tempCanvas, 0, 0, canvas.width, canvas.height);
}

// Mettre à jour la prévisualisation avec superposition
function updatePreview() {
    if (!originalImage) return;

    const previewCanvas = document.getElementById('previewCanvas');
    const originalCanvas = document.getElementById('originalCanvas');
    previewCanvas.width = originalCanvas.width;
    previewCanvas.height = originalCanvas.height;
    
    const ctx = previewCanvas.getContext('2d');
    ctx.fillStyle = 'white';
    ctx.fillRect(0, 0, previewCanvas.width, previewCanvas.height);

    // Appliquer chaque couche avec sa couleur et opacité
    const channelNames = ['red', 'green', 'blue'];
    
    channelNames.forEach(channelName => {
        const tambour = document.querySelector(`select[data-channel="${channelName}"]`).value;
        if (tambour === 'none') return;
        
        const opacity = parseInt(document.getElementById(`${channelName}OpacitySlider`).value) / 100;
        const color = RISO_COLORS[tambour];
        
        if (!color || !channels[channelName]) return;

        // Créer canvas temporaire pour la couche colorisée
        const tempCanvas = document.createElement('canvas');
        tempCanvas.width = originalImage.width;
        tempCanvas.height = originalImage.height;
        const tempCtx = tempCanvas.getContext('2d');
        
        // Dessiner le canal en niveaux de gris
        tempCtx.putImageData(channels[channelName], 0, 0);
        
        // Appliquer la couleur du tambour
        const coloredData = tempCtx.getImageData(0, 0, tempCanvas.width, tempCanvas.height);
        const rgb = hexToRgb(color);
        
        for (let i = 0; i < coloredData.data.length; i += 4) {
            const intensity = coloredData.data[i] / 255; // Utiliser le niveau de gris comme intensité
            coloredData.data[i] = rgb.r * intensity;     // R
            coloredData.data[i+1] = rgb.g * intensity;   // G
            coloredData.data[i+2] = rgb.b * intensity;   // B
            coloredData.data[i+3] = 255 * opacity;       // A (avec opacité)
        }
        
        tempCtx.putImageData(coloredData, 0, 0);
        
        // Dessiner sur le canvas de prévisualisation avec blend mode
        ctx.globalCompositeOperation = 'multiply';
        ctx.globalAlpha = opacity;
        ctx.drawImage(tempCanvas, 0, 0, previewCanvas.width, previewCanvas.height);
        ctx.globalAlpha = 1;
        ctx.globalCompositeOperation = 'source-over';
    });
}

// Convertir hex en RGB
function hexToRgb(hex) {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : {r: 0, g: 0, b: 0};
}

// Exporter un canal spécifique
function exportChannel(channelName) {
    if (!channels[channelName] || !originalImage) return;

    const tambour = document.querySelector(`select[data-channel="${channelName}"]`).value;
    if (tambour === 'none') {
        alert('Veuillez sélectionner un tambour pour ce canal avant d\'exporter.');
        return;
    }

    // Créer canvas pour export
    const canvas = document.createElement('canvas');
    canvas.width = originalImage.width;
    canvas.height = originalImage.height;
    const ctx = canvas.getContext('2d');
    
    // Mettre le canal en niveaux de gris
    ctx.putImageData(channels[channelName], 0, 0);

    // Télécharger
    canvas.toBlob(function(blob) {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `riso_${channelName}_${tambour}.png`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    });
}

// Exporter toutes les couches actives en ZIP
async function exportAll() {
    if (typeof JSZip === 'undefined') {
        alert('Fonction ZIP non disponible. Exportez les couches individuellement.');
        return;
    }

    const zip = new JSZip();
    const channelNames = ['red', 'green', 'blue'];
    let exportCount = 0;

    for (const channelName of channelNames) {
        const tambour = document.querySelector(`select[data-channel="${channelName}"]`).value;
        if (tambour === 'none' || !channels[channelName]) continue;

        const canvas = document.createElement('canvas');
        canvas.width = originalImage.width;
        canvas.height = originalImage.height;
        const ctx = canvas.getContext('2d');
        ctx.putImageData(channels[channelName], 0, 0);

        const blob = await new Promise(resolve => canvas.toBlob(resolve));
        zip.file(`riso_${channelName}_${tambour}.png`, blob);
        exportCount++;
    }

    if (exportCount === 0) {
        alert('Aucune couche active à exporter. Assignez des tambours d\'abord.');
        return;
    }

    // Générer et télécharger le ZIP
    const content = await zip.generateAsync({type: 'blob'});
    const url = URL.createObjectURL(content);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'riso_layers.zip';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

// Réinitialiser le séparateur
function resetSeparator() {
    document.getElementById('uploadSection').style.display = 'block';
    document.getElementById('separatorSection').style.display = 'none';
    document.getElementById('imageInput').value = '';
    originalImage = null;
    channels = {red: null, green: null, blue: null};
}
</script>
