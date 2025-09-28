import { router } from '@inertiajs/react';
import { useState, useEffect } from 'react';

// Define a generic type for query parameters
type QueryParams = Record<string, string | number | undefined>;

interface UseTableProps {
    initialState?: QueryParams;
    routeName: string;
    routeParams?: Record<string, any>;
}

export function useTable({
    initialState = {},
    routeName,
    routeParams = {},
}: UseTableProps) {
    const [queryParams, setQueryParams] = useState<QueryParams>(initialState);

    useEffect(() => {
        // On initial load, sync state with URL query params
        const currentParams = new URLSearchParams(window.location.search);
        const newParams: QueryParams = { ...initialState };
        currentParams.forEach((value, key) => {
            newParams[key] = value;
        });
        setQueryParams(newParams);
    }, []);

    const updateQuery = (
        newParams: Partial<QueryParams>,
        options: object = {},
    ) => {
        const updatedParams = { ...queryParams, ...newParams };

        // Reset page on filter change
        if (Object.keys(newParams).some(key => key !== 'page')) {
            delete updatedParams.page;
        }

        setQueryParams(updatedParams);

        router.get(route(routeName, routeParams), updatedParams, {
            preserveState: true,
            preserveScroll: true,
            ...options,
        });
    };

    return {
        queryParams,
        updateQuery,
    };
}
