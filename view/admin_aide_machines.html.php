<!-- CSS pour l'éditeur WYSIWYG -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">

<div class="section">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <h1 class="text-center">Gestion des aides machines</h1>
        <hr>
        
        <!-- Messages d'erreur/succès -->
        <?php if(isset($aide_error)): ?>
          <div class="alert alert-danger">
            <strong>Erreur :</strong> <?= htmlspecialchars($aide_error) ?>
          </div>
        <?php endif; ?>
        
        <?php if(isset($aide_success)): ?>
          <div class="alert alert-success">
            <strong>Succès :</strong> <?= htmlspecialchars($aide_success) ?>
          </div>
        <?php endif; ?>
        
        <!-- Section Ajouter une aide -->
        <div class="row">
          <div class="col-md-12">
            <div class="panel panel-primary">
              <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-plus"></i> Ajouter une aide</h3>
              </div>
              <div class="panel-body">
                <form method="POST" id="add-aide-form">
                  <input type="hidden" name="action" value="add_aide">
                  
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="machine">Machine :</label>
                        <select class="form-control" id="machine" name="machine" required>
                          <option value="">Sélectionner une machine</option>
                          <?php if(isset($all_machines) && !empty($all_machines)): ?>
                            <?php foreach($all_machines as $machine): ?>
                              <option value="<?= htmlspecialchars($machine) ?>"><?= htmlspecialchars($machine) ?></option>
                            <?php endforeach; ?>
                          <?php endif; ?>
                        </select>
                      </div>
                    </div>
                  </div>
                  
                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label for="contenu_aide">Contenu de l'aide :</label>
                        <textarea class="form-control" id="contenu_aide" name="contenu_aide" rows="10" required></textarea>
                        <small class="text-muted">Vous pouvez utiliser du HTML pour formater le contenu (titres, listes, images, etc.)</small>
                      </div>
                    </div>
                  </div>
                  
                  <div class="row">
                    <div class="col-md-12 text-center">
                      <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fa fa-plus"></i> Ajouter l'aide
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Section Liste des aides -->
        <div class="row">
          <div class="col-md-12">
            <div class="panel panel-default">
              <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-list"></i> Aides existantes</h3>
              </div>
              <div class="panel-body">
                <?php if(isset($aides) && !empty($aides)): ?>
                  <div class="table-responsive">
                    <table class="table table-striped table-hover">
                      <thead>
                        <tr>
                          <th>Machine</th>
                          <th>Contenu (aperçu)</th>
                          <th>Date création</th>
                          <th>Dernière modification</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach($aides as $aide): ?>
                          <tr>
                            <td>
                              <strong><?= htmlspecialchars($aide['machine']) ?></strong>
                            </td>
                            <td>
                              <div class="aide-preview">
                                <?= htmlspecialchars(substr(strip_tags($aide['contenu_aide']), 0, 100)) ?>...
                              </div>
                            </td>
                            <td><?= date('d/m/Y à H:i', strtotime($aide['date_creation'])) ?></td>
                            <td><?= date('d/m/Y à H:i', strtotime($aide['date_modification'])) ?></td>
                            <td>
                              <button class="btn btn-sm btn-info edit-aide" data-id="<?= $aide['id'] ?>">
                                <i class="fa fa-edit"></i> Modifier
                              </button>
                              <button class="btn btn-sm btn-danger delete-aide" data-id="<?= $aide['id'] ?>">
                                <i class="fa fa-trash"></i> Supprimer
                              </button>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php else: ?>
                  <div class="alert alert-info text-center">
                    <i class="fa fa-info-circle"></i> Aucune aide enregistrée pour le moment.
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Navigation -->
        <div class="row">
          <div class="col-md-12">
            <div class="panel panel-default">
              <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-arrow-left"></i> Navigation</h3>
              </div>
              <div class="panel-body">
                <a href="?admin" class="btn btn-primary">
                  <i class="fa fa-arrow-left"></i> Retour à l'administration
                </a>
                <a href="?aide_machines" class="btn btn-success">
                  <i class="fa fa-eye"></i> Voir la page publique
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal d'édition -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Modifier l'aide</h4>
      </div>
      <div class="modal-body">
        <form id="edit-form">
          <input type="hidden" id="edit_id" name="id">
          
          <div class="form-group">
            <label for="edit_machine">Machine :</label>
            <select class="form-control" id="edit_machine" name="machine" required>
              <option value="">Sélectionner une machine</option>
              <?php if(isset($all_machines) && !empty($all_machines)): ?>
                <?php foreach($all_machines as $machine): ?>
                  <option value="<?= htmlspecialchars($machine) ?>"><?= htmlspecialchars($machine) ?></option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
          
          <div class="form-group">
            <label for="edit_contenu_aide">Contenu de l'aide :</label>
            <textarea class="form-control" id="edit_contenu_aide" name="contenu_aide" rows="10" required></textarea>
            <small class="text-muted">Vous pouvez utiliser du HTML pour formater le contenu</small>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-primary" id="save-edit">Sauvegarder</button>
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/lang/summernote-fr-FR.min.js"></script>

