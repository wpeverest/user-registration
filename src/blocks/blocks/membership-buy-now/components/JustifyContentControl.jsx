import {
	Tooltip,
	ToolbarButton,
	ButtonGroup,
	Icon
} from "@wordpress/components";
import { alignLeft, alignCenter, alignRight } from "@wordpress/icons";
import { __ } from "@wordpress/i18n";

export default function JustifyControl({ value, onChange }) {
	const options = [
		{ label: "Left", val: "flex-start", icon: alignLeft },
		{ label: "Center", val: "center", icon: alignCenter },
		{ label: "Right", val: "flex-end", icon: alignRight }
	];

	return (
		<div style={{ display: "flex", flexDirection: "column", gap: "6px" }}>
			<div
				style={{ fontWeight: 500, fontSize: "11px" }}
				className="components-input-control__label"
			>
				{__("JUSTIFICATION", "user-registration")}
			</div>

			<ButtonGroup
				style={{
					display: "flex",
					alignItems: "center",
					flexDirection: "row"
				}}
			>
				{options.map((opt) => (
					<Tooltip key={opt.val} text={opt.label}>
						<ToolbarButton
							isPressed={value === opt.val}
							onClick={() => onChange(opt.val)}
						>
							<Icon icon={opt.icon} />
						</ToolbarButton>
					</Tooltip>
				))}
			</ButtonGroup>
		</div>
	);
}
