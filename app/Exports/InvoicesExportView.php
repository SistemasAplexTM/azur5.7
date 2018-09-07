<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\AfterSheet;

class InvoicesExportView implements FromView, WithEvents, WithDrawings
{
    public $view;
    public $data;
    public $remanencias;
    public $name_uds;
    public $name_minuta;

    public function __construct($view, $data = "", $remanencias = "", $name_uds = "", $name_minuta = "")
    {
        $this->view         = $view;
        $this->data         = $data;
        $this->remanencias  = $remanencias;
        $this->name_uds     = $name_uds;
        $this->name_minuta  = $name_minuta;
    }

    public function view(): View
    {
        return view($this->view, [
            'data'          => $this->data,
            'remanencias'   => $this->remanencias,
            'name_uds'      => $this->name_uds,
            'name_minuta'   => $this->name_minuta
        ]);
    }

    public function drawings()
    {
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath(asset('img/logo.png'));
        $drawing->setHeight(90);
        $drawing->setCoordinates('A2');
        $drawing->setOffsetX(5);
        $drawing->setOffsetY(10);

        return $drawing;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $cellRange = 'A1:D150'; // All headers
                /* ANCHO DE COLUMNA PERSONALIZADO */
                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(25);
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(13);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(36);
                /* ALTO DE COLUMNA PERSONALIZADO */
                $event->sheet->getDelegate()->getRowDimension('6')->setRowHeight(26);
                $event->sheet->getDelegate()->getRowDimension('7')->setRowHeight(26);
                /* CENTRAR DATOS DE LA COLUMNA B */
                $event->sheet->styleCells(
                    'B12:B100',
                    [
                        'alignment' => array(
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        )
                    ]
                );
                /* CENTRAR CABECERA DE LA TABLA */
                $event->sheet->styleCells(
                    'A11:D11',
                    [
                        'alignment' => array(
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        )
                    ]
                );
                /* TAMAÑO GENERAL DE LA HOJA */
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(10);
                /* TEXTO AJUSTADO A LA CELDA */
                $event->sheet->getDelegate()->getStyle($cellRange)->getAlignment()->setWrapText(true);

                /* UNIR CELDAS */
                $event->sheet->getDelegate()->mergeCells('C2:D2');
                $event->sheet->getDelegate()->mergeCells('C3:D3');
                $event->sheet->getDelegate()->mergeCells('C4:D4');
                $event->sheet->getDelegate()->mergeCells('C5:D5');
                $event->sheet->getDelegate()->mergeCells('C6:D6');
                $event->sheet->getDelegate()->mergeCells('C7:D7');
                $event->sheet->getDelegate()->mergeCells('C8:D9');

                $event->sheet->getDelegate()->mergeCells('A'.(count($this->data) + 19).':D'.(count($this->data) + 19));
                $event->sheet->getDelegate()->mergeCells('A'.(count($this->data) + 21).':D'.(count($this->data) + 21));

                /* COLOR DE FONDO DE ESTAS CELDAS */
                $event->sheet->getDelegate()->getStyle('A'.(count($this->data) + 14).':D'.(count($this->data) + 22))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFFFF');
                $event->sheet->getDelegate()->getStyle('C2:D9')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFFFF');
                $event->sheet->getDelegate()->getStyle('A2:B8')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFFFF');
                $event->sheet->getDelegate()->getStyle('A'.(count($this->data) + 12).':D'.(count($this->data) + 13))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFFFF');

                /* ESTO PERTENECE AL TEXTO DEBAJO DE LA IMAGEN */
                $event->sheet->getDelegate()->mergeCells('A7:B7');
                $event->sheet->styleCells(
                    'A7:B7',
                    [
                        'alignment' => array(
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        )
                    ]
                );
                $event->sheet->getDelegate()->mergeCells('A8:B8');
                $event->sheet->styleCells(
                    'A8:B8',
                    [
                        'alignment' => array(
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        )
                    ]
                );

                /* ENCABEZADO DE LA TABLA */

                $event->sheet->styleCells(
                    'C2:D9',
                    [
                        'borders' => [
                            'outline' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                                'color' => ['argb' => '000000'],
                            ],
                        ]
                    ]
                );
                $event->sheet->styleCells(
                    'A11:D'.(count($this->data) + 11),
                    [
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ]
                    ]
                );

                /* BORDES */

                $event->sheet->styleCells(
                    'A11:D'.(count($this->data) + 11),
                    [
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ]
                    ]
                );

                $event->sheet->styleCells(
                    'A'.(count($this->data) + 12).':D'.(count($this->data) + 13),
                    [
                        'borders' => [
                            'outline' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ]
                    ]
                );

                /* BORDES DEL FOOT DE LA TABLA DONDE ESTAN LAS FIRMAS */
 
                $event->sheet->styleCells(
                    'A'.(count($this->data) + 14).':D'.(count($this->data) + 18),
                    [
                        'borders' => [
                            'outline' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ]
                    ]
                );//

                $event->sheet->styleCells(
                    'A'.(count($this->data) + 19).':D'.(count($this->data) + 22),
                    [
                        'borders' => [
                            'outline' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ]
                    ]
                );
            },
        ];
    }
}
