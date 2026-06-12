Add-Type -AssemblyName System.Drawing

$width = 3200
$height = 1700
$outPath = "c:\xampp\htdocs\research\report_assets\functional_workflow_diagram.png"

$bmp = New-Object System.Drawing.Bitmap($width, $height)
$g = [System.Drawing.Graphics]::FromImage($bmp)
$g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
$g.Clear([System.Drawing.Color]::White)

$fontTitle = New-Object System.Drawing.Font("Segoe UI", 38, [System.Drawing.FontStyle]::Bold)
$fontStep = New-Object System.Drawing.Font("Segoe UI", 24, [System.Drawing.FontStyle]::Regular)
$fontSmall = New-Object System.Drawing.Font("Segoe UI", 18, [System.Drawing.FontStyle]::Regular)
$fontDecision = New-Object System.Drawing.Font("Segoe UI", 20, [System.Drawing.FontStyle]::Regular)

$penMain = New-Object System.Drawing.Pen([System.Drawing.Color]::Black, 5)
$penArrow = New-Object System.Drawing.Pen([System.Drawing.Color]::Black, 5)
$penArrow.CustomEndCap = New-Object System.Drawing.Drawing2D.AdjustableArrowCap(14, 16)
$brushText = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::Black)
$brushWhite = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::White)

function DrawStep($graphics, $pen, $brush, $font, $textBrush, $x, $y, $w, $h, $text) {
    $rect = New-Object System.Drawing.Rectangle($x, $y, $w, $h)
    $rectF = New-Object System.Drawing.RectangleF([float]$x, [float]$y, [float]$w, [float]$h)
    $graphics.FillRectangle($brush, $rect)
    $graphics.DrawRectangle($pen, $rect)
    $sf = New-Object System.Drawing.StringFormat
    $sf.Alignment = [System.Drawing.StringAlignment]::Center
    $sf.LineAlignment = [System.Drawing.StringAlignment]::Center
    $graphics.DrawString($text, $font, $textBrush, $rectF, $sf)
}

function DrawTerminator($graphics, $pen, $brush, $font, $textBrush, $x, $y, $w, $h, $text) {
    $path = New-Object System.Drawing.Drawing2D.GraphicsPath
    $r = 40
    $path.AddArc($x, $y, $r, $r, 180, 90)
    $path.AddArc($x+$w-$r, $y, $r, $r, 270, 90)
    $path.AddArc($x+$w-$r, $y+$h-$r, $r, $r, 0, 90)
    $path.AddArc($x, $y+$h-$r, $r, $r, 90, 90)
    $path.CloseFigure()
    $graphics.FillPath($brush, $path)
    $graphics.DrawPath($pen, $path)

    $rectF = New-Object System.Drawing.RectangleF([float]$x, [float]$y, [float]$w, [float]$h)
    $sf = New-Object System.Drawing.StringFormat
    $sf.Alignment = [System.Drawing.StringAlignment]::Center
    $sf.LineAlignment = [System.Drawing.StringAlignment]::Center
    $graphics.DrawString($text, $font, $textBrush, $rectF, $sf)
}

function DrawDecision($graphics, $pen, $brush, $font, $textBrush, $cx, $cy, $w, $h, $text) {
    $halfW = [int]($w / 2)
    $halfH = [int]($h / 2)
    [System.Drawing.Point[]]$points = @(
        (New-Object System.Drawing.Point([int]$cx, [int]($cy - $halfH))),
        (New-Object System.Drawing.Point([int]($cx + $halfW), [int]$cy)),
        (New-Object System.Drawing.Point([int]$cx, [int]($cy + $halfH))),
        (New-Object System.Drawing.Point([int]($cx - $halfW), [int]$cy))
    )
    $graphics.FillPolygon($brush, $points)
    $graphics.DrawPolygon($pen, $points)

    $rectF = New-Object System.Drawing.RectangleF([float]($cx - $halfW + 20), [float]($cy - $halfH + 20), [float]($w - 40), [float]($h - 40))
    $sf = New-Object System.Drawing.StringFormat
    $sf.Alignment = [System.Drawing.StringAlignment]::Center
    $sf.LineAlignment = [System.Drawing.StringAlignment]::Center
    $graphics.DrawString($text, $font, $textBrush, $rectF, $sf)
}

