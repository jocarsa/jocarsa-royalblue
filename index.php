<?php
session_start();
date_default_timezone_set('Europe/Madrid');

include "inc/dbinit.php";

// --------------------------------------------------
// STEP 1: Selección de Recurso
// --------------------------------------------------
if ($step === 1) {
    include "partes/recurso.php";
    exit;
}

// --------------------------------------------------
// STEP 2: Calendario Semanal con checkboxes
// --------------------------------------------------
if ($step === 2) {
    include "partes/calendariosemanal.php";
    exit;
}

// --------------------------------------------------
// STEP 3: Datos personales
// --------------------------------------------------
if ($step === 3) {
    include "partes/datospersonales.php";
    exit;
}

// --------------------------------------------------
// STEP 4: Confirmación y guardado
// --------------------------------------------------
if ($step === 4) {
    include "partes/confirmacionyguardado.php";
    exit;
}

// --------------------------------------------------
// DONE: Mensaje final
// --------------------------------------------------
if (isset($_GET['step']) && $_GET['step'] === 'done') {
    include "partes/mensajefinal.php";
    exit;
}

