import { ChakraProvider, extendTheme } from "@chakra-ui/react";
import React from "react";
import { StateProvider } from "../context/StateProvider";
import SetupWizard from "./SetupWizard";

const theme = extendTheme({
	fonts: {
		heading:
			"'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif",
		body: "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif"
	},
	colors: {
		brand: {
			50: "#eef1ff",
			100: "#d4daff",
			200: "#b8c1ff",
			300: "#9ba8ff",
			400: "#7e8fff",
			500: "#475BB2",
			600: "#3A4B9C",
			700: "#2f3da6",
			800: "#252f89",
			900: "#1c246d"
		}
	},
	styles: {
		global: {
			body: {
				bg: "#F8F8FA"
			}
		}
	},
	components: {
		Button: {
			baseStyle: {
				fontWeight: "500",
				borderRadius: "4px"
			}
		},
		Card: {
			baseStyle: {
				container: {
					borderRadius: "8px"
				}
			}
		},
		Radio: {
			baseStyle: {
				control: {
					_checked: {
						bg: "brand.500",
						borderColor: "brand.500",
						_hover: {
							bg: "brand.600",
							borderColor: "brand.600"
						}
					}
				}
			}
		},
		Checkbox: {
			baseStyle: {
				control: {
					_checked: {
						bg: "brand.500",
						borderColor: "brand.500",
						_hover: {
							bg: "brand.600",
							borderColor: "brand.600"
						}
					}
				}
			}
		}
	}
});

const App: React.FC = () => {
	return (
		<StateProvider>
			<ChakraProvider theme={theme}>
				<SetupWizard />
			</ChakraProvider>
		</StateProvider>
	);
};

export default App;
