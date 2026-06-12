Add-Type -AssemblyName System.Drawing

$width = 3600
$height = 1900
$outPath = "c:\xampp\htdocs\research\report_assets\dfd_level2_simple_bw.png"

$bmp = New-Object System.Drawing.Bitmap($width, $height)
$g = [System.Drawing.Graphics]::FromImage($bmp)
$g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
$g.Clear([System.Drawing.Color]::White)

$fontTitle = New-Object System.Drawing.Font("Segoe UI", 22, [System.Drawing.FontStyle]::Bold)
$fontLabel = New-Object System.Drawing.Font("Segoe UI", 30, [System.Drawing.FontStyle]::Regular)
$fontSmall = New-Object System.Drawing.Font("Segoe UI", 22, [System.Drawing.FontStyle]::Regular)

$penBlack = New-Object System.Drawing.Pen([System.Drawing.Color]::FromArgb(45,45,45), 5)
$penArrow = New-Object System.Drawing.Pen([System.Drawing.Color]::Black, 5)
$penArrow.CustomEndCap = New-Object System.Drawing.Drawing2D.AdjustableArrowCap(14, 16)

$brushEntity = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::White)
$brushProcess = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::White)
$brushStore = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::White)
$brushText = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(20,20,20))
$brushSub = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::Black)


function DrawBox($graphics, $brush, $pen, $font, $textBrush, $x, $y, $w, $h, $text) {
    $rect = New-Object System.Drawing.Rectangle($x, $y, $w, $h)
    $rectF = New-Object System.Drawing.RectangleF([float]$x, [float]$y, [float]$w, [float]$h)
    $graphics.FillRectangle($brush, $rect)
    $graphics.DrawRectangle($pen, $rect)
    $sf = New-Object System.Drawing.StringFormat
    $sf.Alignment = [System.Drawing.StringAlignment]::Center
    $sf.LineAlignment = [System.Drawing.StringAlignment]::Center
    $graphics.DrawString($text, $font, $textBrush, $rectF, $sf)
}

function DrawProcess($graphics, $brush, $pen, $font, $textBrush, $x, $y, $w, $h, $text) {
    $path = New-Object System.Drawing.Drawing2D.GraphicsPath
    $r = 14
    $path.AddArc($x, $y, $r, $r, 180, 90)
    $path.AddArc($x+$w-$r, $y, $r, $r, 270, 90)
    $path.AddArc($x+$w-$r, $y+$h-$r, $r, $r, 0, 90)
    $path.AddArc($x, $y+$h-$r, $r, $r, 90, 90)
    $path.CloseFigure()
    $graphics.FillPath($brush, $path)
    $graphics.DrawPath($pen, $path)
    $rect = New-Object System.Drawing.Rectangle($x, $y, $w, $h)
    $rectF = New-Object System.Drawing.RectangleF([float]$x, [float]$y, [float]$w, [float]$h)
    $sf = New-Object System.Drawing.StringFormat
    $sf.Alignment = [System.Drawing.StringAlignment]::Center
    $sf.LineAlignment = [System.Drawing.StringAlignment]::Center
    $graphics.DrawString($text, $font, $textBrush, $rectF, $sf)
}

function DrawStore($graphics, $brush, $pen, $font, $textBrush, $x, $y, $w, $h, $text) {
    $rect = New-Object System.Drawing.Rectangle($x, $y, $w, $h)
    $rectF = New-Object System.Drawing.RectangleF([float]$x, [float]$y, [float]$w, [float]$h)
    $graphics.FillRectangle($brush, $rect)
    $graphics.DrawRectangle($pen, $rect)
    $graphics.DrawLine($pen, $x+8, $y, $x+8, $y+$h)
    $graphics.DrawLine($pen, $x+$w-8, $y, $x+$w-8, $y+$h)
    $sf = New-Object System.Drawing.StringFormat
    $sf.Alignment = [System.Drawing.StringAlignment]::Center
    $sf.LineAlignment = [System.Drawing.StringAlignment]::Center
    $graphics.DrawString($text, $font, $textBrush, $rectF, $sf)
}

function DrawArrow($graphics, $pen, $x1, $y1, $x2, $y2, $label, $font, $brush) {
    $graphics.DrawLine($pen, $x1, $y1, $x2, $y2)
    if ($label -and $label.Trim().Length -gt 0) {
        $mx = ($x1 + $x2) / 2
        $my = ($y1 + $y2) / 2
        $graphics.DrawString($label, $font, $brush, $mx + 4, $my - 16)
    }
}

