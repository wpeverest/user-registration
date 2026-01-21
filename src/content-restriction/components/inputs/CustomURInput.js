export function CustomURIInput({ value, onChange }) {
	const [uri = "", regex = false] = Array.isArray(value)
		? value
		: ["", false];
	return (
		<div
			className="urcr-custom-uri-input"
			style={{ display: "flex", width: "100%", gap: 8 }}
		>
			<input
				type="text"
				className="components-text-control__input urcr-condition-value-input urcr-condition-value-date"
				value={uri}
				onChange={(e) => onChange([e.target.value, regex])}
				style={{ flex: 1 }}
			/>
		</div>
	);
}
