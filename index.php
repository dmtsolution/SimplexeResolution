<?php 
// Haut de page
 require('header.php');
 ?>

    <div class="titre-simplexe">
        <h3>Méthode de Simplexe en une phase</h3>
    </div>

    <div class="simplexe-contenaire">
        <div class="simplexe-parametres">
            <form action="ajout-variables.php" method="get">
                <div class="simplexe-variables-decisions mt-2">
                    <label><b>Nombre de variables de décisions :</b></label>
                    <select name="v-decisions" class="form-select form-select-sm mt-1" aria-label="Small select example" required>
                        <option value="" selected disabled hidden>Choisir un nombre</option>
                        <?php for ($i = 1; $i <= 10; $i++) { ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php } ?>
                    </select>
                    <div class="invalid-feedback">Ce champ est obligatoire.</div>
                </div>
                <div class="simplexe-contraintes mt-4 mb-4">
                    <label><b>Nombre de contraintes :</b></label>
                    <select name="contraintes" class="form-select form-select-sm mt-1" aria-label="Small select example" required>
                        <option value="" selected disabled hidden>Choisir un nombre</option>
                        <?php for ($i = 1; $i <= 10; $i++) { ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php } ?>
                    </select>
                    <div class="invalid-feedback">Ce champ est obligatoire.</div>
                </div>
                <button class="btn btn-primary">CONTINUER</button>
            </form>
        </div>
    </div>

<?php 
// Pied de de page
 require('footer.php');
 ?>
</body>
</html>

