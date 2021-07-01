<?php namespace Lovata\Shopaholic\Models;

use Lovata\Toolbox\Classes\Helper\AbstractExportModelInCSV;

/**
 * Class BrandExport
 *
 * @package Lovata\Shopaholic\Models
 * @author  Sergey Zakharevich, s.zakharevich@lovata.com, LOVATA Group
 *
 * @mixin \October\Rain\Database\Builder
 * @mixin \Eloquent
 */
class BrandExport extends AbstractExportModelInCSV
{
    /** @var string */
    public $table = 'lovata_shopaholic_brands';

    /**
     * Get list.
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Brand[]
     */
    protected function getItemList()
    {
        return Brand::get();
    }
}
