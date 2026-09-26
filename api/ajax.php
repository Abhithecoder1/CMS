<?php
// api/ajax.php
require_once __DIR__ . '/../includes/auth.php';
requireAuth();

$action = $_REQUEST['action'] ?? '';
$pdo = getDbConnection();

switch ($action) {

    // -------------------------------------------------------------
    // PATIENTS
    // -------------------------------------------------------------
    case 'search_patients_autocomplete':
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 1) {
            jsonResponse([]);
        }
        $stmt = $pdo->prepare("SELECT id, name, contact, sex, age, weight, address, allergies FROM patients WHERE name LIKE ? OR contact LIKE ? ORDER BY name ASC LIMIT 10");
        $stmt->execute(["%$q%", "%$q%"]);
        $patients = $stmt->fetchAll();
        jsonResponse($patients);
        break;

    case 'get_patient_details':
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT p.*,
            (SELECT COALESCE(SUM(fee), 0) FROM treatments WHERE patient_id = p.id) as total_fees
            FROM patients p WHERE p.id = ?");
        $stmt->execute([$id]);
        $patient = $stmt->fetch();
        if ($patient) {
            jsonResponse(['status' => 'success', 'data' => $patient]);
        } else {
            jsonResponse(['status' => 'error', 'message' => 'Patient not found'], 404);
        }
        break;

    case 'save_patient':
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $sex = trim($_POST['sex'] ?? '');
        $age = !empty($_POST['age']) ? (int)$_POST['age'] : null;
        $weight = !empty($_POST['weight']) ? (float)$_POST['weight'] : null;
        $contact = trim($_POST['contact'] ?? '');
        $allergies = trim($_POST['allergies'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (empty($name)) jsonResponse(['status' => 'error', 'message' => 'Patient Name is required.'], 400);
        if (empty($sex)) jsonResponse(['status' => 'error', 'message' => 'Sex is required.'], 400);

        // Check duplicate contact for new patient
        if ($id === 0 && !empty($contact)) {
            $chk = $pdo->prepare("SELECT id FROM patients WHERE contact = ?");
            $chk->execute([$contact]);
            if ($chk->fetch()) {
                jsonResponse(['status' => 'error', 'message' => 'A patient with this contact number already exists.'], 400);
            }
        }

        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE patients SET name = ?, sex = ?, age = ?, weight = ?, contact = ?, allergies = ?, address = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$name, $sex, $age, $weight, $contact, $allergies, $address, $id]);
            jsonResponse(['status' => 'success', 'message' => 'Patient updated successfully.', 'id' => $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO patients (name, sex, age, weight, contact, allergies, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $sex, $age, $weight, $contact, $allergies, $address]);
            $newId = $pdo->lastInsertId();
            jsonResponse(['status' => 'success', 'message' => 'Patient added successfully.', 'id' => $newId]);
        }
        break;

    case 'delete_patient':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) jsonResponse(['status' => 'error', 'message' => 'Invalid patient ID'], 400);
        $stmt = $pdo->prepare("DELETE FROM patients WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse(['status' => 'success', 'message' => 'Patient deleted successfully.']);
        break;

    // -------------------------------------------------------------
    // DIAGNOSIS & MEDICINES
    // -------------------------------------------------------------
    case 'search_diagnoses_autocomplete':
        $q = trim($_GET['q'] ?? '');
        $stmt = $pdo->prepare("SELECT id, disease FROM diagnoses WHERE disease LIKE ? ORDER BY disease ASC LIMIT 10");
        $stmt->execute(["%$q%"]);
        jsonResponse($stmt->fetchAll());
        break;

    case 'get_diagnosis_medicines':
        $diagId = (int)($_GET['diagnosis_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT dm.*, m.medicine_type, m.medicine_name
            FROM diagnosis_medicines dm
            JOIN medicines m ON dm.medicine_id = m.id
            WHERE dm.diagnosis_id = ?");
        $stmt->execute([$diagId]);
        jsonResponse($stmt->fetchAll());
        break;

    case 'search_medicines_autocomplete':
        $q = trim($_GET['q'] ?? '');
        $stmt = $pdo->prepare("SELECT id, medicine_type, medicine_name FROM medicines WHERE medicine_name LIKE ? OR medicine_type LIKE ? ORDER BY medicine_name ASC LIMIT 10");
        $stmt->execute(["%$q%", "%$q%"]);
        jsonResponse($stmt->fetchAll());
        break;

    case 'save_diagnosis':
        $disease = trim($_POST['disease'] ?? '');
        $medTypes = $_POST['med_type'] ?? [];
        $medNames = $_POST['med_name'] ?? [];
        $medDoses = $_POST['med_dose'] ?? [];
        $medSchedules = $_POST['med_schedule'] ?? [];

        if (empty($disease)) jsonResponse(['status' => 'error', 'message' => 'Disease name is required.'], 400);

        $pdo->beginTransaction();
        try {
            // Check if disease exists or insert
            $stmt = $pdo->prepare("SELECT id FROM diagnoses WHERE disease = ?");
            $stmt->execute([$disease]);
            $diagId = $stmt->fetchColumn();

            if (!$diagId) {
                $stmt = $pdo->prepare("INSERT INTO diagnoses (disease) VALUES (?)");
                $stmt->execute([$disease]);
                $diagId = $pdo->lastInsertId();
            } else {
                // Clear existing relations to update
                $pdo->prepare("DELETE FROM diagnosis_medicines WHERE diagnosis_id = ?")->execute([$diagId]);
            }

            for ($i = 0; $i < count($medNames); $i++) {
                $mType = trim($medTypes[$i] ?? 'TAB');
                $mName = trim($medNames[$i] ?? '');
                $mDose = trim($medDoses[$i] ?? '');
                $mSched = trim($medSchedules[$i] ?? '');

                if (empty($mName)) continue;

                // Ensure medicine exists in medicines table
                $mStmt = $pdo->prepare("SELECT id FROM medicines WHERE medicine_type = ? AND medicine_name = ?");
                $mStmt->execute([$mType, $mName]);
                $medId = $mStmt->fetchColumn();
                if (!$medId) {
                    $mIns = $pdo->prepare("INSERT INTO medicines (medicine_type, medicine_name) VALUES (?, ?)");
                    $mIns->execute([$mType, $mName]);
                    $medId = $pdo->lastInsertId();
                }

                $dmIns = $pdo->prepare("INSERT INTO diagnosis_medicines (diagnosis_id, medicine_id, dose, schedule) VALUES (?, ?, ?, ?)");
                $dmIns->execute([$diagId, $medId, $mDose, $mSched]);
            }

            $pdo->commit();
            jsonResponse(['status' => 'success', 'message' => 'Diagnosis and medicine details saved successfully!']);
        } catch (Exception $e) {
            $pdo->rollBack();
            jsonResponse(['status' => 'error', 'message' => 'Failed to save diagnosis: ' . $e->getMessage()], 500);
        }
        break;

    case 'delete_diagnosis':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) jsonResponse(['status' => 'error', 'message' => 'Invalid ID'], 400);
        $stmt = $pdo->prepare("DELETE FROM diagnoses WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse(['status' => 'success', 'message' => 'Diagnosis deleted successfully.']);
        break;

    // -------------------------------------------------------------
    // TREATMENTS
    // -------------------------------------------------------------
    case 'save_treatment':
        $patientId = (int)($_POST['patient_id'] ?? 0);
        $diseaseName = trim($_POST['disease_name'] ?? '');
        $treatmentDate = trim($_POST['treatment_date'] ?? date('Y-m-d'));
        $treatmentTime = trim($_POST['treatment_time'] ?? date('H:i:s'));
        $doctorDetails = trim($_POST['doctor_details'] ?? '');
        $medicalStoreDetails = trim($_POST['medical_store_details'] ?? '');
        $dt = trim($_POST['dt'] ?? 'No');
        $fee = (float)($_POST['fee'] ?? 0.0);
        $followupDate = trim($_POST['followup_date'] ?? '');

        $medTypes = $_POST['med_type'] ?? [];
        $medNames = $_POST['med_name'] ?? [];
        $medDoses = $_POST['med_dose'] ?? [];
        $medSchedules = $_POST['med_schedule'] ?? [];

        if ($patientId <= 0) jsonResponse(['status' => 'error', 'message' => 'Please select or search a valid patient.'], 400);

        $pdo->beginTransaction();
        try {
            // Find or insert diagnosis
            $diagnosisId = null;
            if (!empty($diseaseName)) {
                $dStmt = $pdo->prepare("SELECT id FROM diagnoses WHERE disease = ?");
                $dStmt->execute([$diseaseName]);
                $diagnosisId = $dStmt->fetchColumn();
                if (!$diagnosisId) {
                    $dIns = $pdo->prepare("INSERT INTO diagnoses (disease) VALUES (?)");
                    $dIns->execute([$diseaseName]);
                    $diagnosisId = $pdo->lastInsertId();
                }
            }

            // Insert treatment
            $tStmt = $pdo->prepare("INSERT INTO treatments (patient_id, diagnosis_id, treatment_date, treatment_time, doctor_details, medical_store_details, dt, fee, followup_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $tStmt->execute([$patientId, $diagnosisId, $treatmentDate, $treatmentTime, $doctorDetails, $medicalStoreDetails, $dt, $fee, !empty($followupDate) ? $followupDate : null]);
            $treatmentId = $pdo->lastInsertId();

            // Insert treatment medicines
            $tmIns = $pdo->prepare("INSERT INTO treatment_medicines (treatment_id, medicine_type, medicine_name, dose, schedule) VALUES (?, ?, ?, ?, ?)");
            $medListForWhatsApp = [];
            for ($i = 0; $i < count($medNames); $i++) {
                $mType = trim($medTypes[$i] ?? 'TAB');
                $mName = trim($medNames[$i] ?? '');
                $mDose = trim($medDoses[$i] ?? '');
                $mSched = trim($medSchedules[$i] ?? '');

                if (empty($mName)) continue;

                $tmIns->execute([$treatmentId, $mType, $mName, $mDose, $mSched]);
                $medListForWhatsApp[] = [
                    'medicine_type' => $mType,
                    'medicine_name' => $mName,
                    'dose' => $mDose,
                    'schedule' => $mSched
                ];
            }

            // Insert followup if date is specified
            if (!empty($followupDate)) {
                $fStmt = $pdo->prepare("INSERT INTO followups (patient_id, treatment_id, followup_date, status) VALUES (?, ?, ?, 'Pending')");
                $fStmt->execute([$patientId, $treatmentId, $followupDate]);
            }

            $pdo->commit();

            // Fetch patient and clinic details for WhatsApp preview URL
            $pStmt = $pdo->prepare("SELECT name, contact FROM patients WHERE id = ?");
            $pStmt->execute([$patientId]);
            $patient = $pStmt->fetch();
            $settings = getClinicSettings();

            $waMsg = buildWhatsAppMessage(
                $settings['clinic_name'],
                $patient['name'],
                $treatmentDate,
                $diseaseName,
                $medListForWhatsApp,
                $followupDate,
                $fee,
                $settings['doctor_name'],
                $settings['contact_no']
            );
            $waUrl = buildWhatsAppUrl($patient['contact'], $waMsg);

            jsonResponse([
                'status' => 'success',
                'message' => 'Treatment saved successfully!',
                'treatment_id' => $treatmentId,
                'whatsapp_url' => $waUrl
            ]);

        } catch (Exception $e) {
            $pdo->rollBack();
            jsonResponse(['status' => 'error', 'message' => 'Failed to save treatment: ' . $e->getMessage()], 500);
        }
        break;

    case 'delete_treatment':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) jsonResponse(['status' => 'error', 'message' => 'Invalid treatment ID'], 400);
        $stmt = $pdo->prepare("DELETE FROM treatments WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse(['status' => 'success', 'message' => 'Treatment deleted successfully.']);
        break;

    // -------------------------------------------------------------
    // DASHBOARD CHART DATA
    // -------------------------------------------------------------
    case 'get_chart_data':
        $range = $_GET['range'] ?? 'daily';

        if ($range === 'daily') {
            // Last 7 days
            $labels = [];
            $values = [];
            for ($i = 6; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-$i days"));
                $labels[] = date('D d M', strtotime($d));
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM treatments WHERE treatment_date = ?");
                $stmt->execute([$d]);
                $values[] = (int)$stmt->fetchColumn();
            }
        } elseif ($range === 'weekly') {
            // Last 6 weeks
            $labels = [];
            $values = [];
            for ($i = 5; $i >= 0; $i--) {
                $start = date('Y-m-d', strtotime("-$i week Monday"));
                $end = date('Y-m-d', strtotime("-$i week Sunday"));
                $labels[] = date('d M', strtotime($start)) . ' - ' . date('d M', strtotime($end));
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM treatments WHERE treatment_date BETWEEN ? AND ?");
                $stmt->execute([$start, $end]);
                $values[] = (int)$stmt->fetchColumn();
            }
        } else {
            // Last 6 months
            $labels = [];
            $values = [];
            for ($i = 5; $i >= 0; $i--) {
                $m = date('Y-m', strtotime("-$i month"));
                $labels[] = date('M Y', strtotime($m . '-01'));
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM treatments WHERE strftime('%Y-%m', treatment_date) = ?");
                $stmt->execute([$m]);
                $values[] = (int)$stmt->fetchColumn();
            }
        }

        jsonResponse(['labels' => $labels, 'values' => $values]);
        break;

    // -------------------------------------------------------------
    // FOLLOW-UPS
    // -------------------------------------------------------------
    case 'update_followup_status':
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'Completed';
        $stmt = $pdo->prepare("UPDATE followups SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$status, $id]);
        jsonResponse(['status' => 'success', 'message' => 'Follow-up status updated.']);
        break;

    case 'delete_followup':
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM followups WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse(['status' => 'success', 'message' => 'Follow-up deleted.']);
        break;

    default:
        jsonResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
}
