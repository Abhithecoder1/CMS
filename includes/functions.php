<?php
// includes/functions.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function getClinicSettings() {
    $pdo = getDbConnection();
    $stmt = $pdo->query("SELECT * FROM users LIMIT 1");
    $user = $stmt->fetch();
    if (!$user) {
        return [
            'doctor_name' => 'Dr. Rajesh Sharma',
            'clinic_name' => 'LifeCare Medical Clinic',
            'qualification' => 'M.B.B.S., M.D.',
            'registration_no' => 'REG-987654',
            'contact_no' => '+91 98765 43210',
            'address' => '101 Medical Center, Healthcare Avenue',
            'medical_store_details' => 'LifeCare Chemist & Pharmacy',
            'cutoff_time' => '16:25'
        ];
    }
    return $user;
}

function formatDate($dateStr) {
    if (empty($dateStr) || $dateStr === '0000-00-00') return 'N/A';
    return date('d M Y', strtotime($dateStr));
}

function formatTime($timeStr) {
    if (empty($timeStr)) return 'N/A';
    return date('h:i A', strtotime($timeStr));
}

function formatCurrency($amount) {
    return '₹' . number_format((float)($amount ?? 0), 2);
}

function buildWhatsAppMessage($clinicName, $patientName, $treatmentDate, $diseaseName, $medicines, $followupDate, $fee, $doctorName, $contactNo) {
    $msg = "🏥 *" . $clinicName . "*\n\n";
    $msg .= "👤 *Patient:* " . $patientName . "\n";
    $msg .= "📅 *Date:* " . formatDate($treatmentDate) . "\n\n";
    $msg .= "🩺 *Diagnosis:*\n" . ($diseaseName ?: 'General Checkup') . "\n\n";
    $msg .= "💊 *Medicines:*\n";

    if (!empty($medicines) && is_array($medicines)) {
        $i = 1;
        foreach ($medicines as $m) {
            $type = $m['medicine_type'] ?? ($m['type'] ?? '');
            $name = $m['medicine_name'] ?? ($m['name'] ?? '');
            $dose = $m['dose'] ?? '';
            $schedule = $m['schedule'] ?? '';

            $msg .= "{$i}. *{$name}*\n";
            if ($type) $msg .= "   Type: {$type}\n";
            if ($dose) $msg .= "   Dose: {$dose}\n";
            if ($schedule) $msg .= "   Schedule: {$schedule}\n";
            $msg .= "\n";
            $i++;
        }
    } else {
        $msg .= "None prescribed\n\n";
    }

    if ($followupDate) {
        $msg .= "🗓️ *Follow-up:* " . formatDate($followupDate) . "\n\n";
    }

    $msg .= "💰 *Treatment Fee:* " . formatCurrency($fee) . "\n\n";
    $msg .= "👨‍⚕️ *" . $doctorName . "*\n";
    $msg .= "📞 " . $contactNo;

    return $msg;
}

function buildWhatsAppUrl($contactNo, $message) {
    // Sanitize phone number (remove non-digits except +)
    $cleanPhone = preg_replace('/[^\d]/', '', $contactNo);
    return "https://api.whatsapp.com/send?phone=" . urlencode($cleanPhone) . "&text=" . rawurlencode($message);
}
