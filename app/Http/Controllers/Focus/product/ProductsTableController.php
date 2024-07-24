<?php
/*
 * Rose Business Suite - Accounting, CRM and POS Software
 * Copyright (c) UltimateKode.com. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */
namespace App\Http\Controllers\Focus\product;

use App\Models\product\Product;
use App\Http\Controllers\Controller;
use App\Models\product\ProductVariation;
use App\Repositories\Focus\product\ProductVariationRepository;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\product\ProductRepository;
use App\Http\Requests\Focus\product\ManageProductRequest;
use Illuminate\Support\Facades\Storage;

/**
 * Class ProductsTableController.
 */
class ProductsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductRepository
     */
    protected $product;
    protected $product_variation;

    /**
     * contructor to initialize repository object
     * @param ProductRepository $product ;
     */
    public function __construct(ProductRepository $product, ProductVariationRepository $product_variation)
    {
        $this->product = $product;
        $this->product_variation = $product_variation;
    }

    /**
     * This method return the data of the model
     * @param ManageProductRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageProductRequest $request)
    {

        if (request('p_rel_id') and (request('p_rel_type') == 2)) {
            $core = $this->product_variation->getForDataTable();

            return Datatables::of($core)
                ->addIndexColumn()
                ->addColumn('name', function ($item) {
                    return '<a class="font-weight-bold" href="' . route('biller.products.show', [$item->product->id]) . '">' . $item->product->name . '</a> <small> ' . $item->name . '</small>';
                })
                ->addColumn('warehouse', function ($item) {

                    return $item->warehouse['title'] . '<img class="media-object img-lg border"
                                                                      src="' . Storage::disk('public')->url('/img/products/' . @$item['image']) . '"
                                                                      alt="Product Image">';
                })
                ->addColumn('category', function ($item) {
                    return $item->product->category->title;
                })
                ->addColumn('qty', function ($item) {
                    return numberFormat($item['qty']) . ' ' . $item->unit;
                })
                ->addColumn('created_at', function ($item) {
                    return dateFormat($item->created_at);
                })
                ->addColumn('price', function ($item) {
                    return amountFormat($item['price']);
                })
                ->addColumn('actions', function ($item) {
                    return $item->product->action_buttons;
                })->rawColumns(['name', 'warehouse', 'category', 'qty', 'created_at', 'price', 'actions'])
                ->make(true);


        } else {


            $core = $this->product->getForDataTable();


            return Datatables::of($core)
                ->addIndexColumn()
                ->addColumn('name', function ($item) {
                    return '<a class="font-weight-bold" href="' . route('biller.products.show', [$item->id]) . '">' . $item->name . '</a>';
                })
                ->addColumn('warehouse', function ($item) {

                    return $item->standard['warehouse']['title'] . '<img class="media-object img-lg border"
                                                                      src="' . Storage::disk('public')->url('/img/products/' . @$item->standard['image']) . '"
                                                                      alt="Product Image">';
                })
                 ->addColumn('code', function ($item) {

                    return $item->standard['code'];
                })
                ->addColumn('category', function ($item) {
                    return $item->category->title;
                })
                ->addColumn('qty', function ($item) {
                    return numberFormat($item->standard['qty']) . ' ' . $item->unit;
                })
                ->addColumn('created_at', function ($item) {
                    return dateFormat($item->created_at);
                })
                ->addColumn('price', function ($item) {
                    return amountFormat($item->standard['price']);
                })
                ->addColumn('actions', function ($item) {
                    return $item->action_buttons;
                })->rawColumns(['name', 'warehouse', 'category', 'qty', 'created_at', 'price', 'actions'])
                ->make(true);

        }
    }

    public function variationTable(ManageProductRequest $request)
    {

        
            $core = $this->product_variation->getForDataTable2();

            return Datatables::of($core)
                ->addIndexColumn()
                ->addColumn('name', function ($item) {
                    return '<a class="font-weight-bold" href="' . route('biller.products.show', [$item->product->id]) . '">' . $item->product->name . '</a> <small> ' . $item->name . '</small>';
                })
                ->addColumn('from_w', function ($item) {

                    return $item->from_w ? $item->from_w->title : 'N/A';
                })
                ->addColumn('warehouse', function ($item) {

                    return $item->warehouse['title'];
                })
                ->addColumn('category', function ($item) {
                    return $item->product->category->title;
                })
                ->addColumn('qty', function ($item) {
                    return numberFormat($item['qty']) . ' ' . $item->unit;
                })
                ->addColumn('created_at', function ($item) {
                    return dateFormat($item->created_at);
                })
                ->addColumn('price', function ($item) {
                    return amountFormat($item['price']);
                })
                ->addColumn('type_action', function ($item) {
                    switch ($item['type_action']) {
                        case 1:
                            return '<div style="color:#ffffff; background:orange; font-size: 14px; padding:4px 5px; border-radius: 4px; text-align:center"><span>Préstamo Temporal</span></div>';
                        case 0:
                            return '<div style="color:#ffffff; background:red; font-size: 14px; padding:4px 5px; border-radius: 4px; text-align:center"><span>Préstamo Permanente</span></div>';
                        case 2:
                            return '<div style="color:#ffffff; background:green; font-size: 14px; padding:4px 5px; border-radius: 4px; text-align:center"><span>Devolución</span></div>';
                        default:
                            return '<span class="badge badge-secondary">Desconocido</span>';
                    }
                })->rawColumns(['name', 'warehouse', 'category', 'qty', 'created_at', 'price', 'actions', 'type_action','from_w'])
                ->make(true);


    }
}
