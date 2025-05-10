<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use \Maatwebsite\Excel\Sheet;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class StatisticsFileExport implements FromView, WithColumnWidths, WithEvents
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
        $active_sheet->getStyle('A1:G1')->applyFromArray([
                    'font' => [
                        'name'      =>  'Calibri',
                        'size'      =>  13,
                        'bold'      =>  true,
                        'color' => ['argb' => '#5a9f3d'],
                    ],

                ]);

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

        foreach(range('C','Z') as $columnID)
        {
            $active_sheet->getColumnDimension($columnID)->setWidth(20);
        }
        $columns = array();
        for ($i = 'A'; $i != 'AV'; $i++) {
            $columns[] = $i;
        }

        foreach ($columns as $columnID) {
            if (strlen($columnID) > 1 && $columnID >= 'AA' && $columnID <= 'AZ') {
                $active_sheet->getColumnDimension($columnID)->setWidth(60);
            }
        }


    }

    public function columnWidths(): array
    {
        return [
            'A' => 100,
            'B' => 20,
            'C'=>20,
            'D'=>20,
            'E'=>20,
            'F'=>20,
            'G'=>20


        ];
    }
    public function view():View
    {
        return view('exports.statistics',[
            'data' => $this->data
        ]);
    }
}
