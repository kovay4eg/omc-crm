#!/usr/bin/env python3
"""Trace the six approved holiday-logo reference crops as path-only SVG files.

The preferred inputs are the clean PNG card crops retained from the approved
board. The original flattened JPG board is also accepted as a fallback. The
script removes only the light background connected to the outside edge, keeps
the original canvas and converts every visible pixel to VTracer paths.
"""

from __future__ import annotations

import argparse
import json
import re
import sys
from collections import deque
from pathlib import Path

from PIL import Image


THEMES = {
    "new-year": {
        "crop": (27, 94, 408, 311),
        "label": "Новорічний логотип Обласного молодіжного центру",
    },
    "christmas": {
        "crop": (446, 94, 407, 311),
        "label": "Різдвяний логотип Обласного молодіжного центру",
    },
    "easter": {
        "crop": (865, 94, 407, 311),
        "label": "Великодній логотип Обласного молодіжного центру",
    },
    "vyshyvanka": {
        "crop": (27, 508, 408, 285),
        "label": "Логотип Обласного молодіжного центру до Дня вишиванки",
    },
    "independence": {
        "crop": (446, 508, 407, 285),
        "label": "Логотип Обласного молодіжного центру до Дня Незалежності України",
    },
    "youth": {
        "crop": (865, 508, 407, 285),
        "label": "Логотип Обласного молодіжного центру до Дня молоді",
    },
}


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser()
    source = parser.add_mutually_exclusive_group(required=True)
    source.add_argument("--source", type=Path)
    source.add_argument("--source-dir", type=Path)
    parser.add_argument(
        "--out-dir",
        type=Path,
        default=Path("public/images/holiday/logos"),
    )
    parser.add_argument(
        "--artifact-dir",
        type=Path,
        default=Path("storage/app/holiday-reference-crops"),
    )
    parser.add_argument(
        "--vtracer-path",
        type=Path,
        default=Path("storage/app/python-tools"),
    )
    return parser.parse_args()


def import_vtracer(search_path: Path):
    try:
        import vtracer  # type: ignore

        return vtracer
    except ModuleNotFoundError:
        sys.path.insert(0, str(search_path.resolve()))
        import vtracer  # type: ignore

        return vtracer


def crop_box(spec: tuple[int, int, int, int]) -> tuple[int, int, int, int]:
    left, top, width, height = spec
    return left, top, left + width, top + height


def is_light_neutral(pixel: tuple[int, int, int, int]) -> bool:
    red, green, blue, alpha = pixel
    return (
        alpha > 0
        and min(red, green, blue) >= 236
        and max(red, green, blue) - min(red, green, blue) <= 18
    )


def remove_connected_background(source: Image.Image) -> Image.Image:
    rgba = source.convert("RGBA")
    width, height = rgba.size
    transparent = bytearray(width * height)
    pending: deque[tuple[int, int]] = deque()

    def queue_if_background(x: int, y: int) -> None:
        index = y * width + x
        if transparent[index]:
            return
        if is_light_neutral(rgba.getpixel((x, y))):
            transparent[index] = 1
            pending.append((x, y))

    for x in range(width):
        queue_if_background(x, 0)
        queue_if_background(x, height - 1)
    for y in range(height):
        queue_if_background(0, y)
        queue_if_background(width - 1, y)

    while pending:
        x, y = pending.popleft()
        for nx, ny in ((x - 1, y), (x + 1, y), (x, y - 1), (x, y + 1)):
            if 0 <= nx < width and 0 <= ny < height:
                queue_if_background(nx, ny)

    pixels = list(rgba.get_flattened_data())
    for index, clear in enumerate(transparent):
        if clear:
            pixels[index] = (255, 255, 255, 0)
    rgba.putdata(pixels)
    return rgba


def annotate_svg(path: Path, key: str, label: str) -> None:
    markup = path.read_text(encoding="utf-8")
    root_match = re.search(r"<svg\b([^>]*)>", markup, flags=re.IGNORECASE)
    if not root_match:
        raise RuntimeError(f"VTracer did not produce a valid SVG for {key}")

    attributes = root_match.group(1)
    if "viewBox=" not in attributes:
        width_match = re.search(r'\bwidth="([0-9.]+)"', attributes)
        height_match = re.search(r'\bheight="([0-9.]+)"', attributes)
        if not width_match or not height_match:
            raise RuntimeError(f"Cannot derive an SVG viewBox for {key}")
        attributes += (
            f' viewBox="0 0 {width_match.group(1)} {height_match.group(1)}"'
            ' preserveAspectRatio="xMidYMid meet"'
        )
    additions = (
        f' role="img" aria-label="{label}" data-holiday-logo="{key}" '
        'data-source="approved-reference-board"'
    )
    replacement = f"<svg{attributes}{additions}>"
    markup = markup[: root_match.start()] + replacement + markup[root_match.end() :]
    path.write_text(markup, encoding="utf-8", newline="\n")


def main() -> None:
    args = parse_args()
    board = None
    source_directory = None
    if args.source is not None:
        source_path = args.source.resolve()
        if not source_path.is_file():
            raise SystemExit(f"Reference image not found: {source_path}")
        board = Image.open(source_path).convert("RGB")
        if board.width < 1272 or board.height < 793:
            raise SystemExit(f"Unexpected reference size: {board.size}")
    else:
        source_directory = args.source_dir.resolve()
        if not source_directory.is_dir():
            raise SystemExit(f"Reference directory not found: {source_directory}")

    args.out_dir.mkdir(parents=True, exist_ok=True)
    args.artifact_dir.mkdir(parents=True, exist_ok=True)
    tracer = import_vtracer(args.vtracer_path)
    manifest: dict[str, dict[str, object]] = {}

    for key, config in THEMES.items():
        if source_directory is not None:
            input_path = source_directory / f"{key}.png"
            if not input_path.is_file():
                raise RuntimeError(f"Reference crop is missing: {input_path}")
            raw = Image.open(input_path).convert("RGBA")
        else:
            assert board is not None
            raw = board.crop(crop_box(config["crop"]))
        raw_path = args.artifact_dir / f"{key}-reference.png"
        raw.save(raw_path, optimize=True)

        transparent = remove_connected_background(raw)
        transparent_path = args.artifact_dir / f"{key}-transparent.png"
        transparent.save(transparent_path, optimize=True)

        svg_path = args.out_dir / f"{key}.svg"
        svg = tracer.convert_pixels_to_svg(
            list(transparent.get_flattened_data()),
            transparent.size,
            colormode="color",
            hierarchical="stacked",
            mode="none",
            filter_speckle=0,
            color_precision=8,
            layer_difference=2,
            corner_threshold=60,
            length_threshold=3.5,
            max_iterations=10,
            splice_threshold=45,
            path_precision=3,
        )
        svg_path.write_text(svg, encoding="utf-8", newline="\n")
        annotate_svg(svg_path, key, str(config["label"]))

        markup = svg_path.read_text(encoding="utf-8")
        if re.search(r"<image\b|data:image|base64|<text\b", markup, re.IGNORECASE):
            raise RuntimeError(f"Non-vector content detected in {svg_path}")

        manifest[key] = {
            "source_crop": config["crop"],
            "raw_size": raw.size,
            "transparent_size": transparent.size,
            "svg_bytes": svg_path.stat().st_size,
        }

    (args.artifact_dir / "manifest.json").write_text(
        json.dumps(manifest, ensure_ascii=False, indent=2),
        encoding="utf-8",
    )


if __name__ == "__main__":
    main()
