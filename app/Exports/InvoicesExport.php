<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class InvoicesExport implements FromView, ShouldAutoSize
{
    public $view;
    public $data;
    public $remanencias;
    public $uds;
    public $remanencia;

    public function __construct($view, $data = "")
    {
        $this->view         = $view;
        $this->data         = $data['datos'];
        $this->remanencias  = $data['remanencias'];
        $this->uds          = $data['uds'];
        $this->remanencia          = $data['remanencia'];
    }

    public function view(): View
    {
        return view($this->view, [
            'data'          => $this->data,
            'remanencias'   => $this->remanencias,
            'uds'           => $this->uds,
            'reman'           => $this->remanencia,
        ]);
    }
}
