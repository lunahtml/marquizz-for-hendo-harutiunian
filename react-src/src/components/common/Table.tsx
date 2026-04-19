import React from 'react';
//react-src/src/components/common/Table.tsx

interface Column<T> {
    key: keyof T | string;
    header: string;
    render?: (item: T) => React.ReactNode;
}

interface Props<T> {
    columns: Column<T>[];
    data: T[];
    actions?: (item: T) => React.ReactNode;
    onRowClick?: (item: T) => void;
}

function Table<T extends { id: string }>({ columns, data, actions, onRowClick }: Props<T>) {
    return (
        <table className="ss-table">
            <thead>
                <tr>
                    {columns.map(col => (
                        <th key={String(col.key)}>{col.header}</th>
                    ))}
                    {actions && <th>Actions</th>}
                </tr>
            </thead>
            <tbody>
                {data.map(item => (
                    <tr
                        key={item.id}
                        onClick={() => onRowClick?.(item)}
                        style={{ cursor: onRowClick ? 'pointer' : 'default' }}
                    >
                        {columns.map(col => (
                            <td key={String(col.key)}>
                                {col.render
                                    ? col.render(item)
                                    : String(item[col.key as keyof T] ?? '')}
                            </td>
                        ))}
                        {actions && (
                            <td onClick={e => e.stopPropagation()}>
                                {actions(item)}
                            </td>
                        )}
                    </tr>
                ))}
            </tbody>
        </table>
    );
}

export default Table;