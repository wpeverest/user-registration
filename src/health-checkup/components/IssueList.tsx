import { Box, Collapse, Flex, useDisclosure } from "@chakra-ui/react";
import { __, _n, sprintf } from "@wordpress/i18n";
import { FiChevronDown } from "react-icons/fi";
import { HealthCheck } from "../api/healthCheckupApi";
import RichText from "./RichText";
import Text from "./Text";

// Leftover issues are secondary on a result screen — the outcome of the delivery
// test is the headline. Collapsed by default so the count is visible without a
// wall of remediation text competing with it.
const IssueList = ({ issues }: { issues: HealthCheck[] }) => {
	const { isOpen, onToggle } = useDisclosure();

	if (issues.length === 0) {
		return null;
	}

	return (
		<Box border="1px solid" borderColor="orange.200" bg="orange.50" borderRadius="8px" mt="18px" overflow="hidden">
			<Flex
				as="button"
				type="button"
				onClick={onToggle}
				width="100%"
				align="center"
				gap="10px"
				p="12px 15px"
				textAlign="left"
				cursor="pointer"
				aria-expanded={isOpen}
			>
				<Text flex="1" fontSize="13px" fontWeight="700" color="orange.700">
					{sprintf(
						/* translators: %d: number of settings still worth fixing */
						_n(
							"There is %d more thing to fix",
							"There are %d more things to fix",
							issues.length,
							"user-registration"
						),
						issues.length
					)}
				</Text>
				<Text fontSize="12px" fontWeight="600" color="orange.700">
					{isOpen ? __("Hide", "user-registration") : __("Show", "user-registration")}
				</Text>
				<Box
					flexShrink={0}
					color="orange.700"
					transform={isOpen ? "rotate(180deg)" : undefined}
					transition="transform 180ms ease"
				>
					<FiChevronDown size={16} />
				</Box>
			</Flex>

			<Collapse in={isOpen} animateOpacity>
				<Box as="ul" listStyleType="disc" pl="32px" pr="15px" pb="14px" m="0">
					{issues.map((issue) => (
						<Box as="li" key={issue.key} mb="7px" _last={{ mb: 0 }} color="orange.700">
							<Text fontSize="12.5px" fontWeight="600" color="orange.700">
								{issue.title}
							</Text>
							<Text fontSize="12px" color="gray.600" mt="1px" lineHeight="1.55">
								<RichText text={issue.fix || issue.message} />
							</Text>
						</Box>
					))}
				</Box>
			</Collapse>
		</Box>
	);
};

export default IssueList;
