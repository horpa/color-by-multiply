# Color by Multiply

Turn a small pixel image into **printable color-by-number style multiplication and division worksheets** for classroom use.

Upload a picture, edit the 10×10 grid in a simple pixel editor, then generate exercises where each colored pixel becomes a math problem based on its row, column, and color.

Available in **Hungarian** and **English**.

## Features

- **Image upload** with optional contrast boost and edge sharpening for clearer 10×10 thumbnails
- **Automatic palette extraction** (1–7 foreground colors; white background for printing)
- **Pixel editor** with color swatches, eraser, click-and-drag painting, and per-color adjustment
- **Exercise generation** in mixed, multiplication-only, or division-only modes
- **Print-ready worksheet** with color legend, exercise list, and solution key

## Requirements

- PHP 8.1 or newer
- PHP extensions: `gd`, `fileinfo`
- A web server (Apache, nginx, IIS, or PHP’s built-in server)

## Quick start

### 1. Clone the repository

```bash
git clone https://github.com/horpa/color-by-multiply.git
cd color-by-multiply
```

### 2. Run with PHP’s built-in server

```bash
composer serve
# or: php -S localhost:8080 -t public
```

Open [http://localhost:8080](http://localhost:8080) in your browser.

### 3. Production deployment

Point your web server **document root** to the `public/` directory.

- `public/` — web-accessible entry point and static assets
- `app/`, `src/`, and `uploads/` must **not** be directly exposed (`.htaccess` files block access on Apache)

Ensure the `uploads/` directory is writable by the web server process.

## Usage

1. Choose a language (HU / EN).
2. Upload an image (JPEG, PNG, WebP, or GIF).
3. Adjust the pixel grid and palette if needed.
4. Select a question type and click **Generate exercises**.
5. Print the worksheet from the browser.

Each non-white pixel produces one exercise. Students find the missing **row** (blue) or **column** (green) value:

- **Multiplication:** `□ × 4 = 12` (find row) or `3 × □ = 12` (find column)
- **Division:** `12 ÷ □ = 4` (find row) or `12 ÷ 3 = □` (find column)

The missing value is shown as a colored write-in box matching the grid labels. The pixel’s color chip helps students locate the matching square on the grid.

## Project structure

```
color-by-multiply/
├── app/                 # Bootstrap, request handling, views, translations
├── public/              # Web root (index.php, CSS, JS)
├── src/
│   ├── Domain/          # Grid, palette, exercise logic
│   └── Infrastructure/  # GD image processing
├── uploads/             # Temporary uploaded images (gitignored)
├── index.php            # Convenience entry when docroot is project root
├── LICENSE
└── README.md
```

## Development

The app uses plain PHP with a lightweight autoloader in `app/bootstrap.php`. No framework or database is required.

Main flow:

1. `public/index.php` bootstraps the app and handles the request.
2. `app/request.php` processes uploads, editor form posts, and session state.
3. `ImageExerciseGenerator` orchestrates domain services for grid and exercise generation.

## Contributing

Contributions are welcome. Please open an issue to discuss larger changes, or send a pull request with a clear description of what you changed and why.

## License

This project is licensed under the [MIT License](LICENSE).
