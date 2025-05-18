import React from 'react';
import { ShowProps } from '../Show';
import { InfoItem } from './InfoItem';

interface ColaboradorDetalhesProps {
    colaborador: Pick<
        ShowProps['colaborador'],
        | 'area_atuacao'
        | 'tecnologias'
        | 'telefone'
        | 'cpf'
        | 'rg'
        | 'uf_rg'
        | 'conta_bancaria'
        | 'agencia'
        | 'codigo_banco'
        | 'curriculo'
    >;
}

export const ColaboradorDetalhes: React.FC<ColaboradorDetalhesProps> =
    React.memo(({ colaborador }) => (
        <div className="grid grid-cols-1 gap-x-6 gap-y-4 md:grid-cols-2">
            <InfoItem
                label="Área de Atuação"
                value={colaborador.area_atuacao}
            />
            <InfoItem label="Tecnologias">
                <div className="input input-bordered flex h-auto min-h-10 flex-wrap items-center gap-1 py-2 break-words whitespace-normal">
                    {colaborador.tecnologias
                        ? colaborador.tecnologias
                              .split(',')
                              .map((tech, idx) => (
                                  <span
                                      key={idx}
                                      className="badge badge-info badge-outline"
                                  >
                                      {tech.trim()}
                                  </span>
                              ))
                        : '-'}
                </div>
            </InfoItem>
            <InfoItem label="Telefone" value={colaborador.telefone} />
            <InfoItem label="CPF" value={colaborador.cpf} />
            <InfoItem label="RG" value={colaborador.rg} />
            <InfoItem label="UF RG" value={colaborador.uf_rg} />
            <InfoItem
                label="Conta Bancária"
                value={colaborador.conta_bancaria}
            />
            <InfoItem label="Agência" value={colaborador.agencia} />
            <InfoItem
                label="Código do Banco"
                value={colaborador.codigo_banco}
            />
            <InfoItem
                label="Currículo"
                value={colaborador.curriculo}
                className="md:col-span-2"
                isTextArea
            />
        </div>
    ));
ColaboradorDetalhes.displayName = 'ColaboradorDetalhes';
