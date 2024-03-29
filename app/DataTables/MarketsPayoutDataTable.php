<?php

namespace App\DataTables;

use App\Models\CustomField;
use App\Models\MarketsPayout;
use Barryvdh\DomPDF\Facade as PDF;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;

class MarketsPayoutDataTable extends DataTable
{
    /**
     * custom fields columns
     * @var array
     */
    public static $customFields = [];

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $dataTable = new EloquentDataTable($query);
        $columns = array_column($this->getColumns(), 'data');
        $dataTable = $dataTable
            ->editColumn('updated_at', function ($markets_payout) {
                return getDateColumn($markets_payout, 'updated_at');
            })
            ->editColumn('amount', function ($markets_payout) {
                return getPriceColumn($markets_payout, 'amount');
            })
            //->addColumn('action', 'markets_payouts.datatables_actions')
            ->rawColumns(array_merge($columns));

        return $dataTable;
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        $columns = [
            [
                'data' => 'market.name',
                'title' => trans('lang.markets_payout_market_id'),

            ],
            [
                'data' => 'method',
                'title' => trans('lang.markets_payout_method'),

            ],
            [
                'data' => 'amount',
                'title' => trans('lang.markets_payout_amount'),

            ],
            [
                'data' => 'paid_date',
                'title' => trans('lang.markets_payout_paid_date'),

            ],
            [
                'data' => 'note',
                'title' => trans('lang.markets_payout_note'),

            ],
            [
                'data' => 'updated_at',
                'title' => trans('lang.markets_payout_updated_at'),
                'searchable' => false,
            ]
        ];

        $hasCustomField = in_array(MarketsPayout::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', MarketsPayout::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.markets_payout_' . $field->name),
                    'orderable' => false,
                    'searchable' => false,
                ]]);
            }
        }
        return $columns;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Post $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(MarketsPayout $model)
    {
        if(auth()->user()->hasRole('admin')){
            return $model->newQuery()->with("market")->select('markets_payouts.*');
        }elseif (auth()->user()->hasRole('manager')){
            return $model->newQuery()->with("market")->join('user_markets','user_markets.market_id','=','markets_payouts.market_id')
                ->where('user_markets.user_id',auth()->id())->select('markets_payouts.*');
        }
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->columns($this->getColumns())
            ->minifiedAjax()
            //->addAction(['width' => '80px', 'printable' => false, 'responsivePriority' => '100'])
            ->parameters(array_merge(
                config('datatables-buttons.parameters'), [
                    'language' => json_decode(
                        file_get_contents(base_path('resources/lang/' . app()->getLocale() . '/datatable.json')
                        ), true)
                ]
            ));
    }

    /**
     * Export PDF using DOMPDF
     * @return mixed
     */
    public function pdf()
    {
        $data = $this->getDataForPrint();
        $pdf = PDF::loadView($this->printPreview, compact('data'));
        return $pdf->download($this->filename() . '.pdf');
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'markets_payoutsdatatable_' . time();
    }
}