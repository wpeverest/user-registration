import React, { useState } from "react";
import {
	Text,
	Link,
	Modal,
	ModalOverlay,
	ModalContent,
	ModalHeader,
	ModalBody,
	ModalFooter,
	Button,
} from "@chakra-ui/react";

import { useStateValue } from "../../context/StateProvider";
import { actionTypes } from "../../context/gettingStartedContext";

const ConsentModal = (openPopup) => {
	const [{ settings, isPro }, dispatch] = useStateValue();
	const [isPopupOpen, setIsPopupOpen] = useState(openPopup);

	const closeModal = (type) => {
		const newChangedValueRef = { ...settings };
		newChangedValueRef["user_registration_allow_usage_tracking"] =
			type === "allow" ? "yes" : "no";
		dispatch({
			type: actionTypes.GET_SETTINGS,
			settings: newChangedValueRef,
		});
	};

	return (
		<Modal isOpen={isPopupOpen} size="xl" isCentered>
			<ModalOverlay />
			<ModalContent>
				<ModalHeader>Contribute to the enhancement</ModalHeader>
				<ModalBody>
					{isPro ? (
						<Text fontSize="16px">
							Help us improve the plugin's features by sharing{" "}
							<Link
								href="https://docs.wpeverest.com/user-registration/docs/miscellaneous-settings/#1-toc-title"
								isExternal
								color="#475BB2"
							>
								non-sensitive plugin data
							</Link>{" "}
							with us.
						</Text>
					) : (
						<Text fontSize="16px">
							Help us improve the plugin's features and receive an
							instant discount coupon with occasional email
							updates by sharing{" "}
							<Link
								href="https://docs.wpeverest.com/user-registration/docs/miscellaneous-settings/#1-toc-title"
								isExternal
								color="#475BB2"
							>
								non-sensitive plugin data
							</Link>{" "}
							with us.
						</Text>
					)}
				</ModalBody>

				<ModalFooter>
					<Button
						variant="ghost"
						onClick={() => {
							closeModal("disallow");
							setIsPopupOpen(false);
						}}
					>
						No Thanks
					</Button>
					<Button
						colorScheme="blue"
						mr={3}
						onClick={() => {
							closeModal("allow");
							setIsPopupOpen(false);
						}}
					>
						Allow
					</Button>
				</ModalFooter>
			</ModalContent>
		</Modal>
	);
};

export default ConsentModal;
