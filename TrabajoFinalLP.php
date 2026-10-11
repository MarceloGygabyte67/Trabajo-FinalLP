<?php
header('Content-Type: application/json; charset=UTF-8');

// Solo aceptar peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'desaprobado',
        'message' => 'Acceso denegado: solo solicitudes POST.'
    ]);
    exit;
}

// ----------------------------------------------------
// 1. CONFIGURACIÓN: Ingresa tu correo personal aquí
// ----------------------------------------------------
$destinatario = "ramirezolamarcelo546@gmail.com"; 

// 2. Captura y sanitización
$nombre         = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$correo         = isset($_POST['correo']) ? trim($_POST['correo']) : '';
$tipo_solicitud = isset($_POST['tipo_solicitud']) ? trim($_POST['tipo_solicitud']) : '';
$detalles       = isset($_POST['detalles']) ? trim($_POST['detalles']) : '';
$confirmacion   = isset($_POST['confirmacion']) ? true : false;

// 3. Reglas de Validación para Aprobar o Desaprobar el Envío
if (empty($nombre) || empty($correo) || empty($tipo_solicitud) || empty($detalles)) {
    echo json_encode([
        'status' => 'desaprobado',
        'message' => 'Faltan campos obligatorios por completar en el formulario.'
    ]);
    exit;
}

if (!$confirmacion) {
    echo json_encode([
        'status' => 'desaprobado',
        'message' => 'Debes marcar la casilla de aceptación para autorizar el envío.'
    ]);
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status' => 'desaprobado',
        'message' => 'La dirección de correo electrónico ingresada no es válida.'
    ]);
    exit;
}

// Sanitización para el correo
$nombre_seguro   = str_replace(["\r", "\n"], '', htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'));
$asunto_seguro   = str_replace(["\r", "\n"], '', htmlspecialchars($tipo_solicitud, ENT_QUOTES, 'UTF-8'));
$detalles_seguro = nl2br(htmlspecialchars($detalles, ENT_QUOTES, 'UTF-8'));

// 4. Plantilla de Correo con estado APROBADO
$asunto_mail = "[SOLICITUD APROBADA] - " . $asunto_seguro;

$cuerpo_correo = "
<!DOCTYPE html>
<html>
<head>
  <meta charset='UTF-8'>
  <style>
    body { font-family: Arial, sans-serif; background-color: #f1f5f9; padding: 20px; color: #1e293b; }
    .card { background: #ffffff; border-radius: 12px; max-width: 580px; margin: auto; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); }
    .header { border-bottom: 2px solid #10b981; padding-bottom: 12px; margin-bottom: 20px; }
    .badge { display: inline-block; padding: 6px 14px; font-weight: bold; border-radius: 8px; font-size: 14px; background: #ecfdf5; color: #10b981; border: 1.5px solid #10b981; margin-top: 8px; }
    .item { margin-bottom: 12px; }
    .item strong { color: #475569; display: inline-block; width: 140px; }
    .box { background: #f8fafc; border-left: 4px solid #10b981; padding: 12px; border-radius: 4px; margin-top: 15px; }
    .footer { font-size: 12px; color: #94a3b8; text-align: center; margin-top: 25px; }
  </style>
</head>
<body>
  <div class='card'>
    <div class='header'>
      <h2 style='margin:0; color:#0f172a;'>Notificación de Envío Autorizado</h2>
      <div class='badge'>ESTADO: SOLICITUD APROBADA</div>
    </div>
    <div class='item'><strong>Solicitante:</strong> {$nombre_seguro}</div>
    <div class='item'><strong>Correo:</strong> {$correo}</div>
    <div class='item'><strong>Motivo:</strong> {$asunto_seguro}</div>
    <div class='item'><strong>Detalles:</strong></div>
    <div class='box'>{$detalles_seguro}</div>
    <div class='footer'>Enviado exitosamente a través del sistema interactivo 3D.</div>
  </div>
</body>
</html>
";

$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=UTF-8\r\n";
$headers .= "From: Sistema 3D <no-reply@" . $_SERVER['SERVER_NAME'] . ">\r\n";
$headers .= "Reply-To: {$correo}\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// 5. Envío mediante función mail()
if (@mail($destinatario, $asunto_mail, $cuerpo_correo, $headers)) {
    echo json_encode([
        'status' => 'aprobado',
        'message' => '¡Tu solicitud fue aprobada con éxito y el mensaje ya se envió directamente a tu correo!'
    ]);
} else {
    // Si estás en local sin SMTP configurado, te lo aprueba lógicamente pero te avisa
    echo json_encode([
        'status' => 'aprobado',
        'message' => '¡Solicitud aprobada con éxito! (En servidor local requiere activar el agente SMTP para la entrega final del correo).'
    ]);
}
exit;
?>