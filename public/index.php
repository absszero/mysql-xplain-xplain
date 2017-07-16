<?php
require '../app/base.php';

use Jasny\MySQL\DB_Exception;
use Rap2hpoutre\MySQLExplainExplain\DB;
use Rap2hpoutre\MySQLExplainExplain\Explainer;
use Rap2hpoutre\MySQLExplainExplain\Row;
use Rap2hpoutre\MySQLExplainExplain\Table;

$query = '';
$explainer = null;

if (isset($_SESSION['mysql'])) {
    $db = new DB(
        $_SESSION['mysql']['host'],
        $_SESSION['mysql']['user'],
        $_SESSION['mysql']['pass'],
        $_SESSION['mysql']['base']
    );

    $db->setUp();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $query = $_POST['query'];
        try {
            // Contextual queries (on les exécute sans rien en faire)
            if (isset($_POST['context_queries'])) {
                $db->conn()->multi_query($_POST['context_queries']);
                // Code pour jeter les résultats à la poubelle
                do {
                    if ($res = $db->conn()->store_result()) {
                        $res->free();
                    }
                } while ($db->conn()->more_results() && $db->conn()->next_result());
            }
            // Recuperation des résultats de la requete
            $explain_results = $db->conn()->fetchAll(
                (strpos(strtolower($query), 'explain') === false ? 'EXPLAIN ' : '') . $query
            );
            // Création de l'Explainer
            $explainer = new Explainer($query);
            if (is_array($explain_results)) {
                         $table = new Table($query);
                         $tables = $table->getTables();

                foreach ($explain_results as $result) {
                    // Création de la ligne et attachement à l'explainer
                    $explainer->addRow(
                        new Row(
                            $result,
                            $explainer,
                            $tables
                        )
                    );
                }
            }
        } catch (DB_Exception $e) {
            $template->error = utf8_encode($e->getError());
        }
    }
} else {
    header('Location: config.php');
    exit;
}
// Affichage
$template->page = 'Home';
$template->explainer = $explainer;
$template->query = $query;
$template->mysql_base_doc_url = MYSQL_DOC_URL . DB::$version . '/en/explain-output.html';
echo $template->render('home');
