# 🏎️ PHP 8.4 Validation Benchmark

> Benchmarking **9 validation approaches** across **200,000 product items** on PHP 8.4.24

## 📋 Test Setup

| | |
|---|---|
| 🐘 **PHP Version** | 8.4.24 (Alpine) |
| 📦 **Data Set** | 200,000 product objects (~80% valid, ~20% invalid), seeded |
| 🏗️ **Object** | `Product { sku, price, quantity, active }` |
| 🔁 **Sampling** | 3 timed repeat(s) after an untimed 1,000-item warmup; each sample repeats the pass until it clears 0.5s; median reported |
| 🐳 **Runtime** | Docker (`php:8.4-cli-alpine`) |
| 📅 **Date** | 2026-08-04 |

Resolved libraries on this PHP version:

| Package | Version |
|---|---|
| `illuminate/validation` | 12.64.0 |
| `respect/validation` | 2.5.0 |
| `symfony/validator` | 8.1.2 |
| `webmozart/assert` | 2.4.1 |

---

## 🏆 Results — Sorted by Speed

| Rank | Validator | Median time | Validations/s | Spread | vs Baseline |
|:---:|---|---:|---:|---:|---:|
| 🥇 | **Pure PHP** | 0.009s | 21,718,375/s | 42.7% | 1.0x |
| 🥈 | **Value Objects** | 0.033s | 6,044,451/s | 1.3% | 3.6x slower |
| 🥉 | **webmozart/assert** | 0.037s | 5,373,944/s | 4.2% | 4.0x slower |
| 4️⃣ | **filter_var()** | 0.041s | 4,850,306/s | 2.4% | 4.5x slower |
| 5️⃣ | **Property Hooks 🆕** | 0.053s | 3,756,543/s | 3.3% | 5.8x slower |
| 6️⃣ | **Respect Validation** | 0.076s | 2,641,055/s | 7.5% | 8.2x slower |
| 7️⃣ | **Symfony Attributes** | 1.806s | 110,729/s | 3.7% | 196.1x slower |
| 8️⃣ | **Symfony Validator** | 2.101s | 95,179/s | 2.2% | 228.2x slower |
| 9️⃣ | **Laravel Validation** | 12.017s | 16,642/s | 3.3% | 1305.0x slower |

---

## 📊 Speed Comparison (validations/second)

```
Pure PHP           ██████████████████████████████████████████████████ 21,718,375/s  🥇
Value Objects      ██████████████         6,044,451/s  🥈
webmozart/assert   ████████████               5,373,944/s  🥉
filter_var()       ███████████                  4,850,306/s  4️⃣
Property Hooks     █████████                        3,756,543/s  5️⃣
Respect Validation ██████                                 2,641,055/s  6️⃣
Symfony Attributes ▏                                                110,729/s  7️⃣
Symfony Validator  ▏                                                95,179/s  8️⃣
Laravel Validation ▏                                                16,642/s  9️⃣
```

## ⏱️ Execution Time (seconds, median)

```
Pure PHP           ▏                                                  0.009s  ⚡
Value Objects      ▏                                                  0.033s  
webmozart/assert   ▏                                                  0.037s  
filter_var()       ▏                                                  0.041s  
Property Hooks     ▏                                                  0.053s  
Respect Validation ▏                                                  0.076s  
Symfony Attributes ████████                             1.806s  
Symfony Validator  █████████                          2.101s  
Laravel Validation ██████████████████████████████████████████████████  12.017s  🐌
```

## 🧠 Memory Usage

| Validator | Peak Memory | Delta from Baseline |
|---|---:|---:|
| Pure PHP | 38.00 MB | — |
| Value Objects | 38.00 MB | — |
| webmozart/assert | 38.00 MB | — |
| filter_var() | 38.00 MB | — |
| Property Hooks | 38.00 MB | — |
| Respect Validation | 42.00 MB | +4.00 MB |
| Symfony Attributes | 38.00 MB | — |
| Symfony Validator | 38.00 MB | — |
| Laravel Validation | 42.00 MB | +4.00 MB |

> 💡 Memory differences are minimal — all approaches fit comfortably within a few MB of each other.

---

## 🔬 Tiers Analysis

### ⚡ Tier 1 — Native Speed (≥ 1M validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Pure PHP** | 21,718,375/s | Direct `if` checks on typed properties | Baseline — no allocation, no calls |
| **Value Objects** | 6,044,451/s | Constructor validation + `try/catch` | Object allocation + exception path on failure |
| **webmozart/assert** | 5,373,944/s | Static assertion methods + `try/catch` | Static call overhead + exception path |
| **filter_var()** | 4,850,306/s | PHP built-in C filter functions | Redundant type checks on already-typed props |
| **Property Hooks** | 3,756,543/s | PHP 8.4+ `set` hooks with validation | Hook dispatch per property write |
| **Respect Validation** | 2,641,055/s | Rule chain evaluated per field | Result objects built per rule evaluation |

### 🚶 Tier 2 — Library Speed (100k – 1M validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Symfony Attributes** | 110,729/s | Attribute-mapped constraints on an object | Constraint tree walk + metadata lookup |

### 🐢 Tier 3 — Framework Speed (< 100k validations/s)

| Approach | Validations/s | Strategy | Cost per item |
|---|---:|---|---|
| **Symfony Validator** | 95,179/s | `Collection` constraint over an array | Constraint tree walk + array conversion |
| **Laravel Validation** | 16,642/s | New `Validator` per `make()` call | Validator + translator construction per item |

---

## 💡 Key Takeaways

1. 🏆 **Pure PHP is ~1305x faster than Laravel Validation** — simple `if` checks on typed properties are unbeatable
2. **Property Hooks** — 3,756,543/s (5.8x vs Pure PHP): Hook dispatch per property write
3. **webmozart/assert** — 5,373,944/s (4.0x vs Pure PHP): Static call overhead + exception path
4. **filter_var()** — 4,850,306/s (4.5x vs Pure PHP): Redundant type checks on already-typed props
5. ✅ **All 9 approaches produce identical results** — correctness verified (160,169 valid / 39,831 invalid)

---

## 🚀 Run It Yourself

```bash
# Full matrix (PHP 8.2 - 8.5) + comparison report
./run.sh

# Single PHP version
PHP_VERSIONS=8.5 ./run.sh

# Custom output directory, dataset size, repeats
./run.sh /path/to/results
BENCH_COUNT=50000 BENCH_REPEATS=5 ./run.sh

# Local PHP (>= 8.2)
composer update
php benchmark.php
```

Per-version reports land in `RESULTS-<version>.md`, raw numbers in `results-<version>.json`, and the cross-version diff in `COMPARISON.md`.
