#!/usr/bin/env python3
"""Verify the normalized statute preview against its official source PDF."""

from __future__ import annotations

import argparse
import hashlib
import re
from pathlib import Path

from pypdf import PdfReader


EXPECTED_SOURCE_SHA256 = (
    "701F31E4C1F3143AD9D406B49CEACB978D9A7D3287D4E2E6CCB7FA55C2E173C4"
)
A4_BOX = (0.0, 0.0, 595.32, 841.92)


def sha256(path: Path) -> str:
    digest = hashlib.sha256()

    with path.open("rb") as stream:
        for chunk in iter(lambda: stream.read(1024 * 1024), b""):
            digest.update(chunk)

    return digest.hexdigest().upper()


def dereference(value):
    return value.get_object() if hasattr(value, "get_object") else value


def normalize_text(value: str | None) -> str:
    return re.sub(r"\s+", " ", value or "").strip()


def verify(source: Path, output: Path) -> dict[str, str | int]:
    source_sha256 = sha256(source)
    output_sha256 = sha256(output)

    if source_sha256 != EXPECTED_SOURCE_SHA256:
        raise AssertionError(f"Unexpected source SHA-256: {source_sha256}")

    source_reader = PdfReader(str(source), strict=True)
    output_reader = PdfReader(str(output), strict=True)

    assert not output_reader.is_encrypted
    assert len(source_reader.pages) == len(output_reader.pages) == 10

    for index, page in enumerate(output_reader.pages, 1):
        assert int(page.get("/UserUnit", 1)) == 1
        assert page.rotation % 360 == 0, (index, page.rotation)

        for name in ("mediabox", "cropbox", "trimbox", "bleedbox", "artbox"):
            box = getattr(page, name)
            actual = tuple(
                map(float, (box.left, box.bottom, box.right, box.top))
            )
            assert all(
                abs(actual_value - expected_value) <= 0.05
                for actual_value, expected_value in zip(actual, A4_BOX)
            ), (index, name, actual)

        assert "/AA" not in page

        for annotation in page.get("/Annots") or []:
            assert dereference(annotation).get("/Subtype") != "/Widget"

    root = dereference(output_reader.trailer["/Root"])
    assert "/OpenAction" not in root
    assert "/AA" not in root
    assert "/AcroForm" not in root
    names = dereference(root.get("/Names"))
    assert not names or "/JavaScript" not in names

    for index in range(1, 9):
        assert normalize_text(
            source_reader.pages[index].extract_text()
        ) == normalize_text(output_reader.pages[index].extract_text()), index + 1

    for index in (0, 9):
        source_images = source_reader.pages[index].images
        output_images = output_reader.pages[index].images
        assert len(source_images) == len(output_images) == 1
        assert hashlib.sha256(source_images[0].data).digest() == hashlib.sha256(
            output_images[0].data
        ).digest(), index + 1

    return {
        "source_sha256": source_sha256,
        "output_sha256": output_sha256,
        "pages": len(output_reader.pages),
        "a4_boxes": "PASS",
        "text_pages_2_9": "PASS",
        "images_pages_1_10": "PASS",
        "active_content": "NONE",
    }


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("source", type=Path)
    parser.add_argument("output", type=Path)
    args = parser.parse_args()

    print(verify(args.source.resolve(), args.output.resolve()))


if __name__ == "__main__":
    main()
