import { expect, type Page } from "@playwright/test";
import { STRONG_PASSWORD, uniqueEmail, uniqueUsername } from "./env";
import { ensurePage, restNonce } from "./wp";

/** The id of the first registration form on the site. */
export async function firstFormId(page: Page): Promise<number> {
  const nonce = await restNonce(page);
  const id = await page.evaluate(async (nonce) => {
    const r = await fetch("/wp-json/wp/v2/user_registration?per_page=1&status=publish", {
      headers: { "X-WP-Nonce": nonce },
      credentials: "same-origin",
    });
    if (r.ok) {
      const j = await r.json();
      if (Array.isArray(j) && j[0]?.id) return j[0].id as number;
    }
    return null;
  }, nonce);
  if (id) return id;

  // The CPT is not always REST-exposed; fall back to the admin list table.
  await page.goto("/wp-admin/edit.php?post_type=user_registration");
  const href = await page
    .locator("#the-list tr[id^='post-']")
    .first()
    .getAttribute("id");
  const parsed = Number((href ?? "").replace("post-", ""));
  if (!parsed) throw new Error("no registration form exists on this site");
  return parsed;
}

/** Publish a page that renders the given form, and return its URL. */
export async function registrationPageFor(page: Page, formId: number) {
  return await ensurePage(page, {
    slug: `qa-registration-${formId}`,
    title: `QA Registration ${formId}`,
    content: `<!-- wp:shortcode -->[user_registration_form id="${formId}"]<!-- /wp:shortcode -->`,
  });
}

export type NewUser = { username: string; email: string; password: string };

/**
 * Fill and submit a registration form on the front end.
 *
 * The form is `form.register` inside `div.ur-frontend-form` — not a form with
 * that class, which is the easy locator to get wrong. Optional fields are only
 * filled when the form actually has them, because the field set is per-form
 * configuration, not a product constant.
 *
 * If the form carries a membership selector the free plan is chosen: a paid
 * plan would divert into a gateway and this helper is for registration, not
 * for payment.
 */
export async function registerOn(page: Page, url: string, user?: Partial<NewUser>): Promise<NewUser> {
  const account: NewUser = {
    username: user?.username ?? uniqueUsername(),
    email: user?.email ?? uniqueEmail(),
    password: user?.password ?? STRONG_PASSWORD,
  };

  await page.goto(url);
  const form = page.locator("div.ur-frontend-form form.register");
  await expect(form).toBeVisible();

  await form.locator("#user_login").fill(account.username);
  await form.locator("#user_email").fill(account.email);
  await form.locator("#user_pass").fill(account.password);

  const confirm = form.locator("#user_confirm_password");
  if (await confirm.count()) await confirm.fill(account.password);
  const confirmEmail = form.locator("#user_confirm_email");
  if (await confirmEmail.count()) await confirmEmail.fill(account.email);

  await selectFreeMembership(form);

  const privacy = form.locator("input[id^='privacy_policy']");
  if (await privacy.count()) await privacy.first().check();

  await form.locator("button[type=submit], input[type=submit]").first().click();
  return account;
}

/**
 * Choose the free plan in a membership field, if one is present.
 *
 * The plan ids are site data, not product data, so they are matched on the
 * rendered price rather than hardcoded — a spec pinned to "membership 71"
 * breaks the first time somebody edits the plans.
 */
async function selectFreeMembership(form: ReturnType<Page["locator"]>) {
  // The membership field is re-rendered by the module's own JS after load, and
  // the rendered control is named `urm_membership` — the server-side markup
  // ships `ur-membership-id`, which is gone by the time a user could click it.
  // Querying the server name finds nothing and silently skips a required field.
  const radios = form.locator("input[name='urm_membership']");
  try {
    await radios.first().waitFor({ state: "attached", timeout: 15_000 });
  } catch {
    return; // this form genuinely has no membership field
  }
  const count = await radios.count();
  if (count === 0) return;

  for (let i = 0; i < count; i++) {
    const radio = radios.nth(i);
    const id = await radio.getAttribute("id");
    const label = form.locator(`label[for="${id}"]`);
    const text = ((await label.count()) ? await label.innerText() : "").toLowerCase();
    if (/\bfree\b/.test(text) || /\$\s*0(\.0+)?\b/.test(text)) {
      await radio.check();
      return;
    }
  }
  // No free plan on this form: take the first so the field validates, and let
  // the caller's assertions decide whether that is acceptable.
  await radios.first().check();
}

/** Log in through the My Account front-end form. */
export async function loginToMyAccount(page: Page, username: string, password: string) {
  await page.goto("/my-account/");
  await page.fill("input[name=username]", username);
  await page.fill("input[name=password]", password);
  await page.locator("button:has-text('Login'), input[type=submit][name=login]").first().click();
  await page.waitForLoadState("domcontentloaded");
}
