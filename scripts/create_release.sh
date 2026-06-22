#!/bin/bash
# create_release.sh
# Usage: ./scripts/create_release.sh v1.9.1 "GeoTracker v1.9.1"
# Requires: gh (GitHub CLI) configured, zip installed

set -euo pipefail
TAG="$1"
TITLE="$2"
VERSION="1.9.1"
PKG_DIR="/tmp/geotracker_pkg_${VERSION}"
ZIP_NAME="geotracker-${VERSION}.zip"

echo "Preparing package in $PKG_DIR"
rm -rf "$PKG_DIR"
mkdir -p "$PKG_DIR"

# Copy module files from source/ to package root
cp -R source/* "$PKG_DIR/"

# Ensure mod_geotracker.xml version is correct
# (This script does not modify XML; ensure you bumped version in source/mod_geotracker.xml before running)

cd /tmp
zip -r "$ZIP_NAME" "$(basename $PKG_DIR)" >/dev/null
mv "$ZIP_NAME" "$(pwd)/$ZIP_NAME"
# Move ZIP to repo root
mv "$ZIP_NAME" "$(git rev-parse --show-toplevel)/$ZIP_NAME"

# Create Git tag and release draft using gh
cd "$(git rev-parse --show-toplevel)"
# Create annotated tag
git tag -a "$TAG" -m "Release $TAG"
git push origin "$TAG"

# Create a draft release and upload zip
gh release create "$TAG" "$ZIP_NAME" --title "$TITLE" --notes-file RELEASE_NOTES_v1.9.1.md --draft

echo "Created draft release $TAG and uploaded $ZIP_NAME"
