#!/usr/bin/env bash
set -euo pipefail

IMAGE_NAME="php-validator-benchmarks"
RESULTS_DIR="${1:-$(pwd)}"
PHP_VERSION="${PHP_VERSION:-8.5}"
BENCH_COUNT="${BENCH_COUNT:-1000000}"

echo "Building Docker image (PHP ${PHP_VERSION})..."
docker build --build-arg "PHP_VERSION=${PHP_VERSION}" -t "$IMAGE_NAME" .

echo ""
echo "Running benchmark (${BENCH_COUNT} items)..."
docker run --rm -u "$(id -u):$(id -g)" \
  -e "BENCH_COUNT=${BENCH_COUNT}" \
  -v "$RESULTS_DIR":/results "$IMAGE_NAME"

echo ""
echo "Results saved to:"
echo "  $RESULTS_DIR/RESULTS.md"
