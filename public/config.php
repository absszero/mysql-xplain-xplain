<?php
use Rap2hpoutre\MySQLExplainExplain\DB;

require '../app/base.php';

// Enregistrement de Configuration MySQl
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach (['host', 'user', 'pass', 'base'] as $query) {
            if (!isset($_POST[$query])) {
                $_POST[$query] = '';
            }
        }
        $_SESSION['mysql'] = array(
            'host' => $_POST['host'],
            'user' => $_POST['user'],
            'pass' => $_POST['pass'],
            'base' => $_POST['base'],
        );

        $db = new DB(
            $_POST['host'],
            $_POST['user'],
            $_POST['pass'],
            $_POST['base']
        );
        $db->setUp();

        // Login permanent : à faire plus propre et plus secure
        $conf_dir = '../conf';
        if (isset($_POST['permanent_login']) && $_POST['permanent_login'] == '1') {
            if (!file_exists($conf_dir)) {
                mkdir($conf_dir);
            }
            file_put_contents(
                $conf_dir . '/db.php',
                '<?php return array(
                    \'host\' => \'' . $_POST['host'] . '\',
                    \'user\' => \'' . $_POST['user'] . '\',
                    \'pass\' => \'' . $_POST['pass'] . '\',
                    \'base\' => \'' . $_POST['base'] . '\'
                );'
            );
        } else {
            if (file_exists($conf_dir . '/db.php')) {
                unlink($conf_dir . '/db.php');
            }
            $_SESSION['mysql'] = array(
                'host' => $_POST['host'],
                'user' => $_POST['user'],
                'pass' => $_POST['pass'],
                'base' => $_POST['base']
            );
        }
        // Redirection
        $_SESSION['flash_message'] = 'MySQL connection successful :)';
        header('Location: index.php');
        exit;
    } catch (\Exception $e) {
        $template->error = utf8_encode($e->getMessage());
    }
}

// Affichage
$template->page = 'Config';
echo $template->render('config');