function DrawArrow($graphics, $pen, $x1, $y1, $x2, $y2, $label, $font, $textBrush) {
    $graphics.DrawLine($pen, $x1, $y1, $x2, $y2)
    if ($label -and $label.Trim().Length -gt 0) {
        $mx = ($x1 + $x2) / 2
        $my = ($y1 + $y2) / 2
        $graphics.DrawString($label, $font, $textBrush, $mx + 10, $my - 30)
    }
}

$g.DrawString("Functional Workflow Diagram", $fontTitle, $brushText, 60, 30)

# Main flow blocks
DrawTerminator $g $penMain $brushWhite $fontStep $brushText 80 250 420 130 "Start"
DrawStep $g $penMain $brushWhite $fontStep $brushText 620 250 560 130 "Student Submission"
DrawStep $g $penMain $brushWhite $fontStep $brushText 1300 250 560 130 "Admissions Review"
DrawDecision $g $penMain $brushWhite $fontDecision $brushText 2040 315 520 220 "Documents Approved?"
DrawStep $g $penMain $brushWhite $fontStep $brushText 2360 80 700 130 "Request Resubmission"
DrawStep $g $penMain $brushWhite $fontStep $brushText 2360 250 700 130 "Finance Clearance"
DrawDecision $g $penMain $brushWhite $fontDecision $brushText 2720 575 520 220 "Payment Cleared?"
DrawStep $g $penMain $brushWhite $fontStep $brushText 2360 760 700 130 "Notify Pending/Rejected"
DrawStep $g $penMain $brushWhite $fontStep $brushText 2360 1080 700 130 "Card Generation"
DrawStep $g $penMain $brushWhite $fontStep $brushText 2360 1320 700 130 "Card Verification"
DrawTerminator $g $penMain $brushWhite $fontStep $brushText 2360 1520 700 130 "End"

# Feedback path node
DrawStep $g $penMain $brushWhite $fontStep $brushText 1300 760 560 130 "Resubmission"

# Main arrows
DrawArrow $g $penArrow 500 315 620 315 "" $fontSmall $brushText
DrawArrow $g $penArrow 1180 315 1300 315 "" $fontSmall $brushText
DrawArrow $g $penArrow 1860 315 1780 315 "" $fontSmall $brushText
DrawArrow $g $penArrow 2300 315 2360 315 "Yes" $fontSmall $brushText

# Admissions rejection/resubmission branch
DrawArrow $g $penArrow 2040 205 2040 145 "No" $fontSmall $brushText
DrawArrow $g $penArrow 2040 145 2360 145 "" $fontSmall $brushText
DrawArrow $g $penArrow 2360 145 1580 760 "" $fontSmall $brushText
DrawArrow $g $penArrow 1580 760 1580 380 "" $fontSmall $brushText

# Finance to decision
DrawArrow $g $penArrow 2710 380 2710 465 "" $fontSmall $brushText

# Payment not cleared branch
DrawArrow $g $penArrow 2720 685 2720 760 "No" $fontSmall $brushText
DrawArrow $g $penArrow 2360 825 1580 825 "" $fontSmall $brushText
DrawArrow $g $penArrow 1580 825 1580 380 "" $fontSmall $brushText

# Payment cleared branch
DrawArrow $g $penArrow 2720 685 2720 1080 "Yes" $fontSmall $brushText
DrawArrow $g $penArrow 2710 1210 2710 1320 "" $fontSmall $brushText
DrawArrow $g $penArrow 2710 1450 2710 1520 "" $fontSmall $brushText

# Save
$bmp.Save($outPath, [System.Drawing.Imaging.ImageFormat]::Png)

# Cleanup
$fontTitle.Dispose()
$fontStep.Dispose()
$fontSmall.Dispose()
$fontDecision.Dispose()
$penMain.Dispose()
$penArrow.Dispose()
$brushText.Dispose()
$brushWhite.Dispose()
$g.Dispose()
$bmp.Dispose()

Write-Output "Generated: $outPath"