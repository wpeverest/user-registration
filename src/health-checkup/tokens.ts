/**
 * The setup wizard's in-card type scale and colours, in one place.
 *
 * Read off src/welcome/components/steps/* and SetupWizard.tsx rather than
 * invented: this screen sits on the wizard's shell, so its content has to use
 * the wizard's sizes too. Before this the checkup ran three sizes of its own
 * (12.5 / 13 / 13.5px) against the wizard's 14px, which read as a different
 * product the moment the two were seen together.
 *
 * Named by role, not by size, so a later change to the wizard is one edit here.
 */
export const TYPE = {
	/** Step heading — wizard: fontSize 21px, fontWeight 600. */
	heading: "21px",
	/** A heading inside the card body. */
	subheading: "16px",
	/** Everything readable: descriptions, findings, remedies. */
	body: "14px",
	/** Secondary notes that must not compete with body copy. */
	small: "13px",
	/** Status badges and the like. */
	badge: "11px",
} as const;

export const COLOR = {
	/** Titles and anything emphasised — wizard: gray.800. */
	title: "gray.800",
	/** Body copy — wizard: gray.600. */
	body: "gray.600",
	/** Asides and last-resort notes — wizard's skip link: #999999. */
	muted: "gray.500",
	/** Links and the active step — wizard: #475BB2. */
	link: "#475BB2",
} as const;
