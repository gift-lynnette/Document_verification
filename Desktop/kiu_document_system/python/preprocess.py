from __future__ import annotations

from pathlib import Path
from typing import Optional

import numpy as np


def preprocess_image(path: str, expected_type: str | None = None) -> Optional[str]:
    """Return a temporary preprocessed image path when OpenCV is available."""
    variants = preprocess_image_variants(path, expected_type)
    return variants[0] if variants else None


def preprocess_image_variants(path: str, expected_type: str | None = None) -> list[str]:
    """Return preprocessed image paths, with bank-slip-focused variants when applicable."""
    try:
        import cv2  # type: ignore
    except Exception:
        return []

    source = Path(path)
    image = cv2.imread(str(source))
    if image is None:
        return []

    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
    gray = cv2.fastNlMeansDenoising(gray, h=25)
    base_processed = cv2.adaptiveThreshold(
        gray,
        255,
        cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
        cv2.THRESH_BINARY,
        31,
        11,
    )

    outputs: list[str] = []

    out_path = source.with_name(source.stem + "_ocr_preprocessed.png")
    cv2.imwrite(str(out_path), base_processed)
    outputs.append(str(out_path))

    if expected_type == "bank_slip":
        # Add a high-contrast variant to make handwritten totals clearer.
        clahe = cv2.createCLAHE(clipLimit=2.5, tileGridSize=(8, 8))
        enhanced = clahe.apply(gray)
        bank_processed = cv2.adaptiveThreshold(
            enhanced,
            255,
            cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
            cv2.THRESH_BINARY,
            35,
            8,
        )
        bank_path = source.with_name(source.stem + "_ocr_bank_preprocessed.png")
        cv2.imwrite(str(bank_path), bank_processed)
        outputs.append(str(bank_path))

        # Crop the lower section where TOTAL and amount-in-words are typically placed.
        height, width = bank_processed.shape[:2]
        y0 = int(height * 0.55)
        y1 = int(height * 0.98)
        x0 = int(width * 0.04)
        x1 = int(width * 0.96)
        if y1 > y0 and x1 > x0:
            lower_crop = bank_processed[y0:y1, x0:x1]
            if lower_crop.size > 0:
                crop_path = source.with_name(source.stem + "_ocr_bank_total_crop.png")
                cv2.imwrite(str(crop_path), lower_crop)
                outputs.append(str(crop_path))

    return outputs


def preprocess_handwritten_digit_line_variants(path: str, max_long_edge: int = 3600) -> list[str]:
    """Extra preprocessing for handwritten totals (often blue ink) on slips or tight crops."""
    try:
        import cv2  # type: ignore
    except Exception:
        return []

    source = Path(path)
    image = cv2.imread(str(source))
    if image is None:
        return []

    outputs: list[str] = []

    def _maybe_upscale(img: np.ndarray) -> np.ndarray:
        h, w = img.shape[:2]
        long_edge = max(h, w)
        if long_edge < 650:
            scale = min(6.0, max_long_edge / float(long_edge))
            return cv2.resize(img, None, fx=scale, fy=scale, interpolation=cv2.INTER_CUBIC)
        if long_edge < 1200:
            scale = min(3.5, max_long_edge / float(long_edge))
            return cv2.resize(img, None, fx=scale, fy=scale, interpolation=cv2.INTER_CUBIC)
        return img

    bgr = _maybe_upscale(image)
    gray = cv2.cvtColor(bgr, cv2.COLOR_BGR2GRAY)
    sharpen = cv2.addWeighted(
        gray, 1.25, cv2.GaussianBlur(gray, (0, 0), 3), -0.25, 0
    )

    # Blue-ink handwriting mask (deposit slips often filled in blue).
    hsv = cv2.cvtColor(bgr, cv2.COLOR_BGR2HSV)
    blue_mask = cv2.inRange(hsv, (95, 40, 40), (140, 255, 255))
    blue_mask = cv2.morphologyEx(blue_mask, cv2.MORPH_CLOSE, np.ones((3, 3), np.uint8), iterations=1)
    on_white = np.full(gray.shape, 255, dtype=np.uint8)
    on_white[blue_mask > 0] = sharpen[blue_mask > 0]
    blue_thresh = cv2.adaptiveThreshold(
        on_white, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C, cv2.THRESH_BINARY, 41, 9
    )
    thicken = cv2.dilate(cv2.bitwise_not(blue_thresh), np.ones((2, 2), np.uint8), iterations=1)
    thicken = cv2.bitwise_not(thicken)

    bp = source.with_name(source.stem + "_ocr_digit_blue_thresh.png")
    cv2.imwrite(str(bp), blue_thresh)
    outputs.append(str(bp))

    bp2 = source.with_name(source.stem + "_ocr_digit_blue_thick.png")
    cv2.imwrite(str(bp2), thicken)
    outputs.append(str(bp2))

    # High-contrast gray path (helps when ink is not clearly blue).
    clahe = cv2.createCLAHE(clipLimit=3.0, tileGridSize=(8, 8))
    g2 = clahe.apply(sharpen)
    g_thresh = cv2.adaptiveThreshold(
        g2, 255, cv2.ADAPTIVE_THRESH_MEAN_C, cv2.THRESH_BINARY_INV, 25, 10
    )
    gp = source.with_name(source.stem + "_ocr_digit_gray_thresh.png")
    cv2.imwrite(str(gp), g_thresh)
    outputs.append(str(gp))

    # Inverted (dark ink on bright paper captured as inverted by some scanners).
    inv = cv2.bitwise_not(blue_thresh)
    ip = source.with_name(source.stem + "_ocr_digit_blue_inv.png")
    cv2.imwrite(str(ip), inv)
    outputs.append(str(ip))

    return outputs
