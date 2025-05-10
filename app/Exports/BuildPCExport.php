<?php

namespace App\Exports;


use Illuminate\Contracts\View\View;
// use Maatwebsite\Excel\Concerns\ToModel;
// use Maatwebsite\Excel\Concerns\WithMapping;
// use Maatwebsite\Excel\Concerns\FromCollection;
// use Maatwebsite\Excel\Concerns\WithHeadings;
// use Maatwebsite\Excel\Concerns\WithHeadingRow;
// use Maatwebsite\Excel\Concerns\WithDrawings;
// use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
// use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use \Maatwebsite\Excel\Sheet;



class BuildPCExport implements FromView, WithEvents
{
    use RegistersEventListeners;

    private $data;

    public function __construct($data)
    {
       $this->data = $data;

    }

    public static function afterSheet(AfterSheet $event)
    {

        // Create Style Arrays
        $default_font_style = [
            'font' => ['name' => 'Arial', 'size' => 10],
            'background' => [
                'color'=> '#808080'
            ]
        ];

        $strikethrough = [
            'font' => ['bold' => true],
        ];
        $borders=[  'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],

        ],];
        $backgroup_color=['argb' => [
            'color'=> '#808080'
        ],];
        $styleArray =  ['fill' => [
            'color' => array('rgb' => '000000')
        ]];



        // Get Worksheet
        $active_sheet = $event->sheet->getDelegate();


        //-------------------------------------------------
        $lastColumn = $event->sheet->getHighestColumn();
        $lastRow = $event->sheet->getHighestRow();
        $range = 'A11:' . $lastColumn . $lastRow;
        $event->sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '#5a9f3d'],
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $active_sheet->getStyle($lastRow-1)->getAlignment()->setHorizontal('center');
        $active_sheet->getStyle($lastRow)->getAlignment()->setHorizontal('center');
        $active_sheet->getStyle($lastRow-1)->applyFromArray($strikethrough);
        $active_sheet->getStyle($lastRow)->applyFromArray($strikethrough);


        $range1= $lastColumn . $lastRow-2;
        $active_sheet->getStyle($range1)->applyFromArray([
            'font' => [
                'name'      =>  'Calibri',
                'size'      =>  12,
                'bold'      =>  true,
                'color' => ['argb' => 'EB2B02'],
            ],

        ]);


        $active_sheet->getRowDimension('1')->setRowHeight(30);
        $active_sheet->getRowDimension('2')->setRowHeight(30);
        $active_sheet->getRowDimension('3')->setRowHeight(30);
        $active_sheet->getRowDimension('4')->setRowHeight(30);
        $active_sheet->getRowDimension('5')->setRowHeight(30);
        $active_sheet->getRowDimension('6')->setRowHeight(30);

        $active_sheet->getStyle('A1:E1')->applyFromArray([
            'font' => [
                'name'      =>  'Calibri',
                'size'      =>  20,
                'bold'      =>  true,
                'color' => ['argb' => '#5a9f3d'],
            ],

        ]);
        $active_sheet->getStyle('A1:E1')->getAlignment()->setHorizontal('center');
         $event->sheet->getDelegate()->getRowDimension('10')->setRowHeight(30);

         $active_sheet->getStyle('A2:E2')->applyFromArray([
            'font' => [
                'name'      =>  'Calibri',
                'size'      =>  13,
                'bold'      =>  true,
                'color' => ['argb' => '#5a9f3d'],
            ],

        ]);
        $event->sheet->getDelegate()->getRowDimension('2')->setRowHeight(20);

        $active_sheet->getStyle('A3:E3')->applyFromArray([
            'font' => [
                'name'      =>  'Calibri',
                'size'      =>  13,
                'bold'      =>  true,
                'color' => ['argb' => '#5a9f3d'],
            ],

        ]);
        $event->sheet->getDelegate()->getRowDimension('3')->setRowHeight(20);
        $active_sheet->getStyle('A5:E5')->applyFromArray($styleArray);
        $active_sheet->getStyle('A5:E5')->applyFromArray($borders);
        $active_sheet->getStyle('A5:E5')->applyFromArray($strikethrough);
        $active_sheet->getStyle('A6:E6')->applyFromArray($styleArray);
        $active_sheet->getStyle('A6:E6')->applyFromArray($borders);
        $active_sheet->getStyle('A6:E6')->applyFromArray($strikethrough);

        $active_sheet->getStyle('E6')->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '5a9f3d',
                 ]
            ],

        ]);
        $active_sheet->getStyle('E6')->applyFromArray([
            'font' => [
                'name'      =>  'Calibri',
                'size'      =>  11,
                'bold'      =>  true,
                'color' => ['argb' => 'fff0ed'],
            ],

        ]);
        $active_sheet->getStyle('E6')->getAlignment()->setHorizontal('center');
        $active_sheet->getRowDimension('6')->setRowHeight(20);


        $active_sheet->getStyle('A7:E7')->applyFromArray($styleArray);
        $active_sheet->getStyle('A7:E7')->applyFromArray($borders);
        $active_sheet->getStyle('A7:E7')->applyFromArray($strikethrough);

        $active_sheet->getStyle('A8:E8')->applyFromArray($styleArray);
        $active_sheet->getStyle('A8:E8')->applyFromArray($borders);
        $active_sheet->getStyle('A8:E8')->applyFromArray($strikethrough); //A10:B12

        $active_sheet->getStyle('A10:F10')->applyFromArray($styleArray);

        $active_sheet->getStyle('A10:F10')->applyFromArray($strikethrough);
        $active_sheet->getStyle('A10:F10')->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '5a9f3d',
                 ]
            ],

        ]);
        $active_sheet->getStyle('A10:E10')->applyFromArray([
            'font' => [
                'name'      =>  'Calibri',
                'size'      =>  11,
                'bold'      =>  true,
                'color' => ['argb' => 'fff0ed'],
            ],

        ]);
        $active_sheet->getStyle('A10:F10')->getAlignment()->setHorizontal('center');
         $event->sheet->getDelegate()->getRowDimension('1')->setRowHeight(20);


        $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(70);
        $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(15);
        $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(20);
        $event->sheet->getDelegate()->getColumnDimension('E')->setWidth(30);
        $event->sheet->getDelegate()->getColumnDimension('F')->setWidth(30);


    }





    public function view():View
    {
        return view('exports.buildPCExcel',[
            'data' => $this->data
        ]);
    }
}
