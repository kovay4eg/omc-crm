#!/usr/bin/env python3
"""Create a safe, uniform A4 preview PDF without modifying the source file."""

from __future__ import annotations

import argparse
import hashlib
from copy import deepcopy
from pathlib import Path

from pypdf import PdfReader, PdfWriter, Transformation
from pypdf._page import PageObject
from pypdf.generic import NameObject, NumberObject, RectangleObject


A4_WIDTH = 595.32
A4_HEIGHT = 841.92


def sha256(path: Path) -> str:
    digest = hashlib.sha256()

    with path.open("rb") as stream:
        for chunk in iter(lambda: stream.read(1024 * 1024), b""):
            digest.update(chunk)

    return digest.hexdigest().upper()


def is_a4_page(page: PageObject) -> bool:
    return (
        page.rotation % 360 == 0
        and abs(float(page.mediabox.left)) < 0.01
        and abs(float(page.mediabox.bottom)) < 0.01
        and abs(float(page.mediabox.width) - A4_WIDTH) < 0.05
        and abs(float(page.mediabox.height) - A4_HEIGHT) < 0.05
    )


def set_a4_boxes(page: PageObject) -> None:
    box = RectangleObject((0, 0, A4_WIDTH, A4_HEIGHT))
    page.mediabox = RectangleObject(box)
    page.cropbox = RectangleObject(box)
    page.trimbox = RectangleObject(box)
    page.bleedbox = RectangleObject(box)
    page.artbox = RectangleObject(box)
    page[NameObject("/Rotate")] = NumberObject(0)


def normalize_page(source_page: PageObject) -> PageObject:
    page = deepcopy(source_page)

    if is_a4_page(page):
        set_a4_boxes(page)
        return page

    if page.rotation % 360:
        rotation_writer = PdfWriter()
        rotation_writer.add_page(page)
        page = rotation_writer.pages[0]
        page.transfer_rotation_to_content()

    source_width = float(page.mediabox.width)
    source_height = float(page.mediabox.height)
    source_left = float(page.mediabox.left)
    source_bottom = float(page.mediabox.bottom)
    scale = min(A4_WIDTH / source_width, A4_HEIGHT / source_height)
    translate_x = (A4_WIDTH - source_width * scale) / 2 - source_left * scale
    translate_y = (A4_HEIGHT - source_height * scale) / 2 - source_bottom * scale

    normalized = PageObject.create_blank_page(width=A4_WIDTH, height=A4_HEIGHT)
    normalized.merge_transformed_page(
        page,
        Transformation().scale(scale).translate(translate_x, translate_y),
        expand=False,
    )
    set_a4_boxes(normalized)

    return normalized


def build_preview(source: Path, output: Path, expected_sha256: str) -> str:
    actual_sha256 = sha256(source)

    if actual_sha256 != expected_sha256.upper():
        raise ValueError(
            f"Unexpected source SHA-256: {actual_sha256}; expected {expected_sha256.upper()}"
        )

    reader = PdfReader(str(source), strict=True)

    if reader.is_encrypted:
        raise ValueError("Encrypted source PDFs are not supported")

    if len(reader.pages) != 10:
        raise ValueError(f"Expected 10 pages, got {len(reader.pages)}")

    writer = PdfWriter()
    writer.add_metadata(
        {
            "/Title": "Статут Полтавського обласного молодіжного центру",
            "/Subject": "Нормалізована A4-копія для перегляду",
            "/SourceSHA256": actual_sha256,
        }
    )

    for source_page in reader.pages:
        page = normalize_page(source_page)
        writer.add_page(page)
        writer.pages[-1].compress_content_streams()

    output.parent.mkdir(parents=True, exist_ok=True)
    temporary = output.with_suffix(output.suffix + ".tmp")

    with temporary.open("wb") as stream:
        writer.write(stream)

    temporary.replace(output)

    return sha256(output)


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("source", type=Path)
    parser.add_argument("output", type=Path)
    parser.add_argument("--expected-sha256", required=True)
    args = parser.parse_args()

    output_sha256 = build_preview(
        args.source.resolve(),
        args.output.resolve(),
        args.expected_sha256,
    )
    print(output_sha256)


if __name__ == "__main__":
    main()
