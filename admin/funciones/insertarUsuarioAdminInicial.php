<?php
function insertarUsuarioAdminInicial($pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE usuario=:u");
    $stmt->execute([':u'=>'jocarsa']);
    $existe = $stmt->fetchColumn();
    if (!$existe) {
        $ins = $pdo->prepare("INSERT INTO users(nombre,email,usuario,password)
                              VALUES(:n,:e,:u,:p)");
        $ins->execute([
            ':n'=>'Jose Vicente Carratala',
            ':e'=>'info@josevicentecarratala.com',
            ':u'=>'jocarsa',
            ':p'=>'jocarsa'
        ]);
    }
}
?>
