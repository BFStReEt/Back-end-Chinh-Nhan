<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use \Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class productTechnologyExport implements FromView, WithColumnWidths, WithStyles, WithEvents
{
    use RegistersEventListeners;
    private $data;

    public function __construct($data)
    {
       $this->data = $data;

    }
    public static function afterSheet(AfterSheet $event)
    {
        $active_sheet = $event->sheet->getDelegate();
        $active_sheet->getStyle('A1:T1')->applyFromArray([
                    'font' => [
                        'name'      =>  'Calibri',
                        'size'      =>  13,
                        'bold'      =>  true,
                        'color' => ['argb' => '#5a9f3d'],
                    ],

                ]);
        // $active_sheet->getStyle('A1:T1')->applyFromArray([
        //     'fill' => [
        //         'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        //         'startColor' => [
        //                 'rgb' => 'aaab52',
        //                 ]
        //         ],

        //         ]);
                $lastColumn = $event->sheet->getHighestColumn();
                $lastRow = $event->sheet->getHighestRow();

                $range = 'A1:' . $lastColumn . $lastRow;

                $event->sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '#000000'],
                        ],
                    ],
                ]);

                $rangeColumn = 'A1:'.$lastColumn.'1';
                $event->sheet->getStyle($rangeColumn)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => 'aaab52',
                         ]
                    ],
                    'font' => [
                        'name'      =>  'Calibri',
                        'size'      =>  13,
                        'bold'      =>  true,
                        'color' => ['argb' => '#5a9f3d'],
                    ],
                ]);
                $event->sheet->getStyle($range)->getAlignment()->setHorizontal('center');
                for($i=1;$i<=$lastRow;$i++){
                    $active_sheet->getRowDimension($i)->setRowHeight(30);
                }
                // dd('C,'.$lastColumn);
                // $range1='C,'.$lastColumn;
                foreach(range('C','Z') as $columnID)
                {
                    $active_sheet->getColumnDimension($columnID)->setWidth(60);
                }
                $columns = array();
                for ($i = 'A'; $i != 'AV'; $i++) {
                    $columns[] = $i;
                }

                foreach ($columns as $columnID) {
                    if (strlen($columnID) > 1 && $columnID >= 'AA' && $columnID <= 'AU') {
                        $active_sheet->getColumnDimension($columnID)->setWidth(60);
                    }
                }
                //$active_sheet->getRowDimension('1')->setRowHeight(30);

    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 100,
            // 'C' => 60,
            // 'D' => 20,
            // 'E' => 20,
            // 'F' => 20,
            // 'G' => 60,
            // 'H' => 80,
            // 'I' => 70,
            // 'J' => 40,
            // 'K' => 100,
            // 'L' => 100,
            // 'M' => 20,
            // 'N' => 20,
            // 'O' => 20,
            // 'P' => 100,
            // 'Q' => 100,
            // 'R' => 20,
            // 'S' => 20,
            // 'T' => 20,

        ];
    }
    public function styles(Worksheet $sheet)
    {
        // $sheet->getStyle('A1')->getFont()->setBold(true);
        // $sheet->getStyle('B1')->getFont()->setBold(true);
        // $sheet->getStyle('C1')->getFont()->setBold(true);
        // $sheet->getStyle('D1')->getFont()->setBold(true);
        // $sheet->getStyle('E1')->getFont()->setBold(true);
        // $sheet->getStyle('F1')->getFont()->setBold(true);
        // $sheet->getStyle('G1')->getFont()->setBold(true);
        // $sheet->getStyle('H1')->getFont()->setBold(true);
        // $sheet->getStyle('I1')->getFont()->setBold(true);
        // $sheet->getStyle('J1')->getFont()->setBold(true);
        // $sheet->getStyle('K1')->getFont()->setBold(true);
        // $sheet->getStyle('L1')->getFont()->setBold(true);
        // $sheet->getStyle('M1')->getFont()->setBold(true);
        // $sheet->getStyle('N1')->getFont()->setBold(true);
        // $sheet->getStyle('O1')->getFont()->setBold(true);
        // $sheet->getStyle('P1')->getFont()->setBold(true);
        // $sheet->getStyle('Q1')->getFont()->setBold(true);
        // $sheet->getStyle('R1')->getFont()->setBold(true);
        // $sheet->getStyle('S1')->getFont()->setBold(true);
        // $sheet->getStyle('T1')->getFont()->setBold(true);
    }
    public function view():View
    {
        return view('exports.productTech',[
            'data' => $this->data
        ]);
    }
}
