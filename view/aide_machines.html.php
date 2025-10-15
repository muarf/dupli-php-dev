<!-- CSS pour les icônes de machines -->
<style>
.machine-icon {
    display: inline-block;
    margin-right: 8px;
    font-size: 1.2em;
}

.duplicopieur-icon {
    color: #337ab7;
}

.photocopieur-icon {
    color: #5cb85c;
}

.aide-content {
    background-color: #f9f9f9;
    border-left: 4px solid #337ab7;
    padding: 20px;
    margin: 15px 0;
    border-radius: 4px;
}

.search-box {
    margin-bottom: 30px;
}

.machine-card {
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
}

.machine-header {
    background-color: #f5f5f5;
    padding: 15px;
    cursor: pointer;
    border-bottom: 1px solid #ddd;
}

.machine-header:hover {
    background-color: #e9e9e9;
}

.machine-header h4 {
    margin: 0;
    display: flex;
    align-items: center;
}

.machine-content {
    padding: 20px;
    display: none;
}

.machine-content.active {
    display: block;
}

.no-aide {
    text-align: center;
    color: #999;
    font-style: italic;
    padding: 40px;
}

.accordion-toggle {
    float: right;
    font-size: 1.2em;
    transition: transform 0.3s ease;
}

.accordion-toggle.active {
    transform: rotate(180deg);
}
</style>

<div class="section">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <h1 class="text-center">
          <i class="fa fa-question-circle" style="color: #337ab7;"></i>
          Aide & Tutoriels des Machines
        </h1>
        <hr>
        
        <div class="row">
          <div class="col-md-12">
            <div class="alert alert-info text-center">
              <i class="fa fa-info-circle"></i>
              <strong>Bienvenue dans l'aide des machines !</strong>
              Sélectionnez une machine ci-dessous pour consulter son aide et ses tutoriels.
            </div>
          </div>
        </div>
        
        <!-- Sélecteur de machine -->
        <div class="row">
          <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
              <div class="panel-heading">
                <h3 class="panel-title">
                  <i class="fa fa-search"></i> Rechercher une machine
                </h3>
              </div>
              <div class="panel-body">
                <form method="GET" id="machine-search-form">
                  <input type="hidden" name="aide_machines" value="1">
                  <div class="form-group">
                    <label for="machine-select">Sélectionner une machine :</label>
                    <select class="form-control" id="machine-select" name="machine">
                      <option value="">-- Choisir une machine --</option>
                      <?php if(isset($all_machines) && !empty($all_machines)): ?>
                        <?php foreach($all_machines as $machine): ?>
                          <option value="<?= htmlspecialchars($machine) ?>" 
                                  <?= (isset($machine_selectionnee) && $machine_selectionnee === $machine) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($machine) ?>
                          </option>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </select>
                  </div>
                  <div class="text-center">
                    <button type="submit" class="btn btn-primary">
                      <i class="fa fa-search"></i> Consulter l'aide
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Affichage de l'aide sélectionnée -->
        <?php if(isset($aide_selectionnee) && !empty($aide_selectionnee)): ?>
          <div class="row">
            <div class="col-md-12">
              <div class="panel panel-primary">
                <div class="panel-heading">
                  <h3 class="panel-title">
                    <i class="fa fa-print"></i>
                    Aide pour : <?= htmlspecialchars($aide_selectionnee['machine']) ?>
                  </h3>
                </div>
                <div class="panel-body">
                  <div class="aide-content">
                    <?= $aide_selectionnee['contenu_aide'] ?>
                  </div>
                  <div class="text-muted text-right">
                    <small>
                      <i class="fa fa-calendar"></i>
                      Dernière mise à jour : <?= date('d/m/Y à H:i', strtotime($aide_selectionnee['date_modification'])) ?>
                    </small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php elseif(isset($machine_selectionnee) && empty($aide_selectionnee)): ?>
          <div class="row">
            <div class="col-md-12">
              <div class="alert alert-warning text-center">
                <i class="fa fa-exclamation-triangle"></i>
                <strong>Aucune aide disponible</strong> pour la machine "<?= htmlspecialchars($machine_selectionnee) ?>".
                <br>
                <small>Contactez l'administrateur pour ajouter des informations d'aide pour cette machine.</small>
              </div>
            </div>
          </div>
        <?php endif; ?>
        
        <!-- Liste de toutes les aides disponibles -->
        <?php if(isset($aides_by_machine) && !empty($aides_by_machine)): ?>
          <div class="row">
            <div class="col-md-12">
              <div class="panel panel-default">
                <div class="panel-heading">
                  <h3 class="panel-title">
                    <i class="fa fa-list"></i> 
                    Toutes les aides disponibles
                    <span class="badge"><?= count($aides_by_machine) ?> machine(s)</span>
                  </h3>
                </div>
                <div class="panel-body">
                  <div class="row">
                    <?php foreach($aides_by_machine as $machine_name => $aide): ?>
                      <div class="col-md-6 col-lg-4">
                        <div class="machine-card">
                          <div class="machine-header" onclick="toggleMachineContent('<?= $aide['id'] ?>')">
                            <h4>
                              <i class="fa fa-print machine-icon"></i>
                              <?= htmlspecialchars($machine_name) ?>
                              <i class="fa fa-chevron-down accordion-toggle" id="toggle-<?= $aide['id'] ?>"></i>
                            </h4>
                          </div>
                          <div class="machine-content" id="content-<?= $aide['id'] ?>">
                            <div class="aide-content">
                              <?= $aide['contenu_aide'] ?>
                            </div>
                            <div class="text-muted text-right">
                              <small>
                                <i class="fa fa-calendar"></i>
                                Mis à jour : <?= date('d/m/Y', strtotime($aide['date_modification'])) ?>
                              </small>
                            </div>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php else: ?>
          <div class="row">
            <div class="col-md-12">
              <div class="alert alert-info text-center">
                <i class="fa fa-info-circle"></i>
                Aucune aide n'est encore disponible pour les machines.
                <br>
                <small>Contactez l'administrateur pour ajouter des informations d'aide.</small>
              </div>
            </div>
          </div>
        <?php endif; ?>
        
        <!-- Navigation -->
        <div class="row">
          <div class="col-md-12">
            <div class="panel panel-default">
              <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-arrow-left"></i> Navigation</h3>
              </div>
              <div class="panel-body">
                <a href="?accueil" class="btn btn-primary">
                  <i class="fa fa-arrow-left"></i> Retour à l'accueil
                </a>
                <a href="?devis" class="btn btn-success">
                  <i class="fa fa-calculator"></i> Faire un devis
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Fonction pour basculer l'affichage du contenu d'une machine
function toggleMachineContent(id) {
    var content = document.getElementById('content-' + id);
    var toggle = document.getElementById('toggle-' + id);
    
    if (content.classList.contains('active')) {
        content.classList.remove('active');
        toggle.classList.remove('active');
    } else {
        content.classList.add('active');
        toggle.classList.add('active');
    }
}

// Auto-submit du formulaire de sélection
document.getElementById('machine-select').addEventListener('change', function() {
    if (this.value) {
        document.getElementById('machine-search-form').submit();
    }
});

// Animation pour les accordéons
$(document).ready(function() {
    $('.machine-header').click(function() {
        $(this).find('.accordion-toggle').toggleClass('active');
    });
});
</script>
