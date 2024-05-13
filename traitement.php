<?php
// Haut de page
require('header.php');
?>

<!-- Pour faciliter la navigation  -->
<div class="lien-navigation mx-2">
   <a href="index.php"><b>Accueil</b></a> / <a href="ajout-variables.php?v-decisions=<?=$_GET['v_decisions']?>&contraintes=<?=$_GET['contraintes_count']?>"><b>Objectif de la fonction et coefficients</b></a> / Solution du simplexe
</div>

<?php
// Fonction pour résoudre le problème du simplexe
function solveSimplex($numVariables, $numConstraints, $A, $b, $c) {
    $MAX_VARIABLES = 10;
    $MAX_CONSTRAINTS = 10;
    $tableauSimplexe = array_fill(0, $MAX_CONSTRAINTS + 1, array_fill(0, $MAX_VARIABLES + $numConstraints + 1, 0));
    $pivotColumn = 0; // Initialisation de $pivotColumn
    $pivotRow = -1; // Initialisation de $pivotRow
    $iteration = 0;
    $iterationsTable = array();

    // Initialisation du tableau du simplexe
    for ($i = 0; $i <= $numConstraints; $i++) {
        for ($j = 0; $j <= $numVariables + $numConstraints; $j++) {
            $tableauSimplexe[$i][$j] = 0;
        }
    }

    // Remplissage du tableau du simplexe avec les coefficients de A et b
    for ($i = 0; $i < $numConstraints; $i++) {
        for ($j = 0; $j < $numVariables; $j++) {
            $tableauSimplexe[$i][$j] = $A[$i][$j];
        }
        $tableauSimplexe[$i][$numVariables + $i] = 1;
        $tableauSimplexe[$i][$numVariables + $numConstraints] = $b[$i];
    }

    // Ajout de la fonction objectif dans la dernière ligne du tableau
    for ($j = 0; $j < $numVariables; $j++) {
        $tableauSimplexe[$numConstraints][$j] = -$c[$j];
    }

    // Application de l'algorithme du simplexe
    while (true) {
        $iterationData = array();
        $pivotColumn = 0;
        // Recherche de la colonne pivot
        for ($j = 0; $j < $numVariables + $numConstraints; $j++) {
            if ($tableauSimplexe[$numConstraints][$j] < $tableauSimplexe[$numConstraints][$pivotColumn]) {
                $pivotColumn = $j;
            }
        }
        if ($tableauSimplexe[$numConstraints][$pivotColumn] >= 0) {
            break; // Solution optimale trouvée
        }

        $pivotRow = -1;
        // Recherche de la ligne pivot
        for ($i = 0; $i < $numConstraints; $i++) {
            if ($tableauSimplexe[$i][$pivotColumn] > 0) {
                if ($pivotRow == -1 || $tableauSimplexe[$i][$numVariables + $numConstraints] / $tableauSimplexe[$i][$pivotColumn] < $tableauSimplexe[$pivotRow][$numVariables + $numConstraints] / $tableauSimplexe[$pivotRow][$pivotColumn]) {
                    $pivotRow = $i;
                }
            }
        }
        if ($pivotRow == -1) {
            return array('solution' => null, 'iterationsTable' => null, 'unbounded' => true);
        }

        // Mise à jour du tableau du simplexe
        $pivot = $tableauSimplexe[$pivotRow][$pivotColumn];
        for ($i = 0; $i <= $numConstraints; $i++) {
            for ($j = 0; $j <= $numVariables + $numConstraints; $j++) {
                if ($i != $pivotRow && $j != $pivotColumn) {
                    $tableauSimplexe[$i][$j] -= $tableauSimplexe[$pivotRow][$j] * $tableauSimplexe[$i][$pivotColumn] / $pivot;
                }
            }
        }
        for ($j = 0; $j <= $numVariables + $numConstraints; $j++) {
            if ($j != $pivotColumn) {
                $tableauSimplexe[$pivotRow][$j] /= $pivot;
            }
        }
        for ($i = 0; $i <= $numConstraints; $i++) {
            if ($i != $pivotRow) {
                $tableauSimplexe[$i][$pivotColumn] /= -$pivot;
            }
        }
        $tableauSimplexe[$pivotRow][$pivotColumn] = 1 / $pivot;

        // Stockage des données de l'itération
        $iterationData['iteration'] = ++$iteration;
        $iterationData['tableau'] = $tableauSimplexe;
        $iterationsTable[] = $iterationData;
    }

    // Stockage de la solution optimale
    $solution = array();
    for ($i = 0; $i < $numVariables; $i++) {
        $base = -1;
        for ($j = 0; $j < $numConstraints; $j++) {
            if ($tableauSimplexe[$j][$i] == 1) {
                if ($base == -1) {
                    $base = $j;
                } else {
                    $base = -2; // Plusieurs variables de base pour cette colonne
                    break;
                }
            }
        }
        if ($base >= 0) {
            $solution["x" . ($i + 1)] = $tableauSimplexe[$base][$numVariables + $numConstraints];
        } else if ($base == -1) {
            $solution["x" . ($i + 1)] = 0.00;
        } else {
            $solution["x" . ($i + 1)] = "Variable multiple";
        }
    }
    $solution["z"] = $tableauSimplexe[$numConstraints][$numVariables + $numConstraints];

    // Retourne la solution optimale et le tableau des itérations
    return array('solution' => $solution, 'iterationsTable' => $iterationsTable, 'unbounded' => false);
}

