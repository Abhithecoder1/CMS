<?php
// config/db.php

define('DB_FILE', __DIR__ . '/../database/clinic.sqlite');

function getDbConnection() {
    static $pdo = null;
    if ($pdo === null) {
        $dbDir = dirname(DB_FILE);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0777, true);
        }

        $pdo = new PDO('sqlite:' . DB_FILE);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON;');
        $pdo->exec('PRAGMA journal_mode = WAL;');
    }
    return $pdo;
}

function initDatabase() {
    $pdo = getDbConnection();

    // 1. users / clinic settings table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        doctor_name TEXT NOT NULL,
        clinic_name TEXT NOT NULL,
        qualification TEXT DEFAULT 'M.B.B.S., M.D.',
        registration_no TEXT DEFAULT 'REG-987654',
        contact_no TEXT DEFAULT '+91 98765 43210',
        address TEXT DEFAULT '101 Medical Center, Healthcare Avenue, City',
        medical_store_details TEXT DEFAULT 'City Pharmacy, Main Road',
        cutoff_time TEXT DEFAULT '16:25',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. patients
    $pdo->exec("CREATE TABLE IF NOT EXISTS patients (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        sex TEXT NOT NULL,
        age INTEGER,
        weight REAL,
        contact TEXT NOT NULL,
        allergies TEXT,
        address TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 3. diagnoses
    $pdo->exec("CREATE TABLE IF NOT EXISTS diagnoses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        disease TEXT UNIQUE NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 4. medicines
    $pdo->exec("CREATE TABLE IF NOT EXISTS medicines (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        medicine_type TEXT NOT NULL,
        medicine_name TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(medicine_type, medicine_name)
    )");

    // 5. diagnosis_medicines
    $pdo->exec("CREATE TABLE IF NOT EXISTS diagnosis_medicines (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        diagnosis_id INTEGER NOT NULL,
        medicine_id INTEGER NOT NULL,
        dose TEXT NOT NULL,
        schedule TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (diagnosis_id) REFERENCES diagnoses(id) ON DELETE CASCADE,
        FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE
    )");

    // 6. treatments
    $pdo->exec("CREATE TABLE IF NOT EXISTS treatments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        patient_id INTEGER NOT NULL,
        diagnosis_id INTEGER,
        treatment_date DATE NOT NULL,
        treatment_time TIME NOT NULL,
        doctor_details TEXT,
        medical_store_details TEXT,
        dt TEXT DEFAULT 'No', -- DT Yes / No
        fee REAL DEFAULT 0.00,
        followup_date DATE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
        FOREIGN KEY (diagnosis_id) REFERENCES diagnoses(id) ON DELETE SET NULL
    )");

    // 7. treatment_medicines
    $pdo->exec("CREATE TABLE IF NOT EXISTS treatment_medicines (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        treatment_id INTEGER NOT NULL,
        medicine_type TEXT NOT NULL,
        medicine_name TEXT NOT NULL,
        dose TEXT NOT NULL,
        schedule TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (treatment_id) REFERENCES treatments(id) ON DELETE CASCADE
    )");

    // 8. followups
    $pdo->exec("CREATE TABLE IF NOT EXISTS followups (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        patient_id INTEGER NOT NULL,
        treatment_id INTEGER NOT NULL,
        followup_date DATE NOT NULL,
        status TEXT DEFAULT 'Pending', -- Pending / Completed / Cancelled
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
        FOREIGN KEY (treatment_id) REFERENCES treatments(id) ON DELETE CASCADE
    )");

    seedDatabase($pdo);
}

function seedDatabase($pdo) {
    // Seed default user if empty
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($userCount == 0) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, doctor_name, clinic_name, qualification, registration_no, contact_no, address, medical_store_details, cutoff_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'doctor',
            password_hash('doctor123', PASSWORD_DEFAULT),
            'Dr. Rajesh Sharma',
            'LifeCare Medical Clinic & Research Center',
            'M.B.B.S., M.D. (Medicine)',
            'REG-2012-88452',
            '+91 98765 43210',
            'Suite 402, Care Plaza, MG Road, Mumbai',
            'LifeCare Chemist & Pharmacy, Ground Floor',
            '16:25'
        ]);
    }

    // Seed default demo data if empty
    $patientCount = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
    if ($patientCount == 0) {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $prevWeek = date('Y-m-d', strtotime('-5 days'));
        $prevMonth = date('Y-m-d', strtotime('-25 days'));
        $futureDate = date('Y-m-d', strtotime('+3 days'));

        // Sample patients
        $patientsData = [
            ['Abhay Harsora', 'Male', 34, 72.5, '+91 98250 12345', 'Dust, Penicillin', '12 Ring Road, Area A', $today],
            ['Amit Patel', 'Male', 45, 68.0, '+91 98251 23456', 'None', '45 Green Park, Area B', $today],
            ['Ankit Shah', 'Male', 29, 62.0, '+91 98252 34567', 'Sulfa drugs', '78 Sector 4, Area C', $yesterday],
            ['Priya Sharma', 'Female', 31, 55.4, '+91 98253 45678', 'Pollen', '102 Royal Heights, Area D', $yesterday],
            ['Meera Verma', 'Female', 52, 60.0, '+91 98254 56789', 'Aspirin', '55 Sunshine Villa', $prevWeek],
            ['Rahul Mehta', 'Male', 38, 76.0, '+91 98255 67890', 'None', '89 Station Road', $prevMonth],
            ['Suresh Joshi', 'Male', 61, 70.0, '+91 98256 78901', 'Lactose', '14 Lake View Appt', '2026-01-10'],
            ['Neha Gupta', 'Female', 26, 51.0, '+91 98257 89012', 'None', '203 Orchid Towers', '2026-02-15'],
        ];

        $insertPatient = $pdo->prepare("INSERT INTO patients (name, sex, age, weight, contact, allergies, address, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $patientIds = [];
        foreach ($patientsData as $p) {
            $insertPatient->execute([$p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $p[7] . ' ' . sprintf('%02d:%02d:00', rand(9, 19), rand(10, 59))]);
            $patientIds[$p[0]] = $pdo->lastInsertId();
        }

        // Diagnoses & Medicines
        $diagnosesData = [
            'VIRAL FEVER (? DF)' => [
                ['CAP', 'NOGACID D', '4', '1-----1'],
                ['TAB', 'B-VOLT Z', '4', '1-----1'],
                ['TAB', 'METACIN', '6', '1-1-1']
            ],
            'ACUTE GASTRITIS' => [
                ['CAP', 'PANTO-D', '5', '1-----0'],
                ['TAB', 'GELUSIL', '10', '1-1-1']
            ],
            'HYPERTENSION' => [
                ['TAB', 'AMLOGARD 5MG', '30', '1-----0'],
                ['TAB', 'TELMA 40', '30', '1-----0']
            ],
            'UPPER RESPIRATORY TRACT INFECTION' => [
                ['TAB', 'AZITHRAL 500', '3', '1-----0'],
                ['SYP', 'ALERID EXPECTORANT', '1', '1-1-1']
            ],
            'DIABETES MELLITUS TYPE 2' => [
                ['TAB', 'GLYCOMET 500 SR', '30', '1-----1']
            ]
        ];

        $insertDiagnosis = $pdo->prepare("INSERT INTO diagnoses (disease) VALUES (?)");
        $insertMedicine = $pdo->prepare("INSERT INTO medicines (medicine_type, medicine_name) VALUES (?, ?)");
        $insertDiagMed = $pdo->prepare("INSERT INTO diagnosis_medicines (diagnosis_id, medicine_id, dose, schedule) VALUES (?, ?, ?, ?)");

        $diagIds = [];
        foreach ($diagnosesData as $disease => $meds) {
            $insertDiagnosis->execute([$disease]);
            $diagId = $pdo->lastInsertId();
            $diagIds[$disease] = $diagId;

            foreach ($meds as $m) {
                // Check if medicine exists
                $stmt = $pdo->prepare("SELECT id FROM medicines WHERE medicine_type = ? AND medicine_name = ?");
                $stmt->execute([$m[0], $m[1]]);
                $medId = $stmt->fetchColumn();
                if (!$medId) {
                    $insertMedicine->execute([$m[0], $m[1]]);
                    $medId = $pdo->lastInsertId();
                }
                $insertDiagMed->execute([$diagId, $medId, $m[2], $m[3]]);
            }
        }

        // Sample treatments
        $treatmentsData = [
            [
                'patient_name' => 'Abhay Harsora',
                'disease' => 'VIRAL FEVER (? DF)',
                'date' => $today,
                'time' => '10:15:00',
                'dt' => 'No',
                'fee' => 500.00,
                'followup' => $futureDate,
                'meds' => $diagnosesData['VIRAL FEVER (? DF)']
            ],
            [
                'patient_name' => 'Amit Patel',
                'disease' => 'ACUTE GASTRITIS',
                'date' => $today,
                'time' => '17:30:00', // After 4:25 PM cutoff
                'dt' => 'Yes',
                'fee' => 350.00,
                'followup' => $futureDate,
                'meds' => $diagnosesData['ACUTE GASTRITIS']
            ],
            [
                'patient_name' => 'Ankit Shah',
                'disease' => 'HYPERTENSION',
                'date' => $yesterday,
                'time' => '11:00:00',
                'dt' => 'No',
                'fee' => 400.00,
                'followup' => date('Y-m-d', strtotime('+7 days')),
                'meds' => $diagnosesData['HYPERTENSION']
            ],
            [
                'patient_name' => 'Priya Sharma',
                'disease' => 'UPPER RESPIRATORY TRACT INFECTION',
                'date' => $yesterday,
                'time' => '18:15:00',
                'dt' => 'No',
                'fee' => 450.00,
                'followup' => date('Y-m-d', strtotime('+5 days')),
                'meds' => $diagnosesData['UPPER RESPIRATORY TRACT INFECTION']
            ],
            [
                'patient_name' => 'Meera Verma',
                'disease' => 'DIABETES MELLITUS TYPE 2',
                'date' => $prevWeek,
                'time' => '14:20:00',
                'dt' => 'No',
                'fee' => 600.00,
                'followup' => date('Y-m-d', strtotime('+30 days')),
                'meds' => $diagnosesData['DIABETES MELLITUS TYPE 2']
            ]
        ];

        $insertTreatment = $pdo->prepare("INSERT INTO treatments (patient_id, diagnosis_id, treatment_date, treatment_time, doctor_details, medical_store_details, dt, fee, followup_date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insertTreatMed = $pdo->prepare("INSERT INTO treatment_medicines (treatment_id, medicine_type, medicine_name, dose, schedule) VALUES (?, ?, ?, ?, ?)");
        $insertFollowup = $pdo->prepare("INSERT INTO followups (patient_id, treatment_id, followup_date, status, created_at) VALUES (?, ?, ?, ?, ?)");

        foreach ($treatmentsData as $t) {
            $pId = $patientIds[$t['patient_name']];
            $dId = $diagIds[$t['disease']];
            $createdAt = $t['date'] . ' ' . $t['time'];

            $insertTreatment->execute([
                $pId, $dId, $t['date'], $t['time'],
                'Dr. Rajesh Sharma (M.B.B.S., M.D.)',
                'LifeCare Chemist & Pharmacy, Ground Floor',
                $t['dt'], $t['fee'], $t['followup'], $createdAt
            ]);
            $tId = $pdo->lastInsertId();

            foreach ($t['meds'] as $m) {
                $insertTreatMed->execute([$tId, $m[0], $m[1], $m[2], $m[3]]);
            }

            if (!empty($t['followup'])) {
                $insertFollowup->execute([$pId, $tId, $t['followup'], 'Pending', $createdAt]);
            }
        }
    }
}

// Auto-initialize
initDatabase();
