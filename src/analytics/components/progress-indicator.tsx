import { Progress } from './ui/progress';
import { useIsFetching } from '@tanstack/react-query';
import { useEffect, useRef, useState } from 'react';

export const ProgressIndicator = () => {
	const isFetching = useIsFetching({
		queryKey: ['overview', 'forms', 'preferences'],
	});
	const [width, setWidth] = useState(0);
	const intervalRef = useRef<NodeJS.Timeout | null>(null);
	const timeoutRef = useRef<NodeJS.Timeout | null>(null);

	useEffect(() => {
		if (intervalRef.current) clearInterval(intervalRef.current);
		if (timeoutRef.current) clearTimeout(timeoutRef.current);
		if (isFetching) {
			setWidth(20);
			document.body.dataset.wait = 'true';
			intervalRef.current = setInterval(() => {
				setWidth((prevWidth) => {
					if (prevWidth >= 90) {
						if (intervalRef.current) clearInterval(intervalRef.current);
						return 90;
					}
					return prevWidth + 10;
				});
			}, 500);
		} else {
			setWidth(100);
			timeoutRef.current = setTimeout(() => {
				setWidth(0);
			}, 200);
			document.body.removeAttribute('data-wait');
		}

		return () => {
			if (intervalRef.current) clearInterval(intervalRef.current);
			if (timeoutRef.current) clearTimeout(timeoutRef.current);
			document.body.removeAttribute('data-wait');
		};
	}, [isFetching]);

	if (width === 0) return null;

	return (
		<div className="UR-Progress-Indicator-Container">
			<Progress value={width} className="UR-Progress-Indicator-Progress" />
		</div>
	);
};

ProgressIndicator.displayName = 'ProgressIndicator';
