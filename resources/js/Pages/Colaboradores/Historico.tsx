import { Paginated } from '@/Components/Paggination';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { PageProps, User, UsuarioProjeto } from '@/types';

interface ShowPageProps extends PageProps {
    colaborador: User;
    historico: Paginated<UsuarioProjeto>;
}

export default function HistoricoPage({
    colaborador,
    historico,
}: ShowPageProps) {
    return (
        <Authenticated
            header={
                <div className="flex items-center justify-between">
                    <h1 className="text-base-content text-xl leading-tight font-semibold">
                        Histórico de Participação - {colaborador.name}
                    </h1>
                    <button className="btn" onClick={() => history.back()}>
                        Voltar
                    </button>
                </div>
            }
        >
            <div className="container mx-auto p-6">
                <div className="mb-6">
                    <div className="flex flex-col gap-2">
                        <div className="font-semibold">{colaborador.name}</div>
                        <div className="text-base-content/70 text-sm">
                            {colaborador.email}
                        </div>
                        <div className="flex gap-2">
                            {colaborador.area_atuacao?.map((area) => (
                                <span
                                    key={area}
                                    className="badge badge-soft badge-info"
                                >
                                    {area}
                                </span>
                            ))}
                        </div>
                    </div>
                </div>
                <div className="overflow-x-auto">
                    <table className="table-zebra table">
                        <thead>
                            <tr>
                                <th>Projeto</th>
                                <th>Função</th>
                                <th>Tipo de Vínculo</th>
                                <th>Status</th>
                                <th>Bolsa</th>
                                <th>Carga Horária</th>
                                <th>Data Início</th>
                                <th>Data Fim</th>
                            </tr>
                        </thead>
                        <tbody>
                            {historico.data.length === 0 ? (
                                <tr>
                                    <td colSpan={7} className="text-center">
                                        Nenhum histórico encontrado.
                                    </td>
                                </tr>
                            ) : (
                                historico.data.map((item) => (
                                    <tr key={item.id}>
                                        <td>{item.projeto?.nome ?? '-'}</td>
                                        <td>{item.funcao}</td>
                                        <td>{item.tipo_vinculo}</td>
                                        <td>
                                            <span className="badge badge-outline">
                                                {item.status}
                                            </span>
                                        </td>
                                        <td>
                                            {item.valor_bolsa ? (
                                                <span className="badge badge-success">
                                                    {(
                                                        item.valor_bolsa / 100
                                                    ).toLocaleString('pt-BR', {
                                                        style: 'currency',
                                                        currency: 'BRL',
                                                    })}
                                                </span>
                                            ) : (
                                                <span className="badge badge-secondary">
                                                    ---
                                                </span>
                                            )}
                                        </td>
                                        <td>{item.carga_horaria}h</td>
                                        <td>
                                            {item.data_inicio
                                                ? new Date(
                                                      item.data_inicio,
                                                  ).toLocaleDateString('pt-BR')
                                                : '-'}
                                        </td>
                                        <td>
                                            {item.data_fim
                                                ? new Date(
                                                      item.data_fim,
                                                  ).toLocaleDateString('pt-BR')
                                                : '-'}
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
                {/* Paginação, se necessário */}
                {/* <Pagination ... /> */}
            </div>
        </Authenticated>
    );
}
