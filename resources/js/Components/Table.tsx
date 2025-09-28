import React from 'react';
import { Paginated } from './Paggination';

// Generic row data type
export type RowData = Record<string, any>;

export interface ColumnDefinition<T extends RowData> {
    header: string;
    accessor: keyof T;
    render?: (row: T) => React.ReactNode;
}

interface TableProps<T extends RowData> {
    data: Paginated<T>;
    columns: ColumnDefinition<T>[];
    emptyMessage?: string;
    onRowClick?: (row: T) => void;
}

export function Table<T extends RowData>({
    data,
    columns,
    emptyMessage = 'Nenhum item encontrado.',
    onRowClick,
}: TableProps<T>) {
    const handleRowClick = (row: T) => {
        if (onRowClick) {
            onRowClick(row);
        }
    };

    return (
        <div className="overflow-x-auto">
            <table className="table-zebra table">
                <thead>
                    <tr>
                        {columns.map((column) => (
                            <th key={String(column.accessor)}>{column.header}</th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {data.data.length > 0 ? (
                        data.data.map((row) => (
                            <tr
                                key={row.id}
                                onClick={() => handleRowClick(row)}
                                className={onRowClick ? 'hover:bg-base-200 cursor-pointer' : ''}
                            >
                                {columns.map((column) => (
                                    <td key={String(column.accessor)}>
                                        {column.render
                                            ? column.render(row)
                                            : row[column.accessor]}
                                    </td>
                                ))}
                            </tr>
                        ))
                    ) : (
                        <tr>
                            <td
                                colSpan={columns.length}
                                className="text-base-content/60 text-center"
                            >
                                {emptyMessage}
                            </td>
                        </tr>
                    )}
                </tbody>
            </table>
        </div>
    );
}
