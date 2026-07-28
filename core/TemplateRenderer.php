<?php

namespace App\Core;

class TemplateRenderer
{
    const FONT_SOLAIMANLIPI = 'solaimanlipi';
    const BORDER_COLOR = [0, 103, 71];
    const BORDER_WIDTH = 2;
    const LOGO_X = 45;
    const LOGO_Y = 18;
    const LOGO_W = 20;
    const LOGO_H = 20;
    const BORDER_X = 10;
    const BORDER_Y = 10;

    private static array $bnMonths = [
        'January' => 'জানুয়ারি', 'February' => 'ফেব্রুয়ারি',
        'March' => 'মার্চ', 'April' => 'এপ্রিল',
        'May' => 'মে', 'June' => 'জুন',
        'July' => 'জুলাই', 'August' => 'আগস্ট',
        'September' => 'সেপ্টেম্বর', 'October' => 'অক্টোবর',
        'November' => 'নভেম্বর', 'December' => 'ডিসেম্বর',
    ];

    private static array $bnNumbers = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

    private static array $religionMap = [
        'islam' => 'ইসলাম', 'hindu' => 'হিন্দু', 'buddhist' => 'বৌদ্ধ', 'christian' => 'খ্রিস্টান',
    ];

    private static array $genderMap = [
        'male' => 'পুরুষ', 'female' => 'মহিলা', 'other' => 'অন্যান্য',
    ];

    private static array $maritalMap = [
        'single' => 'অবিবাহিত', 'married' => 'বিবাহিত', 'divorced' => 'বিচ্ছেদিত', 'widowed' => 'বিধবা/বিপত্নীক',
    ];

    public static function en2bn(string $str): string
    {
        $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return str_replace($en, self::$bnNumbers, $str);
    }

    public static function formatDateBn(string $date): string
    {
        $ts = strtotime($date);
        if (!$ts) return $date;
        $day = (int) date('j', $ts);
        $monthEn = date('F', $ts);
        $monthBn = self::$bnMonths[$monthEn] ?? $monthEn;
        $year = self::en2bn(date('Y', $ts));
        $dayBn = self::en2bn((string) $day);
        $suffix = 'ই';
        if (in_array($day, [1, 21, 31])) $suffix = 'লা';
        elseif (in_array($day, [2, 3, 22, 23])) $suffix = 'রা';
        elseif (in_array($day, [4, 24])) $suffix = 'ঠা';
        return $dayBn . $suffix . ' ' . $monthBn . ' ' . $year;
    }

    public static function mapReligion(?string $religion): string
    {
        return self::$religionMap[strtolower($religion ?? '')] ?? ($religion ?: 'ইসলাম');
    }

    public static function mapGender(?string $gender): string
    {
        return self::$genderMap[strtolower($gender ?? '')] ?? ($gender ?: 'পুরুষ');
    }

    public static function mapMarital(?string $status): string
    {
        return self::$maritalMap[strtolower($status ?? '')] ?? ($status ?: '');
    }

    public static function mapOccupation(?string $occupation): string
    {
        return $occupation ?: 'কৃষক';
    }

    public static function applyLanguage(array $data, string $lang = 'bn'): array
    {
        if ($lang === 'en') {
            $data['name_bn'] = $data['name_en'] ?? $data['name_bn'];
            $data['father_name'] = $data['father_name_en'] ?? $data['father_name'];
            $data['mother_name'] = $data['mother_name_en'] ?? $data['mother_name'];
            $data['spouse_name'] = $data['spouse_name_en'] ?? ($data['spouse_name'] ?? '');
            $data['village'] = $data['village_en'] ?? $data['village'];
        } elseif ($lang === 'both') {
            $data['name_bn'] = $data['name_bn'] . ($data['name_en'] ? ' (' . $data['name_en'] . ')' : '');
            $data['father_name'] = $data['father_name'] . ($data['father_name_en'] ? ' (' . $data['father_name_en'] . ')' : '');
            $data['mother_name'] = $data['mother_name'] . ($data['mother_name_en'] ? ' (' . $data['mother_name_en'] . ')' : '');
            $v = $data['spouse_name'] ?? '';
            $ve = $data['spouse_name_en'] ?? '';
            $data['spouse_name'] = $v . ($ve ? ' (' . $ve . ')' : '');
            $data['village'] = $data['village'] . ($data['village_en'] ? ' (' . $data['village_en'] . ')' : '');
        }
        return $data;
    }

