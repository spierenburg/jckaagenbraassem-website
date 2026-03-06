<?php
// === CONFIGURATIE ===
$recaptcha_secret = 'HIER_JE_SECRET_KEY'; // Vul je reCAPTCHA v3 Secret Key in
$ontvanger = 'info@jckaagenbraassem.nl';
$onderwerp_prefix = 'Judoclub Kaag en Braassem';

// === VERWERK FORMULIER ===
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.html');
    exit;
}

// reCAPTCHA verificatie
$recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
if (empty($recaptcha_response)) {
    header('Location: contact.html?status=captcha');
    exit;
}

$verify = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($recaptcha_secret) . '&response=' . urlencode($recaptcha_response));
$result = json_decode($verify, true);

if (!$result['success'] || $result['score'] < 0.5) {
    header('Location: contact.html?status=captcha');
    exit;
}

// Formulierdata ophalen en sanitizen
$naam = htmlspecialchars(trim($_POST['naam'] ?? ''), ENT_QUOTES, 'UTF-8');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$telefoon = htmlspecialchars(trim($_POST['telefoon'] ?? ''), ENT_QUOTES, 'UTF-8');
$kind_naam = htmlspecialchars(trim($_POST['kind-naam'] ?? ''), ENT_QUOTES, 'UTF-8');
$leeftijd = htmlspecialchars(trim($_POST['leeftijd'] ?? ''), ENT_QUOTES, 'UTF-8');
$bericht = htmlspecialchars(trim($_POST['bericht'] ?? ''), ENT_QUOTES, 'UTF-8');

// Validatie
if (empty($naam) || empty($email) || empty($kind_naam) || empty($leeftijd)) {
    header('Location: contact.html?status=error');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: contact.html?status=error');
    exit;
}

// E-mail samenstellen
$onderwerp = "$onderwerp_prefix — Proefles aanvraag van $naam";

$body = "Nieuwe proefles aanvraag via de website:\n\n";
$body .= "Naam ouder/verzorger: $naam\n";
$body .= "E-mailadres: $email\n";
$body .= "Telefoon: $telefoon\n";
$body .= "Naam kind: $kind_naam\n";
$body .= "Leeftijd kind: $leeftijd jaar\n";
if (!empty($bericht)) {
    $body .= "\nBericht:\n$bericht\n";
}

$headers = "From: noreply@jckaagenbraassem.nl\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Verstuur
$verzonden = mail($ontvanger, $onderwerp, $body, $headers);

if ($verzonden) {
    header('Location: bedankt.html');
} else {
    header('Location: contact.html?status=error');
}
exit;
