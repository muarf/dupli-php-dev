<div class="section">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <h1 class="text-center">
          <i class="fa fa-desktop"></i> Console <?php echo htmlspecialchars($wrapper['machine_name']); ?>
        </h1>
        <hr>
        
        <div class="row">
          <!-- Informations sur la console -->
          <div class="col-md-4">
            <div class="panel panel-info">
              <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-info-circle"></i> Informations</h3>
              </div>
              <div class="panel-body">
                <p><strong>Machine :</strong> <?php echo htmlspecialchars($wrapper['machine_name']); ?></p>
                <p><strong>Type :</strong> <?php echo htmlspecialchars($wrapper['console_type']); ?></p>
                <p><strong>URL :</strong> <a href="<?php echo htmlspecialchars($wrapper['console_url']); ?>" target="_blank"><?php echo htmlspecialchars($wrapper['console_url']); ?></a></p>
                
                <a href="<?php echo htmlspecialchars($wrapper['console_url']); ?>" target="_blank" class="btn btn-primary btn-block">
                  <i class="fa fa-external-link"></i> Ouvrir la console dans un nouvel onglet
                </a>
              </div>
            </div>
          </div>
          
          <!-- Section des scans -->
          <div class="col-md-8">
            <div class="panel panel-default">
              <div class="panel-heading">
                <h3 class="panel-title">
                  <i class="fa fa-file-image-o"></i> Derniers scans
                  <button onclick="location.reload()" class="btn btn-xs btn-default pull-right">
                    <i class="fa fa-refresh"></i> Rafraîchir
                  </button>
                </h3>
              </div>
              <div class="panel-body">
                <div id="scans-placeholder">
                  <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> Chargement des scans...
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Console intégrée via iframe -->
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-desktop"></i> Console Web</h3>
          </div>
          <div class="panel-body" style="padding: 0; position: relative;">
              <iframe 
              id="riso-console-iframe"
              src="/riso-proxy/" 
              style="width: 100%; height: 800px; border: none; min-height: 600px;"
              frameborder="0"
              allowfullscreen>
              <p>Votre navigateur ne supporte pas les iframes. <a href="<?php echo htmlspecialchars($wrapper['console_url']); ?>" target="_blank">Ouvrir la console dans un nouvel onglet</a></p>
            </iframe>
            
            <script type="text/javascript">
            // Attendre que l'iframe soit chargé et afficher un message postMessage
            setTimeout(function() {
              var iframe = document.getElementById('console-iframe');
              var placeholder = document.getElementById('scans-placeholder');
              
              // Créer un listener pour recevoir les données des scans de l'iframe
              window.addEventListener('message', function(event) {
                // Vérifier que le message provient de notre iframe (pas nécessaire car même origin grâce au proxy)
                if (event.data && event.data.type === 'scans-data') {
                  var scans = event.data.scans;
                  
                  if (scans && scans.length > 0) {
                    var html = '<div class="table-responsive"><table class="table table-striped"><thead><tr><th>Nom du document</th><th>Propriétaire</th><th>Pages</th><th>Date</th><th>Actions</th></tr></thead><tbody>';
                    
                        scans.forEach(function(scan) {
                          var fileName = scan.name.replace(/^logo/, '').trim();
                          // Lien vers la console RISO pour téléchargement manuel
                          html += '<tr><td>' + escapeHtml(fileName) + '</td><td>' + escapeHtml(scan.owner) + '</td><td>' + escapeHtml(scan.pages) + '</td><td>' + escapeHtml(scan.date) + '</td><td><a href="http://192.168.1.110/UI/IE/NewUIpage/Page/RC_Scan.phtml" target="_blank" class="btn btn-xs btn-success"><i class="fa fa-download"></i> Télécharger</a></td></tr>';
                        });
                    
                    html += '</tbody></table></div>';
                    placeholder.innerHTML = html;
                  }
                }
              });
              
              function escapeHtml(text) {
                var map = {
                  '&': '&amp;',
                  '<': '&lt;',
                  '>': '&gt;',
                  '"': '&quot;',
                  "'": '&#039;'
                };
                return text.replace(/[&<>"']/g, function(m) { return map[m]; });
              }
            }, 1000);
            
            // Fonction pour montrer le scan dans l'iframe
            function showScanInIframe(fileName) {
              alert('Recherchez \"' + fileName + '\" dans l\'iframe ci-dessous, cochez-le, puis cliquez sur \"Télécharger\".');
            }
            </script>
          </div>
        </div>
        
        <!-- Bouton retour -->
        <div class="row">
          <div class="col-md-12">
            <a href="?accueil" class="btn btn-default btn-block">
              <i class="fa fa-arrow-left"></i> Retour à l'accueil
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

