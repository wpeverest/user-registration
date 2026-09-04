import { defineConfig, devices } from "@playwright/test";

/**
 * Playwright configuration for the claudegrill QA suite.
 *
 * The runner (`scripts/run-suite.mjs`) always exports TGQA_BASE_URL,
 * TGQA_ADMIN_USER and TGQA_ADMIN_PASS before invoking this, resolving them from
 * `.themegrill-qa/.env.local`, the environment or CI secrets. Reading them here
 * rather than hardcoding a site is what lets the same specs run locally and in
 * CI without an edit.
 *
 * Tiers are expressed as title tags, not projects, because the runner filters
 * with `--grep`:
 *   @fresh — runs against a clean install; safe to run anywhere
 *   @demo  — needs seeded content (an untagged test counts as @demo)
 */
const baseURL = process.env.TGQA_BASE_URL ?? "http://localhost:8888";

export default defineConfig({
  testDir: "tests/e2e/specs",
  // One worker: these specs mutate global plugin settings (login options,
  // security roles), so parallel workers would race on shared site state.
  workers: 1,
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  timeout: 90_000,
  expect: { timeout: 15_000 },
  reporter: [
    ["list"],
    // The path the manifest declares as json_report; run-suite parses this.
    ["json", { outputFile: "test-results/results.json" }],
  ],
  use: {
    baseURL,
    // Local by Flywheel serves a self-signed certificate on https hosts.
    ignoreHTTPSErrors: true,
    actionTimeout: 20_000,
    navigationTimeout: 30_000,
    trace: "retain-on-failure",
    screenshot: "only-on-failure",
    video: "off",
  },
  projects: [{ name: "chromium", use: { ...devices["Desktop Chrome"] } }],
});