// Lecture des entrées depuis l'URL
$numVariables = $_GET['v_decisions'];
$numConstraints = $_GET['contraintes_count'];
$c = array(); // Coefficients de la fonction objective
$A = array(); // Coefficients des contraintes
$b = array(); // Valeurs des contraintes

// Lecture des coefficients de la fonction objectif
for ($i = 1; $i <= $numVariables; $i++) {
    $c[] = $_GET['var' . $i];
}

// Lecture des coefficients et valeurs des contraintes
for ($i = 1; $i <= $numConstraints; $i++) {
    $A_row = array();
    for ($j = 1; $j <= $numVariables; $j++) {
        $A_row[] = $_GET['cont' . $i . '_var' . $j];
    }
    $A[] = $A_row;
    $b[] = $_GET['cont' . $i . '_val'];
}

// Résolution du problème du simplexe
$solveData = solveSimplex($numVariables, $numConstraints, $A, $b, $c);

// Affichage de la solution optimale
echo '<div class="container">';
echo '<div class="row justify-content-center">';
echo '<div class="col-md-8">';

echo '<div class="titre-simplexe">
        <h3>Solution du simplexe</h3>
    </div>';

// Affichage du message pour le problème non borné
if ($solveData['unbounded']) {
    echo '<div class="titre-simplexe">
            <h4 class="text-center mt-4" style="color:red;"><b>Problème non borné</b></h4>
            <div class="text-center mt-4"><a href="ajout-variables.php?v-decisions=' . $_GET['v_decisions'] . '&contraintes=' . $_GET['contraintes_count'] . '"><button type="button" class="btn btn-lg btn-secondary">Retour</button></a></div>
        </div>';
} else {
    // Affichage de la solution optimale
    echo '<div class="simplexe-contenaire">
            <div class="simplexe-parametres">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Solution optimale</th>
                            <th scope="col">Valeurs</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($solveData['solution'] as $key => $value) {
        echo '<tr>
                <th scope="row">' . $key . '</th>
                <td>' . (($key == 'z') ? '<span style="color:green;font-weight:bold;">' . (is_numeric($value) ? number_format($value, 2) : $value) . '</span>' : (is_numeric($value) ? number_format($value, 2) : $value)) . '</td>
            </tr>';
    }
    
    echo '</tbody>
        </table>
    </div>
</div>';


    echo '<div class ="noter-bien">
        <div class="noter-fils">
        NB : On optient pas toujours les bonnes valeurs des xi (x1, x2 ...) dans la solution ci-dessus par rapport à la complexité du problème (affectation difficile).
        Pour éviter donc de se tromper, consultez alors les valeurs des xi de la solution dans le dernier tableau des itérations au niveau de la dernière colonne.
        La valeur de z sera toujours la bonne !
        </div>
    </div>';

    // Affichage du tableau des différentes itérations
    echo '<div class="titre-simplexe">
            <h3>Tableau des itérations</h3>
        </div>
        <div class="resolution-contenaire mb-4">
                <div class="table-responsive" style="overflow-y: auto;">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">Iteration</th>
                                <th scope="col">Tableau du simplexe</th>
                            </tr>
                        </thead>
                        <tbody>';

    foreach ($solveData['iterationsTable'] as $iterationData) {
        echo '<tr>
                <td>' . $iterationData['iteration'] . '</td>
                <td>
                    <table class="table table-bordered">';

        foreach ($iterationData['tableau'] as $row) {
            echo '<tr>';
            foreach ($row as $cell) {
                echo '<td>' . number_format($cell, 2) . '</td>';
            }
            echo '</tr>';
        }

        echo '</table>
                </td>
            </tr>';
    }

    echo '</tbody>
        </table>
    </div>
</div>';
}
echo '</div>';
echo '</div>';
echo '</div>';
?>

<?php 
// Pied de de page
 require('footer.php');
 ?>

</body>
</html>