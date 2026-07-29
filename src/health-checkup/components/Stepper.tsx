import { Box } from "@chakra-ui/react";
import { FiCheck, FiMail } from "react-icons/fi";

export type WizardStep =
	| "intro"
	| "scan"
	| "test"
	| "result-good"
	| "result-none"
	| "result-spam";

const STEP_INDEX: Record<WizardStep, number> = {
	intro: 0,
	scan: 1,
	test: 3,
	"result-none": 3,
	"result-spam": 3,
	"result-good": 4,
};

const POSITIONS = [0, 25, 50, 75, 100];

const Stepper = ({ step }: { step: WizardStep }) => {
	const current = STEP_INDEX[step];
	const success = step === "result-good";

	return (
		<Box px="15px" pt="6px" pb="22px">
			<Box position="relative" height="2px" bg="gray.200" borderRadius="3px">
				<Box
					position="absolute"
					top="0"
					left="0"
					bottom="0"
					width={`${POSITIONS[current]}%`}
					bg="primary.500"
					borderRadius="3px"
					transition="width 550ms cubic-bezier(.65,0,.35,1)"
				/>
			</Box>
			<Box position="relative" height="30px" mt="-15px">
				{POSITIONS.map((pos, index) => {
					if (index < current) {
						return (
							<Box
								key={pos}
								position="absolute"
								top="50%"
								left={`${pos}%`}
								transform="translate(-50%, -50%)"
								width="16px"
								height="16px"
								borderRadius="50%"
								bg="primary.500"
								color="white"
								display="flex"
								alignItems="center"
								justifyContent="center"
							>
								<FiCheck size={9} />
							</Box>
						);
					}

					if (index === current) {
						return (
							<Box
								key={pos}
								position="absolute"
								top="50%"
								left={`${pos}%`}
								transform="translate(-50%, -50%)"
								width="30px"
								height="30px"
								borderRadius="9px"
								bg={success ? "green.600" : "primary.500"}
								color="white"
								display="flex"
								alignItems="center"
								justifyContent="center"
								boxShadow="0 4px 10px -3px rgba(67, 56, 202, 0.5)"
								transition="background 400ms ease"
							>
								<FiMail size={15} />
							</Box>
						);
					}

					return (
						<Box
							key={pos}
							position="absolute"
							top="50%"
							left={`${pos}%`}
							transform="translate(-50%, -50%)"
							width="9px"
							height="9px"
							borderRadius="50%"
							bg="white"
							border="2px solid"
							borderColor="gray.300"
						/>
					);
				})}
			</Box>
		</Box>
	);
};

export default Stepper;
