/**
 * The plugin's legacy admin CSS styles every bare `<button>` inside
 * `.user-registration` (see assets/css/components/_button.scss): a drop shadow,
 * a transition, and — the one that actually breaks layout — `+ button {
 * margin-left: 6px }`, which indents every button that follows a sibling.
 *
 * On a React screen that shows up as a hairline under a row header and as a
 * stack of "cards" where the first is flush left and the rest are 6px in.
 *
 * That rule already exempts `.chakra-button`, so wearing the class is how a
 * React-rendered button opts out. Chakra's own `Button` gets it automatically;
 * anything using `as="button"` on a layout primitive does not, and needs this.
 *
 * Applied via className rather than an inline style because the legacy selector
 * is `.user-registration button:not(.chakra-button)` — specificity (0,2,1),
 * which beats any single emotion class Chakra would generate for a style prop.
 */
export const LEGACY_BUTTON_OPT_OUT = "chakra-button";
