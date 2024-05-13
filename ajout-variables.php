<?php 
// Haut de page
 require('header.php');
 ?>

     <!-- Pour faciliter la navigation  -->
     <div class="lien-navigation mx-2">
        <a href="/"><b>Accueil</b></a> / Objectif de la fonction et coefficients 
    </div>

<div class="simplexe-contenaire">
        <div class="simplexe-parametres mt-4">
            <form action="traitement.php" method="get">
                <div class="simplexe-but mt-2">
                    <label><b>Objectif de la fonction : Maximiser ou Minimiser ?</b></label>
                    <select name="objectif" class="form-select form-select-sm mt-1" aria-label="Small select example" required onchange="Objectif(this)">
                        <option value="" selected disabled hidden>Choisir l'objectif</option>
                        <option value="maximiser">Maximiser</option>
                        <option value="minimiser">Minimiser</option>
                    </select>
                    <div class="invalid-feedback">Ce champ est obligatoire.</div>
                </div>

                <div class="simplexe-fonction mt-4 masquer">
                    <label class="mb-1"><b>Fonction Objectif :</b></label><br>
                    <p id="objectif"></p>
                    <?php
                    $v_decisions = $_GET['v-decisions'];
                    for ($i = 1; $i <= $v_decisions; $i++) {
                        echo '<input required name="var' . $i . '" type="number" class="form-control"> <span>x' . $i . '</span>';
                        if ($i < $v_decisions) {
                            echo ' + '; 
                        }
                    }
                    ?>
                </div>
                <div class="simplexe-contraintes mt-3 masquer">
                    <label class="mb-1"><b>Contraintes :</b></label><br>
                    <?php
                    $v_decisions = $_GET['v-decisions'];
                    $contraintes = $_GET['contraintes'];
                    for ($i = 1; $i <= $contraintes; $i++) {
                        echo '<div class="contrainte">';
                        for ($j = 1; $j <= $v_decisions; $j++) {
                            echo '<input required name="cont' . $i . '_var' . $j . '" type="number" class="form-control" id="cont' . $i . '_var' . $j . '"> ';
                            echo '<label for="cont' . $i . '_var' . $j . '">x' . $j . '</label> ';
                            if ($j < $v_decisions) {
                                echo '+ ';
                            }
                        }
                        echo '<select name="cont' . $i . '_ineq" class="form-select form-select-sm" id="cont' . $i . '_ineq">';
                        echo '<option value="<="><=</option>';
                        echo '<option value=">=">>=</option>';
                        echo '</select> ';
                        echo '<input required name="cont' . $i . '_val" type="number" class="form-control" id="cont' . $i . '_val">';
                        echo '</div>';
                    }
                    ?>

                    <div class="positivite mt-4">
                        <?php
                        echo '<label><b>Contraintes de positivité :</b> ';
                        for ($i = 1; $i <= $v_decisions; $i++) {
                            echo 'x' . $i . ' >= 0';
                            if ($i < $v_decisions) {
                                echo ', '; 
                            }
                        }
                        echo '</label>';
                        ?>
                    </div>

                      <!-- Champs cachés pour envoyer les valeurs -->
                    <input type="hidden" name="v_decisions" value="<?php echo $_GET['v-decisions']; ?>">
                    <input type="hidden" name="contraintes_count" value="<?php echo $_GET['contraintes']; ?>">


                </div>
                <button type="submit" class="btn btn-success mt-4 masquer">Résoudre</button>
            </form>
        </div> 
    </div>

    <script>
        function Objectif(Objectif) {
            const objectif_choisi = Objectif.options[Objectif.selectedIndex];
            const simplexe_fonction = document.querySelector('.simplexe-fonction');
            const bouton = document.querySelector('.btn')
            const simplexe_contraintes = document.querySelector('.simplexe-contraintes');

            const objectif = document.getElementById('objectif');

            if (objectif_choisi.value === 'maximiser') {
                simplexe_fonction.classList.remove('masquer');
                simplexe_contraintes.classList.remove('masquer');
                bouton.classList.remove('masquer');
                objectif.innerHTML = "Max(z) = ";
            } else if (objectif_choisi.value === 'minimiser') {
                simplexe_fonction.classList.remove('masquer');
                simplexe_contraintes.classList.remove('masquer');
                bouton.classList.remove('masquer');
                objectif.innerHTML = "Min(z) = ";
            } else {
                simplexe_fonction.classList.add('masquer');
                simplexe_contraintes.classList.add('masquer');
                bouton.classList.add('masquer');

            }
        }
    </script>