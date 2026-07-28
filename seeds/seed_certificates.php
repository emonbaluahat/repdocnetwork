<?php
define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/vendor/autoload.php';

$envFile = APP_ROOT . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            $pos = strpos($line, '=');
            $_ENV[trim(substr($line, 0, $pos))] = trim(substr($line, $pos + 1));
            putenv($line);
        }
    }
}
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/database.php';

use App\Core\Database;

$db = Database::getInstance();
$pdo = $db->getPdo();

try {
    $pdo->beginTransaction();

    // ── 40 Certificate Types (matching Prottoyon) ──
    $certTypes = [
        ['birth', 'জন্ম নিবন্ধন সনদ', 'Birth Registration Certificate', 'vital'],
        ['death', 'মৃত্যু সনদ', 'Death Certificate', 'vital'],
        ['marriage', 'বিবাহ সনদ', 'Marriage Certificate', 'vital'],
        ['divorce', ' divorce সনদ', 'Divorce Certificate', 'vital'],
        ['character', 'চরিত্র সনদ', 'Character Certificate', 'general'],
        ['guardian', 'অভিভাবক সনদ', 'Guardian Certificate', 'general'],
        ['domicile', 'ডোমিসাইল সনদ', 'Domicile Certificate', 'general'],
        ['income', 'আয় সনদ', 'Income Certificate', 'financial'],
        ['residence', 'প্রতিবাস সনদ', 'Residence Certificate', 'general'],
        ['inheritance', 'ওয়ারিশান সনদ', 'Inheritance Certificate', 'legal'],
        ['heir', 'ভারপ্রাপ্ত ওয়ারিশ সনদ', 'Heir Certificate', 'legal'],
        ['successor', 'উত্তরাধিকার সনদ', 'Successor Certificate', 'legal'],
        ['solvency', 'সচ্ছলতা সনদ', 'Solvency Certificate', 'financial'],
        ['land', 'জমি সনদ', 'Land Certificate', 'land'],
        ['business', 'ব্যবসা সনদ', 'Business Certificate', 'financial'],
        ['trade', ' trade সনদ', 'Trade Certificate', 'financial'],
        ['occupation', 'পেশা সনদ', 'Occupation Certificate', 'general'],
        ['student', 'ছাত্র/ছাত্রী সনদ', 'Student Certificate', 'education'],
        ['educational', 'শিক্ষাগত যোগ্যতা সনদ', 'Educational Qualification Certificate', 'education'],
        ['madrasa', 'মাদ্রাসা শিক্ষা সনদ', 'Madrasa Education Certificate', 'education'],
        ['freedom_fighter', 'মুক্তিযোদ্ধা সনদ', 'Freedom Fighter Certificate', 'general'],
        ['family', 'পরিবার সনদ', 'Family Certificate', 'general'],
        ['dependency', 'নির্ভরশীলতা সনদ', 'Dependency Certificate', 'general'],
        ['widow', 'বিধবা সনদ', 'Widow Certificate', 'general'],
        ['disabled', 'প্রতিবন্ধী সনদ', 'Disability Certificate', 'general'],
        ['medical', 'চিকিৎসা সনদ', 'Medical Certificate', 'health'],
        ['vaccination', 'টিকা সনদ', 'Vaccination Certificate', 'health'],
        ['cattle', 'গবাদি পশু সনদ', 'Cattle Certificate', 'agriculture'],
        ['crop', 'ফসল সনদ', 'Crop Certificate', 'agriculture'],
        ['no_objection', 'আপত্তি নেই সনদ', 'No Objection Certificate', 'legal'],
        ['transfer', 'স্থানান্তর সনদ', 'Transfer Certificate', 'general'],
        ['experience', 'অভিজ্ঞতা সনদ', 'Experience Certificate', 'general'],
        ['training', 'প্রশিক্ষণ সনদ', 'Training Certificate', 'education'],
        ['workshop', 'কর্মশালা সনদ', 'Workshop Certificate', 'education'],
        ['seminar', 'সেমিনার সনদ', 'Seminar Certificate', 'education'],
        ['apprenticeship', 'শিক্ষানবিশ সনদ', 'Apprenticeship Certificate', 'education'],
        ['pension', 'পেনশন সনদ', 'Pension Certificate', 'financial'],
        ['old_age', 'বয়স্ক ভাতা সনদ', 'Old Age Allowance Certificate', 'financial'],
        ['widow_allowance', 'বিধবা ভাতা সনদ', 'Widow Allowance Certificate', 'financial'],
        ['freedom_fighter_allowance', 'মুক্তিযোদ্ধা ভাতা সনদ', 'Freedom Fighter Allowance Certificate', 'financial'],
    ];

    $catStmt = $pdo->prepare("INSERT INTO certificate_types (slug, name_bn, name_en, category, fee, status) VALUES (?, ?, ?, ?, 0.00, 'active')");
    $typeIds = [];
    foreach ($certTypes as $ct) {
        $catStmt->execute($ct);
        $typeIds[] = (int) $pdo->lastInsertId();
    }
    echo "  Certificate Types: " . count($certTypes) . "\n";

    // ── Common fields (used by most certificates) ──
    $commonFields = [
        ['applicant_name', 'আবেদনকারীর নাম', 'text', 1, 10],
        ['father_name', 'পিতার নাম', 'text', 1, 20],
        ['mother_name', 'মাতার নাম', 'text', 1, 30],
        ['spouse_name', 'স্বামী/স্ত্রীর নাম', 'text', 0, 40],
        ['present_address', 'বর্তমান ঠিকানা', 'textarea', 1, 50],
        ['permanent_address', 'স্থায়ী ঠিকানা', 'textarea', 0, 60],
        ['nid', 'জাতীয় পরিচয়পত্র নং', 'text', 0, 70],
        ['birth_date', 'জন্ম তারিখ', 'date', 1, 80],
        ['nationality', 'জাতীয়তা', 'select', 1, 90],
        ['religion', 'ধর্ম', 'select', 0, 100],
        ['occupation', 'পেশা', 'text', 0, 110],
        ['gender', 'লিঙ্গ', 'select', 1, 5],
    ];

    $fieldStmt = $pdo->prepare(
        "INSERT INTO certificate_fields (certificate_type_id, field_name, label_bn, field_type, required, position) VALUES (?, ?, ?, ?, ?, ?)"
    );

    $fieldsCount = 0;

    // Field definitions for 5 priority types
    $priorityTypes = [
        'birth' => [
            ['child_name', 'সন্তানের নাম', 'text', 1, 1],
            ['father_name', 'পিতার নাম', 'text', 1, 10],
            ['mother_name', 'মাতার নাম', 'text', 1, 20],
            ['date_of_birth', 'জন্ম তারিখ', 'date', 1, 30],
            ['place_of_birth', 'জন্মস্থান', 'text', 1, 40],
            ['gender', 'লিঙ্গ', 'select', 1, 50],
            ['weight', 'জন্ম ওজন', 'text', 0, 60],
            ['father_nid', 'পিতার এনআইডি', 'text', 1, 70],
            ['mother_nid', 'মাতার এনআইডি', 'text', 1, 80],
            ['permanent_address', 'স্থায়ী ঠিকানা', 'textarea', 1, 90],
        ],
        'death' => [
            ['deceased_name', 'মৃতের নাম', 'text', 1, 1],
            ['father_name', 'পিতার নাম', 'text', 1, 10],
            ['mother_name', 'মাতার নাম', 'text', 1, 20],
            ['spouse_name', 'স্বামী/স্ত্রীর নাম', 'text', 0, 30],
            ['date_of_death', 'মৃত্যু তারিখ', 'date', 1, 40],
            ['place_of_death', 'মৃত্যুস্থান', 'text', 1, 50],
            ['cause_of_death', 'মৃত্যুর কারণ', 'text', 0, 60],
            ['age_at_death', 'মৃত্যুকালে বয়স', 'text', 1, 70],
            ['gender', 'লিঙ্গ', 'select', 1, 80],
            ['nid', 'জাতীয় পরিচয়পত্র নং', 'text', 0, 85],
        ],
        'marriage' => [
            ['groom_name', 'বরের নাম', 'text', 1, 1],
            ['bride_name', 'কনের নাম', 'text', 1, 5],
            ['groom_father', 'বরের পিতার নাম', 'text', 1, 10],
            ['bride_father', 'কনের পিতার নাম', 'text', 1, 15],
            ['groom_mother', 'বরের মাতার নাম', 'text', 1, 20],
            ['bride_mother', 'কনের মাতার নাম', 'text', 1, 25],
            ['marriage_date', 'বিবাহের তারিখ', 'date', 1, 30],
            ['dower_amount', 'মোহরানার পরিমাণ', 'text', 1, 40],
            ['kazi_name', 'কাজীর নাম', 'text', 1, 50],
            ['witness_1', 'সাক্ষী ১', 'text', 1, 60],
            ['witness_2', 'সাক্ষী ২', 'text', 1, 70],
        ],
        'character' => [
            ['applicant_name', 'আবেদনকারীর নাম', 'text', 1, 1],
            ['father_name', 'পিতার নাম', 'text', 1, 10],
            ['mother_name', 'মাতার নাম', 'text', 1, 20],
            ['present_address', 'বর্তমান ঠিকানা', 'textarea', 1, 30],
            ['permanent_address', 'স্থায়ী ঠিকানা', 'textarea', 1, 40],
            ['nid', 'জাতীয় পরিচয়পত্র নং', 'text', 1, 50],
            ['birth_date', 'জন্ম তারিখ', 'date', 1, 60],
            ['occupation', 'পেশা', 'text', 0, 70],
            ['known_since', 'পরিচিতির সময়কাল', 'text', 0, 80],
            ['character_note', 'চরিত্র সম্পর্কে মন্তব্য', 'textarea', 0, 90],
        ],
        'income' => [
            ['applicant_name', 'আবেদনকারীর নাম', 'text', 1, 1],
            ['father_name', 'পিতার নাম', 'text', 1, 10],
            ['mother_name', 'মাতার নাম', 'text', 1, 20],
            ['present_address', 'বর্তমান ঠিকানা', 'textarea', 1, 30],
            ['nid', 'জাতীয় পরিচয়পত্র নং', 'text', 1, 40],
            ['occupation', 'পেশা', 'text', 1, 50],
            ['monthly_income', 'মাসিক আয়', 'text', 1, 60],
            ['income_source', 'আয়ের উৎস', 'text', 1, 70],
            ['annual_income', 'বার্ষিক আয়', 'text', 0, 80],
            ['tax_payer', 'কর প্রদানকারী', 'select', 0, 90],
        ],
    ];

    foreach ($priorityTypes as $slug => $fields) {
        // Get type id
        $q = $pdo->prepare("SELECT id FROM certificate_types WHERE slug = ?");
        $q->execute([$slug]);
        $typeId = (int) $q->fetchColumn();
        if (!$typeId) continue;

        foreach ($fields as $f) {
            $fieldStmt->execute([$typeId, $f[0], $f[1], $f[2], $f[3], $f[4]]);
            $fieldsCount++;
        }
    }

    echo "  Certificate Fields: {$fieldsCount}\n";

    // ── HTML Templates for 5 priority certificate types ──
    // Template for Birth Certificate
    $birthTemplate = '<div class="certificate a4-landscape">
    <div class="certificate-header">
        <img src="'.APP_URL.'/assets/img/govt-logo.png" class="govt-logo">
        <h1>বাংলাদেশ সরকার</h1>
        <h2>Government of the People\'s Republic of Bangladesh</h2>
        <h3>{{union_name}} ইউনিয়ন পরিষদ</h3>
        <h4>জন্ম নিবন্ধন সনদ</h4>
        <h5>Birth Registration Certificate</h5>
    </div>
    <div class="certificate-body">
        <p class="certificate-text">এটি প্রত্যয়ন করা যাচ্ছে যে</p>
        <p class="applicant-name">{{child_name}}</p>
        <table class="details-table">
            <tr><td>পিতার নাম / Father\'s Name</td><td>{{father_name}}</td></tr>
            <tr><td>মাতার নাম / Mother\'s Name</td><td>{{mother_name}}</td></tr>
            <tr><td>জন্ম তারিখ / Date of Birth</td><td>{{date_of_birth}}</td></tr>
            <tr><td>জন্মস্থান / Place of Birth</td><td>{{place_of_birth}}</td></tr>
            <tr><td>লিঙ্গ / Gender</td><td>{{gender}}</td></tr>
            <tr><td>জন্ম ওজন / Birth Weight</td><td>{{weight}}</td></tr>
            <tr><td>সনদ নম্বর / Certificate No</td><td>{{certificate_no}}</td></tr>
            <tr><td>ইস্যুর তারিখ / Issue Date</td><td>{{issue_date}}</td></tr>
        </table>
    </div>
    <div class="certificate-footer">
        <div class="qrcode">{{qr_code}}</div>
        <div class="signature-area">
            <div class="signature-line"></div>
            <p>ইস্যুকারী / Issuer</p>
            <p>{{chairman_name}}</p>
            <p>চেয়ারম্যান / Chairman</p>
        </div>
        <div class="verification-note">
            <p>এই সনদটি প্রত্যয়ন পোর্টাল (prottoyon.gov.bd) হতে ইস্যুকৃত এবং অনলাইনে যাচাইযোগ্য।</p>
            <p>ভেরিফিকেশন কোড: {{verification_code}}</p>
        </div>
    </div>
</div>';

    // Template for Death Certificate
    $deathTemplate = '<div class="certificate a4-landscape">
    <div class="certificate-header">
        <img src="'.APP_URL.'/assets/img/govt-logo.png" class="govt-logo">
        <h1>বাংলাদেশ সরকার</h1>
        <h2>Government of the People\'s Republic of Bangladesh</h2>
        <h3>{{union_name}} ইউনিয়ন পরিষদ</h3>
        <h4>মৃত্যু সনদ</h4>
        <h5>Death Certificate</h5>
    </div>
    <div class="certificate-body">
        <p class="certificate-text">এটি প্রত্যয়ন করা যাচ্ছে যে</p>
        <p class="applicant-name">{{deceased_name}}</p>
        <table class="details-table">
            <tr><td>পিতার নাম / Father\'s Name</td><td>{{father_name}}</td></tr>
            <tr><td>মাতার নাম / Mother\'s Name</td><td>{{mother_name}}</td></tr>
            <tr><td>স্বামী/স্ত্রীর নাম / Spouse\'s Name</td><td>{{spouse_name}}</td></tr>
            <tr><td>মৃত্যু তারিখ / Date of Death</td><td>{{date_of_death}}</td></tr>
            <tr><td>মৃত্যুস্থান / Place of Death</td><td>{{place_of_death}}</td></tr>
            <tr><td>মৃত্যুর কারণ / Cause of Death</td><td>{{cause_of_death}}</td></tr>
            <tr><td>মৃত্যুকালে বয়স / Age at Death</td><td>{{age_at_death}}</td></tr>
            <tr><td>লিঙ্গ / Gender</td><td>{{gender}}</td></tr>
            <tr><td>সনদ নম্বর / Certificate No</td><td>{{certificate_no}}</td></tr>
            <tr><td>ইস্যুর তারিখ / Issue Date</td><td>{{issue_date}}</td></tr>
        </table>
    </div>
    <div class="certificate-footer">
        <div class="qrcode">{{qr_code}}</div>
        <div class="signature-area">
            <div class="signature-line"></div>
            <p>ইস্যুকারী / Issuer</p>
            <p>{{chairman_name}}</p>
            <p>চেয়ারম্যান / Chairman</p>
        </div>
        <div class="verification-note">
            <p>এই সনদটি প্রত্যয়ন পোর্টাল (prottoyon.gov.bd) হতে ইস্যুকৃত এবং অনলাইনে যাচাইযোগ্য।</p>
            <p>ভেরিফিকেশন কোড: {{verification_code}}</p>
        </div>
    </div>
</div>';

    // Template for Marriage Certificate
    $marriageTemplate = '<div class="certificate a4-landscape">
    <div class="certificate-header">
        <img src="'.APP_URL.'/assets/img/govt-logo.png" class="govt-logo">
        <h1>বাংলাদেশ সরকার</h1>
        <h2>Government of the People\'s Republic of Bangladesh</h2>
        <h3>{{union_name}} ইউনিয়ন পরিষদ</h3>
        <h4>বিবাহ সনদ</h4>
        <h5>Marriage Certificate</h5>
    </div>
    <div class="certificate-body">
        <p class="certificate-text">এটি প্রত্যয়ন করা যাচ্ছে যে</p>
        <table class="details-table">
            <tr><td>বরের নাম / Groom\'s Name</td><td>{{groom_name}}</td></tr>
            <tr><td>বরের পিতার নাম / Groom\'s Father</td><td>{{groom_father}}</td></tr>
            <tr><td>বরের মাতার নাম / Groom\'s Mother</td><td>{{groom_mother}}</td></tr>
            <tr><td>কনের নাম / Bride\'s Name</td><td>{{bride_name}}</td></tr>
            <tr><td>কনের পিতার নাম / Bride\'s Father</td><td>{{bride_father}}</td></tr>
            <tr><td>কনের মাতার নাম / Bride\'s Mother</td><td>{{bride_mother}}</td></tr>
            <tr><td>বিবাহের তারিখ / Marriage Date</td><td>{{marriage_date}}</td></tr>
            <tr><td>মোহরানার পরিমাণ / Dower Amount</td><td>{{dower_amount}}</td></tr>
            <tr><td>কাজীর নাম / Kazi\'s Name</td><td>{{kazi_name}}</td></tr>
            <tr><td>সাক্ষী ১ / Witness 1</td><td>{{witness_1}}</td></tr>
            <tr><td>সাক্ষী ২ / Witness 2</td><td>{{witness_2}}</td></tr>
            <tr><td>সনদ নম্বর / Certificate No</td><td>{{certificate_no}}</td></tr>
            <tr><td>ইস্যুর তারিখ / Issue Date</td><td>{{issue_date}}</td></tr>
        </table>
    </div>
    <div class="certificate-footer">
        <div class="qrcode">{{qr_code}}</div>
        <div class="signature-area">
            <div class="signature-line"></div>
            <p>ইস্যুকারী / Issuer</p>
            <p>{{chairman_name}}</p>
            <p>চেয়ারম্যান / Chairman</p>
        </div>
        <div class="verification-note">
            <p>এই সনদটি প্রত্যয়ন পোর্টাল (prottoyon.gov.bd) হতে ইস্যুকৃত এবং অনলাইনে যাচাইযোগ্য।</p>
            <p>ভেরিফিকেশন কোড: {{verification_code}}</p>
        </div>
    </div>
</div>';

    // Template for Character Certificate
    $characterTemplate = '<div class="certificate a4-landscape">
    <div class="certificate-header">
        <img src="'.APP_URL.'/assets/img/govt-logo.png" class="govt-logo">
        <h1>বাংলাদেশ সরকার</h1>
        <h2>Government of the People\'s Republic of Bangladesh</h2>
        <h3>{{union_name}} ইউনিয়ন পরিষদ</h3>
        <h4>চরিত্র সনদ</h4>
        <h5>Character Certificate</h5>
    </div>
    <div class="certificate-body">
        <p class="certificate-text">এটি প্রত্যয়ন করা যাচ্ছে যে</p>
        <p class="applicant-name">{{applicant_name}}</p>
        <table class="details-table">
            <tr><td>পিতার নাম / Father\'s Name</td><td>{{father_name}}</td></tr>
            <tr><td>মাতার নাম / Mother\'s Name</td><td>{{mother_name}}</td></tr>
            <tr><td>বর্তমান ঠিকানা / Present Address</td><td>{{present_address}}</td></tr>
            <tr><td>স্থায়ী ঠিকানা / Permanent Address</td><td>{{permanent_address}}</td></tr>
            <tr><td>জাতীয় পরিচয়পত্র / NID</td><td>{{nid}}</td></tr>
            <tr><td>জন্ম তারিখ / Date of Birth</td><td>{{birth_date}}</td></tr>
            <tr><td>পেশা / Occupation</td><td>{{occupation}}</td></tr>
            <tr><td>পরিচিতির সময়কাল / Known Since</td><td>{{known_since}}</td></tr>
            <tr><td>চরিত্র সম্পর্কে / Character Note</td><td>{{character_note}}</td></tr>
            <tr><td>সনদ নম্বর / Certificate No</td><td>{{certificate_no}}</td></tr>
            <tr><td>ইস্যুর তারিখ / Issue Date</td><td>{{issue_date}}</td></tr>
        </table>
        <p class="certificate-text">উপরোক্ত ব্যক্তির চরিত্র ভালো এবং সে সমাজের একজন সৎ নাগরিক।</p>
    </div>
    <div class="certificate-footer">
        <div class="qrcode">{{qr_code}}</div>
        <div class="signature-area">
            <div class="signature-line"></div>
            <p>ইস্যুকারী / Issuer</p>
            <p>{{chairman_name}}</p>
            <p>চেয়ারম্যান / Chairman</p>
        </div>
        <div class="verification-note">
            <p>এই সনদটি প্রত্যয়ন পোর্টাল (prottoyon.gov.bd) হতে ইস্যুকৃত এবং অনলাইনে যাচাইযোগ্য।</p>
            <p>ভেরিফিকেশন কোড: {{verification_code}}</p>
        </div>
    </div>
</div>';

    // Template for Income Certificate
    $incomeTemplate = '<div class="certificate a4-landscape">
    <div class="certificate-header">
        <img src="'.APP_URL.'/assets/img/govt-logo.png" class="govt-logo">
        <h1>বাংলাদেশ সরকার</h1>
        <h2>Government of the People\'s Republic of Bangladesh</h2>
        <h3>{{union_name}} ইউনিয়ন পরিষদ</h3>
        <h4>আয় সনদ</h4>
        <h5>Income Certificate</h5>
    </div>
    <div class="certificate-body">
        <p class="certificate-text">এটি প্রত্যয়ন করা যাচ্ছে যে</p>
        <p class="applicant-name">{{applicant_name}}</p>
        <table class="details-table">
            <tr><td>পিতার নাম / Father\'s Name</td><td>{{father_name}}</td></tr>
            <tr><td>মাতার নাম / Mother\'s Name</td><td>{{mother_name}}</td></tr>
            <tr><td>বর্তমান ঠিকানা / Present Address</td><td>{{present_address}}</td></tr>
            <tr><td>জাতীয় পরিচয়পত্র / NID</td><td>{{nid}}</td></tr>
            <tr><td>পেশা / Occupation</td><td>{{occupation}}</td></tr>
            <tr><td>মাসিক আয় / Monthly Income</td><td>{{monthly_income}} টাকা</td></tr>
            <tr><td>আয়ের উৎস / Source of Income</td><td>{{income_source}}</td></tr>
            <tr><td>বার্ষিক আয় / Annual Income</td><td>{{annual_income}} টাকা</td></tr>
            <tr><td>কর প্রদানকারী / Tax Payer</td><td>{{tax_payer}}</td></tr>
            <tr><td>সনদ নম্বর / Certificate No</td><td>{{certificate_no}}</td></tr>
            <tr><td>ইস্যুর তারিখ / Issue Date</td><td>{{issue_date}}</td></tr>
        </table>
    </div>
    <div class="certificate-footer">
        <div class="qrcode">{{qr_code}}</div>
        <div class="signature-area">
            <div class="signature-line"></div>
            <p>ইস্যুকারী / Issuer</p>
            <p>{{chairman_name}}</p>
            <p>চেয়ারম্যান / Chairman</p>
        </div>
        <div class="verification-note">
            <p>এই সনদটি প্রত্যয়ন পোর্টাল (prottoyon.gov.bd) হতে ইস্যুকৃত এবং অনলাইনে যাচাইযোগ্য।</p>
            <p>ভেরিফিকেশন কোড: {{verification_code}}</p>
        </div>
    </div>
</div>';

    // Template variable definitions
    $templates = [
        ['birth', 'জন্ম নিবন্ধন সনদ', 'Birth Registration Certificate', $birthTemplate, 'certificate'],
        ['death', 'মৃত্যু সনদ', 'Death Certificate', $deathTemplate, 'certificate'],
        ['marriage', 'বিবাহ সনদ', 'Marriage Certificate', $marriageTemplate, 'certificate'],
        ['character', 'চরিত্র সনদ', 'Character Certificate', $characterTemplate, 'certificate'],
        ['income', 'আয় সনদ', 'Income Certificate', $incomeTemplate, 'certificate'],
    ];

    $tplStmt = $pdo->prepare(
        "INSERT INTO document_templates (shop_id, name, category, template_type, content, variables, paper_size, status, created_by, created_at, updated_at)
         VALUES (1, ?, 'certificate', 'html', ?, NULL, 'A4_LANDSCAPE', 'active', 1, NOW(), NOW())"
    );

    foreach ($templates as $t) {
        $name = $t[1] . ' (' . $t[2] . ')';
        $tplStmt->execute([$name, $t[3]]);
    }
    echo "  Templates: " . count($templates) . "\n";

    $pdo->commit();
    echo "\nCertificate seed completed.\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