# Entities
DrawBox $g $brushEntity $penBlack $fontLabel $brushText 80 150 500 150 "Student"
DrawBox $g $brushEntity $penBlack $fontLabel $brushText 80 390 500 150 "Admissions Officer"
DrawBox $g $brushEntity $penBlack $fontLabel $brushText 80 630 500 150 "Finance Officer"
DrawBox $g $brushEntity $penBlack $fontLabel $brushText 80 870 500 150 "Admin"

# Processes
DrawProcess $g $brushProcess $penBlack $fontLabel $brushText 760 130 760 170 "2.1 Upload Documents"
DrawProcess $g $brushProcess $penBlack $fontLabel $brushText 760 370 760 170 "3.1 Verify Documents"
DrawProcess $g $brushProcess $penBlack $fontLabel $brushText 760 610 760 170 "4.1 Verify Payment"
DrawProcess $g $brushProcess $penBlack $fontLabel $brushText 760 850 760 170 "5.1 Generate Green Card"
DrawProcess $g $brushProcess $penBlack $fontLabel $brushText 760 1090 760 170 "6.1 Notify + Log"

# Data stores
DrawStore $g $brushStore $penBlack $fontLabel $brushText 2200 90 900 140 "D3 Document Uploads"
DrawStore $g $brushStore $penBlack $fontLabel $brushText 2200 280 900 140 "D4 Payment Submissions"
DrawStore $g $brushStore $penBlack $fontLabel $brushText 2200 470 900 140 "D5 Payment Verifications"
DrawStore $g $brushStore $penBlack $fontLabel $brushText 2200 660 900 140 "D7 Green Cards"
DrawStore $g $brushStore $penBlack $fontLabel $brushText 2200 850 900 140 "D8 Notifications"
DrawStore $g $brushStore $penBlack $fontLabel $brushText 2200 1040 900 140 "D9 Audit Logs"
DrawStore $g $brushStore $penBlack $fontLabel $brushText 2200 1230 900 140 "D1 Users"

# Flows
DrawArrow $g $penArrow 580 225 760 215 "documents" $fontSmall $brushSub
DrawArrow $g $penArrow 1520 215 2200 160 "save files" $fontSmall $brushSub
DrawArrow $g $penArrow 1520 215 2200 350 "payment proof" $fontSmall $brushSub

DrawArrow $g $penArrow 580 465 760 455 "review action" $fontSmall $brushSub
DrawArrow $g $penArrow 2200 160 1520 455 "docs" $fontSmall $brushSub
DrawArrow $g $penArrow 1520 455 2200 540 "decision" $fontSmall $brushSub

DrawArrow $g $penArrow 580 705 760 695 "payment decision" $fontSmall $brushSub
DrawArrow $g $penArrow 2200 350 1520 695 "payment data" $fontSmall $brushSub
DrawArrow $g $penArrow 2200 540 1520 695 "admissions status" $fontSmall $brushSub
DrawArrow $g $penArrow 1520 695 2200 540 "finance result" $fontSmall $brushSub

DrawArrow $g $penArrow 2200 540 1520 935 "cleared" $fontSmall $brushSub
DrawArrow $g $penArrow 1520 935 2200 730 "card record" $fontSmall $brushSub
DrawArrow $g $penArrow 1520 1175 2200 920 "notify" $fontSmall $brushSub
DrawArrow $g $penArrow 1520 1175 2200 1110 "audit" $fontSmall $brushSub
DrawArrow $g $penArrow 580 945 760 1175 "admin actions" $fontSmall $brushSub
DrawArrow $g $penArrow 580 945 2200 1300 "manage users" $fontSmall $brushSub

# Save
$bmp.Save($outPath, [System.Drawing.Imaging.ImageFormat]::Png)

# Cleanup
$fontTitle.Dispose()
$fontLabel.Dispose()
$fontSmall.Dispose()
$penBlack.Dispose()
$penArrow.Dispose()
$brushEntity.Dispose()
$brushProcess.Dispose()
$brushStore.Dispose()
$brushText.Dispose()
$brushSub.Dispose()
$g.Dispose()
$bmp.Dispose()

Write-Output "Generated: $outPath"