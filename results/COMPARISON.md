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
| **Pure PHP** | 32,355,096 | 25,947,801 | 22,656,377 | 28,011,365 | ▼ -13.4% |
| **Value Objects** | 6,714,664 | 6,423,112 | 6,070,185 | 6,705,790 | ▼ -0.1% |
| **webmozart/assert** | 5,769,638 | 5,799,384 | 5,546,954 | 5,716,856 | ▼ -0.9% |
| **filter_var()** | 5,055,959 | 4,962,378 | 4,857,466 | 4,754,652 | ▼ -6.0% |
| **Property Hooks** | n/a | n/a | 3,815,751 | 4,076,376 | n/a |
| **Respect Validation** ⚠️ | 2,747,395 | 2,758,997 | 2,794,367 | 244,009 | ▼ -91.1% |
| **Symfony Attributes** ⚠️ | 125,159 | 126,059 | 112,673 | 115,029 | ▼ -8.1% |
| **Symfony Validator** ⚠️ | 112,186 | 106,442 | 95,928 | 99,823 | ▼ -11.0% |
| **Laravel Validation** | 16,395 | 16,414 | 16,667 | 16,790 | ▲ +2.4% |

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
| **Pure PHP** | ▼ -19.8% | ▼ -12.7% | ▲ +23.6% |
| **Value Objects** | ▼ -4.3% | ▼ -5.5% | ▲ +10.5% |
| **webmozart/assert** | ▲ +0.5% | ▼ -4.4% | ▲ +3.1% |
| **filter_var()** | ▼ -1.9% | ▼ -2.1% | ▼ -2.1% |
| **Property Hooks** | n/a | n/a | ▲ +6.8% |
| **Respect Validation** | ▲ +0.4% | ▲ +1.3% | ▼ -91.3% |
| **Symfony Attributes** | ▲ +0.7% | ▼ -10.6% | ▲ +2.1% |
| **Symfony Validator** | ▼ -5.1% | ▼ -9.9% | ▲ +4.1% |
| **Laravel Validation** | ▲ +0.1% | ▲ +1.5% | ▲ +0.7% |

---

## Noise Floor

Spread between the fastest and slowest repeat, as a percentage of the median. The tables above use the fastest repeat, which sidesteps most of this, but a row with a large spread was contended while it ran — treat small deltas on those rows with suspicion.

| Validator | PHP 8.2 | PHP 8.3 | PHP 8.4 | PHP 8.5 |
|---|---:|---:|---:|---:|
| **Pure PHP** | 180.0% | 30.8% | 42.7% | 29.9% |
| **Value Objects** | 4.0% | 3.4% | 1.3% | 3.6% |
| **webmozart/assert** | 7.2% | 7.2% | 4.2% | 4.5% |
| **filter_var()** | 6.1% | 4.3% | 2.4% | 11.9% |
| **Property Hooks** | n/a | n/a | 3.3% | 5.7% |
| **Respect Validation** | 4.1% | 5.3% | 7.5% | 3.3% |
| **Symfony Attributes** | 4.5% | 32.1% | 3.7% | 10.1% |
| **Symfony Validator** | 4.0% | 6.5% | 2.2% | 1.9% |
| **Laravel Validation** | 10.1% | 3.0% | 3.3% | 6.5% |

## Correctness Cross-Check

Every validator on every PHP version returned the same verdict split: **160,169 valid / 39,831 invalid** — the dataset is seeded, so this is comparable across columns.
