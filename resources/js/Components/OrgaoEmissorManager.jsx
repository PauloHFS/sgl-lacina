import React, { useState } from 'react';
import { useForm } from '@inertiajs/react';
import InputLabel from './InputLabel';
import TextInput from './TextInput';
import InputError from './InputError';
import PrimaryButton from './PrimaryButton';
import SecondaryButton from './SecondaryButton';

// Modal Component
const Modal = ({ show, onClose, children }) => {
    if (!show) {
        return null;
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto">
            <div className="fixed inset-0 bg-black opacity-50" onClick={onClose}></div>
            <div className="relative w-full max-w-lg p-8 mx-auto bg-base-100 rounded-lg shadow-lg">
                {children}
            </div>
        </div>
    );
};

export default function OrgaoEmissorManager({ orgaos }) {
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [editingOrgao, setEditingOrgao] = useState(null);

    const { data, setData, post, put, delete: destroy, errors, reset, processing } = useForm({
        id: '',
        nome: '',
        sigla: '',
    });

    const openModal = (orgao = null) => {
        if (orgao) {
            setEditingOrgao(orgao);
            setData({ id: orgao.id, nome: orgao.nome, sigla: orgao.sigla });
        } else {
            setEditingOrgao(null);
            reset();
        }
        setIsModalOpen(true);
    };

    const closeModal = () => {
        setIsModalOpen(false);
        setEditingOrgao(null);
        reset();
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        if (editingOrgao) {
            put(route('configuracoes.orgaos-emissores.update', editingOrgao.id), {
                onSuccess: () => closeModal(),
            });
        } else {
            post(route('configuracoes.orgaos-emissores.store'), {
                onSuccess: () => closeModal(),
            });
        }
    };

    const handleDelete = (orgao) => {
        if (confirm(`Tem certeza que deseja excluir o órgão "${orgao.nome}"?`)) {
            destroy(route('configuracoes.orgaos-emissores.destroy', orgao.id));
        }
    };

    return (
        <div className="p-4 sm:p-8 bg-base-100 shadow sm:rounded-lg">
            <section>
                <header className="flex justify-between items-center">
                    <div>
                        <h2 className="text-lg font-medium text-base-content">Órgãos Emissores de RG</h2>
                        <p className="mt-1 text-sm text-base-content/70">
                            Gerencie os órgãos emissores que podem ser selecionados pelos usuários.
                        </p>
                    </div>
                    <PrimaryButton onClick={() => openModal()}>Adicionar Novo</PrimaryButton>
                </header>

                <div className="mt-6 space-y-4">
                    <div className="overflow-x-auto">
                        <table className="table w-full">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Sigla</th>
                                    <th className="w-24">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                {orgaos.map((orgao) => (
                                    <tr key={orgao.id}>
                                        <td>{orgao.nome}</td>
                                        <td>{orgao.sigla}</td>
                                        <td className="flex space-x-2">
                                            <button onClick={() => openModal(orgao)} className="btn btn-xs btn-outline btn-info">Editar</button>
                                            <button onClick={() => handleDelete(orgao)} className="btn btn-xs btn-outline btn-error">Excluir</button>
                                        </td>
                                    </tr>
                                ))}
                                {orgaos.length === 0 && (
                                    <tr>
                                        <td colSpan="3" className="text-center">Nenhum órgão emissor cadastrado.</td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                <Modal show={isModalOpen} onClose={closeModal}>
                    <form onSubmit={handleSubmit} className="p-6">
                        <h2 className="text-lg font-medium text-base-content">
                            {editingOrgao ? 'Editar Órgão Emissor' : 'Adicionar Novo Órgão Emissor'}
                        </h2>

                        <div className="mt-6">
                            <InputLabel htmlFor="nome" value="Nome" />
                            <TextInput
                                id="nome"
                                className="mt-1 block w-full"
                                value={data.nome}
                                onChange={(e) => setData('nome', e.target.value)}
                                required
                                isFocused
                                autoComplete="off"
                            />
                            <InputError className="mt-2" message={errors.nome} />
                        </div>

                        <div className="mt-4">
                            <InputLabel htmlFor="sigla" value="Sigla" />
                            <TextInput
                                id="sigla"
                                className="mt-1 block w-full"
                                value={data.sigla}
                                onChange={(e) => setData('sigla', e.target.value)}
                                required
                                autoComplete="off"
                            />
                            <InputError className="mt-2" message={errors.sigla} />
                        </div>

                        <div className="mt-6 flex justify-end">
                            <SecondaryButton onClick={closeModal}>Cancelar</SecondaryButton>
                            <PrimaryButton className="ml-3" disabled={processing}>
                                {processing ? 'Salvando...' : 'Salvar'}
                            </PrimaryButton>
                        </div>
                    </form>
                </Modal>
            </section>
        </div>
    );
}
