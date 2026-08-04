# PHP Version Comparison — Validation Throughput

200,000 items, seed 20260804, 3 timed repeat(s) per validator after an untimed 1,000-item warmup. Fast validators repeat the pass inside each sample until it clears 0.5s, so a 20ms loop is not timed against scheduler jitter. Same machine, same dataset, PHP 8.2 as the baseline column.

**Every number below is the fastest of the 3 repeats**, normalised to one pass. Machine interference only ever makes a sample slower, so the best sample is the closest available estimate of uncontended throughput — the per-version `RESULTS-*.md` files report medians instead.

Generated 2026-08-04 from `results-*.json`.

---

## What Each Column Ran

| | PHP 8.2 | PHP 8.3 | PHP 8.4 | PHP 8.5 |
|---|---:|---:|---:|---:|
| **PHP** | 8.2.33 | 8.3.33 | 8.4.24 | 8.5.9 |
| `illuminate/validation` | 12.64.0 | 12.64.0 | 12.64.0 | 12.64.0 |
| `respect/validation` | 2.5.0 | 2.5.0 | 2.5.0 | 3.1.2 |
| `symfony/validator` | 7.4.15 | 7.4.15 | 8.1.2 | 8.1.2 |
| `webmozart/assert` | 2.4.1 | 2.4.1 | 2.4.1 | 2.4.1 |

## Throughput (validations/s, best of 3)

| Validator | PHP 8.2 | PHP 8.3 | PHP 8.4 | PHP 8.5 | 8.2 → 8.5 |
|---|---:|---:|---:|---:|---:|
| **Pure PHP** | 11,965,995 | 15,392,710 | 15,852,230 | 13,218,456 | ▲ +10.5% |
| **Value Objects** | 2,641,102 | 2,865,332 | 2,700,065 | 2,864,703 | ▲ +8.5% |
| **webmozart/assert** | 2,418,617 | 1,866,095 | 2,791,524 | 2,611,045 | ▲ +8.0% |
| **filter_var()** | 1,916,796 | 2,238,891 | 2,233,410 | 2,119,483 | ▲ +10.6% |
| **Property Hooks** | n/a | n/a | 1,863,835 | 1,878,512 | n/a |
| **Respect Validation** ⚠️ | 1,465,296 | 1,466,133 | 1,298,221 | 120,836 | ▼ -91.8% |
| **Symfony Attributes** ⚠️ | 60,343 | 51,078 | 59,308 | 53,999 | ▼ -10.5% |
| **Symfony Validator** ⚠️ | 51,471 | 48,943 | 48,777 | 43,604 | ▼ -15.3% |
| **Laravel Validation** | 7,787 | 7,734 | 7,811 | 7,870 | ▲ +1.1% |

Positive = faster on the newer PHP. `n/a` = the approach does not exist on that version.

> ⚠️ **Library version changed across these columns** — the delta is runtime *plus* library, not runtime alone:
>
> - **Respect Validation**: PHP 8.2 → `respect/validation 2.5.0`, PHP 8.5 → `respect/validation 3.1.2`
> - **Symfony Attributes**: PHP 8.2 → `symfony/validator 7.4.15`, PHP 8.4 → `symfony/validator 8.1.2`
> - **Symfony Validator**: PHP 8.2 → `symfony/validator 7.4.15`, PHP 8.4 → `symfony/validator 8.1.2`

---

## Version-to-Version Change (%)

| Validator | 8.2 → 8.3 | 8.3 → 8.4 | 8.4 → 8.5 |
|---|---:|---:|---:|
| **Pure PHP** | ▲ +28.6% | ▲ +3.0% | ▼ -16.6% |
| **Value Objects** | ▲ +8.5% | ▼ -5.8% | ▲ +6.1% |
| **webmozart/assert** | ▼ -22.8% | ▲ +49.6% | ▼ -6.5% |
| **filter_var()** | ▲ +16.8% | ▼ -0.2% | ▼ -5.1% |
| **Property Hooks** | n/a | n/a | ▲ +0.8% |
| **Respect Validation** | ▲ +0.1% | ▼ -11.5% | ▼ -90.7% |
| **Symfony Attributes** | ▼ -15.4% | ▲ +16.1% | ▼ -9.0% |
| **Symfony Validator** | ▼ -4.9% | ▼ -0.3% | ▼ -10.6% |
| **Laravel Validation** | ▼ -0.7% | ▲ +1.0% | ▲ +0.8% |

---

## Noise Floor

Spread between the fastest and slowest repeat, as a percentage of the median. The tables above use the fastest repeat, which sidesteps most of this, but a row with a large spread was contended while it ran — treat small deltas on those rows with suspicion.

| Validator | PHP 8.2 | PHP 8.3 | PHP 8.4 | PHP 8.5 |
|---|---:|---:|---:|---:|
| **Pure PHP** | 105.7% | 69.8% | 75.8% | 57.7% |
| **Value Objects** | 16.4% | 41.1% | 26.7% | 51.0% |
| **webmozart/assert** | 52.4% | 24.4% | 5.3% | 8.9% |
| **filter_var()** | 28.0% | 8.7% | 8.5% | 42.0% |
| **Property Hooks** | n/a | n/a | 6.6% | 38.5% |
| **Respect Validation** | 3.6% | 3.0% | 2.3% | 32.4% |
| **Symfony Attributes** | 1.2% | 5.7% | 1.3% | 7.2% |
| **Symfony Validator** | 18.5% | 10.3% | 15.6% | 12.8% |
| **Laravel Validation** | 8.8% | 4.7% | 31.1% | 0.7% |

## Correctness Cross-Check

Every validator on every PHP version returned the same verdict split: **160,169 valid / 39,831 invalid** — the dataset is seeded, so this is comparable across columns.
