<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PcaExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $ppps;

    public function __construct($ppps)
    {
        $this->ppps = $ppps;
    }

    public function collection()
    {
        return $this->ppps;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nome do Item/Serviço',
            'Descrição',
            'Justificativa',
            'Valor Estimado (R$)',
            'Origem do Recurso',
            'Mês Início Prestação', // NOVO
            'Data Ideal',
            'Responsável',
            'Setor',
            'Status',
            'Data de Criação'
        ];
    }

    public function map($ppp): array
    {
        return [
            $ppp->id,
            $ppp->nome_item,
            $ppp->descricao_item,
            $ppp->justificativa_item,
            'R$ ' . number_format($ppp->valor_estimado, 2, ',', '.'),
            $ppp->origem_recurso,
            $ppp->mes_inicio_prestacao ? ([
                '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março',
                '04' => 'Abril', '05' => 'Maio', '06' => 'Junho',
                '07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro',
                '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
            ][$ppp->mes_inicio_prestacao] ?? $ppp->mes_inicio_prestacao) . ' de 2026' : '', // NOVO
            $ppp->data_ideal ? $ppp->data_ideal->format('d/m/Y') : '',
            $ppp->user->name ?? '',
            $ppp->user->department ?? '',
            $ppp->status->nome ?? '',
            $ppp->created_at->format('d/m/Y H:i')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo do cabeçalho
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4472C4']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
            // Bordas para toda a tabela
            'A1:K' . ($this->ppps->count() + 1) => [
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                ]
            ]
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // ID
            'B' => 30,  // Nome
            'C' => 40,  // Descrição
            'D' => 40,  // Justificativa
            'E' => 15,  // Valor
            'F' => 15,  // Origem
            'G' => 12,  // Data
            'H' => 20,  // Responsável
            'I' => 15,  // Setor
            'J' => 15,  // Status
            'K' => 18   // Data Criação
        ];
    }
}