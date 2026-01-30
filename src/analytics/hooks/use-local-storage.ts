import { useState } from "react";

type DispatchAction<T> = T | ((prevState: T) => T);

export default function useLocalStorage<T>(key: string, initialValue: T) {
	const [value, setValue] = useState(() => {
		try {
			let data = localStorage.getItem(key);
			return (data ? JSON.parse(data) : undefined) || initialValue;
		} catch {
			return initialValue;
		}
	});

	function handleDispatch(action: DispatchAction<T>) {
		if (typeof action === "function") {
			setValue((prevState) => {
				const newValue = (action as (prevState: T) => T)(prevState);
				localStorage.setItem(key, JSON.stringify(newValue));
				return newValue;
			});
		} else {
			setValue(action);
			localStorage.setItem(key, JSON.stringify(action));
		}
	}

	function clearState() {
		setValue(undefined as T);
		localStorage.removeItem(key);
	}

	return [value as T, handleDispatch, clearState] as const;
}
