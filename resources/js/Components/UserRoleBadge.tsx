import React from 'react';
import { Role } from '@/types';

interface UserRoleBadgeProps {
    role: Role;
}

const UserRoleBadge: React.FC<UserRoleBadgeProps> = ({ role }) => {
    const roleStyles: Record<Role, { text: string; className: string }> = {
        coordenador_master: {
            text: 'Master',
            className: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        },
        coordenador: {
            text: 'Coordenador',
            className: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        },
        colaborador: {
            text: 'Colaborador',
            className: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        },
    };

    const style = roleStyles[role] || roleStyles.colaborador;

    return (
        <span
            className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${style.className}`}>
            {style.text}
        </span>
    );
};

export default UserRoleBadge;
