<?php
// Haut de page
require('header.php');
?>


<!-- Pour faciliter la navigation  -->
<div class="lien-navigation mx-2">
   <a href="index.php"><b>Accueil</b></a> / <a href="ajout-variables.php?v-decisions=<?=$_GET['v_decisions']?>&contraintes=<?=$_GET['contraintes_count']?>"><b>Objectif de la fonction et coefficients</b></a> / Solution du simplexe
</div>

<?php
// Fonction pour résoudre le problème du simplexe pour la maximisation
function solveMaximisation($numVariables, $numConstraints, $A, $b, $c) {
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

// Fonction pour résoudre le problème du simplexe pour la minimisation
function solveMinimisation($numVariables, $numConstraints, $A, $b, $c) {
    // Inverse des coefficients de la fonction objectif pour la minimisation
    $c = array_map(function($value) { return -$value; }, $c);
    // Appel de la fonction de résolution pour la maximisation avec les coefficients inversés
    return solveMaximisation($numVariables, $numConstraints, $A, $b, $c);
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

// Vérification s'il faut maximiser ou minimiser
$minimiser = isset($_GET['objectif']) && $_GET['objectif'] == 'minimiser';

// Résolution du problème du simplexe en fonction de l'objectif (maximisation ou minimisation)
if ($minimiser) {
    $solveData = solveMinimisation($numVariables, $numConstraints, $A, $b, $c);
} else {
    $solveData = solveMaximisation($numVariables, $numConstraints, $A, $b, $c);
}

// Affichage de la solution optimale
echo '<div class="container">';
echo '<div class="row justify-content-center">';
echo '<div class="col-md-8">';

echo '<div class="titre-simplexe">';
echo '<h3>Solution du simplexe</h3>';
echo '</div>';

// Affichage du message pour le problème non borné
if ($solveData['unbounded']) {
    echo '<div class="titre-simplexe">';
    echo '<h4 class="text-center mt-4" style="color:red;"><b>Problème non borné</b></h4>';
    echo '<div class="text-center mt-4"><a href="ajout-variables.php?v-decisions=' . $_GET['v_decisions'] . '&contraintes=' . $_GET['contraintes_count'] . '"><button type="button" class="btn btn-lg btn-secondary">Retour</button></a></div>';
    echo '</div>';
} else {
    // Affichage de la solution optimale
    echo '<div class="simplexe-contenaire">';
    echo '<div class="simplexe-parametres">';
    echo '<table class="table">';
    echo '<thead>';
    echo '<tr>';
    echo '<th scope="col">Solution optimale</th>';
    echo '<th scope="col">Valeurs</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    foreach ($solveData['solution'] as $key => $value) {
        echo '<tr>';
        echo '<th scope="row">' . $key . '</th>';
        echo '<td>' . (($key == 'z') ? '<span style="color:green;font-weight:bold;">' . (is_numeric($value) ? number_format($value, 2) : $value) . '</span>' : (is_numeric($value) ? number_format($value, 2) : $value)) . '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    echo '</div>';

    echo '<hr>'; // Ajout de la ligne horizontale ici

    if(isset($_GET['objectif']) && $_GET['objectif'] !== 'minimiser'){
    echo '<div class ="noter-bien">';
    echo '<div class="noter-fils">';
    echo 'NB : On obtient pas toujours les bonnes valeurs des xi (x1, x2 ...) dans la solution ci-dessus par rapport au fait que leurs positions changent (affectation difficile avec cet algorithme). Pour éviter donc de se tromper, consultez alors les valeurs des xi de la solution dans le dernier tableau des itérations au niveau de la dernière colonne au-dessus de la valeur de z. La valeur de z sera toujours la bonne !';
    echo '</div>';
    echo '</div>';
    }else{
        echo "L'algorithme a des difficultés à trouver les valeurs des xi mais le z est toujours le bon.
        Cependant, un autre algorithme est aussi disponible où vous rentrez juste les coefficients, décidez de maximiser ou minimiser, entrez le nombre de variables et contraintes, ensuite obtiendrez les xi et le z de la solution (version de début en python avant la mise en ligne par convertion en PHP ce qui a causé ce problème).
        Lien : <a href='https://colab.research.google.com/drive/1ImwQgbvkU8jSVIruBvSH8MuZ8TijYfx8?usp=sharing' target=_blank>OnePhaseSimple</a> (exécutez via le bouton situé à gauche tu texte # membres du groupe)";
    }

    if(isset($_GET['objectif']) && $_GET['objectif'] !== 'minimiser'){
    // Affichage du tableau des différentes itérations
    echo '<div class="titre-simplexe">';
    echo '<h3>Tableau des itérations</h3>';
    echo '</div>';
    echo '<div class="resolution-contenaire mb-4">';
    echo '<div class="table-responsive" style="overflow-y: auto;">';
    echo '<table class="table table-striped table-bordered">';
    echo '<thead>';
    echo '<tr>';
    echo '<th scope="col">Iteration</th>';
    echo '<th scope="col">Tableau du simplexe</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    foreach ($solveData['iterationsTable'] as $iterationData) {
        echo '<tr>';
        echo '<td>' . $iterationData['iteration'] . '</td>';
        echo '<td>';
        echo '<table class="table table-bordered">';

        foreach ($iterationData['tableau'] as $row) {
            echo '<tr>';
            foreach ($row as $cell) {
                echo '<td>' . number_format($cell, 2) . '</td>';
            }
            echo '</tr>';
        }

        echo '</table>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    echo '</div>';
   }
}

echo '</div>';
echo '</div>';
echo '</div>';


// Pied de de page
 require('footer.php');
 ?>

</body>
</html>