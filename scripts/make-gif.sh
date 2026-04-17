#!/usr/bin/env bash
# Converts a screen recording (.mov / .mp4) into an optimized demo GIF.
#
# Usage:
#   ./scripts/make-gif.sh input.mov [output.gif]
#
# Output defaults to docs/screenshots/demo.gif.
# Tuned for 1280x720 display at 20fps — good balance of size and quality.

set -euo pipefail

input="${1:?usage: make-gif.sh input.mov [output.gif]}"
output="${2:-docs/screenshots/demo.gif}"
width="${PERISCOPE_GIF_WIDTH:-1280}"
fps="${PERISCOPE_GIF_FPS:-20}"

command -v ffmpeg >/dev/null || { echo 'ffmpeg not found — brew install ffmpeg'; exit 1; }
command -v gifski >/dev/null || { echo 'gifski not found — brew install gifski'; exit 1; }

mkdir -p "$(dirname "$output")"
tmpdir="$(mktemp -d)"
trap 'rm -rf "$tmpdir"' EXIT

echo "→ Extracting frames at ${fps}fps, scaled to ${width}px wide…"
ffmpeg -y -i "$input" \
    -vf "fps=${fps},scale=${width}:-2:flags=lanczos" \
    -loglevel error \
    "${tmpdir}/frame-%04d.png"

echo "→ Encoding GIF with gifski…"
gifski --fps "$fps" --width "$width" --quality 85 \
    -o "$output" "${tmpdir}"/frame-*.png

size=$(du -h "$output" | cut -f1)
frames=$(ls "${tmpdir}" | wc -l | tr -d ' ')
echo "✓ ${output} (${size}, ${frames} frames)"
