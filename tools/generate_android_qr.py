from __future__ import annotations

import argparse
from pathlib import Path

import qrcode
import qrcode.image.svg
from PIL import Image, ImageChops, ImageDraw, ImageFont


DEFAULT_URL = "https://omc.pl.ua/download/android"


def _font(size: int, *, bold: bool = False) -> ImageFont.FreeTypeFont:
    font_name = "arialbd.ttf" if bold else "arial.ttf"
    candidates = [
        Path("C:/Windows/Fonts") / font_name,
        Path("/System/Library/Fonts/Supplemental") / ("Arial Bold.ttf" if bold else "Arial.ttf"),
        Path("/usr/share/fonts/truetype/dejavu") / ("DejaVuSans-Bold.ttf" if bold else "DejaVuSans.ttf"),
    ]
    for candidate in candidates:
        if candidate.exists():
            return ImageFont.truetype(str(candidate), size)
    return ImageFont.load_default(size=size)


def _trim_white(image: Image.Image) -> Image.Image:
    rgb = image.convert("RGB")
    background = Image.new("RGB", rgb.size, "white")
    difference = ImageChops.difference(rgb, background).convert("L")
    difference = difference.point(lambda value: 255 if value > 16 else 0)
    bounds = difference.getbbox()
    return rgb.crop(bounds) if bounds else rgb


def build_qr(url: str, logo_path: Path) -> tuple[Image.Image, qrcode.QRCode]:
    qr = qrcode.QRCode(
        version=None,
        error_correction=qrcode.constants.ERROR_CORRECT_H,
        box_size=24,
        border=4,
    )
    qr.add_data(url)
    qr.make(fit=True)
    image = qr.make_image(fill_color="#0B1020", back_color="white").convert("RGB")

    logo = _trim_white(Image.open(logo_path))
    max_logo = int(image.width * 0.17)
    logo.thumbnail((max_logo, max_logo), Image.Resampling.LANCZOS)

    padding = max(16, image.width // 60)
    card_size = max(logo.width, logo.height) + padding * 2
    card = Image.new("RGB", (card_size, card_size), "white")
    card.paste(logo, ((card_size - logo.width) // 2, (card_size - logo.height) // 2))
    image.paste(card, ((image.width - card_size) // 2, (image.height - card_size) // 2))
    return image, qr


def build_poster(qr_image: Image.Image, url: str) -> Image.Image:
    width, height = 1400, 1750
    canvas = Image.new("RGB", (width, height), "#F6F7FF")
    draw = ImageDraw.Draw(canvas)

    draw.rounded_rectangle((70, 70, width - 70, height - 70), 64, fill="white", outline="#DDE1FF", width=4)
    draw.rounded_rectangle((70, 70, width - 70, 270), 64, fill="#2538E5")
    draw.rectangle((70, 205, width - 70, 270), fill="#2538E5")

    title = "ЗАВАНТАЖИТИ ЗАСТОСУНОК"
    subtitle = "ОМЦ CRM · ANDROID"
    title_font = _font(54, bold=True)
    subtitle_font = _font(34, bold=True)
    body_font = _font(30)
    small_font = _font(24)

    title_box = draw.textbbox((0, 0), title, font=title_font)
    draw.text(((width - (title_box[2] - title_box[0])) / 2, 103), title, font=title_font, fill="white")
    subtitle_box = draw.textbbox((0, 0), subtitle, font=subtitle_font)
    draw.text(((width - (subtitle_box[2] - subtitle_box[0])) / 2, 183), subtitle, font=subtitle_font, fill="#DCE2FF")

    qr_size = 1030
    qr_resized = qr_image.resize((qr_size, qr_size), Image.Resampling.NEAREST)
    canvas.paste(qr_resized, ((width - qr_size) // 2, 320))

    instruction = "Відскануйте камерою телефона"
    instruction_box = draw.textbbox((0, 0), instruction, font=body_font)
    draw.text(((width - (instruction_box[2] - instruction_box[0])) / 2, 1405), instruction, font=body_font, fill="#151823")

    url_box = draw.textbbox((0, 0), url, font=small_font)
    draw.text(((width - (url_box[2] - url_box[0])) / 2, 1470), url, font=small_font, fill="#626A84")
    draw.text((width / 2, 1580), "Постійне посилання · QR змінювати не потрібно", font=small_font, fill="#7B8098", anchor="mm")
    return canvas


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--url", default=DEFAULT_URL)
    parser.add_argument("--logo", type=Path, required=True)
    parser.add_argument("--output-dir", type=Path, required=True)
    args = parser.parse_args()

    args.output_dir.mkdir(parents=True, exist_ok=True)
    qr_image, qr = build_qr(args.url, args.logo)
    qr_image.save(args.output_dir / "omc-android-download-qr-square.png", optimize=True)
    build_poster(qr_image, args.url).save(
        args.output_dir / "omc-android-download-qr.png",
        optimize=True,
    )

    svg = qr.make_image(image_factory=qrcode.image.svg.SvgPathImage)
    svg.save(args.output_dir / "omc-android-download-qr.svg")


if __name__ == "__main__":
    main()
