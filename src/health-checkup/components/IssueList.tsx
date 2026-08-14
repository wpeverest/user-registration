import { Box, Collapse, Flex, useDisclosure } from "@chakra-ui/react";
import { __, _n, sprintf } from "@wordpress/i18n";
import { FiAlertCircle, FiChevronDown } from "react-icons/fi";
import { HealthCheck } from "../api/healthCheckupApi";
import { COLOR, TYPE } from "../tokens";
import { LEGACY_BUTTON_OPT_OUT } from "../utils/legacyButtonOptOut";
import RichText from "./RichText";
import Text from "./Text";

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
	defaultOpen = true,
}: {
	issues: HealthCheck[];
	label?: string;
	/**
	 * Open unless told otherwise. Where the message did arrive these findings are
	 * the only thing left to read, so hiding them would be hiding the point. The
	 * failed screen closes them instead: it leads with a recommendation, and a
	 * list of alternatives unfurled beneath it competes with that.
	 */
	defaultOpen?: boolean;
}) => {
	const { isOpen, onToggle } = useDisclosure({ defaultIsOpen: defaultOpen });

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
			<FiChevronDown size={16} />
		</Box>
	);

	const itemContent = (issue: HealthCheck) => (
		<>
			<Text fontSize={TYPE.body} fontWeight="600" color={COLOR.title}>
				{issue.title}
			</Text>
			<Text fontSize={TYPE.body} color={COLOR.body} mt="3px" lineHeight="1.6">
				<RichText text={issue.fix || issue.message} />
			</Text>
		</>
	);

	const items = issues.map((issue, index) => (
		<Box key={issue.key} mt={index === 0 ? 0 : "12px"}>
			{itemContent(issue)}
		</Box>
	));

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

				<Text flex="1" fontSize={TYPE.body} fontWeight="600" color={COLOR.title}>
					{heading}
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
