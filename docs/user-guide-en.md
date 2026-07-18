# Color by Multiply — User Guide (English)

This guide explains how to create printable multiplication and division worksheets from a small pixel image. It is written for teachers working with children around ages 6–7.

## Introduction

**Color by Multiply** turns a picture into a 10×10 color-by-number grid. Each colored square on the grid becomes a math exercise based on its **row**, **column**, and **color**.

Students use the grid to find row and column numbers, then solve short multiplication or division problems. The worksheet is ready to print, including a color legend, exercise list, and answer key.

The app is available in **English** and **Hungarian**.

## Getting started

1. Open the Color by Multiply website in your browser.
2. At the top of the page, choose your language:
   - **English**
   - **Magyar** (Hungarian)
3. The rest of the page (buttons, labels, and messages) will appear in the language you selected.

You can switch languages at any time using the language dropdown. The page reloads in the new language.

## Upload an image

1. Click **Choose image** and select a picture from your computer.
2. Optional processing options (both are on by default and recommended):
   - **Boost contrast** — makes colors clearer on the small 10×10 grid.
   - **Sharpen edges** — helps separate shapes when the image is scaled down.
3. Click **Load image and open editor**.

### Supported file types

- JPEG
- PNG
- WebP
- GIF

### Tips for a good result

- Simple, bold shapes work best (faces, icons, letters, simple objects).
- Avoid very detailed photos; fine details are lost on a 10×10 grid.
- Strong color differences between areas help the app pick a clean palette.

## Pixel editor

After upload, the **Pixel editor** opens with your image converted to a 10×10 grid.

### Toolbar

- **Background: white** — white squares are not part of the exercises. They stay empty on the worksheet.
- **Eraser (white)** — click to select the eraser, then paint squares white again.
- **Add color** — add another color to the palette (up to 7 colors in total).

### Color palette

Each color in the palette has:

- A **color swatch** — click to select that color for painting.
- A **Change** button — opens the color picker to adjust that palette color.

When you change a palette color, every painted square using that color updates automatically.

### Painting on the grid

1. Click a color swatch to select it.
2. Click squares on the grid to paint them.
3. Click and drag (or touch and drag on a tablet) to paint several squares quickly.

Paint at least one non-white square before generating exercises.

## Understanding the grid

The worksheet grid uses two number lines:

| Color | Meaning |
|-------|---------|
| **Blue numbers** (left side) | **Row** numbers (1–10) |
| **Green numbers** (top) | **Column** numbers (1–10) |
| **White squares** | Background — no exercise |

To locate a colored square: read the **blue row number** on its row and the **green column number** above its column.

Example: a red square in row 3 and column 4 sits where row 3 and column 4 meet.

## Generating exercises

At the bottom of the pixel editor:

1. Choose a **Question type**:
   - **Mixed** — alternates multiplication and division exercises.
   - **Multiplication only** — every exercise is multiplication.
   - **Division only** — every exercise is division.
2. Click **Generate exercises from edited grid**.

Each **non-white pixel** produces one exercise. If the grid has no colored pixels, the app shows an error and asks you to add at least one.

## How exercises work

Exercises ask students to find a missing **row** or **column** number — not the final product.

The missing value is shown as a **write-in box**:

- **Light blue box with dark blue outline** — write the **row** number.
- **Light green box with dark green outline** — write the **column** number.

Known row numbers appear in **blue text**. Known column numbers appear in **green text**.

### Multiplication examples

| What students see | What they find |
|-------------------|----------------|
| `[blue box] × 4 = 12` | Row number (3) |
| `3 × [green box] = 12` | Column number (4) |

### Division examples

| What students see | What they find |
|-------------------|----------------|
| `12 ÷ [blue box] = 4` | Row number (3) |
| `12 ÷ 3 = [green box]` | Column number (4) |

### Color chip

Each exercise ends with a small **color chip** (colored square + number). This matches the color legend on the worksheet and tells students **which square on the grid** the exercise belongs to.

Students can:

1. Find the matching color on the grid using the chip.
2. Read the row (blue) and column (green) for that square.
3. Use those numbers to solve the equation.

Exercises are listed in three columns with space between them for easy reading and writing.

## Printing

1. After generating exercises, scroll to the **Worksheet** section.
2. Click **Print preview** to open your browser’s print dialog.
3. The printout includes:
   - **Page 1 — Worksheet:** the 10×10 grid, color legend, and exercises (without answers).
   - **Page 2 — Solutions:** a preview image and the full answer key for the teacher.

Use your browser’s print settings to choose paper size, margins, and whether to print both pages.

The on-screen **Print preview** button is hidden on the printed page itself.

## Tips and troubleshooting

### General tips

- Start with a simple image and adjust colors in the editor if needed.
- You can use up to **7 foreground colors** plus white background.
- Fewer colors often produce clearer worksheets for young children.
- Mixed question type gives variety; use multiplication-only or division-only when focusing on one skill.

### Common messages

| Message | What to do |
|---------|------------|
| *Add at least one colored pixel before generating exercises.* | Paint at least one square with a palette color (not white). |
| *Please upload a valid image file.* | Choose a file and submit the upload form again. |
| *The uploaded file is not a valid image.* | Use a standard JPEG, PNG, WebP, or GIF file. |
| *The file could not be stored.* | Try again; if the problem continues, contact whoever hosts the website. |

### Add color button is disabled

The palette holds a maximum of **7 colors**. When you already have 7 colors, **Add color** is grayed out. Remove a color by not using it, or edit existing colors with **Change** instead.

---

*For technical setup and installation, see the main [README](../README.md). For the Hungarian guide, see [Felhasználói útmutató (HU)](user-guide-hu.md). For students, see [Student guide (EN)](student-guide-en.md).*
