import {
	Button,
	Modal,
	ModalBody,
	ModalCloseButton,
	ModalContent,
	ModalFooter,
	ModalHeader,
	ModalOverlay,
	Textarea,
	useToast,
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import { useRef } from "react";
import Text from "./Text";

interface ReportModalProps {
	isOpen: boolean;
	onClose: () => void;
	report: string;
}

const ReportModal = ({ isOpen, onClose, report }: ReportModalProps) => {
	const toast = useToast();
	const textareaRef = useRef<HTMLTextAreaElement>(null);

	const handleCopy = async () => {
		let copied = false;

		// The Clipboard API needs a secure context (https, or the literal
		// hostname "localhost") — plain http:// admin sites like this one
		// don't get it, so `navigator.clipboard` is simply undefined there.
		if (navigator.clipboard && window.isSecureContext) {
			try {
				await navigator.clipboard.writeText(report);
				copied = true;
			} catch (error) {
				copied = false;
			}
		}

		if (!copied && textareaRef.current) {
			textareaRef.current.focus();
			textareaRef.current.select();
			try {
				copied = document.execCommand("copy");
			} catch (error) {
				copied = false;
			}
		}

		toast({
			title: copied
				? __("Copied", "user-registration")
				: __("Couldn't copy automatically — text is selected, press Ctrl/Cmd+C.", "user-registration"),
			status: copied ? "success" : "warning",
			duration: copied ? 1600 : 4000,
			isClosable: true,
		});
	};

	const handleDownload = () => {
		const blob = new Blob([report], { type: "text/plain" });
		const anchor = document.createElement("a");
		anchor.href = URL.createObjectURL(blob);
		anchor.download = "email-health-report.txt";
		anchor.click();
		URL.revokeObjectURL(anchor.href);
	};

	return (
		<Modal isOpen={isOpen} onClose={onClose} isCentered size="lg">
			<ModalOverlay />
			<ModalContent>
				<ModalHeader pb="2px">
					{__("Diagnostic report", "user-registration")}
					<Text fontSize="12.5px" fontWeight="400" color="gray.500" mt="4px">
						{__("Copy this and paste it into an email or support ticket.", "user-registration")}
					</Text>
				</ModalHeader>
				<ModalCloseButton />
				<ModalBody>
					<Textarea
						ref={textareaRef}
						value={report}
						readOnly
						fontFamily="mono"
						fontSize="11.8px"
						lineHeight="1.7"
						height="260px"
						bg="gray.50"
						color="gray.600"
					/>
				</ModalBody>
				<ModalFooter gap="10px">
					<Button colorScheme="primary" fontSize="13.5px" fontWeight="600" onClick={handleCopy}>
						{__("Copy to clipboard", "user-registration")}
					</Button>
					<Button variant="outline" fontSize="13.5px" fontWeight="600" onClick={handleDownload}>
						{__("Download .txt", "user-registration")}
					</Button>
				</ModalFooter>
			</ModalContent>
		</Modal>
	);
};

export default ReportModal;
