import { Link } from '@inertiajs/react';

export interface Paginated<T> {
    data: Array<T>;
    current_page: number;
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}
interface PaginationProps<T> {
    paginated: Paginated<T>;
    onPageChange?: (page: number) => void;
    preserveScroll?: boolean;
    preserveState?: boolean;
}

export default function Pagination<T>({
    paginated,
    onPageChange,
    preserveScroll = true,
    preserveState = true,
}: PaginationProps<T>) {
    if (paginated.last_page <= 1 || paginated.data.length === 0) {
        return null;
    }

    const extractPageNumber = (url: string | null): number | null => {
        if (!url) return null;
        const pageMatch = url.match(/[?&]page=(\d+)/);
        return pageMatch && pageMatch[1] ? parseInt(pageMatch[1], 10) : null;
    };

    const handlePageClick = (url: string | null) => {
        if (!onPageChange) return;

        const page = extractPageNumber(url);
        if (page !== null) {
            onPageChange(page);
        }
    };

    return (
        <nav
            aria-label="Pagination"
            className="mt-6 flex items-center justify-between"
        >
            <div className="text-sm text-gray-700">
                Showing{' '}
                <span className="font-medium">{paginated.from || 0}</span> to{' '}
                <span className="font-medium">{paginated.to || 0}</span> of{' '}
                <span className="font-medium">{paginated.total}</span> results
            </div>

            <ul className="flex space-x-1">
                {paginated.links.map((link, i) => {
                    const baseClasses = link.active
                        ? 'bg-blue-600 text-white'
                        : 'bg-white text-gray-700 hover:bg-gray-100';

                    const disabledClasses = !link.url
                        ? 'opacity-50 cursor-not-allowed'
                        : '';

                    return (
                        <li key={i}>
                            {onPageChange ? (
                                <button
                                    className={`rounded px-4 py-2 text-sm ${baseClasses} ${disabledClasses}`}
                                    onClick={() => handlePageClick(link.url)}
                                    disabled={!link.url}
                                >
                                    <span
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                </button>
                            ) : link.url ? (
                                <Link
                                    href={link.url}
                                    className={`rounded px-4 py-2 text-sm ${baseClasses}`}
                                    preserveScroll={preserveScroll}
                                    preserveState={preserveState}
                                >
                                    <span
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                </Link>
                            ) : (
                                <span
                                    className={`rounded px-4 py-2 text-sm ${baseClasses} ${disabledClasses}`}
                                >
                                    <span
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                </span>
                            )}
                        </li>
                    );
                })}
            </ul>
        </nav>
    );
}
