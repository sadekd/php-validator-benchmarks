#!/usr/bin/env bash
set -euo pipefail

IMAGE_NAME="php-validator-benchmarks"
RESULTS_DIR="${1:-$(pwd)/results}"
mkdir -p "$RESULTS_DIR"

# PHP_VERSION (single) still works; PHP_VERSIONS (comma-separated) drives the matrix.
PHP_VERSIONS="${PHP_VERSIONS:-${PHP_VERSION:-8.2,8.3,8.4,8.5}}"
BENCH_COUNT="${BENCH_COUNT:-200000}"
BENCH_REPEATS="${BENCH_REPEATS:-3}"
BENCH_SEED="${BENCH_SEED:-20260804}"

IFS=',' read -ra VERSIONS <<< "$PHP_VERSIONS"

echo "Matrix: ${VERSIONS[*]}"
echo "Dataset: ${BENCH_COUNT} items x ${BENCH_REPEATS} repeat(s), seed ${BENCH_SEED}"
echo ""

for version in "${VERSIONS[@]}"; do
  echo "=============================================="
  echo " PHP ${version}"
  echo "=============================================="
  docker build --build-arg "PHP_VERSION=${version}" -t "${IMAGE_NAME}:${version}" .

  docker run --rm -u "$(id -u):$(id -g)" \
    -e "BENCH_COUNT=${BENCH_COUNT}" \
    -e "BENCH_REPEATS=${BENCH_REPEATS}" \
    -e "BENCH_SEED=${BENCH_SEED}" \
    -v "$RESULTS_DIR":/results "${IMAGE_NAME}:${version}"
  echo ""
done

LAST_VERSION="${VERSIONS[${#VERSIONS[@]}-1]}"

if [ "${#VERSIONS[@]}" -gt 1 ]; then
  echo "Building comparison report..."
  docker run --rm -u "$(id -u):$(id -g)" \
    --entrypoint php \
    -v "$RESULTS_DIR":/results "${IMAGE_NAME}:${LAST_VERSION}" \
    compare.php /results
fi

# RESULTS.md tracks the newest version in the matrix; per-version reports sit alongside it.
if [ -f "$RESULTS_DIR/RESULTS-${LAST_VERSION}.md" ]; then
  cp "$RESULTS_DIR/RESULTS-${LAST_VERSION}.md" "$RESULTS_DIR/RESULTS.md"
fi

echo ""
echo "Results saved to:"
for version in "${VERSIONS[@]}"; do
  echo "  $RESULTS_DIR/RESULTS-${version}.md"
done
if [ "${#VERSIONS[@]}" -gt 1 ]; then
  echo "  $RESULTS_DIR/COMPARISON.md"
fi
echo "  $RESULTS_DIR/RESULTS.md (copy of PHP ${LAST_VERSION})"
