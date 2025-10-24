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
                <?php if(empty($scans)): ?>
                  <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> Aucun scan trouvé. 
                    <br><small>Les scans seront affichés ici une fois disponibles depuis la console.</small>
                  </div>
                <?php else: ?>
                  <div class="table-responsive">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th>Nom du document</th>
                          <th>Pages</th>
                          <th>Date</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach($scans as $scan): ?>
                          <tr>
                            <td><?php echo htmlspecialchars($scan['name']); ?></td>
                            <td><?php echo htmlspecialchars($scan['pages']); ?></td>
                            <td><?php echo htmlspecialchars($scan['date']); ?></td>
                            <td>
                              <a href="?console_wrapper&id=<?php echo $wrapper['id']; ?>&download=<?php echo urlencode($scan['name']); ?>" class="btn btn-xs btn-success">
                                <i class="fa fa-download"></i> Télécharger
                              </a>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php endif; ?>
              </div>
            </div>
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

