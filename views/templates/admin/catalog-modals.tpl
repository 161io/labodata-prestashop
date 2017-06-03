{* Copyright (c) 161 SARL, https://161.io *}

<div class="modal fade" id="modal-labodata-import">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <div class="modal-title">
          <strong>{l s='Confirmation d\'importation ?' mod='labodata'}</strong>
          <span class="text-muted" data-bought="1">{l s='Vous avez déjà acquis cette fiche' mod='labodata'}</span>

        </div>
      </div>
      <div class="modal-body">
        <div class="alert alert-info">
          <strong>{l s='Avant d\'importer votre fiche produit, avez-vous pensé à créer les Caractéristiques depuis l\'onglet ci-dessus ?' mod='labodata'}</strong><br/>
          {l s='Pour un référencement naturel optimal, nous vous conseillons de remanier les textes sur vos produits phares.' mod='labodata'}<br/>
        </div>

        <p data-bought="0">
          {l s='Vous êtes sur le point d\'acquérir les contenus' mod='labodata'}
          <strong data-type="image">{l s='images' mod='labodata'}</strong> <strong data-type="content">{l s='descriptifs' mod='labodata'}</strong> <strong data-type="full">{l s='images ET descriptifs' mod='labodata'}</strong>
          {l s='de la fiche produit :' mod='labodata'}<br/>
          <strong data-val="title">X</strong> {l s='pour un montant de' mod='labodata'} <strong data-val="price">X&euro;</strong>
        </p>
        <p data-bought="1">
          {l s='Vous êtes sur le point d\'importer les contenus' mod='labodata'}
          <strong data-type="image">{l s='images' mod='labodata'}</strong> <strong data-type="content">{l s='descriptifs' mod='labodata'}</strong> <strong data-type="full">{l s='images ET descriptifs' mod='labodata'}</strong>
          {l s='de la fiche produit :' mod='labodata'}<br/>
          <strong data-val="title">X</strong>
        </p>

        <form action="#" class="radio">
          {l s='Mode d\'importation :' mod='labodata'}<br>
          <label><input type="radio" name="modal-action" value="edit" checked="checked">{l s='Importer cette fiche dans le produit existant' mod='labodata'} <em>({l s='si le produit n\'existe pas, il sera automatiquement créé' mod='labodata'})</em></label><br/>
          <label><input type="radio" name="modal-action" value="add">{l s='Créer un nouveau produit dans le catalogue' mod='labodata'}</label><br/>
          <label data-bought="0"><input type="radio" name="modal-action" value="buy">{l s='Acquérir sans importer' mod='labodata'} <em>({l s='vous pourrez utiliser cette fiche dans un second temps' mod='labodata'})</em></label>
        </form>
      </div>
      <div class="modal-footer">
        <div class="checkbox pull-left">
          <label><input type="checkbox" name="modal-ignore">{l s='Ne plus afficher cette fenêtre pendant cette session' mod='labodata'}</label>
        </div>

        <button type="button" class="btn btn-default" data-dismiss="modal">{l s='Annuler' mod='labodata'}</button>
        <button type="button" class="btn btn-success" data-submit="modal"><i class="icon-download"></i>
          <span data-bought="0">{l s='Acquérir' mod='labodata'} (<span data-val="price">X&euro;</span>)</span>
          <span data-bought="1">{l s='Importer (Gratuit)' mod='labodata'}</span>
        </button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="modal-labodata-import-group">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <div class="modal-title">
          <strong>{l s='Confirmation d\'importation par lot ?' mod='labodata'}</strong>
        </div>
      </div>
      <div class="modal-body">
        <div class="alert alert-info">
          <strong>{l s='Avant d\'importer vos fiches produits, avez-vous pensé à créer les Caractéristiques depuis l\'onglet ci-dessus ?' mod='labodata'}</strong><br/>
          {l s='Pour un référencement naturel optimal, nous vous conseillons de remanier les textes sur vos produits phares.' mod='labodata'}<br/>
          {l s='Le montant total ci-dessous ne prendra pas en compte les fiches déjà acquises.' mod='labodata'}<br/>
        </div>

        <p>
          {l s='Vous êtes sur le point d\'importer les contenus' mod='labodata'}
          <strong data-type="image">{l s='images' mod='labodata'}</strong> <strong data-type="content">{l s='descriptifs' mod='labodata'}</strong> <strong data-type="full">{l s='images ET descriptifs' mod='labodata'}</strong>
          {l s='de' mod='labodata'} <strong><span data-val="product">X</span> {l s='fiche(s)' mod='labodata'}</strong> {l s='produit(s) pour un montant de' mod='labodata'} <strong data-val="credit">X&euro;</strong><br/>
        </p>

        <form action="#" class="radio">
          {l s='Mode d\'importation :' mod='labodata'}<br>
          <label><input type="radio" name="modal-action" value="edit" checked="checked">{l s='Importer les fiches dans les produits existants' mod='labodata'} <em>({l s='si un des produits n\'existe pas, il sera automatiquement créé' mod='labodata'})</em></label><br/>
          <label><input type="radio" name="modal-action" value="add">{l s='Créer des nouveaux produits dans le catalogue' mod='labodata'} <em>({l s='vos produits existants ne seront pas impactés' mod='labodata'})</em></label><br/>
          <label><input type="radio" name="modal-action" value="buy">{l s='Acquérir sans importer' mod='labodata'} <em>({l s='vous pourrez utiliser ces fiches dans un second temps' mod='labodata'})</em></label>
        </form>

        <p class="text-center text-danger"><strong>{l s='Durant l\'importation, ne cliquez sur aucun bouton pour ne pas interrompre le traitement.' mod='labodata'}</strong></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{l s='Annuler' mod='labodata'}</button>
        <button type="button" class="btn btn-success" data-submit="modal"><i class="icon-download"></i>
          <span data-bought="0">{l s='Acquérir' mod='labodata'} (<span data-val="credit">X&euro;</span>)</span>
          <span data-bought="1">{l s='Importer (Gratuit)' mod='labodata'}</span>
        </button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="modal-labodata-credit">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <div class="modal-title"><strong>{l s='Attention' mod='labodata'}</strong>
        </div>
      </div>
      <div class="modal-body">
        {l s='Vous ne disposez pas de crédit suffisant pour acccèder à cette(ces) fiche(s) produit(s).' mod='labodata'}<br/>
        {l s='Souhaitez-vous approvisionner votre compte ?' mod='labodata'}
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{l s='Non' mod='labodata'}</button>
        <button type="button" class="btn btn-success" data-submit="modal">{l s='Oui' mod='labodata'}</button>
      </div>
    </div>
  </div>
</div>