    public static function parseTemplate(string $content, array $data): string
    {
        $lang = $data['lang'] ?? 'bn';
        $data = self::applyLanguage($data, $lang);

        $data['nid_bn'] = !empty($data['nid']) ? self::en2bn($data['nid']) : '';
        $data['certificate_no_bn'] = !empty($data['certificate_no']) ? self::en2bn($data['certificate_no']) : '';

        $data['d_o_b'] = !empty($data['dob']) ? self::formatDateBn($data['dob']) : ($data['dob'] ?? '');
        $data['issue_date_bn'] = !empty($data['issue_date']) ? self::formatDateBn($data['issue_date']) : '';

        $data['ward_no_bn'] = !empty($data['ward_no']) ? self::en2bn($data['ward_no']) : '০১';

        $data['wish_text'] = $data['wish_text'] ?? '— তাঁর ভবিষ্যৎ জীবন সুখময়, সমৃদ্ধ ও উজ্জ্বল হোক। —';

        $result = self::processConditionals($content, $data);

        foreach ($data as $key => $value) {
            if (is_scalar($value) || is_null($value)) {
                $result = str_replace('{{' . $key . '}}', (string) ($value ?? ''), $result);
            }
        }

        $result = preg_replace('/\{\{[a-zA-Z_][a-zA-Z0-9_]*\}\}/', '', $result);

        return $result;
    }

    private static function processConditionals(string $content, array $data): string
    {
        return preg_replace_callback(
            '/\{%if\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*%\}(.*?)\{%endif%\}/s',
            function ($matches) use ($data) {
                $varName = $matches[1];
                $innerContent = $matches[2];
                if (!empty($data[$varName]) && $data[$varName] !== '') {
                    return $innerContent;
                }
                return '';
            },
            $content
        );
    }

    public static function renderHTML(array $template, array $data, array $signatories = []): string
    {
        $body = self::parseTemplate($template['content'], $data);

        $chairman = $signatories['chairman'] ?? 'চেয়ারম্যান';
        $secretary = $signatories['secretary'] ?? 'সচিব';
        $preparer = $signatories['preparer'] ?? '';

        $unionName = $data['union_name'] ?? 'ইউনিয়ন পরিষদ';
        $upazilaName = $data['upazila_name'] ?? '';
        $districtName = $data['district_name'] ?? '';
        $officeTypeLabel = $data['office_type_label'] ?? 'ইউনিয়ন পরিষদ';
        $certTypeName = $data['cert_type_name_bn'] ?? 'সনদ';
        $certNo = $data['certificate_no'] ?? '';
        $issueDate = $data['issue_date_bn'] ?? $data['issue_date'] ?? '';
        $logoUrl = $data['logo_url'] ?? asset('assets/images/gov_logo.png');
        $verifyUrl = url('verify/' . ($data['verification_code'] ?? $certNo));

        $html = '<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>' . e($certTypeName) . ' – ' . e($unionName) . '</title>
<link href="https://fonts.maateen.me/solaiman-lipi/font.css" rel="stylesheet"/>
<link href="' . asset('assets/css/certificate.css') . '" rel="stylesheet"/>
</head>
<body>

<div class="cert">

  <div class="watermark">
    <img src="' . $logoUrl . '" alt="জলছাপ"/>
  </div>

  <div class="header">
    <div class="header-logo">
      <img src="' . $logoUrl . '" alt="গণপ্রজাতন্ত্রী বাংলাদেশ সরকার"/>
    </div>
    <div class="header-right">
      <div class="org-name">' . e($unionName) . ' ' . e($officeTypeLabel) . '</div>
      <div class="org-addr">' . e($upazilaName) . ', ' . e($districtName) . ', বাংলাদেশ</div>
    </div>
  </div>

  <div class="cert-title-row">
    <span class="cert-type">' . e($certTypeName) . '</span>
  </div>

  <div class="meta">
    <div class="meta-item">
      <span class="meta-label">সনদ নম্বরঃ</span>
      <span class="meta-value">' . e($certNo) . '</span>
    </div>
    <div class="meta-item">
      <span class="meta-label">ইস্যুর তারিখঃ</span>
      <span class="meta-value">' . e($issueDate) . '</span>
    </div>
  </div>

  <div class="main">
    <div class="left-col">
      ' . $body . '
    </div>
  </div>

  <div class="footer-box">
    <div class="footer">
      <div class="qr-section" id="qrSection" data-url="' . $verifyUrl . '">
        <div id="qrcode"></div>
      </div>
      <div class="sig">
        <div class="sig-line"><span class="sig-dots">· · · · ·</span></div>
        <div class="sig-name">' . e($chairman) . '</div>
        <div class="sig-role">চেয়ারম্যান</div>
        <div class="sig-role">' . e($unionName) . ' ' . e($officeTypeLabel) . '</div>
      </div>
    </div>
    <div class="bottom-bar">
      Verify: <a href="' . $verifyUrl . '" target="_blank">' . $verifyUrl . '</a>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(function() {
  var size = 100;
  var url = document.getElementById("qrSection").getAttribute("data-url");
  new QRCode(document.getElementById("qrcode"), {
    text: url,
    width: size,
    height: size,
    colorDark: "#000000",
    colorLight: "#ffffff",
    correctLevel: QRCode.CorrectLevel.H
  });
})();
</script>

<style>
@media print {
  .no-print { display: none !important; }
}
</style>
<div class="no-print" style="position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;gap:8px;">
  <button onclick="window.print()" style="padding:10px 22px;background:#1a2e4a;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:14px;font-weight:600;box-shadow:0 4px 12px rgba(0,0,0,0.25);display:flex;align-items:center;gap:6px;">
    <span style="font-size:18px;">🖨</span> Print / PDF
  </button>
</div>
<script>
document.addEventListener("keydown",function(e){if((e.ctrlKey||e.metaKey)&&e.key==="p"){e.preventDefault();window.print()}});
</script>

</body>
</html>';

        return $html;
    }

