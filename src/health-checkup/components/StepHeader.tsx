import { Box, Flex } from "@chakra-ui/react";
import { ReactNode } from "react";
import { COLOR, TYPE } from "../tokens";
import Text from "./Text";

/**
 * Every step opens the same way: what this step is, then the detail.
 *
 * No position marker. It lived here as "Step 2 of 5" while the bar above already
 * showed five named, numbered nodes with the current one lit — the same fact
 * twice, and the bar's version is the one that also says what the other steps
 * are.
 */
const StepHeader = ({
	title,
	description,
	icon,
}: {
	title: ReactNode;
	description?: ReactNode;
	/** The result step's status disc; the others have nothing to show here. */
	icon?: ReactNode;
}) => (
	<Box mb="22px">
		<Flex align="center" gap="12px">
			{icon}
			<Text as="h2" fontSize={TYPE.heading} fontWeight="600" letterSpacing="-0.01em" color={COLOR.title}>
				{title}
			</Text>
		</Flex>

		{description && (
			<Text fontSize={TYPE.body} lineHeight="1.62" color={COLOR.body} mt="11px">
				{description}
			</Text>
		)}
	</Box>
);

export default StepHeader;
