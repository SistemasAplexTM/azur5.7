<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\AfterSheet;

class ExportProvider implements FromView, WithEvents, WithDrawings
{
    public $view;
    public $data;

    public $dm;
    public $provider;
    public $type_product;

    public function __construct($view, $data = [], $dm = false, $provider = false, $type_product = false)


    {
        $this->view  = $view;
        $this->data  = $data;
        $this->dm    = $dm;
        $this->provider    = $provider;
        $this->type_product    = $type_product;
    }

    public function view(): View
    {
        return view($this->view, [
            'dm'            => $this->dm,
            'provider'      => $this->provider,
            'type_product'  => $this->type_product,

            'data'          => $this->data ?? [],

            'header'        => $this->dm,
            'provider'      => $this->provider,
            'type_product'  => $this->type_product
        ]);
    }

    public function drawings()
    {
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath(public_path('img/logo.png'));//para el proyecto local.. hay que pasar la ruta con asset('img/logo.png')
        // $drawing->setPath(asset('img/logo.png'));//para el proyecto local.. hay que pasar la ruta con asset('img/logo.png')
        $drawing->setHeight(80);

        $drawing->setCoordinates('A2');
        $drawing->setOffsetX(5);
        $drawing->setOffsetY(10);

        return $drawing;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $cellRange = 'A1:E150'; // All headers
                /* ANCHO DE COLUMNA PERSONALIZADO */
                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(25);
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(13);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('E')->setWidth(15);
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
                    'A11:E11',
                    [
                        'alignment' => array(
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        ),
                        'font' => [
                            'bold' => true,
                        ],
                    ]
                );
                /* TAMAÑO GENERAL DE LA HOJA */
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(10);
                /* TEXTO AJUSTADO A LA CELDA */
                $event->sheet->getDelegate()->getStyle($cellRange)->getAlignment()->setWrapText(true);

                /* UNIR CELDAS */
                $event->sheet->getDelegate()->mergeCells('C2:E2');
                $event->sheet->getDelegate()->mergeCells('C3:E3');
                $event->sheet->getDelegate()->mergeCells('C4:E4');
                $event->sheet->getDelegate()->mergeCells('C5:E5');
                $event->sheet->getDelegate()->mergeCells('C6:E6');
                $event->sheet->getDelegate()->mergeCells('C7:E7');
                $event->sheet->getDelegate()->mergeCells('C8:E8');
                $event->sheet->getDelegate()->mergeCells('A'.(count($this->data ?? []) + 13).':E'.(count($this->data ?? []) + 13));


                /* COLOR DE FONDO DE ESTAS CELDAS */
                $event->sheet->getDelegate()->getStyle('C2:E9')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFFFF');
                $event->sheet->getDelegate()->getStyle('A2:B8')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFFFF');

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
                    'C2:E9',
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
                    'C7:E8',
                    [
                      'alignment' => array(
                          'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                      ),
                      'font' => [
                          'bold' => true,
                      ],
                    ]
                );
                $event->sheet->styleCells(
                    'C6:E6',
                    [
                      'alignment' => array(
                          'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                      ),
                      'font' => [
                          'bold' => true,
                      ],
                    ]
                );
                $event->sheet->styleCells(
                    'A'.(count($this->data ?? []) + 13).':E'.(count($this->data ?? []) + 13),

                    [
                      'font' => [
                          'bold' => true,
                          'color' => ['argb' => \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED]
                      ]
                    ]
                );
                $event->sheet->styleCells(
                    'A11:E'.(count($this->data) + 11),
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
                    'D'.(count($this->data) + 12).':E'.(count($this->data) + 12),
                    [
                      'font' => [
                          'bold' => true,
                      ],
                      'borders' => [
                          'allBorders' => [
                              'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                              'color' => ['argb' => '000000'],
                          ],
                      ]
                    ]
                );
                // FORMATO DE MONEDA DESDE E12 HASTA TODO EL DETALLE
                $event->sheet->getDelegate()->getStyle('D12:E'.(count($this->data) + 11))->getNumberFormat()
                ->setFormatCode('_-$ * #,##0_-;-$ * #,##0_-;_-$ * "-"_-;_-@_-');
                // FORMATO DE MONEDA DEL TOTAL
                $event->sheet->getDelegate()->getStyle('E'.(count($this->data) + 12))->getNumberFormat()
                ->setFormatCode('_-$ * #,##0_-;-$ * #,##0_-;_-$ * "-"_-;_-@_-');
            },
        ];
    }
}