    public static function renderPDF(array $template, array $data, array $signatories = [], ?string $outputPath = null): string
    {
        if (!class_exists('TCPDF')) {
            return self::renderHTML($template, $data, $signatories);
        }

        $body = self::parseTemplate($template['content'], $data);

        $chairman = $signatories['chairman'] ?? 'চেয়ারম্যান';
        $unionName = $data['union_name'] ?? 'ইউনিয়ন পরিষদ';
        $upazilaName = $data['upazila_name'] ?? '';
        $districtName = $data['district_name'] ?? '';
        $certTypeName = $data['cert_type_name_bn'] ?? 'সনদ';
        $certNo = $data['certificate_no'] ?? '';
        $issueDate = $data['issue_date_bn'] ?? $data['issue_date'] ?? '';
        $logoUrl = $data['logo_url'] ?? APP_ROOT . '/assets/images/gov_logo.png';
        $verifyUrl = url('verify/' . ($data['verification_code'] ?? $certNo));

        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator(APP_NAME);
        $pdf->SetAuthor(APP_NAME);
        $pdf->SetTitle('Certificate - ' . $certNo);
        $pdf->SetSubject($certTypeName);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(20, 20, 20);
        $pdf->AddPage();

        $borderStyle = ['width' => self::BORDER_WIDTH, 'color' => self::BORDER_COLOR];
        $pageW = $pdf->getPageWidth();
        $pageH = $pdf->getPageHeight();
        $pdf->Rect(self::BORDER_X, self::BORDER_Y, $pageW - 20, $pageH - 20, 'D', $borderStyle);

        $pdf->SetFont('freeserif', '', 14);

        if (file_exists(str_replace(APP_URL, APP_ROOT, $logoUrl)) || file_exists($logoUrl)) {
            $logoPath = file_exists($logoUrl) ? $logoUrl : str_replace(APP_URL, APP_ROOT, $logoUrl);
            if (file_exists($logoPath)) {
                $pdf->Image($logoPath, self::LOGO_X, self::LOGO_Y, self::LOGO_W, self::LOGO_H, 'PNG');
            }
        }

        $pdf->SetY(22);
        $pdf->SetFont('freeserif', 'B', 16);
        $pdf->Cell(0, 8, 'বাংলাদেশ সরকার', 0, 1, 'C');
        $pdf->SetFont('freeserif', '', 12);
        $pdf->Cell(0, 7, "Government of the People's Republic of Bangladesh", 0, 1, 'C');

        $unionFull = $unionName . ' ইউনিয়ন পরিষদ';
        $pdf->SetFont('freeserif', 'B', 13);
        $pdf->Cell(0, 7, $unionFull, 0, 1, 'C');

        $pdf->SetFont('freeserif', 'B', 13);
        $pdf->Cell(0, 7, $certTypeName, 0, 1, 'C');

        $pdf->Line(30, $pdf->GetY() + 2, $pageW - 30, $pdf->GetY() + 2);
        $pdf->Ln(8);

        $pdf->SetFont('freeserif', '', 13);
        $pdf->Cell(0, 8, 'এটি প্রত্যয়ন করা যাচ্ছে যে', 0, 1, 'C');
        $pdf->Ln(3);

        $pdf->SetFont('freeserif', 'B', 16);
        $pdf->Cell(0, 10, $data['name_bn'] ?? '', 0, 1, 'C');
        $pdf->Ln(3);

        $details = [
            ['পিতার নাম', $data['father_name'] ?? 'N/A'],
            ['মাতার নাম', $data['mother_name'] ?? 'N/A'],
            ['জাতীয় পরিচয়পত্র নং', $data['nid'] ?? 'N/A'],
            ['সনদ নম্বর', $certNo],
            ['ইস্যুর তারিখ', $issueDate],
        ];
        $pdf->SetFont('freeserif', '', 12);
        foreach ($details as $d) {
            $pdf->Cell(60, 7, $d[0], 0, 0, 'R');
            $pdf->Cell(4);
            $pdf->SetFont('freeserif', 'B', 12);
            $pdf->Cell(0, 7, $d[1], 0, 1, 'L');
            $pdf->SetFont('freeserif', '', 12);
        }

        $pdf->Ln(5);
        $pdf->SetFont('freeserif', '', 10);
        $pdf->Cell(0, 7, 'এই সনদটি প্রত্যয়ন পোর্টাল (prottoyon.gov.bd) হতে ইস্যুকৃত এবং অনলাইনে যাচাইযোগ্য।', 0, 1, 'C');

        $pdf->Ln(15);
        $issuerLabel = 'ইস্যুকারী / Issuer: ' . $chairman;
        $pdf->SetFont('freeserif', '', 11);
        $pdf->Cell(0, 7, $issuerLabel, 0, 1, 'C');
        $pdf->Cell(70, 0, '', 'T', 0, 'C');
        $pdf->Cell(50);
        $pdf->Cell(70, 0, '', 'T', 1, 'C');
        $pdf->SetFont('freeserif', '', 11);
        $pdf->Cell(70, 6, 'স্বাক্ষর / Signature', 0, 0, 'C');
        $pdf->Cell(50);
        $pdf->Cell(70, 6, 'স্বাক্ষর / Signature', 0, 1, 'C');

        try {
            if (class_exists('\Endroid\QrCode\QrCode') && class_exists('\Endroid\QrCode\Writer\PngWriter')) {
                $qrCode = new \Endroid\QrCode\QrCode($verifyUrl);
                $writer = new \Endroid\QrCode\Writer\PngWriter();
                $qrResult = $writer->write($qrCode);
                $qrPath = tempnam(sys_get_temp_dir(), 'qr') . '.png';
                file_put_contents($qrPath, $qrResult->getString());
                $pdf->Image($qrPath, $pageW - 50, $pageH - 55, 30, 30, 'PNG');
                unlink($qrPath);
            }
        } catch (\Exception $e) {
        }

        $pdf->Ln(10);
        $pdf->SetFont('', 'I', 8);
        $pdf->Cell(0, 5, 'Verify at: ' . $verifyUrl . ' | Certificate No: ' . $certNo, 0, 1, 'C');

        if ($outputPath) {
            $pdf->Output($outputPath, 'F');
            return $outputPath;
        }
        return $pdf->Output('certificate.pdf', 'S');
    }
}
