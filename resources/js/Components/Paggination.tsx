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
            className="mt-6 flex flex-col items-center gap-4"
        >
            <div className="text-base-content/70 text-sm">
                Mostrando de{' '}
                <span className="font-medium">{paginated.from || 0}</span> a{' '}
                <span className="font-medium">{paginated.to || 0}</span> de{' '}
                <span className="font-medium">{paginated.total}</span>{' '}
                resultados
            </div>

            <div className="join">
                {paginated.links.map((link, i) => {
                    const isDefaultPrevious = link.label === '&laquo; Previous';
                    const isDefaultNext = link.label === 'Next &raquo;';
                    const isKeyPrevious = link.label === 'pagination.previous';
                    const isKeyNext = link.label === 'pagination.next';
                    const isPortugueseAnterior =
                        link.label.toLowerCase() === 'anterior';
                    const isPortugueseProximo =
                        link.label.toLowerCase() === 'próximo';

                    const isPreviousLink =
                        isDefaultPrevious ||
                        isKeyPrevious ||
                        isPortugueseAnterior;
                    const isNextLink =
                        isDefaultNext || isKeyNext || isPortugueseProximo;

                    if (isPreviousLink || isNextLink) {
                        const icon = isPreviousLink ? (
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                strokeWidth={1.5}
                                stroke="currentColor"
                                className="h-4 w-4"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    d="M15.75 19.5L8.25 12l7.5-7.5"
                                />
                            </svg>
                        ) : (
                            // isNextLink
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                strokeWidth={1.5}
                                stroke="currentColor"
                                className="h-4 w-4"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    d="M8.25 4.5l7.5 7.5-7.5 7.5"
                                />
                            </svg>
                        );

                        const ariaLabel = isPreviousLink
                            ? 'Página Anterior'
                            : 'Próxima Página';

                        if (onPageChange) {
                            return (
                                <button
                                    key={link.label + i + '-btn'}
                                    className={`btn btn-sm btn-square join-item ${!link.url ? 'btn-disabled' : 'btn-ghost'}`}
                                    onClick={() => handlePageClick(link.url)}
                                    disabled={!link.url}
                                    aria-label={ariaLabel}
                                >
                                    {icon}
                                </button>
                            );
                        } else if (link.url) {
                            return (
                                <Link
                                    key={link.label + i + '-link'}
                                    href={link.url}
                                    className={`btn btn-sm btn-square join-item btn-ghost`}
                                    preserveScroll={preserveScroll}
                                    preserveState={preserveState}
                                    aria-label={ariaLabel}
                                >
                                    {icon}
                                </Link>
                            );
                        } else {
                            // Disabled button
                            return (
                                <button
                                    key={link.label + i + '-dis'}
                                    className={`btn btn-sm btn-square join-item btn-disabled`}
                                    disabled
                                    aria-label={ariaLabel}
                                >
                                    {icon}
                                </button>
                            );
                        }
                    } else if (link.label === '...') {
                        return (
                            <button
                                key={'ellipsis' + i}
                                className="btn btn-sm join-item btn-disabled"
                                disabled
                            >
                                {link.label}
                            </button>
                        );
                    } else {
                        // Page numbers
                        if (onPageChange) {
                            return (
                                <button
                                    key={link.label + i + '-btn-num'}
                                    className={`btn btn-sm join-item ${link.active ? 'btn-primary' : !link.url ? 'btn-disabled' : 'btn-ghost'}`}
                                    onClick={() => handlePageClick(link.url)}
                                    disabled={!link.url}
                                >
                                    {link.label}
                                </button>
                            );
                        } else if (link.url) {
                            return (
                                <Link
                                    key={link.label + i + '-link-num'}
                                    href={link.url}
                                    className={`btn btn-sm join-item ${link.active ? 'btn-primary' : 'btn-ghost'}`}
                                    preserveScroll={preserveScroll}
                                    preserveState={preserveState}
                                >
                                    {link.label}
                                </Link>
                            );
                        } else {
                            return (
                                <button
                                    key={link.label + i + '-dis-num'}
                                    className="btn btn-sm join-item btn-disabled"
                                    disabled
                                >
                                    {link.label}
                                </button>
                            );
                        }
                    }
                })}
            </div>
        </nav>
    );
}
