<div class="section">
  <div class="container">
    <div class="row">
      <div class="col-md-10 col-md-offset-1">
        <h1 class="text-center"><i class="fa fa-desktop"></i> Gestion des consoles machines</h1>
        <hr>
        
        <?php if(isset($message)): ?>
        <div class="alert alert-<?php echo $message['type']; ?> alert-dismissible" role="alert">
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <?php echo htmlspecialchars($message['text']); ?>
        </div>
        <?php endif; ?>
        
        <!-- Formulaire d'ajout -->
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-plus"></i> Ajouter une console</h3>
          </div>
          <div class="panel-body">
            <form method="POST" action="?admin&console_wrappers">
              <input type="hidden" name="console_wrapper_action" value="add">
              
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Nom de la machine :</label>
                    <input type="text" name="machine_name" class="form-control" required placeholder="Ex: ComColor 7050">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>URL de la console :</label>
                    <input type="text" name="console_url" class="form-control" required placeholder="http://192.168.1.110/">
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Type de console :</label>
                    <select name="console_type" class="form-control">
                      <option value="riso_comcolor" selected>RISO ComColor</option>
                      <option value="generic">Générique</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Endpoint scans :</label>
                    <input type="text" name="scan_endpoint" class="form-control" value="UI/IE/NewUIpage/Page/RC_Scan.phtml" placeholder="Chemin vers la page des scans">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Activer :</label>
                    <br>
                    <label class="checkbox-inline">
                      <input type="checkbox" name="enabled" checked> Oui
                    </label>
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Nom d'utilisateur (optionnel) :</label>
                    <input type="text" name="username" class="form-control" placeholder="Username">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Mot de passe (optionnel) :</label>
                    <input type="password" name="password" class="form-control" placeholder="Password">
                  </div>
                </div>
              </div>
              
              <button type="submit" class="btn btn-success">
                <i class="fa fa-plus"></i> Ajouter la console
              </button>
            </form>
          </div>
        </div>
        
        <!-- Liste des consoles -->
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-list"></i> Consoles configurées</h3>
          </div>
          <div class="panel-body">
            <?php if(empty($console_wrappers)): ?>
              <p class="text-muted">Aucune console configurée.</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th>Nom</th>
                      <th>URL</th>
                      <th>Type</th>
                      <th>État</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach($console_wrappers as $wrapper): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($wrapper['machine_name']); ?></td>
                        <td><?php echo htmlspecialchars($wrapper['console_url']); ?></td>
                        <td><?php echo htmlspecialchars($wrapper['console_type']); ?></td>
                        <td>
                          <?php if($wrapper['enabled']): ?>
                            <span class="label label-success"><i class="fa fa-check"></i> Activé</span>
                          <?php else: ?>
                            <span class="label label-default"><i class="fa fa-times"></i> Désactivé</span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <form method="POST" action="?admin&console_wrappers" style="display:inline;">
                            <input type="hidden" name="console_wrapper_action" value="toggle">
                            <input type="hidden" name="wrapper_id" value="<?php echo $wrapper['id']; ?>">
                            <button type="submit" class="btn btn-xs btn-<?php echo $wrapper['enabled'] ? 'warning' : 'success'; ?>">
                              <i class="fa fa-<?php echo $wrapper['enabled'] ? 'pause' : 'play'; ?>"></i>
                            </button>
                          </form>
                          
                          <form method="POST" action="?admin&console_wrappers" style="display:inline;">
                            <input type="hidden" name="console_wrapper_action" value="test">
                            <input type="hidden" name="wrapper_id" value="<?php echo $wrapper['id']; ?>">
                            <button type="submit" class="btn btn-xs btn-info">
                              <i class="fa fa-plug"></i> Tester
                            </button>
                          </form>
                          
                          <form method="POST" action="?admin&console_wrappers" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette console ?');">
                            <input type="hidden" name="console_wrapper_action" value="delete">
                            <input type="hidden" name="wrapper_id" value="<?php echo $wrapper['id']; ?>">
                            <button type="submit" class="btn btn-xs btn-danger">
                              <i class="fa fa-trash"></i>
                            </button>
                          </form>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
        
        <!-- Bouton retour -->
        <div class="row">
          <div class="col-md-12">
            <a href="?admin" class="btn btn-default btn-block">
              <i class="fa fa-arrow-left"></i> Retour à l'administration
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

