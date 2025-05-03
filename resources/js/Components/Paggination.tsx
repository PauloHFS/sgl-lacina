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
            <div className="text-base-content/70 text-sm">
                Showing{' '}
                <span className="font-medium">{paginated.from || 0}</span> to{' '}
                <span className="font-medium">{paginated.to || 0}</span> of{' '}
                <span className="font-medium">{paginated.total}</span> results
            </div>

            <div className="join">
                {paginated.links.map((link, i) => {
                    // Skip the "..." entries
                    if (
                        link.label === '&laquo; Previous' ||
                        link.label === 'Next &raquo;'
                    ) {
                        return (
                            <div key={i}>
                                {onPageChange ? (
                                    <button
                                        className={`btn btn-sm join-item ${
                                            !link.url ? 'btn-disabled' : ''
                                        }`}
                                        onClick={() =>
                                            handlePageClick(link.url)
                                        }
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
                                        className="btn btn-sm join-item"
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
                                    <span className="btn btn-sm join-item btn-disabled">
                                        <span
                                            dangerouslySetInnerHTML={{
                                                __html: link.label,
                                            }}
                                        />
                                    </span>
                                )}
                            </div>
                        );
                    }

                    return (
                        <div key={i}>
                            {onPageChange ? (
                                <button
                                    className={`btn btn-sm join-item ${
                                        link.active ? 'btn-active' : ''
                                    } ${!link.url ? 'btn-disabled' : ''}`}
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
                                    className={`btn btn-sm join-item ${
                                        link.active ? 'btn-active' : ''
                                    }`}
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
                                    className={`btn btn-sm join-item btn-disabled ${
                                        link.active ? 'btn-active' : ''
                                    }`}
                                >
                                    <span
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                </span>
                            )}
                        </div>
                    );
                })}
            </div>
        </nav>
    );
}
