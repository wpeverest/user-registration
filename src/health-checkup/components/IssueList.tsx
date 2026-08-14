import { Box, Collapse, Flex, useDisclosure } from "@chakra-ui/react";
import { __, _n, sprintf } from "@wordpress/i18n";
import { FiAlertCircle, FiChevronDown } from "react-icons/fi";
import { HealthCheck } from "../api/healthCheckupApi";
import { LEGACY_BUTTON_OPT_OUT } from "../utils/legacyButtonOptOut";
import RichText from "./RichText";
import Text from "./Text";

/**
 * "card"  — a bordered panel. For a result whose findings are the only thing
 *           left to look at, so the panel is the content.
 * "quiet" — a bare line of muted text with a chevron. For the failed screen,
 *           where this is the *alternative* to the recommendation above it and
 *           has to read as the lesser option. A bordered panel there outweighed
 *           the plain-prose recommendation it was supposed to defer to.
 */
type IssueListVariant = "card" | "quiet";

/**
 * A collapsed list of findings, each with its remedy.
 *
 * Used by every result screen, which is why the label is a prop: on a successful
 * send these are optional "suggested improvements"; on a failed one they are the
 * things to fix. Either way they start closed — the outcome of the live test is
 * the headline, and a wall of remediation text underneath competes with it.
 */
const IssueList = ({
	issues,
	label,
	variant = "card",
}: {
	issues: HealthCheck[];
	label?: string;
	variant?: IssueListVariant;
}) => {
	const { isOpen, onToggle } = useDisclosure();

	if (issues.length === 0) {
		return null;
	}

	const heading =
		label ??
		sprintf(
			/* translators: %d: number of optional improvements the scan found */
			_n(
				"%d suggested improvement",
				"%d suggested improvements",
				issues.length,
				"user-registration"
			),
			issues.length
		);

	const chevron = (
		<Box
			flexShrink={0}
			display="flex"
			color="gray.400"
			transform={isOpen ? "rotate(180deg)" : undefined}
			transition="transform 180ms ease"
		>
			<FiChevronDown size={variant === "quiet" ? 15 : 16} />
		</Box>
	);

	const itemContent = (issue: HealthCheck) => (
		<>
			<Text fontSize="12.5px" fontWeight="600" color="gray.700">
				{issue.title}
			</Text>
			<Text fontSize="12.5px" color="gray.600" mt="2px" lineHeight="1.55">
				<RichText text={issue.fix || issue.message} />
			</Text>
		</>
	);

	const items = issues.map((issue, index) => (
		<Box key={issue.key} mt={index === 0 ? 0 : "12px"}>
			{itemContent(issue)}
		</Box>
	));

	if (variant === "quiet") {
		return (
			<Box mt="16px">
				<Flex
					as="button"
					type="button"
					className={LEGACY_BUTTON_OPT_OUT}
					onClick={onToggle}
					align="center"
					gap="6px"
					textAlign="left"
					cursor="pointer"
					aria-expanded={isOpen}
					_hover={{ "& > *": { color: "gray.700" } }}
				>
					<Text fontSize="12px" fontWeight="400" color="gray.500">
						{heading}
					</Text>
					{chevron}
				</Flex>

				<Collapse in={isOpen} animateOpacity>
					{/* Bulleted here, unlike the card variant: these are the several
					    separate things standing between the admin and a delivered
					    email, and a marker each stops them reading as one paragraph. */}
					<Box as="ul" listStyleType="disc" pl="17px" m="0" mt="11px">
						{issues.map((issue, index) => (
							<Box as="li" key={issue.key} mt={index === 0 ? 0 : "10px"} color="gray.400">
								{itemContent(issue)}
							</Box>
						))}
					</Box>
				</Collapse>
			</Box>
		);
	}

	return (
		<Box border="1px solid" borderColor="gray.200" bg="white" borderRadius="9px" mt="16px" overflow="hidden">
			<Flex
				as="button"
				type="button"
				className={LEGACY_BUTTON_OPT_OUT}
				onClick={onToggle}
				width="100%"
				align="center"
				gap="11px"
				p="12px 14px"
				textAlign="left"
				cursor="pointer"
				aria-expanded={isOpen}
				transition="background 140ms ease"
				_hover={{ bg: "gray.50" }}
			>
				<Flex
					flexShrink={0}
					w="26px"
					h="26px"
					borderRadius="full"
					bg="orange.50"
					align="center"
					justify="center"
					color="orange.700"
				>
					<FiAlertCircle size={14} />
				</Flex>

				<Text flex="1" fontSize="13px" fontWeight="600" color="gray.800">
					{heading}
				</Text>

				<Text fontSize="12px" fontWeight="600" color="gray.500" flexShrink={0}>
					{isOpen ? __("Hide", "user-registration") : __("Show", "user-registration")}
				</Text>
				{chevron}
			</Flex>

			<Collapse in={isOpen} animateOpacity>
				{/* Hairline rather than a nested panel: the list belongs to the header
				    above it, and a second border would box content already in a box. */}
				<Box borderTop="1px solid" borderColor="gray.200" p="13px 14px 14px">
					{items}
				</Box>
			</Collapse>
		</Box>
	);
};

export default IssueList;
