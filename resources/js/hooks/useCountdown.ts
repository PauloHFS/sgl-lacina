/* eslint-disable react-you-might-not-need-an-effect/no-event-handler, react-you-might-not-need-an-effect/no-chain-state-updates */
import { useState, useEffect, useCallback } from 'react';

export function useCountdown(initialSeconds: number) {
    const [seconds, setSeconds] = useState(initialSeconds);
    const [isActive, setIsActive] = useState(false);

    useEffect(() => {
        if (isActive && seconds > 0) {
            const interval = setInterval(() => {
                setSeconds((prevSeconds) => prevSeconds - 1);
            }, 1000);

            return () => clearInterval(interval);
        } else if (seconds === 0) {
            setIsActive(false);
        }
    }, [seconds, isActive]);

    const start = useCallback(() => {
        setSeconds(initialSeconds);
        setIsActive(true);
    }, [initialSeconds]);

    const stop = useCallback(() => {
        setIsActive(false);
        setSeconds(0);
    }, []);

    return { seconds, isActive, start, stop };
}
