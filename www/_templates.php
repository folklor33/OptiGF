<h4>BLOC DE CODE</h4>
<div class="row">
    <div class="col-md-7">
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">HEADER</h6>
            </div>
            <div class="card-body">
                <p class="card-text">TEXTE</p>
                <code class="small">
                    CODE
                </code>
            </div>
        </div>
    </div>
</div>

<h4>MENUS DEROULANTS</h4>
<div class="accordion" id="ONGLET0">
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#ONGLET1">
                BUTTON1
            </button>
        </h2>
        <div id="ONGLET1" class="accordion-collapse collapse show" data-bs-parent="#ONGLET0">
            <div class="accordion-body">
                BODY
            </div>
        </div>
    </div>
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ONGLET2">
                BUTTON2
            </button>
        </h2>
        <div id="ONGLET2" class="accordion-collapse collapse" data-bs-parent="#ONGLET0">
            <div class="accordion-body">
                BODY
            </div>
        </div>
    </div>
</div>


<h4 class="mt-4">TABLEAU</h4>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>COLOMN1</th>
                <th>COLOMN2</th>
                <th>COLOMN3</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>


CARTES
<div class="row mt-4">
    <div class="col-md-7">
        <div class="card border-primary">
            <div class="card-body">
                <h5 class="card-title text-primary">PowerShell</h5>
                <p class="card-text">Scripts et commandes pour l'administration Windows.</p>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card border-success">
            <div class="card-body">
                <h5 class="card-title text-success">Group Policy</h5>
                <p class="card-text">Gestion centralisée des configurations système.</p>
            </div>
        </div>
    </div>
</div>