<script>
$(document).ready(function() {
    // Initialiser l'éditeur WYSIWYG
    $('#contenu_aide, #edit_contenu_aide').summernote({
        height: 300,
        lang: 'fr-FR',
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });
    
    // Gestion de l'édition
    $('.edit-aide').click(function() {
        var id = $(this).data('id');
        
        $.get('?admin_aide_machines&ajax=get_aide&id=' + id)
            .done(function(response) {
                if (response.success) {
                    var aide = response.aide;
                    $('#edit_id').val(aide.id);
                    $('#edit_machine').val(aide.machine);
                    
                    // Mettre à jour le contenu de l'éditeur
                    $('#edit_contenu_aide').summernote('code', aide.contenu_aide);
                    
                    $('#editModal').modal('show');
                } else {
                    alert('Erreur lors du chargement de l\'aide: ' + (response.error || 'Erreur inconnue'));
                }
            })
            .fail(function(xhr, status, error) {
                alert('Erreur lors du chargement de l\'aide: ' + error);
            });
    });
    
    // Sauvegarde de l'édition
    $('#save-edit').click(function() {
        var id = $('#edit_id').val();
        var formData = {
            action: 'edit_aide',
            id: id,
            machine: $('#edit_machine').val(),
            contenu_aide: $('#edit_contenu_aide').summernote('code')
        };
        
        $.post('?admin_aide_machines', formData)
            .done(function(response) {
                location.reload();
            })
            .fail(function(xhr, status, error) {
                alert('Erreur lors de la modification: ' + xhr.responseText);
            });
    });
    
    // Gestion de la suppression
    $('.delete-aide').click(function() {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette aide ?')) {
            var id = $(this).data('id');
            
            $.post('?admin_aide_machines', {
                action: 'delete_aide',
                id: id
            }).done(function(response) {
                location.reload();
            });
        }
    });
    
    // Soumission du formulaire d'ajout
    $('#add-aide-form').submit(function(e) {
        e.preventDefault();
        
        // Récupérer le contenu de l'éditeur
        var contenu_aide = $('#contenu_aide').summernote('code');
        
        var formData = {
            action: 'add_aide',
            machine: $('#machine').val(),
            contenu_aide: contenu_aide
        };
        
        $.post('?admin_aide_machines', formData)
            .done(function(response) {
                location.reload();
            })
            .fail(function(xhr, status, error) {
                alert('Erreur lors de l\'ajout: ' + xhr.responseText);
            });
    });
    
    // Fermer le modal et réinitialiser l'éditeur
    $('#editModal').on('hidden.bs.modal', function () {
        $('#edit_contenu_aide').summernote('reset');
    });
});
</script>
