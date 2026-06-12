<?php
/**
 * Expects $cardData with:
 * full_name, registration_number, admission_number, course, college,
 * card_number, issue_date, expiry_date, verification_url, photo_src, qr_src,
 * department, semester, study_year, academic_year, director_signature,
 * director_signature_image, issue_date_display, show_back_link, back_url, _card_mode.
 */
$showBackLink = !empty($cardData['show_back_link']);
$backUrl = (string)($cardData['back_url'] ?? '');
$fullName = (string)($cardData['full_name'] ?? '');
$registrationNumber = (string)($cardData['registration_number'] ?? '');
$admissionNumber = (string)($cardData['admission_number'] ?? '');
$course = (string)($cardData['course'] ?? '');
$semester = ucwords(str_replace('_', ' ', (string)($cardData['semester'] ?? '')));
$studyYear = (string)($cardData['study_year'] ?? '');
$academicYear = (string)($cardData['academic_year'] ?? '');
$department = (string)($cardData['department'] ?? $cardData['college'] ?? '');
$photoSrc = (string)($cardData['photo_src'] ?? '');
$qrSrc = (string)($cardData['qr_src'] ?? '');
$directorSignatureImage = (string)($cardData['director_signature_image'] ?? '');
$directorSignature = (string)($cardData['director_signature'] ?? 'DIRECTOR SIGNATURE');
$issueDateDisplay = (string)($cardData['issue_date_display'] ?? $cardData['issue_date'] ?? '');
$cardMode = (string)($cardData['_card_mode'] ?? 'default');
$isDownloadMode = ($cardMode === 'download');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Green Card Preview</title>
    <style>
        @page { size: 176mm 118mm; margin: 0; }
        @page.download-mode { size: 176mm 95mm; margin: 0; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #eef7f0;
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #0b1220;
        }
        .page-shell {
            max-width: 980px;
            margin: 20px auto;
            padding: 0 12px;
        }
        .mobile-back {
            display: block;
            padding: 0;
            margin: 0 0 16px 0;
        }
        .mobile-back a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 20px;
            background: #0f5132;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            width: 100%;
            box-shadow: 0 2px 6px rgba(15, 81, 50, 0.28);
        }
        .mobile-back a::before {
            content: '<';
            font-weight: 800;
            font-size: 16px;
        }
        .card-shell {
            width: 176mm;
            min-height: 118mm;
            margin: 0 auto;
            border-radius: 12px;
            border: 1.3px solid #0f5132;
            background: #eaf8ef;
            overflow: hidden;
            position: relative;
            box-shadow: 0 12px 28px rgba(12, 58, 37, 0.18);
            page-break-inside: avoid;
        }
        .card-shell.download-mode {
            min-height: 95mm;
        }
        .card-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-28deg);
            font-size: 52mm;
            font-weight: 900;
            letter-spacing: 2.5mm;
            color: rgba(11, 93, 59, 0.11);
            text-transform: uppercase;
            z-index: 0;
            line-height: 1;
            white-space: nowrap;
        }
        .head {
            min-height: 22mm;
            padding: 0 8mm;
            background: #0f6b44;
            color: #ffffff;
            text-align: center;
            position: relative;
            z-index: 1;
            display: table;
            width: 100%;
        }
        .head-inner {
            display: table-cell;
            vertical-align: middle;
        }
        .university {
            font-size: 17px;
            font-weight: 700;
            letter-spacing: 0.7px;
            text-transform: uppercase;
        }
        .title {
            margin-top: 1.2mm;
            font-size: 13px;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }
        .content {
            padding: 8mm 10mm 8mm;
            display: table;
            width: 100%;
            position: relative;
            z-index: 1;
        }
        .left, .right {
            display: table-cell;
            vertical-align: top;
        }
        .left {
            width: 49mm;
            padding-right: 4.8mm;
        }
        .photo-box {
            width: 40mm;
            height: 49mm;
            border: 1px solid #6ea784;
            border-radius: 6px;
            overflow: hidden;
            background: #ffffff;
            margin-left: 1.2mm;
        }
        .photo-box img {
            width: 100%;
            height: 100%;
            display: block;
        }
        .meta {
            margin-top: 3mm;
            font-size: 8px;
            color: #0f172a;
            line-height: 1.45;
            text-transform: uppercase;
            word-wrap: break-word;
            padding-left: 1.2mm;
        }
        .qr-box {
            margin-top: 3mm;
            margin-left: 1.2mm;
            width: 30mm;
            height: 30mm;
            border: 1px solid #6ea784;
            border-radius: 4px;
            background: #ffffff;
            padding: 1.8mm;
        }
        .qr-box img {
            width: 100%;
            height: 100%;
            display: block;
        }
        .row {
            margin-bottom: 2.0mm;
            border-bottom: 1px dotted #7ea98c;
            padding-bottom: 1.0mm;
        }
        .label {
            font-size: 9px;
            color: #000000;
            text-transform: uppercase;
            letter-spacing: 0.32px;
            margin-bottom: 0.4mm;
            font-weight: 800;
        }
        .value {
            font-size: 12.8px;
            color: #000000;
            font-weight: 900;
            line-height: 1.28;
            word-wrap: break-word;
        }
        .cert {
            margin-top: 3.0mm;
            font-size: 8px;
            color: #000000;
            line-height: 1.52;
            text-transform: uppercase;
            font-weight: 800;
        }
        .sign {
            margin-top: 1.6mm;
            display: table;
            width: 100%;
        }
        .sign .left, .sign .right {
            display: table-cell;
            vertical-align: bottom;
        }
        .sign .left {
            width: 44%;
            font-size: 8.6px;
            font-weight: 800;
            color: #000000;
            text-transform: uppercase;
        }
        .sign .right {
            text-align: left;
            padding-top: 0.3mm;
            padding-bottom: 0.2mm;
        }
        .sig-line {
            display: inline-block;
            min-width: 52mm;
            border-bottom: 1px solid #3f6f52;
            height: 2.6mm;
        }
        .sig-name {
            margin-top: 0;
            margin-bottom: 0.6mm;
            font-size: 10px;
            color: #000000;
            font-weight: 900;
        }
        .sig-image-wrap {
            margin-top: 0;
            margin-bottom: 0;
            line-height: 0;
        }
        .sig-image {
            max-width: 40mm;
            height: 8.5mm;
            width: auto;
            display: block;
            margin-left: 0;
        }
        .sig-title {
            margin-top: 0.3mm;
            font-size: 8.2px;
            font-weight: 900;
            color: #000000;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .footer {
            border-top: 1px solid #a7c9b3;
            background: #e9f8ee;
            padding: 3.6mm 10mm;
            font-size: 8.3px;
            color: #1e293b;
            display: table;
            width: 100%;
            position: relative;
            z-index: 1;
        }
        .left-f, .right-f {
            display: table-cell;
            vertical-align: middle;
        }
        .right-f {
            text-align: right;
            font-weight: 700;
            color: #0b5d3b;
            text-transform: uppercase;
        }
        @media print {
            body {
                background: #ffffff;
            }
            .page-shell {
                margin: 0;
                padding: 0;
                max-width: none;
            }
            .card-shell {
                box-shadow: none;
            }
            .mobile-back {
                display: none !important;
            }
        }
        @media (min-width: 821px) {
            .mobile-back {
                display: none;
            }
        }
        @media screen and (max-width: 820px) {
            .content, .left, .right, .footer, .left-f, .right-f, .sign, .sign .left, .sign .right {
                display: block;
                width: 100%;
            }
            .left {
                padding-right: 0;
                margin-bottom: 12px;
            }
            .sign .right {
                margin-top: 8px;
                text-align: left;
            }
            .sig-line {
                min-width: 160px;
            }
            .right-f {
                text-align: left;
                margin-top: 6px;
            }
            .card-shell {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="page-shell">
        <?php if ($showBackLink && $backUrl !== ''): ?>
            <div class="mobile-back">
                <a href="<?php echo htmlspecialchars($backUrl); ?>">Back to Dashboard</a>
            </div>
        <?php endif; ?>

        <div class="card-shell<?php echo $isDownloadMode ? ' download-mode' : ''; ?>">
            <div class="card-watermark" aria-hidden="true">KIU</div>

            <div class="head">
                <div class="head-inner">
                    <div class="university">Kampala International University</div>
                    <div class="title">Student Green Card</div>
                </div>
            </div>

            <div class="content">
                <div class="left">
                    <div class="photo-box">
                        <?php if ($photoSrc !== ''): ?>
                            <img src="<?php echo htmlspecialchars($photoSrc); ?>" alt="Student Photo">
                        <?php endif; ?>
                    </div>
                    <div class="meta">ADM NO: <?php echo htmlspecialchars($admissionNumber); ?></div>
                    <?php if ($qrSrc !== ''): ?>
                        <div class="qr-box">
                            <img src="<?php echo htmlspecialchars($qrSrc); ?>" alt="QR Code">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="right">
                    <div class="row"><div class="label">STUDENT'S NAME</div><div class="value"><?php echo htmlspecialchars($fullName); ?></div></div>
                    <div class="row"><div class="label">REGISTRATION No.</div><div class="value"><?php echo htmlspecialchars($registrationNumber); ?></div></div>
                    <div class="row"><div class="label">COURSE</div><div class="value"><?php echo htmlspecialchars($course); ?></div></div>
                    <div class="row"><div class="label">TERM / SEMESTER</div><div class="value"><?php echo htmlspecialchars($semester); ?></div></div>
                    <div class="row"><div class="label">YEAR</div><div class="value"><?php echo htmlspecialchars($studyYear); ?></div></div>
                    <div class="row"><div class="label">ACADEMIC YEAR</div><div class="value"><?php echo htmlspecialchars($academicYear); ?></div></div>
                    <div class="row"><div class="label">DEPARTMENT</div><div class="value"><?php echo htmlspecialchars($department); ?></div></div>

                    <div class="cert">
                        THIS IS TO CERTIFY THAT THE ABOVE NAMED HAS REGISTERED AS A STUDENT
                        OF THE STATED COURSE FOR THE ACADEMIC YEAR INDICATED ABOVE.
                    </div>

                    <div class="sign">
                        <div class="left">CERTIFICATE ISSUED BY</div>
                        <div class="right">
                            <?php if ($directorSignatureImage !== ''): ?>
                                <div class="sig-image-wrap">
                                    <img class="sig-image" src="<?php echo htmlspecialchars($directorSignatureImage); ?>" alt="Director Signature">
                                </div>
                            <?php else: ?>
                                <div class="sig-name"><?php echo htmlspecialchars($directorSignature); ?></div>
                            <?php endif; ?>
                            <span class="sig-line"></span>
                            <div class="sig-title">DIRECTOR OF ADMISSIONS</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer">
                <div class="left-f">Issued: <?php echo htmlspecialchars($issueDateDisplay); ?></div>
                <div class="right-f">Official Green Card</div>
            </div>
        </div>
    </div>
</body>
</html>
