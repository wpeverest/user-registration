import { expect, type Page } from "@playwright/test";
import { ADMIN_PASS, ADMIN_USER } from "./env";

/**
 * Log in to wp-admin.
 *
 * WordPress periodically interrupts the first admin login with the
 * "confirm your administration email" screen. It is not an error and it does
 * not appear every time, so it cannot be asserted on — it just has to be
 * stepped past, or every spec becomes flaky on a schedule nobody controls.
 */
export async function loginAsAdmin(page: Page, user = ADMIN_USER, pass = ADMIN_PASS) {
  await page.goto("/wp-login.php");
  await page.fill("#user_login", user);
  await page.fill("#user_pass", pass);
  await page.click("#wp-submit");
  await page.waitForLoadState("domcontentloaded");

  if (page.url().includes("confirm_admin_email")) {
    const later = page.locator("a.admin-email__later, a:has-text('Remind me later')");
    if (await later.count()) await later.first().click();
    else await page.goto("/wp-admin/");
    await page.waitForLoadState("domcontentloaded");
  }
  await expect(page.locator("#wpadminbar")).toBeVisible();
}

/** Open a User Registration admin screen by its `page=` slug. */
export async function gotoAdminPage(page: Page, slug: string, extra = "") {
  await page.goto(`/wp-admin/admin.php?page=${slug}${extra}`);
  await page.waitForLoadState("domcontentloaded");
}

/**
 * Read an option through the REST API using the logged-in admin's own nonce.
 *
 * Asserting on the stored option rather than on a success notice is the whole
 * point: the notice can appear while the write silently failed, which is
 * exactly the class of bug this product's history is full of.
 */
export async function restNonce(page: Page): Promise<string> {
  await page.goto("/wp-admin/options-general.php");
  const nonce = await page.evaluate(
    () => (window as any).wpApiSettings?.nonce ?? null,
  );
  if (!nonce) throw new Error("could not read the REST nonce from wp-admin");
  return nonce;
}

/** Create or update a page by slug, returning its permalink. */
export async function ensurePage(
  page: Page,
  { slug, title, content }: { slug: string; title: string; content: string },
): Promise<string> {
  const nonce = await restNonce(page);
  return await page.evaluate(
    async ({ slug, title, content, nonce }) => {
      const headers = { "Content-Type": "application/json", "X-WP-Nonce": nonce };
      const found = await fetch(
        `/wp-json/wp/v2/pages?slug=${encodeURIComponent(slug)}&status=publish,draft`,
        { headers, credentials: "same-origin" },
      ).then((r) => r.json());
      const body = JSON.stringify({ title, slug, status: "publish", content });
      const res = await fetch(
        found?.[0]?.id ? `/wp-json/wp/v2/pages/${found[0].id}` : "/wp-json/wp/v2/pages",
        { method: "POST", headers, credentials: "same-origin", body },
      );
      const json = await res.json();
      if (!res.ok) throw new Error(`page write failed: ${res.status} ${JSON.stringify(json).slice(0, 200)}`);
      return json.link as string;
    },
    { slug, title, content, nonce },
  );
}

/**
 * Delete a user the suite created, by email, using the admin's REST nonce.
 *
 * Every registration-based spec creates a real account on the site under test.
 * Without this the site accumulates one user per test per run, and — because
 * membership subscription rows outlive the user they belong to (see
 * knowledge.md, "Observed while porting the suite") — the plans those users
 * joined slowly become undeletable. Cleaning up is not optional here.
 *
 * Matching is on the exact email, which is generated on the `@example.test`
 * domain, so this can never select an account a person actually uses.
 * Failures are swallowed: teardown must never turn a passing test red.
 */
export async function deleteUserByEmail(page: Page, email: string): Promise<void> {
  if (!email.endsWith("@example.test")) return;
  try {
    const nonce = await restNonce(page);
    await page.evaluate(
      async ({ email, nonce }) => {
        const headers = { "X-WP-Nonce": nonce };
        const found = await fetch(
          `/wp-json/wp/v2/users?search=${encodeURIComponent(email)}&context=edit&per_page=20`,
          { headers, credentials: "same-origin" },
        ).then((r) => (r.ok ? r.json() : []));
        for (const u of Array.isArray(found) ? found : []) {
          if (u?.email !== email) continue;
          await fetch(`/wp-json/wp/v2/users/${u.id}?force=true&reassign=false`, {
            method: "DELETE",
            headers,
            credentials: "same-origin",
          });
        }
      },
      { email, nonce },
    );
  } catch {
    /* teardown is best-effort */
  }
}
